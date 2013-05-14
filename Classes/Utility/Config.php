<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 CERDAN Yohann <cerdanyohann@yahoo.fr>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

class Tx_Recordsmanager_Utility_Config
{

	/**
	 * Get all config of recordsmanager
	 *
	 * @param int $type
	 * @return array
	 */
	public function getAllConfigs($type) {
		$items = array();
		$allItems = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tx_recordsmanager_config', 'type=' . $type . ' AND deleted=0 AND hidden=0', '', 'sorting');
		$usergroups = explode(',', $GLOBALS['BE_USER']->user['usergroup']);
		if (!empty($allItems)) {
			foreach ($allItems as $key => $row) {
				$configgroups = explode(',', $row['permsgroup']);
				$checkRights = array_intersect($usergroups, $configgroups);
				if (($GLOBALS['BE_USER']->isAdmin()) || (count($checkRights) > 0)) {
					$items[] = $row;
				}
			}
		}
		return $items;
	}

	/**
	 * Get formated fields names of a row
	 *
	 * @param array  $row
	 * @param string $table
	 * @return array
	 */
	public function getResultRowTitles($row, $table) {
		$tableHeader = array();
		$conf = $GLOBALS['TCA'][$table];
		foreach ($row as $fieldName => $fieldValue) {
			$tableHeader[$fieldName] = $GLOBALS['LANG']->sL($conf['columns'][$fieldName]['label'] ? $conf['columns'][$fieldName]['label'] : $fieldName, 1);
		}
		return $tableHeader;
	}

	/**
	 * Process every columns of a row to convert value
	 *
	 * @param array  $row
	 * @param string $table
	 * @return array
	 */
	public function getResultRow($row, $table, $excludeFields = '', $export = FALSE) {
		$record = array();
		foreach ($row as $fieldName => $fieldValue) {
			if (!t3lib_div::inList($excludeFields, $fieldName)) {
				$record[$fieldName] = t3lib_BEfunc::getProcessedValueExtra($table, $fieldName, $fieldValue, 0, $row['uid']);
				if (trim($record[$fieldName]) == 'N/A') {
					$record[$fieldName] = '';
				}
			} else {
				$record[$fieldName] = t3lib_BEfunc::getProcessedValue($table, $fieldName, $fieldValue, 0, 1, 1, $row['uid'], TRUE);
				if (empty($record[$fieldName])) {
					$record[$fieldName] = $fieldValue;
				}
				if (trim($record[$fieldName]) == 'N/A') {
					$record[$fieldName] = '';
				}
			}
			if ($export === TRUE) {
				// add path to files
				if ($GLOBALS['TCA'][$table]['columns'][$fieldName]['config']['type'] == 'group' && $GLOBALS['TCA'][$table]['columns'][$fieldName]['config']['internal_type'] == 'file') {
					$files = t3lib_div::trimExplode(',', $record[$fieldName]);
					$newFiles = array();
					foreach ($files as $file) {
						$newFiles[] = t3lib_div::getIndpEnv('TYPO3_REQUEST_HOST') . '/' . $GLOBALS['TCA'][$table]['columns'][$fieldName]['config']['uploadfolder'] . '/' . $file;
					}
					$record[$fieldName] = implode(', ', $newFiles);
				}
			}
		}
		return $record;
	}

}