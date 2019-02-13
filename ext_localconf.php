<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig('
    options.saveDocNew.tx_recordsmanager_config=1
'
);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tceforms.php']['getMainFieldsClass'][] = 'EXT:recordsmanager/Classes/Hooks/class.tx_recordsmanager_callhooks.php:tx_recordsmanager_callhooks';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['postProcessValue'][] = 'EXT:recordsmanager/Classes/Hooks/class.tx_recordsmanager_callhooks.php:tx_recordsmanager_callhooks->BE_postProcessValue';
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include'][$_EXTKEY] = 'EXT:' . $_EXTKEY . '/Classes/Eid/Index.php';

?>