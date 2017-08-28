<?php

namespace Sng\Recordsmanager\Utility;

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

class Powermail
{

    /**
     * Get header from a powermail record
     *
     * @param array $row
     * @return array
     */
    public function getHeadersFromRow($row)
    {
        $headers = array();
        $piVars = \TYPO3\CMS\Core\Utility\GeneralUtility::xml2array($row['piVars'], 'piVars');
        foreach ($piVars as $key => $value) {
            $headers[$key] = self::getLabelfromBackend($key, $value);
        }
        return $headers;
    }

    /**
     * Get the latest record of a query (to get the headers for powermail)
     *
     * @param $query
     * @return array
     */
    public function getLastRecord($query)
    {
        $query['ORDERBY'] = 'crdate DESC';
        return $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', $query['FROM'], $query['WHERE'], $query['GROUPBY'], $query['ORDERBY'], $query['LIMIT']);
    }

    /**
     * Return the powermail rows
     *
     * @param $row
     * @param $headers
     * @return array
     */
    public function getRow($row, $headers)
    {
        $currentRow = array();
        $piVars = \TYPO3\CMS\Core\Utility\GeneralUtility::xml2array($row['piVars'], 'piVars');
        foreach ($headers as $header => $label) {
            if (is_array($piVars[$header])) {
                $currentRow[$header] = implode(', ', array_filter($piVars[$header]));
            } else {
                $currentRow[$header] = $piVars[$header];
            }
        }
        return $currentRow;
    }

    /**
     * Method getLabelfromBackend() to get label to current field (Extract from powermail)
     *
     * @param    string $name  The uid with "uid" prefix
     * @param    string $value I have no dam idea about this var
     * @return    string
     */
    public function getLabelfromBackend($name, $value)
    {
        // $name like uid55
        if (strpos($name, 'uid') !== false) {
            $uid = str_replace('uid', '', $name);

            $select = 'f.title';
            $from = '
				tx_powermail_fields f
				LEFT JOIN tx_powermail_fieldsets fs
				ON (
					f.fieldset = fs.uid
				)
				LEFT JOIN tt_content c
				ON (
					c.uid = fs.tt_content
				)';
            $where = '
				c.deleted = 0
				AND c.hidden = 0
				AND (
					c.starttime <= ' . time() . '
				)
				AND (
					c.endtime = 0
					OR c.endtime>' . time() . '
				)
				AND (
					c.fe_group = ""
					OR c.fe_group IS NULL
					OR c.fe_group = "0"
					OR (
						c.fe_group LIKE "%,0,%"
						OR c.fe_group LIKE "0,%"
						OR c.fe_group LIKE "%,0"
						OR c.fe_group = "0"
					)
					OR (
						c.fe_group LIKE "%,-1,%"
						OR c.fe_group LIKE "-1,%"
						OR c.fe_group LIKE "%,-1"
						OR c.fe_group = "-1"
					)
				)
				AND f.uid = ' . intval($uid) . '
				AND f.deleted = 0';
            $groupBy = $orderBy = $limit = '';
            // GET title where fields.flexform LIKE <value index="vDEF">vorname</value>
            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $from, $where, $groupBy, $orderBy, $limit);

            if ($res) {
                $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
            }

            // If title was found return it
            if (isset($row['title'])) {
                return $row['title'];

                // If no title was found return
            } else {
                if ($uid < 100000) {
                    return 'POWERMAIL ERROR: No title to current field found in DB';
                }
            }

            // No uid55 so return $name
        } else {
            return $name;
        }
        return null;
    }

}