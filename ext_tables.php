<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Resources/Private/Php/class.tx_recordsmanager_flexfill.php');

$TCA['tx_recordsmanager_config'] = array(
    'ctrl' => array(
        'title'             => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xml:tx_recordsmanager_config',
        'label'             => 'title',
        'tstamp'            => 'tstamp',
        'crdate'            => 'crdate',
        'cruser_id'         => 'cruser_id',
        'adminOnly'         => 1,
        'rootLevel'         => -1,
        'type'              => 'type',
        'sortby'            => 'sorting',
        'default_sortby'    => 'ORDER BY crdate',
        'delete'            => 'deleted',
        'enablecolumns'     => array(
            'disabled' => 'hidden',
        ),
        'dynamicConfigFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Configuration/Tca/Config.php',
        //'iconfile'          => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/icon_tx_recordsmanager_config.gif',
        'iconfile'          => 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/icon_tx_recordsmanager_config.gif',
    ),
);

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
                'icon'   => 'EXT:' . $_EXTKEY . '/ext_icon.gif',
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
                    'icon'   => 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/insert.gif',
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
                    'icon'   => 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/edit.gif',
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
                    'icon'   => 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/export.gif',
                    'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml:exporttitle',
                )
            );
        }

    }

    //\TYPO3\CMS\Extbase\Utility\ExtensionUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'recordsmanager');
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:recordsmanager/Configuration/TypoScript/setup.txt">');
}

?>
