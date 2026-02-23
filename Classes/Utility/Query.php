<?php

declare(strict_types=1);

namespace Sng\Recordsmanager\Utility;

/*
 * This file is part of the "recordsmanager" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Doctrine\DBAL\Result;
use Sng\Recordsmanager\Events\GetQueryEvent;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
     */
    public function getQuery(): array
    {
        if ($this->checkPids === true && ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend()) {
            $pids = $this->checkPids();
            if ($pids !== []) {
                $this->query['WHERE'] .= ' AND pid IN (' . implode(',', $pids) . ')';
            } else {
                $this->query['WHERE'] .= ' AND 1=2';
            }
        }

        if ($this->isPowermail()) {
            $this->query['SELECT'] = '*';
        }

        $event = new GetQueryEvent($this->query);
        GeneralUtility::makeInstance(EventDispatcher::class)->dispatch($event);
        $this->query = $event->getQuery();

        return $this->query;
    }

    /**
     * Build the query (fill the query array)
     */
    public function buildQuery(): void
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
        $this->query['WHERE'] .= ($this->config['extrawhere'] !== '') ? ' ' . $this->config['extrawhere'] : '';
        $this->query['GROUPBY'] = $this->config['extragroupby'];
        $this->query['ORDERBY'] = $this->config['extraorderby'];
        $this->query['LIMIT'] = $this->config['extralimit'];
    }

    public static function getSqlFromQueryArray(array $queryArray): string
    {
        $sql = 'SELECT ' . $queryArray['SELECT'] . ' FROM ' . $queryArray['FROM'] . ' WHERE ' . $queryArray['WHERE'];
        $sql .= empty($queryArray['GROUPBY']) ? '' : ' GROUP BY ' . $queryArray['GROUPBY'];
        $sql .= empty($queryArray['ORDERBY']) ? '' : ' ORDER BY ' . $queryArray['ORDERBY'];

        return $sql . (empty($queryArray['LIMIT']) ? '' : ' LIMIT ' . $queryArray['LIMIT']);
    }

    public function prepareAndExecuteQuery(): Result
    {
        $queryArray = $this->getQuery();
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($this->config['sqltable']);
        $statement = $connection->prepare(self::getSqlFromQueryArray($queryArray));
        return $statement->executeQuery();
    }

    public function getNbRowsFromStatement()
    {
        $statement = $this->prepareAndExecuteQuery();
        return $statement->rowCount();
    }

    /**
     * Exec the query (fill headers en rows arrays)
     */
    public function execQuery(): void
    {
        $mailRepository = null;
        $mail = null;
        $this->rows = [];
        $statement = $this->prepareAndExecuteQuery();

        $first = true;
        $fieldsToHide = [];
        if (!empty($this->config['hidefields'])) {
            $fieldsToHide = GeneralUtility::trimExplode(',', $this->config['hidefields']);
        }

        if ($this->isPowermail()) {
            $mailRepository = GeneralUtility::makeInstance(\In2code\Powermail\Domain\Repository\MailRepository::class);
        }

        while ($row = $statement->fetchAssociative()) {
            if ($this->isPowermail()) {
                /** @var \In2code\Powermail\Domain\Model\Mail $mail */
                $mail = $mailRepository->findByUid($row['uid']);
            }

            if ($first) {
                $first = false;
                $this->headers = Config::getResultRowTitles($row, $this->query['FROM']);
                if ($this->isPowermail()) {
                    $this->headers = array_intersect_key($this->headers, array_flip(GeneralUtility::trimExplode(',', $this->config['sqlfields'])));
                    $powermailHeaders = [];
                    foreach ($mail->getAnswers() as $answer) {
                        /** @var \In2code\Powermail\Domain\Model\Answer $answer */
                        $powermailHeaders [] = $answer->getField()->getTitle();
                    }

                    $this->headers = array_merge($this->headers, $powermailHeaders);
                }

                if ($this->isEidExport()) {
                    $extraTsHeaders = array_keys(Misc::loadAndExecTS($this->config['extrats'], $row, $this->query['FROM']));
                    $this->headers = array_merge($this->headers, ['recordsmanagerkey'], $extraTsHeaders);
                }
            }

            if (!$this->isPowermail()) {
                $records = Config::getResultRow($row, $this->query['FROM'], $this->config['excludefields'] ?? '', $this->exportMode);
            } else {
                // if this is a powermail export, we dont need to process all the fields
                $records = $row;
            }

            if ($this->isPowermail()) {
                $records = array_intersect_key($records, $this->headers);

                foreach ($mail->getAnswers() as $answer) {
                    if (Config::getFormat() === 'json') {
                        $records [$answer->getField()->getUid()] = [
                            'label' => $answer->getField()->getTitle(),
                            'marker' => $answer->getField()->getMarkerOriginal(),
                            'uid' => $answer->getField()->getUid(),
                            'value' => $answer->getValue(),
                        ];
                    } else {
                        $records [] = $answer->getValue();
                    }
                }

            }

            if ($this->isEidExport()) {
                $arrayToEncode = [];
                $arrayToEncode['uidconfig'] = $this->config['uid'] ?? 0;
                $arrayToEncode['uidrecord'] = $records['uid'];
                if (empty($this->config['disabledomaininkey'])) {
                    $arrayToEncode['uidserver'] = $_SERVER['SERVER_NAME'];
                }

                $records['recordsmanagerkey'] = md5(serialize($arrayToEncode));
                // add special typoscript value
                $markerValues = Misc::convertToMarkerArray($records);
                $extraTs = str_replace(
                    array_keys($markerValues),
                    array_values($markerValues),
                    $this->config['extrats']
                );
                $records = array_merge($records, Misc::loadAndExecTS($extraTs, $row, $this->query['FROM']));
                // hide fields if necessary
                foreach ($fieldsToHide as $fieldToHide) {
                    unset($records[$fieldToHide]);
                }
            }

            $this->rows[] = $records;
        }
    }

    protected function isEidExport(): bool
    {
        return ($this->exportMode === true) && ($this->config['type'] === 3);
    }

    /**
     * Return pid that are allow for tu current be_users
     */
    public function checkPids(): array
    {
        $pids = [];
        $currentQuery = $this->query;
        $currentQuery['SELECT'] = 'DISTINCT pid';
        $currentQuery['ORDERBY'] = '';
        $currentQuery['LIMIT'] = '';
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($currentQuery['FROM']);
        $statement = $connection->prepare(self::getSqlFromQueryArray($currentQuery));
        $result = $statement->executeQuery();
        while ($row = $result->fetchAssociative()) {
            $pageinfo = BackendUtility::readPageAccess($row['pid'], $GLOBALS['BE_USER']->getPagePermsClause(1));
            if ($pageinfo !== false) {
                $pids[] = $row['pid'];
            }
        }

        return $pids;
    }

    public function isPowermail()
    {
        return (
            $this->query['FROM'] === 'tx_powermail_domain_model_mails' || $this->query['FROM'] === 'tx_powermail_domain_model_mail') &&
            GeneralUtility::inList(
                '2,3',
                $this->config['type']
            );
    }

    public function setConfig($config): void
    {
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function setCheckPids($checkPids): void
    {
        $this->checkPids = $checkPids;
    }

    public function getCheckPids()
    {
        return $this->checkPids;
    }

    public function setQuery($query): void
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

    public function setSelect($value): void
    {
        $this->query['SELECT'] = $value;
    }

    public function getSelect()
    {
        return $this->query['SELECT'];
    }

    public function setFrom($value): void
    {
        $this->query['FROM'] = $value;
    }

    public function getFrom()
    {
        return $this->query['FROM'];
    }

    public function setWhere($value): void
    {
        $this->query['WHERE'] = $value;
    }

    public function getWhere()
    {
        return $this->query['WHERE'];
    }

    public function setGroupBy($value): void
    {
        $this->query['GROUPBY'] = $value;
    }

    public function getGroupBy()
    {
        return $this->query['GROUPBY'];
    }

    public function setOrderBy($value): void
    {
        $this->query['ORDERBY'] = $value;
    }

    public function getOrderBy()
    {
        return $this->query['ORDERBY'];
    }

    public function setLimit($value): void
    {
        $this->query['LIMIT'] = $value;
    }

    public function getLimit()
    {
        return $this->query['LIMIT'];
    }

    public function setExportMode($exportMode): void
    {
        $this->exportMode = $exportMode;
    }
}
