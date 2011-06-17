<?php

class tx_recordsmanager_flexfill
{
	// List of exclude fields that are not process in insert/edit view
	public $excludeFields = 'uid,pid,deleted,t3ver_oid,t3ver_id,t3ver_wsid,t3ver_label,t3ver_state,t3ver_stage,t3ver_count,t3ver_tstamp,t3ver_move_id,t3_origuid,l18n_parent,l18n_diffsource';

	public function getFields(&$params, &$fObj) {
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

	/**
	 * Get TCA description of a table
	 */

	public function getTableTCA($table) {
		global $TCA;
		t3lib_div::loadTCA($table);
		return $TCA[$table];
	}

	/**
	 * Get columns from TCA by avoid providing some field
	 */

	public function getEditFields(&$params, &$fObj) {
		if ($params['row']['sqltable'] != '') {
			$tableTCA = self::getTableTCA($params['row']['sqltable']);
			$params['items'] = array();
			foreach ($tableTCA['columns'] as $field => $fieldValue) {
				if (!t3lib_div::inList($this->excludeFields, $field)) {
					$params['items'][] = array($field, $field);
				}
			}
		}
	}

	/**
	 * Get an array with all the field to hide in tceform
	 */

	public function getDiffFieldsFromTable($table, $defaultFields) {
		$fields = array();
		$res = $GLOBALS['TYPO3_DB']->sql_query('SHOW COLUMNS FROM ' . $table);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_row($res)) {
			if (!t3lib_div::inList($this->excludeFields, $row[0])) {
				$label = $row[0];
				$value = $row[0];
				$fields [] = $value;
			}
		}
		return array_diff($fields, explode(',', $defaultFields));
	}

}

?>