<?php

namespace Sng\Recordsmanager\Controller;

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
        $doc = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\Template\\DocumentTemplate');
        $pageRenderer = $doc->getPageRenderer();

        if (version_compare(TYPO3_version, '7.0.0', '>=')) {
            $pageRenderer->loadRequireJsModule('TYPO3/CMS/Backend/DateTimePicker');
        } else {
            $pageRenderer->addJsFile($this->backPath . 'sysext/backend/Resources/Public/JavaScript/tceforms.js');
            $pageRenderer->addJsFile($this->backPath . 'js/extjs/ux/Ext.ux.DateTimePicker.js');
        }

        $typo3Settings = array(
            'dateFormat' => array('j-n-Y', 'd/m/Y')
        );

        $pageRenderer->addInlineSettingArray('', $typo3Settings);
        $styleLines = array();
        $styleLines[] = 'div#typo3-docbody{top:58px;}';
        $styleLines[] = 'div#typo3-docheader-row2{height: 30px;}';
        $styleLines[] = 'div#typo3-docheader select {margin: 6px 0 0;}';
        $styleLines[] = 'div#typo3-dblist-pagination{line-height:16px;}';
        $styleLines[] = 'div#typo3-dblist-pagination img {padding-bottom:0px;}';
        $pageRenderer->addCssInlineBlock('recordsmanager', implode(LF, $styleLines));
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
            list($day, $month, $year) = explode('-', $arguments['startdate']);
            $tstamp = mktime(0, 0, 0, $month, $day, $year);
            $queryObject->setWhere($queryObject->getWhere() . ' AND ' . $this->currentConfig['sqltable'] . '.' . $filterField . '>=' . $tstamp);
        }

        if (!empty($arguments['enddate'])) {
            list($day, $month, $year) = explode('-', $arguments['enddate']);
            $tstamp = mktime(0, 0, 0, $month, $day, $year);
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

        $filename = 'TYPO3_' . $query->getFrom() . '_export_' . date('dmy-Hi') . '.xlsx';
        
        require_once __DIR__ . '/../../Contrib/PHPExcel.php';

        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        
        $headerStyleArray = array(
            'font'  => array(
                'bold'  => true,
                'size'  => 12,
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
        header('Content-Disposition: attachment; filename="'.$filename);
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
     * Set the current config record
     */
    public function setCurrentConfig()
    {
        $arguments = $this->request->getArguments();
        if (!empty($arguments['menuitem'])) {
            $this->currentConfig = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', 'tx_recordsmanager_config', 'uid=' . intval($arguments['menuitem']));
        }
    }

}
