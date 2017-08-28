<?php

namespace Sng\Recordsmanager\Controller;

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

class ExportController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    protected $currentConfig;

    /**
     * action index
     *
     * @return void
     */
    public function indexAction()
    {
        $allConfigs = \Sng\Recordsmanager\Utility\Config::getAllConfigs(2);

        if (empty($allConfigs)) {
            return null;
        }

        $this->currentConfig = $allConfigs[0];
        $this->setCurrentConfig();

        $this->buildCalendar();
        $query = $this->buildQuery();
        $query->setCheckPids(false);
        $query->setConfig($this->currentConfig);
        $query->setExportMode(true);
        $query->execQuery();
        $this->exportRecords($query);

        $this->view->assign('moreThanSeven', version_compare(TYPO3_version, '7.0.0', '>='));

        $this->view->assign('currentconfig', $this->currentConfig);
        $this->view->assign('arguments', $this->request->getArguments());
        $this->view->assign('menuitems', $allConfigs);

        if ($query->getNbRows() > 0) {
            $this->view->assign('headers', $query->getHeaders());
            $this->view->assign('rows', $query->getRows());
            $this->view->assign('exportmodes', $this->getExportUrls());
        }
    }

    /**
     * Build the calendar (load js and send datas)
     */
    public function buildCalendar()
    {
        $arguments = $this->request->getArguments();
        $this->view->assign('startdate', $arguments['startdate']);
        $this->view->assign('enddate', $arguments['enddate']);
    }

    /**
     * Convert all export modes to urls
     *
     * @return array
     */
    public function getExportUrls()
    {
        $urlsExport = array();
        $modes = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $this->currentConfig['exportmode']);
        foreach ($modes as $mode) {
            $urlsExport[] = array($mode, $this->getExportUrl($mode));
        }
        return $urlsExport;
    }

    /**
     * Get export url
     *
     * @return string
     */
    public function getExportUrl($mode)
    {
        $argKey = strtolower('tx_' . $this->request->getControllerExtensionKey() . '_' . $this->request->getPluginName());
        $arguments = $this->request->getArguments();
        $urlArguments = array();
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
        $arguments = $this->request->getArguments();

        $filterField = 'tstamp';
        if (empty($row['exportfilterfield']) !== true) {
            $filterField = $this->currentConfig['exportfilterfield'];
        }

        $queryObject = new \Sng\Recordsmanager\Utility\Query();
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
     */
    public function exportToXML(\Sng\Recordsmanager\Utility\Query $query, $forceDisplay = false)
    {
        $xmlData = self::exportRecordsToXML($query->getQuery());
        if ($forceDisplay === false) {
            $filename = 'TYPO3_' . $query->getFrom() . '_export_' . date('dmy-Hi') . '.xml';
            $mimeType = 'application/octet-stream';
            header('Content-Type: ' . $mimeType);
            header('Content-Disposition: attachment; filename=' . $filename);
            echo utf8_decode($xmlData);
            exit;
        } else {
            echo utf8_decode($xmlData);
        }
    }

    /**
     * Export to CSV
     *
     * @param \Sng\Recordsmanager\Utility\Query $query
     */
    public function exportToCSV(\Sng\Recordsmanager\Utility\Query $query, $forceDisplay = false)
    {
        $rowArr = array();
        $rows = array_merge(array($query->getHeaders()), $query->getRows());

        foreach ($rows as $row) {
            // utf8 with BOM for Excel
            $rowArr[] = chr(0xEF) . chr(0xBB) . chr(0xBF) . utf8_encode(self::cleanString(\TYPO3\CMS\Core\Utility\GeneralUtility::csvValues($row), true));
        }

        if (count($rowArr)) {
            if ($forceDisplay === false) {
                $filename = 'TYPO3_' . $query->getFrom() . '_export_' . date('dmy-Hi') . '.csv';
                $mimeType = 'application/octet-stream';
                header('Content-Type: ' . $mimeType);
                header('Content-Disposition: attachment; filename=' . $filename);
                echo(implode(CRLF, $rowArr));
                exit;
            } else {
                echo(implode(CRLF, $rowArr));
            }
        }
    }

    /**
     * Export to Excel
     *
     * @param \Sng\Recordsmanager\Utility\Query $query
     */
    public function exportToEXCEL(\Sng\Recordsmanager\Utility\Query $query)
    {
        $rows = array_merge(array($query->getHeaders()), $query->getRows());

        $filename = 'TYPO3_' . $query->getFrom() . '_export_' . date('dmy-Hi') . '.xls';

        require_once(PATH_site . "typo3conf/ext/recordsmanager/Resources/Private/Php/PHPExcel-1.8.1/Classes/PHPExcel.php");

        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);

        $headerStyleArray = array(
            'font' => array(
                'bold' => true,
                'size' => 12,
            )
        );
        $line = 1;
        foreach ($rows as $row) {
            $col = 0;
            foreach ($row as $field => $value) {
                if ($line == 1) {
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $line, $value);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $line)->applyFromArray($headerStyleArray);
                } else {
                    if (is_numeric($value)) {
                        $value = $value . " ";
                    }
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $line, $value);
                }
                $col++;
            }
            $line++;
        }
        // return file
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename);
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    /**
     * Export a query array to xml
     *
     * @param array $query
     * @return string
     */
    public function exportRecordsToXML($query)
    {
        $xmlObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Sng\\Recordsmanager\\Utility\\Xml', 'typo3_export');
        $xmlObj->setRecFields($query['FROM'], $query['SELECT']);
        $xmlObj->renderHeader();
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($query['SELECT'], $query['FROM'], $query['WHERE'], $query['GROUPBY'], $query['ORDERBY'], $query['LIMIT']);
        $xmlObj->renderRecords($query['FROM'], $res);
        $xmlObj->renderFooter();
        $GLOBALS['TYPO3_DB']->sql_free_result($res);
        return $xmlObj->getResult();
    }

    /**
     * Clean a string
     *
     * @param $string
     * @return string
     */
    public function cleanString($string, $deleteLr = false)
    {
        $quotes = array(
            "\xe2\x82\xac" => "\xc2\x80", /* EURO SIGN */
            "\xe2\x80\x9a" => "\xc2\x82", /* SINGLE LOW-9 QUOTATION MARK */
            "\xc6\x92"     => "\xc2\x83", /* LATIN SMALL LETTER F WITH HOOK */
            "\xe2\x80\x9e" => "\xc2\x84", /* DOUBLE LOW-9 QUOTATION MARK */
            "\xe2\x80\xa6" => "\xc2\x85", /* HORIZONTAL ELLIPSIS */
            "\xe2\x80\xa0" => "\xc2\x86", /* DAGGER */
            "\xe2\x80\xa1" => "\xc2\x87", /* DOUBLE DAGGER */
            "\xcb\x86"     => "\xc2\x88", /* MODIFIER LETTER CIRCUMFLEX ACCENT */
            "\xe2\x80\xb0" => "\xc2\x89", /* PER MILLE SIGN */
            "\xc5\xa0"     => "\xc2\x8a", /* LATIN CAPITAL LETTER S WITH CARON */
            "\xe2\x80\xb9" => "\xc2\x8b", /* SINGLE LEFT-POINTING ANGLE QUOTATION */
            "\xc5\x92"     => "\xc2\x8c", /* LATIN CAPITAL LIGATURE OE */
            "\xc5\xbd"     => "\xc2\x8e", /* LATIN CAPITAL LETTER Z WITH CARON */
            "\xe2\x80\x98" => "\xc2\x91", /* LEFT SINGLE QUOTATION MARK */
            "\xe2\x80\x99" => "\xc2\x92", /* RIGHT SINGLE QUOTATION MARK */
            "\xe2\x80\x9c" => "\xc2\x93", /* LEFT DOUBLE QUOTATION MARK */
            "\xe2\x80\x9d" => "\xc2\x94", /* RIGHT DOUBLE QUOTATION MARK */
            "\xe2\x80\xa2" => "\xc2\x95", /* BULLET */
            "\xe2\x80\x93" => "\xc2\x96", /* EN DASH */
            "\xe2\x80\x94" => "\xc2\x97", /* EM DASH */
            "\xcb\x9c"     => "\xc2\x98", /* SMALL TILDE */
            "\xe2\x84\xa2" => "\xc2\x99", /* TRADE MARK SIGN */
            "\xc5\xa1"     => "\xc2\x9a", /* LATIN SMALL LETTER S WITH CARON */
            "\xe2\x80\xba" => "\xc2\x9b", /* SINGLE RIGHT-POINTING ANGLE QUOTATION*/
            "\xc5\x93"     => "\xc2\x9c", /* LATIN SMALL LIGATURE OE */
            "\xc5\xbe"     => "\xc2\x9e", /* LATIN SMALL LETTER Z WITH CARON */
            "\xc5\xb8"     => "\xc2\x9f" /* LATIN CAPITAL LETTER Y WITH DIAERESIS*/
        );
        $string = strtr($string, $quotes);
        $string = utf8_decode($string);
        if ($deleteLr === true) {
            $string = str_replace(array("\r\n", "\n\r", "\n", "\r"), " ", $string);
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
            $this->currentConfig = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
                '*',
                'tx_recordsmanager_config',
                'uid=' . intval($arguments['menuitem'])
            );
        }
    }
}
