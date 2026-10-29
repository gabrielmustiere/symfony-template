<?php

declare(strict_types=1);

use NunoMaduro\PhpInsights\Domain\Sniffs\ForbiddenSetterSniff;
use PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer;
use PHP_CodeSniffer\Standards\Generic\Sniffs\Files\LineLengthSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\Formatting\SpaceAfterNotSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\DisallowYodaComparisonSniff;

return [
    'preset' => 'symfony',
    'paths' => [
        __DIR__ . '/src',
    ],
    // Le style est porté par Mago (mago.toml) : on retire les règles qui le contredisent.
    'remove' => [
        DisallowYodaComparisonSniff::class,
        LineLengthSniff::class,
        OrderedClassElementsFixer::class,
        SpaceAfterNotSniff::class,
    ],
    'config' => [
        // Les entités Doctrine générées par make:entity exposent des setters.
        ForbiddenSetterSniff::class => [
            'exclude' => ['src/Entity'],
        ],
    ],
];
