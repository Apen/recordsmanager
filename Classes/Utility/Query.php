<?php

namespace Sng\Recordsmanager\Utility;

/*
 * This file is part of the "recordsmanager" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class Query
{
    protected $query;
    protected $checkPids = true;
    protected $exportMode = false;
    protected $headers;
    protected $rows = [];
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

        if ($this->isPowermail2()) {
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
            if (!GeneralUtility::inList($this->config['sqlfields'], 'uid')) {
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
    }

    /**
     * @param array $queryArray
     * @return string
     */
    public static function getSqlFromQueryArray(array $queryArray)
    {
        $sql = 'SELECT ' . $queryArray['SELECT'] . ' FROM ' . $queryArray['FROM'] . ' WHERE ' . $queryArray['WHERE'];
        $sql .= !empty($queryArray['GROUPBY']) ? ' GROUP BY ' . $queryArray['GROUPBY'] : '';
        $sql .= !empty($queryArray['ORDERBY']) ? ' ORDER BY ' . $queryArray['ORDERBY'] : '';
        $sql .= !empty($queryArray['LIMIT']) ? ' LIMIT ' . $queryArray['LIMIT'] : '';
        return $sql;
    }

    /**
     * Exec the query (fill headers en rows arrays)
     */
    public function execQuery()
    {
        $mailRepository = null;
        $mail = null;
        $queryArray = $this->getQuery();
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($this->config['sqltable']);
        $statement = $connection->prepare(self::getSqlFromQueryArray($queryArray));
        $statement->execute();

        $first = true;
        $rows = [];
        $fieldsToHide = [];
        if (!empty($this->config['hidefields'])) {
            $fieldsToHide = GeneralUtility::trimExplode(',', $this->config['hidefields']);
        }
        if ($this->isPowermail2()) {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $mailRepository = $objectManager->get('In2code\\Powermail\\Domain\\Repository\\MailRepository');
        }
        while ($row = $statement->fetch()) {
            if ($this->isPowermail2()) {
                $mail = $mailRepository->findByUid($row['uid']);
            }
            if ($first) {
                $first = false;
                $this->headers = Config::getResultRowTitles($row, $this->query['FROM']);
                if ($this->isPowermail2()) {
                    $this->headers = array_intersect_key($this->headers, array_flip(GeneralUtility::trimExplode(',', $this->config['sqlfields'])));
                    $powermailHeaders = [];
                    foreach ($mail->getAnswers() as $answer) {
                        $powermailHeaders [] = $answer->getField()->getTitle();
                    }
                    $this->headers = array_merge($this->headers, $powermailHeaders);
                }
                if (($this->exportMode === true) && ($this->config['type'] == 3)) {
                    $extraTsHeaders = array_keys(Misc::loadAndExecTS($this->config['extrats'], $row, $this->query['FROM']));
                    $this->headers = array_merge($this->headers, ['recordsmanagerkey'], $extraTsHeaders);
                }
            }
            $records = Config::getResultRow($row, $this->query['FROM'], $this->config['excludefields'], $this->exportMode);
            if ($this->isPowermail2()) {
                foreach ($mail->getAnswers() as $answer) {
                    $records [] = $answer->getValue();
                }
                $records = array_intersect_key($records, $this->headers);
            }
            if (($this->exportMode === true) && ($this->config['type'] == 3)) {
                $arrayToEncode = [];
                $arrayToEncode['uidconfig'] = $this->config['uid'];
                $arrayToEncode['uidrecord'] = $records['uid'];
                if (empty($this->config['disabledomaininkey'])) {
                    $arrayToEncode['uidserver'] = $_SERVER['SERVER_NAME'];
                }
                $records['recordsmanagerkey'] = md5(serialize($arrayToEncode));
                // add special typoscript value
                $markerValues = Misc::convertToMarkerArray($records);
                $extraTs = str_replace(array_keys($markerValues), array_values($markerValues), $this->config['extrats']);
                $records = array_merge($records, Misc::loadAndExecTS($extraTs, $row, $this->query['FROM']));
                // hide fields if necessary
                if (!empty($fieldsToHide)) {
                    foreach ($fieldsToHide as $fieldToHide) {
                        unset($records[$fieldToHide]);
                    }
                }
            }
            $this->rows[] = $records;
        }
    }

    /**
     * Return pid that are allow for tu current be_users
     *
     * @return array
     */
    public function checkPids()
    {
        $pids = [];
        $currentQuery = $this->query;
        $currentQuery['SELECT'] = 'DISTINCT pid';
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($currentQuery['FROM']);
        $statement = $connection->prepare(Query::getSqlFromQueryArray($currentQuery));
        $statement->execute();
        while ($row = $statement->fetch()) {
            $pageinfo = BackendUtility::readPageAccess($row['pid'], $GLOBALS['BE_USER']->getPagePermsClause(1));
            if ($pageinfo !== false) {
                $pids[] = $row['pid'];
            }
        }
        return $pids;
    }

    public function isPowermail2()
    {
        return ($this->query['FROM'] == 'tx_powermail_domain_model_mails' || $this->query['FROM'] == 'tx_powermail_domain_model_mail') &&
            GeneralUtility::inList('2,3', $this->config['type']);
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
