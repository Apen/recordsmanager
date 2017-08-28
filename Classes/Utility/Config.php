<?php

namespace Sng\Recordsmanager\Utility;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

class Config
{

    /**
     * Get all config of recordsmanager
     *
     * @param int $type
     * @param string $mode
     * @return array
     */
    public static function getAllConfigs($type, $mode = 'db')
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
     * Get a eid config of recordsmanager
     *
     * @param string $eidkey
     * @return array
     */
    public static function getEidConfig($eidkey)
    {
        $row = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', 'tx_recordsmanager_config', 'type=3 AND deleted=0 AND eidkey="' . mysqli_real_escape_string($GLOBALS['TYPO3_DB']->getDatabaseHandle(), $eidkey) . '"');
        if (!empty($row)) {
            return $row;
        }
        $jsonConfigs = \Sng\Recordsmanager\Utility\Config::loadJsonConfigs();
        if (!empty($jsonConfigs[3][$eidkey])) {
            return $jsonConfigs[3][$eidkey];
        }
        return null;
    }

    /**
     * Load all the json config
     *
     * @return array
     */
    public static function loadJsonConfigs()
    {
        $jsonConfigs = array();
        if (!empty($GLOBALS['TSFE']->tmpl->setup['module.']['tx_recordsmanager.']['settings.']['configs_json.'])) {
            foreach ($GLOBALS['TSFE']->tmpl->setup['module.']['tx_recordsmanager.']['settings.']['configs_json.'] as $configPath) {
                $config = json_decode(GeneralUtility::getUrl($configPath), true);
                $config['extrats'] = implode("\r\n",$config['extrats']);
                if (!empty($config['eidkey'])) {
                    $jsonConfigs[$config['type']][$config['eidkey']] = $config;
                } else {
                    $jsonConfigs[$config['type']][] = $config;
                }
            }
        }
        return $jsonConfigs;
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
            if (!GeneralUtility::inList($excludeFields, $fieldName)) {
                $record[$fieldName] = BackendUtility::getProcessedValueExtra($table, $fieldName, $fieldValue, 0, $row['uid']);
                if (trim($record[$fieldName]) == 'N/A') {
                    $record[$fieldName] = '';
                }
            } else {
                if (!empty($GLOBALS['TCA'][$table]['columns'][$fieldName]) && !GeneralUtility::inList('input,check', $GLOBALS['TCA'][$table]['columns'][$fieldName]['config']['type'])) {
                    $record[$fieldName] = BackendUtility::getProcessedValue($table, $fieldName, $fieldValue, 0, 1, 1, $row['uid'], true);
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
                        $files = GeneralUtility::trimExplode(',', $record[$fieldName]);
                        $newFiles = array();
                        foreach ($files as $file) {
                            $newFiles[] = GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST') . '/' . $GLOBALS['TCA'][$table]['columns'][$fieldName]['config']['uploadfolder'] . '/' . $file;
                        }
                        $record[$fieldName] = implode(', ', $newFiles);
                    }
                }
                // fal reference
                if ($GLOBALS['TCA'][$table]['columns'][$fieldName]['config']['type'] == 'inline' && $GLOBALS['TCA'][$table]['columns'][$fieldName]['config']['foreign_table'] == 'sys_file_reference') {
                    $files = BackendUtility::resolveFileReferences($table, $fieldName, $row);
                    $newFiles = array();
                    foreach ($files as $file) {
                        $newFiles [] = GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST') . '/' . $file->getPublicUrl();
                    }
                    $record[$fieldName] = implode(', ', $newFiles);
                }
                // rte
                if ($GLOBALS['TCA'][$table]['columns'][$fieldName]['config']['type'] == 'text' && !empty($GLOBALS['TCA'][$table]['columns'][$fieldName]['config']['wizards']['RTE'])) {
                    $lCobj = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer');
                    $lCobj->start(array(), '');
                    $record[$fieldName] = $lCobj->parseFunc($record[$fieldName], array(), '< lib.parseFunc_RTE');
                }
            }
        }
        return $record;
    }

}
