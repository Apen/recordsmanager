<?php

if (!defined('TYPO3')) {
    die('Access denied.');
}

// typo3 v11 modules initialization
$extensionConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class);
$conf = $extensionConfiguration->get('recordsmanager');

if (((int)$conf['enabledAdd'] === 1) || ((int)$conf['enabledEdit'] === 1) || ((int)$conf['enabledExport'] === 1)) {

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'Recordsmanager',
        'txrecordsmanagerM1',
        '',
        '',
        [],
        [
            'access' => 'user,group',
            'icon' => 'EXT:recordsmanager/Resources/Public/Icons/table.png',
            'labels' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang.xlf:recordsmanagertitle',
        ]
    );

    if ((int)$conf['enabledAdd'] === 1) {
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
            'Recordsmanager',
            'txrecordsmanagerM1',
            'insert',
            '',
            [
                \Sng\Recordsmanager\Controller\InsertController::class => 'index',
            ],
            [
                'access' => 'user,group',
                'icon' => 'EXT:recordsmanager/Resources/Public/Icons/plus.png',
                'labels' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang.xlf:inserttitle',
            ]
        );
    }

    if ((int)$conf['enabledEdit'] === 1) {
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
            'Recordsmanager',
            'txrecordsmanagerM1',
            'edit',
            '',
            [
                \Sng\Recordsmanager\Controller\EditController::class => 'index',
            ],
            [
                'access' => 'user,group',
                'icon' => 'EXT:recordsmanager/Resources/Public/Icons/table-edit.png',
                'labels' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang.xlf:edittitle',
            ]
        );
    }

    if ((int)$conf['enabledExport'] === 1) {
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
            'Recordsmanager',
            'txrecordsmanagerM1',
            'export',
            '',
            [
                \Sng\Recordsmanager\Controller\ExportController::class => 'index',
            ],
            [
                'access' => 'user,group',
                'icon' => 'EXT:recordsmanager/Resources/Public/Icons/export.png',
                'labels' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang.xlf:exporttitle',
            ]
        );
    }
}


