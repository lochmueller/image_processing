<?php

declare(strict_types=1);

$baseDir = dirname(__DIR__, 3);

require $baseDir.'/.Build/vendor/autoload.php';

$finder = PhpCsFixer\Finder::create()
    ->in($baseDir.'/Classes')
    ->in($baseDir.'/Resources/Private/Build');

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@DoctrineAnnotation' => true,
        '@PHP74Migration' => true,
        '@PHP74Migration:risky' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PSR12' => true,
        '@PSR12:risky' => true,
    ])
    ->setFinder($finder);
