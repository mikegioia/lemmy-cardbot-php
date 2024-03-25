<?php

namespace Lemmy;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Rikudou\LemmyApi\Enum\CommentSortType;
use Rikudou\LemmyApi\Enum\Language;
use Rikudou\LemmyApi\Enum\SortType;
use Rikudou\LemmyApi\Exception\LemmyApiException;
use Rikudou\LemmyApi\LemmyApi;
use Rikudou\LemmyApi\Response\View\CommentView;
use Rikudou\LemmyApi\Response\View\PostView;

final class CardBot
{
    private bool $running = false;

    private Logger $debugLog;
    private Logger $errorLog;

    public const int COMMENT_LIMIT = 100;
    public const int POST_LIMIT = 10;

    public function __construct(
        private LemmyApi $api,
        private string $logPath,
        private int $sleepFor,
        private string $username,
        #[\SensitiveParameter] private string $password,
    ) {
        // Set up the debug log
        $this->debugLog = new Logger('cardbot-debug');
        $this->debugLog->pushHandler(new StreamHandler($this->logPath.'/debug.log', Level::Debug));

        // Set up the error log
        $this->errorLog = new Logger('cardbot-error');
        $this->errorLog->pushHandler(new StreamHandler($this->logPath.'/error.log', Level::Debug));
    }

    public function start(): void
    {
        $this->running = true;

        $this->loop();
    }

    public function stop(): void
    {
        $this->running = false;
    }

    public function isRunning(): bool
    {
        return $this->running;
    }

    /**
     * Continually looks for comments and posts, and updates
     * the internal database after processing each one.
     */
    private function loop(): void
    {
        // $this->login();

        while ($this->isRunning()) {
            try {
                // $this->processPosts();
                // $this->processComments();
            } catch (LemmyApiException $e) {
                $this->logException($e);
            }

            sleep($this->sleepFor);

            // Must be called after sleeping, before loop ends
            pcntl_signal_dispatch();
        }
    }

    private function login(): void
    {
        $this->api->login($this->username, $this->password);
    }

    private function processPosts(): void
    {
        $unreadPosts = $this->getUnreadPosts();

        foreach ($unreadPosts as $post) {
            // Lock the post in the database to prevent updating it again

            // Check if the message requests any cards
            // Respond to the post with the card previews
            // $this->processPost();
        }
    }

    private function processComments(): void
    {
        $unreadComments = $this->getUnreadComments();

        foreach ($unreadComments as $comment) {
            // Lock the comment in the database to prevent updating it again

            // Check if the message requests any cards
            // Respond to the comment with the card previews
            // $this->processComment();
        }
    }

    /**
     * Continues to search posts one page at a time
     * until all posts since the last stored ID have
     * been returned. Posts are updated in the internal
     * database when processed.
     *
     * @return array<PostView>
     */
    private function getUnreadPosts(): array
    {
        return $this->api->post()->getPosts(
            limit: self::POST_LIMIT,
            sort: SortType::New
        );
    }

    /**
     * Continues to search comments one page at a time
     * until all comments since the last stored ID have
     * been returned. Comments are updated in the internal
     * database when processed.
     *
     * @return array<CommentView>
     */
    private function getUnreadComments(): array
    {
        return $this->api->comment()->getComments(
            limit: self::COMMENT_LIMIT,
            sortType: CommentSortType::New
        );
    }

    // private function processComment(): void
    // {
    //     $this->api->comment()->create(
    //         post: $mention->post,
    //         content: "Sure, I'll let you know!",
    //         language: Language::English,
    //         parent: $mention->comment,
    //     );
    // }

    private function logException(LemmyApiException $e): void
    {
        // todo implement this yourself
    }
}
