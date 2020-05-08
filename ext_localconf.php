<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig(
    '
    options.saveDocNew.tx_recordsmanager_config=1
'
);

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][\Sng\Recordsmanager\Hooks\TceForms::class] = [
    'before' => [
        \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsRemoveUnused::class
    ]
];
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['postProcessValue'][] = \Sng\Recordsmanager\Hooks\BeFunc::class . '->BE_postProcessValue';
