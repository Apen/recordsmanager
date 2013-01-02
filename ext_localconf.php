<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
t3lib_extMgm::addUserTSConfig('
    options.saveDocNew.tx_recordsmanager_config=1
'
);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tceforms.php']['getMainFieldsClass'][] = 'EXT:recordsmanager/hooks/class.tx_recordsmanager_callhooks.php:tx_recordsmanager_callhooks';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['postProcessValue'][] = 'EXT:recordsmanager/hooks/class.tx_recordsmanager_callhooks.php:tx_recordsmanager_callhooks->BE_postProcessValue';
?>