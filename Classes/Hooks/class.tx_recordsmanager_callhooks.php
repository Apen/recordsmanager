<?php

class tx_recordsmanager_callhooks
{
	private static $dateformat;

	public function getMainFields_preProcess($table, $row, $parent) {
		$recordsHide = explode(',', \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('recordsHide'));
		if (count($recordsHide) > 0) {
			$parent->hiddenFieldListArr = array_merge($parent->hiddenFieldListArr, $recordsHide);
		}
	}

	public function BE_postProcessValue($params) {
		if ($params['colConf']['type'] == 'input' && isset($params['colConf']['eval']) && $params['colConf']['eval'] == 'date') {
			if (self::$dateformat == NULL) {
				$items = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tx_recordsmanager_config', 'type=2 AND deleted=0 AND hidden=0', '', 'sorting');
				if (count($items)) {
					$config = $items[0];
					self::$dateformat = $config['dateformat'];
				} else {
					self::$dateformat = -1;
				}
			}
			if (self::$dateformat != NULL) {
				// remove the parenthesis at the end of the default date format
				$params['value'] = preg_replace('/\s*\(.+\)/', '', $params['value']);
			}
		}
		return $params['value'];
	}

}
