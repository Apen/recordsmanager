<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Resources/Private/Php/class.tx_recordsmanager_flexfill.php');

if (TYPO3_MODE == 'BE') {

    $conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['recordsmanager']);

    if (($conf['enabledAdd'] == 1) || ($conf['enabledEdit'] == 1) || ($conf['enabledExport'] == 1)) {

        // add module after 'Web'
        if (!isset($TBE_MODULES['txrecordsmanagerM1'])) {
            $temp_TBE_MODULES = array();
            foreach ($TBE_MODULES as $key => $val) {
                if ($key === 'web') {
                    $temp_TBE_MODULES[$key] = $val;
                    $temp_TBE_MODULES['txrecordsmanagerM1'] = $val;
                } else {
                    $temp_TBE_MODULES[$key] = $val;
                }
            }
            $TBE_MODULES = $temp_TBE_MODULES;
            unset($temp_TBE_MODULES);
        }

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
            'Sng.' . $_EXTKEY,
            'txrecordsmanagerM1',
            '',
            '',
            array(),
            array(
                'access' => 'user,group',
                'icon'   => 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/table.png',
                'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml:recordsmanagertitle',
            )
        );

        if ($conf['enabledAdd'] == 1) {
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
                'Sng.' . $_EXTKEY,
                'txrecordsmanagerM1', // Make module a submodule of 'web'
                'insert', // Submodule key
                '', // Position
                array(
                    'Insert' => 'index',
                ),
                array(
                    'access' => 'user,group',
                    'icon'   => 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/plus.png',
                    'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml:inserttitle',
                )
            );
        }

        if ($conf['enabledEdit'] == 1) {
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
                'Sng.' . $_EXTKEY,
                'txrecordsmanagerM1', // Make module a submodule of 'web'
                'edit', // Submodule key
                '', // Position
                array(
                    'Edit' => 'index',
                ),
                array(
                    'access' => 'user,group',
                    'icon'   => 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/table-edit.png',
                    'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml:edittitle',
                )
            );
        }

        if ($conf['enabledExport'] == 1) {
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
                'Sng.' . $_EXTKEY,
                'txrecordsmanagerM1', // Make module a submodule of 'web'
                'export', // Submodule key
                '', // Position
                array(
                    'Export' => 'index',
                ),
                array(
                    'access' => 'user,group',
                    'icon'   => 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/export.png',
                    'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml:exporttitle',
                )
            );
        }

    }

    //\TYPO3\CMS\Extbase\Utility\ExtensionUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'recordsmanager');
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:recordsmanager/Configuration/TypoScript/setup.txt">');
}

?>
