<?php

// Finder configuration:
// - Scan the whole project directory (__DIR__)
// - Exclude folders that should not be scanned (var, vendor, node_modules, etc.)
$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude([
        'var',
        'vendor',
        'node_modules',
        'public/bundles',
    ]);

return (new PhpCsFixer\Config())
    // Allow rules that may change behavior (Symfony risky rules)
    ->setRiskyAllowed(true)

    // Set of rules used by the fixer
    ->setRules([
        // Full Symfony coding style rules (very strict)
        '@Symfony' => true,

        // Additional risky Symfony rules
        '@Symfony:risky' => true,

        // Modern PHP standard (replaces PSR-2)
        '@PSR12' => true,

        // ---- Additional improvement rules ----

        // Sort imported classes alphabetically
        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
        ],

        // Remove unused "use" statements automatically
        'no_unused_imports' => true,

        // Order the sections in PHPDoc blocks
        'phpdoc_order' => true,

        // Clean empty spaces inside PHPDoc blocks
        'phpdoc_trim' => true,

        // Keep empty return types for some Symfony components
        'phpdoc_no_empty_return' => false,

        // Align operators like =, =>, etc.
        'binary_operator_spaces' => [
            'default' => 'align_single_space_minimal',
        ],

        // Add a space when using string concatenation (e.g. $a . $b)
        'concat_space' => [
            'spacing' => 'one',
        ],

        // Automatically import constants and functions from global namespace
        'global_namespace_import' => [
            'import_constants' => true,
            'import_functions' => true,
        ],

        // Prefer single quotes over double quotes when possible
        'single_quote' => true,

        // Use short array syntax: []
        'array_syntax' => ['syntax' => 'short'],
    ])

    // Apply the fixer only to the files found by the Finder
    ->setFinder($finder);
