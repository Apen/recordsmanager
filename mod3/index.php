<?php
/**
 * Copyright notice
 *
 *	(c) 2011  <>
 *	All rights reserved
 *
 *	This script is part of the TYPO3 project. The TYPO3 project is
 *	free software; you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation; either version 2 of the License, or
 *	(at your option) any later version.
 *
 *	The GNU General Public License can be found at
 *	http://www.gnu.org/copyleft/gpl.html.
 *
 *	This script is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	This copyright notice MUST APPEAR in all copies of the script!
 */

$LANG->includeLLFile('EXT:recordsmanager/mod3/locallang.xml');
require_once(PATH_t3lib . 'class.t3lib_scbase.php');
require_once(PATH_typo3 . 'class.db_list.inc');
require_once(PATH_typo3 . 'class.db_list_extra.inc');
require_once(PATH_typo3 . 'sysext/cms/layout/class.tx_cms_layout.php');
$BE_USER->modAccess($MCONF, 1); // This checks permissions and exits if the users has no permission for entry.
// DEFAULT initialization of a module [END]
/**
 * Module 'Données' for the 'recordsmanager' extension.
 *
 * @author <>
 * @package TYPO3
 * @subpackage tx_recordsmanager
 */
class tx_recordsmanager_module3 extends t3lib_SCbase
{
	public $pageinfo;
	protected $items = array();
	protected $currentItem = array();
	protected $nbElementsPerPage = 10;
	protected $exportMode = '';

	/**
	 * Initializes the Module
	 *
	 * @return void
	 */

	function init() {
		global $BE_USER, $LANG, $BACK_PATH, $TCA_DESCR, $TCA, $CLIENT, $TYPO3_CONF_VARS;
		$items = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tx_recordsmanager_config', 'type=2 AND deleted=0 AND hidden=0', '', 'sorting');
		$usergroups = explode(',', $BE_USER->user['usergroup']);
		foreach ($items as $key => $row) {
			$configgroups = explode(',', $row['permsgroup']);
			$checkRights = array_intersect($usergroups, $configgroups);
			if (($BE_USER->isAdmin()) || (count($checkRights) > 0)) {
				$this->items [] = $row;
			}
		}
		// Check nb per page
		$nbPerPage = t3lib_div::_GP('nbPerPage');
		if ($nbPerPage !== null) {
			$this->nbElementsPerPage = $nbPerPage;
		}
		parent::init();
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return void
	 */

	function menuConfig() {
		$this->MOD_MENU = array();
		$this->MOD_MENU['function'] = array();
		foreach ($this->items as $key => $row) {
			$this->MOD_MENU['function'] [] = $row['title'];
		}
		parent::menuConfig();
	}

	function main() {
		global $BE_USER, $LANG, $BACK_PATH, $TCA_DESCR, $TCA, $CLIENT, $TYPO3_CONF_VARS;
		// Draw the header.
		$this->doc = t3lib_div::makeInstance('bigDoc');
		$this->doc->styleSheetFile2 = '../typo3conf/ext/recordsmanager/lib/module.css';
		$this->doc->backPath = $BACK_PATH;
		$this->doc->form = '<form action="" method="post" enctype="multipart/form-data">';

		// JavaScript
		$this->doc->getPageRenderer()->loadExtJS();
		$this->doc->getPageRenderer()->addJsFile($this->backPath . '../t3lib/js/extjs/tceforms.js');

		// Define settings for Date Picker
		$typo3Settings = array(
			'dateFormat' => array('j-n-Y', 'j-n-Y')
		);

		$this->doc->getPageRenderer()->addInlineSettingArray('', $typo3Settings);

		$this->doc->JScode = '
			<script language="javascript" type="text/javascript">
			script_ended = 0;
			function jumpToUrl(URL)	{
			document.location = URL;
			}
			</script>
		';

		$this->doc->postCode = '
			<script language="javascript" type="text/javascript">
			script_ended = 1;
			if (top.fsMod) top.fsMod.recentIds["web"] = 0;
			</script>
		';
		$this->content .= $this->doc->startPage($LANG->getLL('title'));

		if (count($this->MOD_MENU['function']) > 0) {
			$this->content .= '<table><tr><td class="functitle">' . $LANG->getLL('choose') . '</td><td align="right">' . $this->doc->funcMenu('', t3lib_BEfunc::getFuncMenu('', 'SET[function]', $this->MOD_SETTINGS['function'], $this->MOD_MENU['function'])) . '</td></tr></table>';
			$this->content .= $this->doc->divider(5);
			$this->moduleContent();
		} else {
			$this->content .= $LANG->getLL('norecords');
		}
	}

	/**
	 * Prints out the module HTML
	 *
	 * @return void
	 */
	function printContent() {
		$this->content .= $this->doc->endPage();
		echo $this->content;
	}

	/**
	 * Generates the module content
	 *
	 * @return void
	 */
	function moduleContent() {
		global $LANG, $BE_USER;
		foreach ($this->items as $key => $row) {
			if ((string)$this->MOD_SETTINGS['function'] == $key) {
				$this->currentItem = $row;
				$query = array();
				// we need to have the uid
				if (!t3lib_div::inList($row['sqlfields'], 'uid')) {
					$query['SELECT'] = 'uid,' . $row['sqlfields'];
				} else {
					$query['SELECT'] = $row['sqlfields'];
				}
				$query['FROM'] = $row['sqltable'];
				$query['WHERE'] = '1=1 AND deleted=0';
				$query['WHERE'] .= ($row['extrawhere'] != '') ? ' ' . $row['extrawhere'] : '';
				$query['GROUPBY'] = '';
				$query['GROUPBY'] .= ($row['extragroupby'] != '') ? $row['extragroupby'] : '';
				$query['ORDERBY'] = '';
				$query['ORDERBY'] .= ($row['extraorderby'] != '') ? $row['extraorderby'] : '';
				$orderby = t3lib_div::_GP('orderby');
				if ($orderby !== NULL) {
					$query['ORDERBY'] = $orderby;
				}
				$query['LIMIT'] = '';
				$query['LIMIT'] .= ($row['extralimit'] != '') ? $row['extralimit'] : '';
				$this->exportMode = ($row['exportmode'] != '') ? $row['exportmode'] : 'xml';

				// filter by date
				$startdate = t3lib_div::_GP('startdate');
				$enddate = t3lib_div::_GP('enddate');
				if (($startdate !== null) && ($startdate != '')) {
					list($day, $month, $year) = explode('-', $startdate);
					$tstamp = mktime(0, 0, 0, $month, $day, $year);
					$query['WHERE'] .= ' AND ' . $row['sqltable'] . '.tstamp>=' . $tstamp;
				}
				if (($enddate !== null) && ($enddate != '')) {
					list($day, $month, $year) = explode('-', $enddate);
					$tstamp = mktime(0, 0, 0, $month, $day, $year);
					$query['WHERE'] .= ' AND ' . $row['sqltable'] . '.tstamp<=' . $tstamp;
				}

				$content = $this->drawTable($query, $LANG->getLL('datas'));
				$modes = explode(',', $this->exportMode);

				foreach ($modes as $mode) {
					switch ($mode) {
						case 'xml':
							$content .= $this->exportToXML($query);
							break;
						case 'csv':
							$content .= $this->exportToCSV($query);
							break;
						case 'excel':
							$content .= $this->exportToEXCEL($query);
							break;
					}
				}

				$this->content .= $content;
			}
		}
	}

	function exportToXML($query) {
		global $LANG;
		$xmlData = $this->exportRecordsToXML($query);
		$content = '';
		$content .= '<br/><input type="submit" name="downloadxml" value="' . sprintf($LANG->getLL('download'), 'XML') . '" onClick="window.location.href=\'index.php\';"><br />';

		// Downloads file:
		if (t3lib_div::_GP('downloadxml')) {
			$filename = 'TYPO3_' . $query['FROM'] . '_export_' . date('dmy-Hi') . '.xml';
			$mimeType = 'application/octet-stream';
			header('Content-Type: ' . $mimeType);
			header('Content-Disposition: attachment; filename=' . $filename);
			echo utf8_decode($xmlData);
			exit;
		}
		return $content;
	}


	function exportToCSV($query) {
		global $LANG;
		$rowArr = array();
		$content = '';
		$content .= '<br/><input type="submit" name="downloadcsv" value="' . sprintf($LANG->getLL('download'), 'CSV') . '" onClick="window.location.href=\'index.php\';"><br />';

		if (t3lib_div::_GP('downloadcsv')) {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($query['SELECT'], $query['FROM'], $query['WHERE'], $query['GROUPBY'], $query['ORDERBY'], $query['LIMIT']);
			if ($query['FROM'] == 'tx_powermail_mails') {
				$convertData = FALSE;
			} else {
				$convertData = TRUE;
			}
			$rows = $this->getAllResults($res, $query['FROM'], $convertData);

			foreach ($rows as $row) {
				$rowArr [] = t3lib_div::csvValues($row);
			}

			if (count($rowArr)) {
				$filename = 'TYPO3_' . $query['FROM'] . '_export_' . date('dmy-Hi') . '.csv';
				$mimeType = 'application/octet-stream';
				header('Content-Type: ' . $mimeType);
				header('Content-Disposition: attachment; filename=' . $filename);
				echo utf8_decode(implode(CRLF, $rowArr));
				exit;
			}
		}

		return $content;
	}

	function exportToEXCEL($query) {
		global $LANG;
		$rowArr = array();
		$content = '';
		$content .= '<br/><input type="submit" name="downloadexcel" value="' . sprintf($LANG->getLL('download'), 'EXCEL') . '" onClick="window.location.href=\'index.php\';"><br />';

		if (t3lib_div::_GP('downloadexcel')) {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($query['SELECT'], $query['FROM'], $query['WHERE'], $query['GROUPBY'], $query['ORDERBY'], $query['LIMIT']);
			if ($query['FROM'] == 'tx_powermail_mails') {
				$convertData = FALSE;
			} else {
				$convertData = TRUE;
			}
			$rows = $this->getAllResults($res, $query['FROM'], $convertData);

			// TODO : process a powermail table
			/*if ($query['FROM'] == 'tx_powermail_mails') {
				foreach ($rows as $row) {
					$piVars = t3lib_div::xml2array($row['piVars'], 'piVars');
				}
			}*/

			$dirName = PATH_site . 'typo3temp/';
			$filename = 'TYPO3_' . $query['FROM'] . '_export_' . date('dmy-Hi') . '.xls';

			require_once(PATH_site . "typo3conf/ext/recordsmanager/lib/php_writeexcel-0.3.0/class.writeexcel_workbook.inc.php");
			require_once(PATH_site . "typo3conf/ext/recordsmanager/lib/php_writeexcel-0.3.0/class.writeexcel_worksheet.inc.php");

			$fname = $dirName . $filename;
			$workbook = &new writeexcel_workbook($fname);
			$worksheet =& $workbook->addworksheet();

			$header =& $workbook->addformat();
			$header->set_bold();
			$header->set_size(12);

			$line = 0;
			foreach ($rows as $row) {
				$col = 0;
				foreach ($row as $field => $value) {
					if ($line == 0) {
						$worksheet->write($line, $col++, utf8_decode($value), $header);
					} else {
						$worksheet->write($line, $col++, utf8_decode($value));
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

		return $content;
	}

	function drawTable($query, $title) {
		global $BE_USER;
		$content = '';

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,pid', $query['FROM'], $query['WHERE'], $query['GROUPBY'], $query['ORDERBY'], $query['LIMIT']);
		$listOfUids = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$pageinfo = t3lib_BEfunc::readPageAccess($row['pid'], $BE_USER->getPagePermsClause(1));
			if ($pageinfo !== false) { // check the right of the page container
				$listOfUids [] = $row['uid'];
			}
		}


		// Page browser
		$pointer = t3lib_div::_GP('pointer');
		$limit = ($pointer !== null) ? $pointer . ',' . $this->nbElementsPerPage : '0,' . $this->nbElementsPerPage;
		$current = ($pointer !== null) ? intval($pointer) : 0;
		$pageBrowser = $this->renderListNavigation($GLOBALS['TYPO3_DB']->sql_num_rows($res), $this->nbElementsPerPage, $current, true);
		$query['WHERE'] .= ' AND uid IN (' . implode(',', $listOfUids) . ')';
		$query['LIMIT'] = $limit;
		$content .= $pageBrowser;
		$GLOBALS['TYPO3_DB']->sql_free_result($res);

		if (count($listOfUids) > 0) {
			// List view
			$result = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($query);
			$content .= $this->formatAllResults($result, $query['FROM'], $title);
			$GLOBALS['TYPO3_DB']->sql_free_result($result);
		}

		return $content;
	}

	function formatAllResults($res, $table, $title) {
		$content = '';
		$content .= $this->drawDBListTitle($title);
		$first = 1;
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			if ($first) {
				$first = 0;
				$headers = $this->getResultRowTitles($row, $table);
				$content .= $this->drawDBListHeader($headers);
			}
			$records = $this->getResultRow($row, $table);
			$content .= $this->drawDBListRows($records);
		}
		$content .= '</table>';
		return $this->drawDBListTable($content);
	}

	function renderListNavigation($totalItems, $iLimit, $firstElementNumber, $alwaysShow = false) {
		$totalPages = ceil($totalItems / $iLimit);

		$content = '';
		$returnContent = '';
		// Show page selector if not all records fit into one page

		$first = $previous = $next = $last = $reload = '';
		$listURLOrig = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'mod.php?M=' . t3lib_div::_GP('M');
		$listURL = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'mod.php?M=' . t3lib_div::_GP('M');
		$listURL .= '&nbPerPage=' . $iLimit;
		$startdate = t3lib_div::_GP('startdate');
		$enddate = t3lib_div::_GP('enddate');
		if ($startdate !== null) {
			$listURL .= '&startdate=' . $startdate;
		}
		if ($enddate !== null) {
			$listURL .= '&enddate=' . $enddate;
		}
		$orderby = t3lib_div::_GP('orderby');
		$listURL .= ($orderby !== null) ? '&orderby=' . $orderby : '';
		$listURLOrig .= ($orderby !== null) ? '&orderby=' . $orderby : '';
		$currentPage = floor(($firstElementNumber + 1) / $iLimit) + 1;
		// First
		if ($currentPage > 1) {
			$labelFirst = $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_common.xml:first');
			$first = '<a href="' . $listURL . '&pointer=0"><img width="16" height="16" title="' . $labelFirst . '" alt="' . $labelFirst . '" src="sysext/t3skin/icons/gfx/control_first.gif"></a>';
		} else {
			$first = '<img width="16" height="16" title="" alt="" src="sysext/t3skin/icons/gfx/control_first_disabled.gif">';
		}
		// Previous
		if (($currentPage - 1) > 0) {
			$labelPrevious = $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_common.xml:previous');
			$previous = '<a href="' . $listURL . '&pointer=' . (($currentPage - 2) * $iLimit) . '"><img width="16" height="16" title="' . $labelPrevious . '" alt="' . $labelPrevious . '" src="sysext/t3skin/icons/gfx/control_previous.gif"></a>';
		} else {
			$previous = '<img width="16" height="16" title="" alt="" src="sysext/t3skin/icons/gfx/control_previous_disabled.gif">';
		}
		// Next
		if (($currentPage + 1) <= $totalPages) {
			$labelNext = $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_common.xml:next');
			$next = '<a href="' . $listURL . '&pointer=' . (($currentPage) * $iLimit) . '"><img width="16" height="16" title="' . $labelNext . '" alt="' . $labelNext . '" src="sysext/t3skin/icons/gfx/control_next.gif"></a>';
		} else {
			$next = '<img width="16" height="16" title="" alt="" src="sysext/t3skin/icons/gfx/control_next_disabled.gif">';
		}
		// Last
		if ($currentPage != $totalPages) {
			$labelLast = $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_common.xml:last');
			$last = '<a href="' . $listURL . '&pointer=' . (($totalPages - 1) * $iLimit) . '"><img width="16" height="16" title="' . $labelLast . '" alt="' . $labelLast . '" src="sysext/t3skin/icons/gfx/control_last.gif"></a>';
		} else {
			$last = '<img width="16" height="16" title="" alt="" src="sysext/t3skin/icons/gfx/control_last_disabled.gif">';
		}

		$pageNumberInput = '<span>' . $currentPage . '</span>';
		$pageIndicator = '<span class="pageIndicator">'
		                 . sprintf($GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_list.xml:pageIndicator'), $pageNumberInput, $totalPages)
		                 . '</span>';

		if ($totalItems > ($firstElementNumber + $iLimit)) {
			$lastElementNumber = $firstElementNumber + $iLimit;
		} else {
			$lastElementNumber = $totalItems;
		}

		$rangeIndicator = '<span class="pageIndicator">'
		                  . sprintf($GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_list.xml:rangeIndicator'), $firstElementNumber + 1, $lastElementNumber)
		                  . '</span>';

		$reload = '<input type="text" name="nbPerPage" id="nbPerPage" size="5" value="' . $iLimit . '"/> / page '
		          . '<a href="#"  onClick="jumpToUrl(\'' . $listURLOrig . '&nbPerPage=\'+document.getElementById(\'nbPerPage\').value+\'&startdate=\'+document.getElementById(\'tceforms-datetimefield-startdate\').value+\'&enddate=\'+document.getElementById(\'tceforms-datetimefield-enddate\').value);">'
		          . '<img width="16" height="16" title="" alt="" src="sysext/t3skin/icons/gfx/refresh_n.gif"></a>';

		if (t3lib_div::int_from_ver(TYPO3_version) < 4004000) {
			$iconStartDate = '<img' . t3lib_iconWorks::skinImg($this->backPath, 'gfx/datepicker.gif', '', 0) . ' style="cursor:pointer; vertical-align:middle;" alt=""' . ' id="picker-tceforms-datetimefield-startdate" />';
			$iconEndDate = '<img' . t3lib_iconWorks::skinImg($this->backPath, 'gfx/datepicker.gif', '', 0) . ' style="cursor:pointer; vertical-align:middle;" alt=""' . ' id="picker-tceforms-datetimefield-enddate" />';
		} else {
			$iconStartDate = t3lib_iconWorks::getSpriteIcon('actions-edit-pick-date', array('style' => 'cursor:pointer;', 'id' => 'picker-tceforms-datetimefield-startdate'));
			$iconEndDate = t3lib_iconWorks::getSpriteIcon('actions-edit-pick-date', array('style' => 'cursor:pointer;', 'id' => 'picker-tceforms-datetimefield-enddate'));
		}

		$dates = $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_common.xml:start') . ':&nbsp;<input type="text" id="tceforms-datetimefield-startdate" value="' . $startdate . '" name="startdate">&nbsp;'
		         . $iconStartDate
		         . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_common.xml:stop') . ':&nbsp;<input type="text" id="tceforms-datetimefield-enddate" value="' . $enddate . '" name="enddate">&nbsp;'
		         . $iconEndDate;

		$content .= '<div id="typo3-dblist-pagination">'
		            . $first . $previous
		            . '<span class="bar">&nbsp;</span>'
		            . $rangeIndicator . '<span class="bar">&nbsp;</span>'
		            . $pageIndicator . '<span class="bar">&nbsp;</span>'
		            . $next . $last . '<span class="bar">&nbsp;</span>'
		            . $dates . '<span class="bar">&nbsp;</span>'
		            . $reload
		            . '</div>';

		$returnContent = $content;

		return $returnContent;
	}

	/**
	 * drawDBListTable
	 *
	 * @param  $content
	 * @return string
	 */

	function drawDBListTable($content) {
		return '<table cellspacing="1" cellpadding="2" border="0" class="typo3-dblist">' . $content . '</table>';
	}

	/**
	 * drawDBListTitle
	 *
	 * @param  $content
	 * @param int $colspan
	 * @return string
	 */

	function drawDBListTitle($content, $colspan = 100) {
		return '<tr class="t3-row-header"><td colspan="' . $colspan . '">' . $content . '</td></tr>';
	}

	/**
	 * drawDBListHeader
	 *
	 * @param  $headers
	 * @return string
	 */

	function drawDBListHeader($headers) {
		$content = '';
		$content .= '<tr class="c-headLine">';
		foreach ($headers as $header) {
			$content .= '<td class="cell">' . $header . '</td>';
		}
		$content .= '</tr>';
		return $content;
	}

	/**
	 * drawDBListRows
	 *
	 * @param  $rows
	 * @return string
	 */

	function drawDBListRows($rows) {
		$content = '';
		$content .= '<tr class="db_list_normal">';
		foreach ($rows as $row) {
			$content .= '<td class="cell">' . $row . '</td>';
		}
		$content .= '</tr>';
		return $content;
	}

	/**
	 * Get all the data according to the TCA (time,relation, etc...) from a sql ressource.
	 *
	 * @param  $res
	 * @param  $table
	 * @return array
	 */

	function getAllResults($res, $table, $convertData = true) {
		$first = 1;
		$recordList = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			if ($first) {
				$first = 0;
				$recordList [] = self::getResultRowTitles($row, $table);
			}
			if ($convertData === true) {
				$recordList [] = self::getResultRow($row, $table);
			} else {
				$recordList [] = $row;
			}
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		return $recordList;
	}

	function getResultRowTitles($row, $table) {
		global $TCA;
		$tableHeader = array();
		$conf = $TCA[$table];
		$listURL = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'mod.php?M=' . t3lib_div::_GP('M');
		foreach ($row as $fieldName => $fieldValue) {
			$title = $GLOBALS['LANG']->sL($conf['columns'][$fieldName]['label'] ? $conf['columns'][$fieldName]['label'] : $fieldName, 1);
			$title .= '&nbsp;&nbsp;<a href="' . $listURL . '&orderby=' . $fieldName . '%20DESC"><img width="7" height="4" alt="" src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/reddown.gif"></a>';
			$title .= '&nbsp;&nbsp;<a href="' . $listURL . '&orderby=' . $fieldName . '%20ASC"><img width="7" height="4" alt="" src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/redup.gif"></a>';
			$tableHeader[$fieldName] = $title;
		}
		return $tableHeader;
	}

	function getResultRow($row, $table) {
		$record = array();
		foreach ($row as $fieldName => $fieldValue) {
			if ((TYPO3_MODE == 'FE')) {
				$GLOBALS['TSFE']->includeTCA();
			}
			$record[$fieldName] = t3lib_BEfunc::getProcessedValueExtra($table, $fieldName, $fieldValue, 0, $row['uid']);
		}
		return $record;
	}

	function exportRecordsToXML($query) {
		$xmlObj = t3lib_div::makeInstance('t3lib_xml', 'typo3_export');
		$xmlObj->setRecFields($query['FROM'], $query['SELECT']);
		$xmlObj->renderHeader();
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($query['SELECT'], $query['FROM'], $query['WHERE'], $query['GROUPBY'], $query['ORDERBY'], $query['LIMIT']);
		$xmlObj->renderRecords($query['FROM'], $res);
		$xmlObj->renderFooter();
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		return $xmlObj->getResult();
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/recordsmanager/mod3/index.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/recordsmanager/mod3/index.php']);
}
// Make instance:
$SOBE = t3lib_div::makeInstance('tx_recordsmanager_module3');
$SOBE->init();
// Include files?
foreach ($SOBE->include_once as $INC_FILE) include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>