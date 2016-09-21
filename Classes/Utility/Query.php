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

class Query
{
    protected $query;
    protected $checkPids = true;
    protected $exportMode = false;
    protected $headers;
    protected $rows;
    protected $config;

    /**
     * Return the current query array
     *
     * @return array
     */
    public function getQuery()
    {
        if ($this->checkPids === true && TYPO3_MODE == 'BE') {
            $pids = $this->checkPids();
            if (count($pids) > 0) {
                $this->query['WHERE'] .= ' AND pid IN (' . implode(',', $pids) . ')';
            }
        }

        if ($this->query['FROM'] == 'tx_powermail_mails' && \TYPO3\CMS\Core\Utility\GeneralUtility::inList('2,3', $this->config['type'])) {
            $this->query['SELECT'] = '*';
        }
        return $this->query;
    }

    /**
     * Build the query (fill the query array)
     */
    public function buildQuery()
    {
        if (!empty($this->config['sqlfields'])) {
            // we need to have the uid
            if (!\TYPO3\CMS\Core\Utility\GeneralUtility::inList($this->config['sqlfields'], 'uid')) {
                $this->query['SELECT'] = 'uid,' . $this->config['sqlfields'];
            } else {
                $this->query['SELECT'] = $this->config['sqlfields'];
            }
        } else {
            $this->query['SELECT'] = '*';
        }

        $this->query['FROM'] = $this->config['sqltable'];
        $this->query['WHERE'] = '1=1 AND deleted=0';
        $this->query['WHERE'] .= ($this->config['extrawhere'] != '') ? ' ' . $this->config['extrawhere'] : '';
        $this->query['GROUPBY'] = ($this->config['extragroupby'] != '') ? $this->config['extragroupby'] : '';
        $this->query['ORDERBY'] = ($this->config['extraorderby'] != '') ? $this->config['extraorderby'] : '';
        $this->query['LIMIT'] = ($this->config['extralimit'] != '') ? $this->config['extralimit'] : '';

        if (!isset($GLOBALS['TCA'][$this->config['sqltable']])) {
            \TYPO3\CMS\Core\Core\Bootstrap::getInstance()->loadCachedTca();
        }
    }

    /**
     * Exec the query (fill headers en rows arrays)
     */
    public function execQuery()
    {
        $res = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($this->getQuery());
        $first = true;
        $rows = array();
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            if ($first) {
                $first = false;
                $this->headers = \Sng\Recordsmanager\Utility\Config::getResultRowTitles($row, $this->query['FROM']);
                if ($this->query['FROM'] == 'tx_powermail_mails' && \TYPO3\CMS\Core\Utility\GeneralUtility::inList('2,3', $this->config['type'])) {
                    $this->headers = array_intersect_key($this->headers, array_flip(\TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $this->config['sqlfields'])));
                    $powermailHeaders = \Sng\Recordsmanager\Utility\Powermail::getHeadersFromRow(\Sng\Recordsmanager\Utility\Powermail::getLastRecord($this->query));
                    $this->headers = array_merge($this->headers, $powermailHeaders);
                }
                if (($this->exportMode === true) && ($this->config['type'] == 3)) {
                    $extraTsHeaders = array_keys(\Sng\Recordsmanager\Utility\Misc::loadAndExecTS($this->config['extrats'], $row, $this->query['FROM']));
                    $this->headers = array_merge($this->headers, array('recordsmanagerkey'), $extraTsHeaders);
                }
            }
            $records = \Sng\Recordsmanager\Utility\Config::getResultRow($row, $this->query['FROM'], $this->config['excludefields'], $this->exportMode);
            if ($this->query['FROM'] == 'tx_powermail_mails' && \TYPO3\CMS\Core\Utility\GeneralUtility::inList('2,3', $this->config['type'])) {
                $records = array_merge($records, \Sng\Recordsmanager\Utility\Powermail::getRow($records, $powermailHeaders));
                $records = array_intersect_key($records, $this->headers);
            }
            if (($this->exportMode === true) && ($this->config['type'] == 3)) {
                $arrayToEncode = array();
                $arrayToEncode['uidconfig'] = $this->config['uid'];
                $arrayToEncode['uidrecord'] = $records['uid'];
                $arrayToEncode['uidserver'] = $_SERVER['SERVER_NAME'];
                $records['recordsmanagerkey'] = md5(serialize($arrayToEncode));
            }
            if (\TYPO3\CMS\Core\Utility\GeneralUtility::inList('2,3', $this->config['type'])) {
                // add special typoscript value
                $markerValues = \Sng\Recordsmanager\Utility\Misc::convertToMarkerArray($records);
                $extraTs = str_replace(array_keys($markerValues), array_values($markerValues), $this->config['extrats']);
                $records = array_merge($records, \Sng\Recordsmanager\Utility\Misc::loadAndExecTS($extraTs, $row, $this->query['FROM']));
            }
            $this->rows[] = $records;
        }
        $GLOBALS['TYPO3_DB']->sql_free_result($res);
    }

    /**
     * Return pid that are allow for tu current be_users
     *
     * @return array
     */
    public function checkPids()
    {
        $pids = array();
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('DISTINCT pid', $this->query['FROM'], $this->query['WHERE'], $this->query['GROUPBY'], $this->query['ORDERBY'], $this->query['LIMIT']);
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            $pageinfo = \TYPO3\CMS\Backend\Utility\BackendUtility::readPageAccess($row['pid'], $GLOBALS['BE_USER']->getPagePermsClause(1));
            if ($pageinfo !== false) {
                $pids[] = $row['pid'];
            }
        }
        $GLOBALS['TYPO3_DB']->sql_free_result($res);
        return $pids;
    }

    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function setCheckPids($checkPids)
    {
        $this->checkPids = $checkPids;
    }

    public function getCheckPids()
    {
        return $this->checkPids;
    }

    public function setQuery($query)
    {
        $this->query = $query;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getRows()
    {
        return $this->rows;
    }

    public function getNbRows()
    {
        return count($this->rows);
    }

    public function setSelect($value)
    {
        $this->query['SELECT'] = $value;
    }

    public function getSelect()
    {
        return $this->query['SELECT'];
    }

    public function setFrom($value)
    {
        $this->query['FROM'] = $value;
    }

    public function getFrom()
    {
        return $this->query['FROM'];
    }

    public function setWhere($value)
    {
        $this->query['WHERE'] = $value;
    }

    public function getWhere()
    {
        return $this->query['WHERE'];
    }

    public function setGroupBy($value)
    {
        $this->query['GROUPBY'] = $value;
    }

    public function getGroupBy()
    {
        return $this->query['GROUPBY'];
    }

    public function setOrderBy($value)
    {
        $this->query['ORDERBY'] = $value;
    }

    public function getOrderBy()
    {
        return $this->query['ORDERBY'];
    }

    public function setLimit($value)
    {
        $this->query['LIMIT'] = $value;
    }

    public function getLimit()
    {
        return $this->query['LIMIT'];
    }

    public function setExportMode($exportMode)
    {
        $this->exportMode = $exportMode;
    }

}