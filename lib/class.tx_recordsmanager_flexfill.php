<?php

class tx_recordsmanager_flexfill
{
	function getFields(&$params, &$fObj) {
		if ($params['row']['sqltable'] != '') {
			$res = $GLOBALS['TYPO3_DB']->sql_query('SHOW COLUMNS FROM ' . $params['row']['sqltable']);
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_row($res)) {
				$label = $row[0];
				$value = $row[0];
				if ($row[0] == 'pid') {
					$value = 'pid as "pageUID"';
				}
				$params['items'][] = array($label, $value);
			}
		}
	}
}

?>