<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude([
        'vendor'
    ]);

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'blank_line_before_statement' => [
            'statements' => [
                'return', 'try', 'throw', 'if',
                'for', 'while', 'switch'
            ]
        ],
        'braces' => false,
        'function_declaration' => [
            'closure_function_spacing' => 'one'
        ],
        'new_with_braces' => false,
        'no_superfluous_phpdoc_tags' => false,
        'not_operator_with_successor_space' => true,
        'ordered_imports' => [
            'sort_algorithm' => 'alpha'
        ],
        'phpdoc_align' => false,
        'single_line_throw' => false,
        'trailing_comma_in_multiline' => false,
        'phpdoc_to_comment' => false
    ])
    ->setFinder($finder)
    ->setUsingCache(false);
