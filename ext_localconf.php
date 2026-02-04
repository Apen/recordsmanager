<?php

if (!defined('TYPO3')) {
    die('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][\Sng\Recordsmanager\Hooks\TceForms::class] = [
    'before' => [
        \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsRemoveUnused::class
    ]
];
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['postProcessValue'][] = \Sng\Recordsmanager\Hooks\BeFunc::class . '->BE_postProcessValue';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup('@import "EXT:recordsmanager/Configuration/TypoScript/setup.typoscript"');
