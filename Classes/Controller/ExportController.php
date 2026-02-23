<?php

declare(strict_types=1);

namespace Sng\Recordsmanager\Controller;

/*
 * This file is part of the "recordsmanager" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Sng\Recordsmanager\Utility\Config;
use Sng\Recordsmanager\Utility\Query;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\CsvUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ExportController extends AbstractController
{
    protected $currentConfig;

    /**
     * action index
     */
    public function indexAction(int $currentPage = 1): \Psr\Http\Message\ResponseInterface
    {
        $allConfigs = Config::getAllConfigs(2);
        $this->createMenu('index', $allConfigs);

        if (empty($allConfigs)) {
            return $this->htmlResponse(null);
        }

        $this->currentConfig = $allConfigs[0];
        $this->setCurrentConfig();

        $this->buildCalendar();
        $query = $this->buildQuery();
        $query->setCheckPids((bool)$this->currentConfig['checkpid']);
        $query->setConfig($this->currentConfig);
        $query->setExportMode(true);
        $this->buildPagination($query, $currentPage);
        $this->exportRecords($query);

        $this->moduleTemplate->assign('currentconfig', $this->currentConfig);
        $this->moduleTemplate->assign('arguments', $this->request->getArguments());

        // params for pagination
        $this->moduleTemplate->assign(
            'additionalParams',
            ['tx_recordsmanager_txrecordsmanagerm1_recordsmanagerexport' => ($this->request->getArguments()['tx_recordsmanager_txrecordsmanagerm1_recordsmanagerexport'] ?? [])]
        );
        $this->moduleTemplate->assign('overwriteDemand', $this->request->getArguments()['tx_recordsmanager_txrecordsmanagerm1_recordsmanagerexport']['overwriteDemand'] ?? []);

        if ($query->getNbRows() > 0) {
            $this->moduleTemplate->assign('headers', $query->getHeaders());
            $this->moduleTemplate->assign('exportmodes', $this->getExportUrls());
        }

        return $this->moduleTemplate->renderResponse('Export/Index');
    }

    /**
     * Build the calendar (load js and send datas)
     */
    public function buildCalendar(): void
    {
        $this->getAllArguments();
        $this->moduleTemplate->assign('startdate', $this->getOverwriteDemand('startdate'));
        $this->moduleTemplate->assign('enddate', $this->getOverwriteDemand('enddate'));
    }

    public function getAllArguments()
    {
        return $this->request->getArguments();
    }

    public function getOverwriteDemand($key)
    {
        $arguments = $this->getAllArguments();
        return $arguments['tx_recordsmanager_txrecordsmanagerm1_recordsmanagerexport']['overwriteDemand'][$key] ?? null;
    }

    /**
     * Convert all export modes to urls
     */
    public function getExportUrls(): array
    {
        $urlsExport = [];
        $modes = GeneralUtility::trimExplode(',', $this->currentConfig['exportmode']);
        foreach ($modes as $mode) {
            $urlsExport[] = [$mode, $this->getExportUrl($mode)];
        }

        return $urlsExport;
    }

    /**
     * Get export url
     */
    public function getExportUrl(string $mode): string
    {
        $argKey = 'tx_recordsmanager_txrecordsmanagerm1_recordsmanagerexport';
        $this->request->getArguments();
        $urlArguments = [];
        $urlArguments[$argKey]['download'] = $mode;
        if (!empty($this->getOverwriteDemand('startdate'))) {
            $urlArguments[$argKey]['overwriteDemand']['startdate'] = $this->getOverwriteDemand('startdate');
        }

        if (!empty($this->getOverwriteDemand('enddate'))) {
            $urlArguments[$argKey]['overwriteDemand']['enddate'] = $this->getOverwriteDemand('enddate');
        }

        return $this->uriBuilder->reset()->setCreateAbsoluteUri(true)->setAddQueryString(true)->setArguments($urlArguments)->uriFor();
    }

    /**
     * Export records if neededl
     */
    public function exportRecords(Query $query): void
    {
        $arguments = $this->request->getArguments();
        $type = $arguments['download'] ?? $arguments['tx_recordsmanager_txrecordsmanagerm1_recordsmanagerexport']['download'] ?? '';
        if ($type !== '') {
            $query->setLimit('');
            $query->execQuery();
            switch ($type) {
                case 'xml':
                    $this->exportToXML($query);
                    break;
                case 'csv':
                    $this->exportToCSV($query);
                    break;
                case 'excel':
                    $this->exportToEXCEL($query);
                    break;
            }
        }
    }

    /**
     * Build the query array
     */
    public function buildQuery(): Query
    {
        $row = null;
        $arguments = $this->request->getArguments();

        $filterField = 'tstamp';
        if (!empty($row['exportfilterfield'])) {
            $filterField = $this->currentConfig['exportfilterfield'];
        }

        $queryObject = GeneralUtility::makeInstance(Query::class);
        $queryObject->setConfig($this->currentConfig);
        $queryObject->buildQuery();

        if (!empty($arguments['orderby'])) {
            $queryObject->setOrderBy(rawurldecode($arguments['orderby']));
        }

        if (!empty($this->getOverwriteDemand('startdate'))) {
            $tstamp = strtotime($this->getOverwriteDemand('startdate'));
            $queryObject->setWhere($queryObject->getWhere() . ' AND ' . $this->currentConfig['sqltable'] . '.' . $filterField . '>=' . $tstamp);
        }

        if (!empty($this->getOverwriteDemand('enddate'))) {
            $tstamp = strtotime($this->getOverwriteDemand('enddate'));
            $queryObject->setWhere($queryObject->getWhere() . ' AND ' . $this->currentConfig['sqltable'] . '.' . $filterField . '<=' . $tstamp);
        }

        return $queryObject;
    }

    /**
     * Export to XML
     */
    public function exportToXML(Query $query, bool $forceDisplay = false): void
    {
        $rows = $query->getRows();
        $xmlData = GeneralUtility::array2xml(
            $rows,
            '',
            0,
            'records',
            0,
            ['useIndexTagForNum' => $query->getFrom()]
        );
        if (!$forceDisplay) {
            $filename = 'TYPO3_' . $query->getFrom() . '_export_' . date('dmy-Hi') . '.xml';
            $mimeType = 'application/octet-stream';
            header('Content-Type: ' . $mimeType);
            header('Content-Disposition: attachment; filename=' . $filename);
            echo mb_convert_encoding($xmlData, 'ISO-8859-1', 'UTF-8');
            exit;
        }

        echo mb_convert_encoding($xmlData, 'ISO-8859-1', 'UTF-8');
    }

    /**
     * Export to CSV
     */
    public function exportToCSV(Query $query, bool $forceDisplay = false): void
    {
        $rowArr = [];
        $rows = array_merge([$query->getHeaders()], $query->getRows());

        foreach ($rows as $row) {
            // utf8 with BOM for Excel
            $rowArr[] = chr(0xEF) . chr(0xBB) . chr(0xBF) . mb_convert_encoding(self::cleanString(CsvUtility::csvValues($row), true));
        }
        if (!$forceDisplay) {
            $filename = 'TYPO3_' . $query->getFrom() . '_export_' . date('dmy-Hi') . '.csv';
            $mimeType = 'application/octet-stream';
            header('Content-Type: ' . $mimeType);
            header('Content-Disposition: attachment; filename=' . $filename);
            echo implode(CRLF, $rowArr);
            exit;
        }

        echo implode(CRLF, $rowArr);
    }

    /**
     * Export to Excel
     *
     * @return never
     */
    public function exportToEXCEL(Query $query): void
    {
        $rows = array_merge([$query->getHeaders()], $query->getRows());
        $filename = 'TYPO3_' . $query->getFrom() . '_export_' . date('dmy-Hi') . '.xlsx';
        if (!class_exists('XLSXWriter')) {
            require_once ExtensionManagementUtility::extPath('recordsmanager') . 'Resources/Private/Php/PHP_XLSXWriter/xlsxwriter.class.php';
        }
        $writer = new \XLSXWriter();
        $writer->writeSheet($rows);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename);
        header('Cache-Control: max-age=0');
        $writer->writeToStdOut();
        exit;
    }

    /**
     * Clean a string
     */
    public function cleanString(string $string, bool $deleteLr = false): string
    {
        $quotes = [
            "\xe2\x82\xac" => "\xc2\x80", // EURO SIGN
            "\xe2\x80\x9a" => "\xc2\x82", // SINGLE LOW-9 QUOTATION MARK
            "\xc6\x92" => "\xc2\x83", // LATIN SMALL LETTER F WITH HOOK
            "\xe2\x80\x9e" => "\xc2\x84", // DOUBLE LOW-9 QUOTATION MARK
            "\xe2\x80\xa6" => "\xc2\x85", // HORIZONTAL ELLIPSIS
            "\xe2\x80\xa0" => "\xc2\x86", // DAGGER
            "\xe2\x80\xa1" => "\xc2\x87", // DOUBLE DAGGER
            "\xcb\x86" => "\xc2\x88", // MODIFIER LETTER CIRCUMFLEX ACCENT
            "\xe2\x80\xb0" => "\xc2\x89", // PER MILLE SIGN
            "\xc5\xa0" => "\xc2\x8a", // LATIN CAPITAL LETTER S WITH CARON
            "\xe2\x80\xb9" => "\xc2\x8b", // SINGLE LEFT-POINTING ANGLE QUOTATION
            "\xc5\x92" => "\xc2\x8c", // LATIN CAPITAL LIGATURE OE
            "\xc5\xbd" => "\xc2\x8e", // LATIN CAPITAL LETTER Z WITH CARON
            "\xe2\x80\x98" => "\xc2\x91", // LEFT SINGLE QUOTATION MARK
            "\xe2\x80\x99" => "\xc2\x92", // RIGHT SINGLE QUOTATION MARK
            "\xe2\x80\x9c" => "\xc2\x93", // LEFT DOUBLE QUOTATION MARK
            "\xe2\x80\x9d" => "\xc2\x94", // RIGHT DOUBLE QUOTATION MARK
            "\xe2\x80\xa2" => "\xc2\x95", // BULLET
            "\xe2\x80\x93" => "\xc2\x96", // EN DASH
            "\xe2\x80\x94" => "\xc2\x97", // EM DASH
            "\xcb\x9c" => "\xc2\x98", // SMALL TILDE
            "\xe2\x84\xa2" => "\xc2\x99", // TRADE MARK SIGN
            "\xc5\xa1" => "\xc2\x9a", // LATIN SMALL LETTER S WITH CARON
            "\xe2\x80\xba" => "\xc2\x9b", // SINGLE RIGHT-POINTING ANGLE QUOTATION
            "\xc5\x93" => "\xc2\x9c", // LATIN SMALL LIGATURE OE
            "\xc5\xbe" => "\xc2\x9e", // LATIN SMALL LETTER Z WITH CARON
            "\xc5\xb8" => "\xc2\x9f", // LATIN CAPITAL LETTER Y WITH DIAERESIS
        ];
        $string = strtr($string, $quotes);
        $string = mb_convert_encoding($string, 'ISO-8859-1', 'UTF-8');
        if ($deleteLr) {
            $string = str_replace(["\r\n", "\n\r", "\n", "\r"], ' ', $string);
        }

        return $string;
    }

    /**
     * Set the current config record
     */
    public function setCurrentConfig(): void
    {
        $arguments = $this->request->getArguments();
        if (!empty($arguments['menuitem'])) {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_recordsmanager_config');
            $queryBuilder
                ->select('*')
                ->from('tx_recordsmanager_config')
                ->where(
                    $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($arguments['menuitem'], \TYPO3\CMS\Core\Database\Connection::PARAM_INT))
                );
            $this->currentConfig = $queryBuilder->executeQuery()->fetchAssociative();
        }
    }
}
