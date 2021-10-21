<?php

namespace Sng\Recordsmanager\Controller;

/*
 * This file is part of the "recordsmanager" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Sng\Recordsmanager\Utility\Config;
use Sng\Recordsmanager\Utility\Query;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\CsvUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class ExportController extends AbstractController
{
    protected $currentConfig;

    /**
     * action index
     */
    public function indexAction(int $currentPage = 1)
    {
        $allConfigs = Config::getAllConfigs(2);
        $this->createMenu('index', $allConfigs);

        if (empty($allConfigs)) {
            return null;
        }

        $this->currentConfig = $allConfigs[0];
        $this->setCurrentConfig();

        $this->buildCalendar();
        $query = $this->buildQuery();
        $query->setCheckPids((bool)$this->currentConfig['checkpid']);
        $query->setConfig($this->currentConfig);
        $query->setExportMode(true);
        $query->execQuery();
        $this->buildPagination($query->getRows(), $currentPage);
        $this->exportRecords($query);

        $this->view->assign('currentconfig', $this->currentConfig);
        $this->view->assign('arguments', $this->request->getArguments());

        if ($query->getNbRows() > 0) {
            $this->view->assign('headers', $query->getHeaders());
            $this->view->assign('exportmodes', $this->getExportUrls());
        }

        $this->moduleTemplate->setContent($this->view->render());
        return $this->htmlResponseCompatibility($this->moduleTemplate->renderContent());
    }

    /**
     * Build the calendar (load js and send datas)
     */
    public function buildCalendar()
    {
        $arguments = $this->getAllArguments();
        $this->view->assign('startdate', $arguments['startdate']);
        $this->view->assign('enddate', $arguments['enddate']);
        // ugly fix to work with widget and TYPO3 <10, will be delete later
        $_GET['tx_recordsmanager_txrecordsmanagerm1_recordsmanagerexport']['startdate'] = $arguments['startdate'];
        $_GET['tx_recordsmanager_txrecordsmanagerm1_recordsmanagerexport']['enddate'] = $arguments['enddate'];
    }

    public function getAllArguments()
    {
        $arguments = $this->request->getArguments();
        if (!empty($arguments['@widget_0'])) {
            if (!empty($arguments['@widget_0']['startdate']) && empty($arguments['startdate'])) {
                $arguments['startdate'] = $arguments['@widget_0']['startdate'];
            }
            if (!empty($arguments['@widget_0']['enddate']) && empty($arguments['enddate'])) {
                $arguments['enddate'] = $arguments['@widget_0']['enddate'];
            }
        }
        return $arguments;
    }

    /**
     * Convert all export modes to urls
     *
     * @return array
     */
    public function getExportUrls()
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
     *
     * @param string $mode
     * @return string
     */
    public function getExportUrl($mode)
    {
        $argKey = strtolower('tx_' . $this->request->getControllerExtensionKey() . '_' . $this->request->getPluginName());
        $arguments = $this->request->getArguments();
        $urlArguments = [];
        $urlArguments[$argKey]['download'] = $mode;
        if (!empty($arguments['startdate'])) {
            $urlArguments[$argKey]['startdate'] = $arguments['startdate'];
        }
        if (!empty($arguments['enddate'])) {
            $urlArguments[$argKey]['enddate'] = $arguments['enddate'];
        }
        return $this->uriBuilder->reset()->setAddQueryString(true)->setArguments($urlArguments)->uriFor();
    }

    /**
     * Export records if neededl
     *
     * @param \Sng\Recordsmanager\Utility\Query $query
     */
    public function exportRecords($query)
    {
        $arguments = $this->request->getArguments();
        if (!empty($arguments['download'])) {
            switch ($arguments['download']) {
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
     *
     * @return \Sng\Recordsmanager\Utility\Query
     */
    public function buildQuery()
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

        if (!empty($arguments['startdate'])) {
            $tstamp = strtotime($arguments['startdate']);
            $queryObject->setWhere($queryObject->getWhere() . ' AND ' . $this->currentConfig['sqltable'] . '.' . $filterField . '>=' . $tstamp);
        }

        if (!empty($arguments['enddate'])) {
            $tstamp = strtotime($arguments['enddate']);
            $queryObject->setWhere($queryObject->getWhere() . ' AND ' . $this->currentConfig['sqltable'] . '.' . $filterField . '<=' . $tstamp);
        }

        return $queryObject;
    }

    /**
     * Export to XML
     *
     * @param \Sng\Recordsmanager\Utility\Query $query
     * @param bool                              $forceDisplay
     */
    public function exportToXML(Query $query, $forceDisplay = false)
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
            echo utf8_decode($xmlData);
            exit;
        }
        echo utf8_decode($xmlData);
    }

    /**
     * Export to CSV
     *
     * @param \Sng\Recordsmanager\Utility\Query $query
     * @param bool                              $forceDisplay
     */
    public function exportToCSV(Query $query, $forceDisplay = false)
    {
        $rowArr = [];
        $rows = array_merge([$query->getHeaders()], $query->getRows());

        foreach ($rows as $row) {
            // utf8 with BOM for Excel
            $rowArr[] = chr(0xEF) . chr(0xBB) . chr(0xBF) . utf8_encode(self::cleanString(CsvUtility::csvValues($row), true));
        }

        if (count($rowArr) > 0) {
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
    }

    /**
     * Export to Excel
     *
     * @param \Sng\Recordsmanager\Utility\Query $query
     */
    public function exportToEXCEL(Query $query)
    {
        $rows = array_merge([$query->getHeaders()], $query->getRows());
        $filename = 'TYPO3_' . $query->getFrom() . '_export_' . date('dmy-Hi') . '.xlsx';
        require_once(Environment::getPublicPath() . '/typo3conf/ext/recordsmanager/Resources/Private/Php/PHP_XLSXWriter/xlsxwriter.class.php');
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
     *
     * @param string $string
     * @param bool   $deleteLr
     * @return string
     */
    public function cleanString($string, $deleteLr = false)
    {
        $quotes = [
            "\xe2\x82\xac" => "\xc2\x80", /* EURO SIGN */
            "\xe2\x80\x9a" => "\xc2\x82", /* SINGLE LOW-9 QUOTATION MARK */
            "\xc6\x92" => "\xc2\x83", /* LATIN SMALL LETTER F WITH HOOK */
            "\xe2\x80\x9e" => "\xc2\x84", /* DOUBLE LOW-9 QUOTATION MARK */
            "\xe2\x80\xa6" => "\xc2\x85", /* HORIZONTAL ELLIPSIS */
            "\xe2\x80\xa0" => "\xc2\x86", /* DAGGER */
            "\xe2\x80\xa1" => "\xc2\x87", /* DOUBLE DAGGER */
            "\xcb\x86" => "\xc2\x88", /* MODIFIER LETTER CIRCUMFLEX ACCENT */
            "\xe2\x80\xb0" => "\xc2\x89", /* PER MILLE SIGN */
            "\xc5\xa0" => "\xc2\x8a", /* LATIN CAPITAL LETTER S WITH CARON */
            "\xe2\x80\xb9" => "\xc2\x8b", /* SINGLE LEFT-POINTING ANGLE QUOTATION */
            "\xc5\x92" => "\xc2\x8c", /* LATIN CAPITAL LIGATURE OE */
            "\xc5\xbd" => "\xc2\x8e", /* LATIN CAPITAL LETTER Z WITH CARON */
            "\xe2\x80\x98" => "\xc2\x91", /* LEFT SINGLE QUOTATION MARK */
            "\xe2\x80\x99" => "\xc2\x92", /* RIGHT SINGLE QUOTATION MARK */
            "\xe2\x80\x9c" => "\xc2\x93", /* LEFT DOUBLE QUOTATION MARK */
            "\xe2\x80\x9d" => "\xc2\x94", /* RIGHT DOUBLE QUOTATION MARK */
            "\xe2\x80\xa2" => "\xc2\x95", /* BULLET */
            "\xe2\x80\x93" => "\xc2\x96", /* EN DASH */
            "\xe2\x80\x94" => "\xc2\x97", /* EM DASH */
            "\xcb\x9c" => "\xc2\x98", /* SMALL TILDE */
            "\xe2\x84\xa2" => "\xc2\x99", /* TRADE MARK SIGN */
            "\xc5\xa1" => "\xc2\x9a", /* LATIN SMALL LETTER S WITH CARON */
            "\xe2\x80\xba" => "\xc2\x9b", /* SINGLE RIGHT-POINTING ANGLE QUOTATION*/
            "\xc5\x93" => "\xc2\x9c", /* LATIN SMALL LIGATURE OE */
            "\xc5\xbe" => "\xc2\x9e", /* LATIN SMALL LETTER Z WITH CARON */
            "\xc5\xb8" => "\xc2\x9f" /* LATIN CAPITAL LETTER Y WITH DIAERESIS*/
        ];
        $string = strtr($string, $quotes);
        $string = utf8_decode($string);
        if ($deleteLr) {
            $string = str_replace(["\r\n", "\n\r", "\n", "\r"], ' ', $string);
        }
        return $string;
    }

    /**
     * Set the current config record
     */
    public function setCurrentConfig()
    {
        $arguments = $this->request->getArguments();
        if (!empty($arguments['menuitem'])) {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_recordsmanager_config');
            $queryBuilder
                ->select('*')
                ->from('tx_recordsmanager_config')
                ->where(
                    $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($arguments['menuitem'], \PDO::PARAM_INT))
                );
            $this->currentConfig = $queryBuilder->execute()->fetch();
        }
    }
}
