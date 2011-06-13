<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

require_once(t3lib_extMgm::extPath($_EXTKEY) . 'lib/class.tx_recordsmanager_flexfill.php');

$TCA['tx_recordsmanager_config'] = array(
	'ctrl' => array(
		'title' => 'LLL:EXT:recordsmanager/locallang_db.xml:tx_recordsmanager_config',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'adminOnly' => 1,
		'rootLevel' => -1,
		'type' => 'type',
		'sortby' => 'sorting',
		'default_sortby' => 'ORDER BY crdate',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY) . 'icon_tx_recordsmanager_config.gif',
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

	t3lib_extMgm::addModule('txrecordsmanagerM1', '', '', t3lib_extMgm::extPath($_EXTKEY) . 'modmain/');

	$conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['recordsmanager']);

	if ($conf['enabledAdd'] == 1) {
		t3lib_extMgm::addModule('txrecordsmanagerM1', 'insert', '', t3lib_extMgm::extPath($_EXTKEY) . 'mod2/');
	}
	if ($conf['enabledEdit'] == 1) {
		t3lib_extMgm::addModule('txrecordsmanagerM1', 'edit', '', t3lib_extMgm::extPath($_EXTKEY) . 'mod1/');
	}
	if ($conf['enabledExport'] == 1) {
		t3lib_extMgm::addModule('txrecordsmanagerM1', 'export', '', t3lib_extMgm::extPath($_EXTKEY) . 'mod3/');
	}
}

?>