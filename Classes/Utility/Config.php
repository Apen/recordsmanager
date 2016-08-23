<?php

namespace Sng\Recordsmanager\Utility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 CERDAN Yohann <cerdanyohann@yahoo.fr>
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

class Config
{

    /**
     * Get all config of recordsmanager
     *
     * @param int $type
     * @return array
     */
    public static function getAllConfigs($type)
    {
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
    public static function getResultRowTitles($row, $table)
    {
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
    public static function getResultRow($row, $table, $excludeFields = '', $export = false)
    {
        $record = array();
        foreach ($row as $fieldName => $fieldValue) {
            if (!\TYPO3\CMS\Core\Utility\GeneralUtility::inList($excludeFields, $fieldName)) {
                $record[$fieldName] = \TYPO3\CMS\Backend\Utility\BackendUtility::getProcessedValueExtra($table, $fieldName, $fieldValue, 0, $row['uid']);
                if (trim($record[$fieldName]) == 'N/A') {
                    $record[$fieldName] = '';
                }
            } else {
                if (!\TYPO3\CMS\Core\Utility\GeneralUtility::inList('input', $GLOBALS['TCA'][$table]['columns'][$fieldName]['config']['type'])) {
                    $record[$fieldName] = \TYPO3\CMS\Backend\Utility\BackendUtility::getProcessedValue($table, $fieldName, $fieldValue, 0, 1, 1, $row['uid'], true);
                } else {
                    $record[$fieldName] = $fieldValue;
                }
                if ($GLOBALS['TCA'][$table]['columns'][$fieldName]['config']['type'] == 'input') {
                    if (($GLOBALS['TCA'][$table]['columns'][$fieldName]['config']['eval'] == 'datetime') || ($GLOBALS['TCA'][$table]['columns'][$fieldName]['config']['eval'] == 'date')) {
                        $record[$fieldName] = $fieldValue;
                    }
                }
                if (empty($record[$fieldName])) {
                    $record[$fieldName] = $fieldValue;
                }
                if (trim($record[$fieldName]) == 'N/A') {
                    $record[$fieldName] = '';
                }
            }
            if ($export === true) {
                // add path to files
                if ($GLOBALS['TCA'][$table]['columns'][$fieldName]['config']['type'] == 'group' && $GLOBALS['TCA'][$table]['columns'][$fieldName]['config']['internal_type'] == 'file') {
                    if (!empty($record[$fieldName])) {
                        $files = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $record[$fieldName]);
                        $newFiles = array();
                        foreach ($files as $file) {
                            $newFiles[] = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST') . '/' . $GLOBALS['TCA'][$table]['columns'][$fieldName]['config']['uploadfolder'] . '/' . $file;
                        }
                        $record[$fieldName] = implode(', ', $newFiles);
                    }
                }
                if ($GLOBALS['TCA'][$table]['columns'][$fieldName]['config']['type'] == 'text' && !empty($GLOBALS['TCA'][$table]['columns'][$fieldName]['config']['wizards']['RTE'])) {

                    $lCobj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer');
                    $lCobj->start(array(), '');
                    $record[$fieldName] = $lCobj->parseFunc($record[$fieldName], array(), '< lib.parseFunc_RTE');
                }
            }
        }
        return $record;
    }

}
