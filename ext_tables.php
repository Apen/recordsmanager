<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

if (TYPO3_MODE == 'BE') {

    $extensionConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class);
    $conf = $extensionConfiguration->get('recordsmanager');

    if (($conf['enabledAdd'] == 1) || ($conf['enabledEdit'] == 1) || ($conf['enabledExport'] == 1)) {

        // add module after 'Web'
        if (!isset($GLOBALS['TBE_MODULES']['txrecordsmanagerM1'])) {
            $temp_TBE_MODULES = [];
            foreach ($GLOBALS['TBE_MODULES'] as $key => $val) {
                if ($key === 'web') {
                    $temp_TBE_MODULES[$key] = $val;
                    $temp_TBE_MODULES['txrecordsmanagerM1'] = $val;
                } else {
                    $temp_TBE_MODULES[$key] = $val;
                }
            }
            $GLOBALS['TBE_MODULES'] = $temp_TBE_MODULES;
            unset($temp_TBE_MODULES);
        }

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
            'Sng.recordsmanager',
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

        if ($conf['enabledAdd'] == 1) {
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
                'Sng.recordsmanager',
                'txrecordsmanagerM1', // Make module a submodule of 'web'
                'insert', // Submodule key
                '', // Position
                [
                    'Insert' => 'index',
                ],
                [
                    'access' => 'user,group',
                    'icon' => 'EXT:recordsmanager/Resources/Public/Icons/plus.png',
                    'labels' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang.xlf:inserttitle',
                ]
            );
        }

        if ($conf['enabledEdit'] == 1) {
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
                'Sng.recordsmanager',
                'txrecordsmanagerM1', // Make module a submodule of 'web'
                'edit', // Submodule key
                '', // Position
                [
                    'Edit' => 'index',
                ],
                [
                    'access' => 'user,group',
                    'icon' => 'EXT:recordsmanager/Resources/Public/Icons/table-edit.png',
                    'labels' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang.xlf:edittitle',
                ]
            );
        }

        if ($conf['enabledExport'] == 1) {
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
                'Sng.recordsmanager',
                'txrecordsmanagerM1', // Make module a submodule of 'web'
                'export', // Submodule key
                '', // Position
                [
                    'Export' => 'index',
                ],
                [
                    'access' => 'user,group',
                    'icon' => 'EXT:recordsmanager/Resources/Public/Icons/export.png',
                    'labels' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang.xlf:exporttitle',
                ]
            );
        }
    }

    //\TYPO3\CMS\Extbase\Utility\ExtensionUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'recordsmanager');
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:recordsmanager/Configuration/TypoScript/setup.txt">');
}
