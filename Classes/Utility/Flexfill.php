<?php

namespace Sng\Recordsmanager\Utility;

/*
 * This file is part of the "recordsmanager" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Flexfill
{
    // List of exclude fields that are not process in insert/edit view
    const excludeFields = 'uid,pid,deleted,t3ver_oid,t3ver_id,t3ver_wsid,t3ver_label,t3ver_state,t3ver_stage,t3ver_count,t3ver_tstamp,t3ver_move_id,t3_origuid,l18n_parent,l18n_diffsource';

    public function getTables(&$params, &$fObj)
    {
        $tables = array_keys($GLOBALS['TCA']);
        sort($tables);
        $params['items'] = [];
        foreach ($tables as $table) {
            $params['items'][] = [$table, $table];
        }
    }

    public function getFields(&$params, &$fObj)
    {
        if (!empty($params['row']['sqltable'])) {
            $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($params['row']['sqltable'][0]);
            $statement = $connection->prepare('SHOW COLUMNS FROM ' . $params['row']['sqltable'][0] . ' ;');
            $statement->execute();
            while ($row = $statement->fetch()) {
                $label = $row['Field'];
                $value = $row['Field'];
                $params['items'][] = [$label, $value];
            }
        }
    }

    /**
     * Get TCA description of a table
     *
     * @param $table
     * @return array
     */
    public function getTableTCA($table)
    {
        global $TCA;
        return $TCA[$table];
    }

    /**
     * Get columns from TCA by avoid providing some field
     */
    public function getEditFields(&$params, &$fObj)
    {
        if (!empty($params['row']['sqltable'])) {
            $tableTCA = self::getTableTCA(is_array($params['row']['sqltable']) ? $params['row']['sqltable'][0] : $params['row']['sqltable']);
            $params['items'] = [];
            foreach ($tableTCA['columns'] as $field => $fieldValue) {
                if (!\TYPO3\CMS\Core\Utility\GeneralUtility::inList(self::excludeFields, $field)) {
                    $params['items'][] = [$field, $field];
                }
            }
        }
    }

    /**
     * Get an array with all the field to hide in tceform
     */
    public static function getDiffFieldsFromTable($table, $defaultFields)
    {
        $fields = [];
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);
        $statement = $connection->prepare('SHOW COLUMNS FROM ' . $table . ' ;');
        $statement->execute();
        while ($row = $statement->fetch()) {
            if (!\TYPO3\CMS\Core\Utility\GeneralUtility::inList(self::excludeFields, $row[0])) {
                $label = $row[0];
                $value = $row[0];
                $fields [] = $value;
            }
        }
        return array_diff($fields, explode(',', $defaultFields));
    }
}
