<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PostRector\Rector\NameImportingPostRector;
use Ssch\TYPO3Rector\Set\Typo3LevelSetList;
use Ssch\TYPO3Rector\TYPO310\v4\UseFileGetContentsForGetUrlRector;
use Ssch\TYPO3Rector\TYPO311\v0\ReplaceInjectAnnotationWithMethodRector;

$rectorConfig = RectorConfig::configure();
$rectorConfig->withSets([
    Typo3LevelSetList::UP_TO_TYPO3_13,
]);
$rectorConfig->withSkip([
    '*Build/*',
    '*/Resources/Private/Php/*',
    '*/Resources/Public/*',
    '*/Configuration/TypoScript/*',
    '*/Configuration/RequestMiddlewares.php',
    ReplaceInjectAnnotationWithMethodRector::class,
    UseFileGetContentsForGetUrlRector::class,
    NameImportingPostRector::class => [
        '*/ClassAliasMap.php',
        '*/ext_localconf.php',
        '*/ext_emconf.php',
        '*/ext_tables.php',
//        '*/Configuration/TCA/*',
        '*/Configuration/RequestMiddlewares.php',
        '*/Configuration/Commands.php',
        '*/Configuration/AjaxRoutes.php',
        '*/Configuration/Extbase/Persistence/Classes.php',
    ],
]);
return $rectorConfig;