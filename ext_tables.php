<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

require_once(t3lib_extMgm::extPath($_EXTKEY) . 'Resources/Private/Php/class.tx_recordsmanager_flexfill.php');

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
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'Configuration/Tca/Config.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY) . 'Resources/Public/Icons/icon_tx_recordsmanager_config.gif',
	),
);

if (TYPO3_MODE == 'BE') {
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

	Tx_Extbase_Utility_Extension::registerModule(
		$_EXTKEY,
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

	$conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['recordsmanager']);

	if ($conf['enabledAdd'] == 1) {
		Tx_Extbase_Utility_Extension::registerModule(
			$_EXTKEY,
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
		Tx_Extbase_Utility_Extension::registerModule(
			$_EXTKEY,
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
		Tx_Extbase_Utility_Extension::registerModule(
			$_EXTKEY,
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

	t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'recordsmanager');
}

?>