<?php

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

class tx_recordsmanager_flexfill
{
    // List of exclude fields that are not process in insert/edit view
    const excludeFields = 'uid,pid,deleted,t3ver_oid,t3ver_id,t3ver_wsid,t3ver_label,t3ver_state,t3ver_stage,t3ver_count,t3ver_tstamp,t3ver_move_id,t3_origuid,l18n_parent,l18n_diffsource';

    public function getTables(&$params, &$fObj)
    {
        $tables = array_keys($GLOBALS['TCA']);
        sort($tables);
        $params['items'] = array();
        foreach ($tables as $table) {
            $params['items'][] = array($table, $table);
        }
    }

    public function getFields(&$params, &$fObj)
    {
        //\TYPO3\CMS\Core\Utility\DebugUtility::debug($params);
        if (!empty($params['row']['sqltable'])) {
            if (is_array($params['row']['sqltable'])) {
                $res = $GLOBALS['TYPO3_DB']->sql_query('SHOW COLUMNS FROM ' . $params['row']['sqltable'][0]);
            } else {
                $res = $GLOBALS['TYPO3_DB']->sql_query('SHOW COLUMNS FROM ' . $params['row']['sqltable']);
            }
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_row($res)) {
                $label = $row[0];
                $value = $row[0];
//				if ($row[0] == 'pid') {
//					$value = 'pid as "pageUID"';
//				}
                $params['items'][] = array($label, $value);
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($res);
        }
    }

    /**
     * Get TCA description of a table
     */
    public function getTableTCA($table)
    {
        global $TCA;
        //\TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA($table);
        return $TCA[$table];
    }

    /**
     * Get columns from TCA by avoid providing some field
     */
    public function getEditFields(&$params, &$fObj)
    {
        if (!empty($params['row']['sqltable'])) {
            if (is_array($params['row']['sqltable'])) {
                $tableTCA = self::getTableTCA($params['row']['sqltable'][0]);
            } else {
                $tableTCA = self::getTableTCA($params['row']['sqltable']);
            }
            $params['items'] = array();
            foreach ($tableTCA['columns'] as $field => $fieldValue) {
                if (!\TYPO3\CMS\Core\Utility\GeneralUtility::inList($this->excludeFields, $field)) {
                    $params['items'][] = array($field, $field);
                }
            }
        }
    }

    /**
     * Get an array with all the field to hide in tceform
     */
    public static function getDiffFieldsFromTable($table, $defaultFields)
    {
        $fields = array();
        $res = $GLOBALS['TYPO3_DB']->sql_query('SHOW COLUMNS FROM ' . $table);
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_row($res)) {
            if (!\TYPO3\CMS\Core\Utility\GeneralUtility::inList(self::excludeFields, $row[0])) {
                $label = $row[0];
                $value = $row[0];
                $fields [] = $value;
            }
        }
        return array_diff($fields, explode(',', $defaultFields));
    }

}
