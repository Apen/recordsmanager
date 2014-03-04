<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 CERDAN Yohann <cerdanyohann@yahoo.fr>
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
class Tx_Recordsmanager_Controller_ExportController extends Tx_Extbase_MVC_Controller_ActionController {
	protected $currentConfig;

	/**
	 * action index
	 *
	 * @return void
	 */
	public function indexAction() {
		$allConfigs = Tx_Recordsmanager_Utility_Config::getAllConfigs(2);
		$this->currentConfig = $allConfigs[0];
		$this->setCurrentConfig();

		$this->buildCalendar();
		$query = $this->buildQuery();
		$query->setCheckPids(FALSE);
		$query->setConfig($this->currentConfig);
		$query->setExportMode(TRUE);
		$query->execQuery();
		$this->exportRecords($query);

		$this->view->assign('headers', $query->getHeaders());
		$this->view->assign('rows', $query->getRows());
		$this->view->assign('currentconfig', $this->currentConfig);
		$this->view->assign('arguments', $this->request->getArguments());
		$this->view->assign('menuitems', $allConfigs);
		$this->view->assign('exportmodes', $this->getExportUrls());
	}

	/**
	 * Build the calendar (load js and send datas)
	 */
	public function buildCalendar() {
		$arguments = $this->request->getArguments();
		$doc = t3lib_div::makeInstance('template');
		$pageRenderer = $doc->getPageRenderer();
		$pageRenderer->addJsFile('/t3lib/js/extjs/tceforms.js', NULL, FALSE);
		$pageRenderer->addJsFile('/t3lib/js/extjs/ux/Ext.ux.DateTimePicker.js', NULL, FALSE);
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
	public function getExportUrls() {
		$urlsExport = array();
		$modes = t3lib_div::trimExplode(',', $this->currentConfig['exportmode']);
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
	public function getExportUrl($mode) {
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
		return $this->uriBuilder->reset()->setAddQueryString(TRUE)->setArguments($urlArguments)->uriFor();
	}

	/**
	 * Export records if neededl
	 *
	 * @param Tx_Recordsmanager_Utility_Query $query
	 */
	public function exportRecords($query) {
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
	 * @return Tx_Recordsmanager_Utility_Query
	 */
	public function buildQuery() {
		$arguments = $this->request->getArguments();

		$filterField = 'tstamp';
		if (empty($row['exportfilterfield']) !== TRUE) {
			$filterField = $this->currentConfig['exportfilterfield'];
		}

		$queryObject = new Tx_Recordsmanager_Utility_Query();
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
	 * @param Tx_Recordsmanager_Utility_Query $query
	 */
	public function exportToXML(Tx_Recordsmanager_Utility_Query $query, $forceDisplay = FALSE) {
		$xmlData = self::exportRecordsToXML($query->getQuery());
		if ($forceDisplay === FALSE) {
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
	 * @param Tx_Recordsmanager_Utility_Query $query
	 */
	public function exportToCSV(Tx_Recordsmanager_Utility_Query $query, $forceDisplay = FALSE) {
		$rowArr = array();
		$rows = array_merge(array($query->getHeaders()), $query->getRows());

		foreach ($rows as $row) {
			$rowArr[] = self::cleanString(t3lib_div::csvValues($row), TRUE);
		}

		if (count($rowArr)) {
			if ($forceDisplay === FALSE) {
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
	 * @param Tx_Recordsmanager_Utility_Query $query
	 */
	public function exportToEXCEL(Tx_Recordsmanager_Utility_Query $query) {
		$rows = array_merge(array($query->getHeaders()), $query->getRows());

		$dirName = PATH_site . 'typo3temp/';
		$filename = 'TYPO3_' . $query->getFrom() . '_export_' . date('dmy-Hi') . '.xls';

		require_once(PATH_site . "typo3conf/ext/recordsmanager/Resources/Private/Php/php_writeexcel-0.3.0/class.writeexcel_workbook.inc.php");
		require_once(PATH_site . "typo3conf/ext/recordsmanager/Resources/Private/Php/php_writeexcel-0.3.0/class.writeexcel_worksheet.inc.php");

		$fname = $dirName . $filename;
		$workbook = new writeexcel_workbook($fname);
		$worksheet = $workbook->addworksheet();

		$header = $workbook->addformat();
		$header->set_bold();
		$header->set_size(12);

		$line = 0;
		foreach ($rows as $row) {
			$col = 0;
			foreach ($row as $field => $value) {
				$value = self::cleanString($value);
				if ($line == 0) {
					$worksheet->write($line, $col++, $value, $header);
				} else {
					if (is_numeric($value)) {
						$value = $value . " ";
					}
					$worksheet->write($line, $col++, $value);
				}
			}
			$line++;
		}

		$workbook->close();

		header("Content-Type: application/x-msexcel; name=\"" . $filename . "\"");
		header("Content-Disposition: inline; filename=\"" . $filename . "\"");
		$fh = fopen($fname, "rb");
		fpassthru($fh);
		unlink($fname);
		exit;
	}

	/**
	 * Export a query array to xml
	 *
	 * @param array $query
	 * @return string
	 */
	public function exportRecordsToXML($query) {
		$xmlObj = t3lib_div::makeInstance('t3lib_xml', 'typo3_export');
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
	public function cleanString($string, $deleteLr = FALSE) {
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
		if ($deleteLr === TRUE) {
			$string = str_replace(array("\r\n", "\n\r", "\n", "\r"), " ", $string);
		}
		return $string;
	}

	/**
	 * Set the current config record
	 */
	public function setCurrentConfig() {
		$arguments = $this->request->getArguments();
		if (!empty($arguments['menuitem'])) {
			$this->currentConfig = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', 'tx_recordsmanager_config', 'uid=' . intval($arguments['menuitem']));
		}
	}

}
