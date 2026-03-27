<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(__DIR__)
    ->exclude(['var', 'vendor', 'config'])
    ->files()
    ->name('*.php');

return (new Config())
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'trailing_comma_in_multiline' => true,
        'no_unused_imports' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'declare_strict_types' => true,
        'phpdoc_align' => true,
        'phpdoc_separation' => true,
        'single_quote' => true,
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true);
