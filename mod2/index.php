<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011  <>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
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

$LANG->includeLLFile('EXT:recordsmanager/mod2/locallang.xml');
require_once(PATH_t3lib . 'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF, 1); // This checks permissions and exits if the users has no permission for entry.
// DEFAULT initialization of a module [END]

/**
 * Module 'Données' for the 'recordsmanager' extension.
 *
 * @author	 <>
 * @package	TYPO3
 * @subpackage	tx_recordsmanager
 */
class  tx_recordsmanager_module2 extends t3lib_SCbase
{
	public $pageinfo;
	protected $items = array();
	protected $currentItem = array();
	protected $disableFields = '';

	/**
	 * Initializes the Module
	 * @return	void
	 */

	function init() {
		global $BE_USER, $LANG, $BACK_PATH, $TCA_DESCR, $TCA, $CLIENT, $TYPO3_CONF_VARS;
		$items = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tx_recordsmanager_config', 'type=1 AND deleted=0 AND hidden=0', '', 'sorting');
		$usergroups = explode(',', $BE_USER->user['usergroup']);
		foreach ($items as $key => $row) {
			$configgroups = explode(',', $row['permsgroup']);
			$checkRights = array_intersect($usergroups, $configgroups);
			if (($BE_USER->isAdmin()) || (count($checkRights) > 0)) {
				$this->items [] = $row;
			}
		}
		parent::init();
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return	void
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
			$this->content .= '<table><tr><td class="functitle">' . $LANG->getLL('choose') . '</td><td align="right">' . $this->doc->funcMenu('', t3lib_BEfunc::getFuncMenu(0, 'SET[function]', $this->MOD_SETTINGS['function'], $this->MOD_MENU['function'])) . '</td></tr></table>';
			$this->content .= $this->doc->divider(5);
			$this->moduleContent();
		} else {
			$this->content .= $LANG->getLL('norecords');
		}
	}

	/**
	 * Prints out the module HTML
	 *
	 * @return	void
	 */
	function printContent() {
		$this->content .= $this->doc->endPage();
		echo $this->content;
	}

	/**
	 * Generates the module content
	 *
	 * @return	void
	 */
	function moduleContent() {
		global $BE_USER, $LANG, $BACK_PATH, $TCA_DESCR, $TCA, $CLIENT, $TYPO3_CONF_VARS;
		$temp_sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
		foreach ($this->items as $key => $row) {
			if ((string)$this->MOD_SETTINGS['function'] == $key) {
				$this->currentItem = $row;

				$addWhere = ' AND ' . $BE_USER->getPagePermsClause(1) . ' ';

				$pids = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
					'count(' . $row['sqltable'] . '.pid) as "nbrecords",' . $row['sqltable'] . '.pid,pages.title',
					$row['sqltable'] . ',pages',
					'pages.uid=' . $row['sqltable'] . '.pid AND ' . $row['sqltable'] . '.deleted=0 ' . $addWhere . 'GROUP BY ' . $row['sqltable'] . '.pid '
				);

				$content = '';

				// All find PIDs
				if (count($pids) > 0) {
					$content .= '<p>' . $LANG->getLL('addrecords') . '<p>';
					$content .= '<table cellspacing="1" cellpadding="1" border="0" class="typo3-dblist">';
					$content .= '<tr class="bgColor5 tableheader"><td width="80%">' . $LANG->getLL('storage') . '</td><td>' . $LANG->getLL('pid') . '</td><td>' . $LANG->getLL('datas') . '</td></tr>';
					foreach ($pids as $pid) {
						$rootline = $temp_sys_page->getRootLine($pid['pid']);
						$path = $temp_sys_page->getPathFromRootline($rootline, 30);
						$content .= '<tr class="bgColor4"><td><img width="16" height="16" alt="" title="" class="absmiddle" src="../../../../typo3/sysext/t3skin/icons/gfx/i/sysf.gif"><a href="mod.php?M=txrecordsmanagerM1_insert&create=' . $pid['pid'] . '">' . $path . '</a></td><td>' . $pid['pid'] . '</td><td>' . $pid['nbrecords'] . '</td></tr>';
					}
					$content .= '</table>';
				}

				if ($row['insertdefaultpid'] != '') {
					// Admin specified PIDs
					$pids = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
						'pages.uid,pages.title',
						'pages',
						'pages.deleted=0 AND pages.uid IN (' . $row['insertdefaultpid'] . ')' . $addWhere
					);

					if (count($pids) > 0) {
						$content .= '<br/><table cellspacing="1" cellpadding="1" border="0" class="typo3-dblist">';
						$content .= '<tr class="bgColor5 tableheader"><td width="80%">' . $LANG->getLL('storageadmin') . '</td><td>' . $LANG->getLL('pid') . '</td><td>' . $LANG->getLL('datas') . '</td></tr>';
						foreach ($pids as $pid) {
							$nb = count($GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid', $row['sqltable'] . '', 'pid=' . $pid['uid'] . ' AND deleted=0 '));
							$rootline = $temp_sys_page->getRootLine($pid['uid']);
							$path = $temp_sys_page->getPathFromRootline($rootline, 30);
							$content .= '<tr class="bgColor4"><td><img width="16" height="16" alt="" title="" class="absmiddle" src="../../../../typo3/sysext/t3skin/icons/gfx/i/sysf.gif"><a href="mod.php?M=txrecordsmanagerM1_insert&create=' . $pid['uid'] . '">' . $path . '</a></td><td>' . $pid['uid'] . '</td><td>' . $nb . '</td></tr>';
						}
						$content .= '</table>';
					}
				}

				$content .= '
					<script type="text/javascript">/*<![CDATA[*/
					var browserWin="";
					function setFormValueOpenBrowser(mode,params) {	//
						var url = "browser.php?mode="+mode+"&bparams="+params;
						browserWin = window.open(url,"Typo3WinBrowser","height=650,width="+(mode=="db"?650:600)+",status=0,menubar=0,resizable=1,scrollbars=1");
						browserWin.focus();
					}
					function setFormValueFromBrowseWin(fName,value,label,exclusiveValues)	{
						var idField = value.split("_");
						jumpToUrl("mod.php?M=txrecordsmanagerM1_insert&create="+idField[1]);
					}
					/*]]>*/</script>
				';

				if ($row['insertchoosepid'] == 1) {
					$content .= '<br/><table><tr>';
					$content .= '<td><input type="hidden" id="recordinsert"/>' . $LANG->getLL('selectpid') . '</td>';
					$content .= '<td><a onclick="setFormValueOpenBrowser(\'db\',\'recordinsert|||pages|\'); return false;" href="#"><img width="15" height="15" border="0" src="sysext/t3skin/icons/gfx/insert3.gif"></a></td></tr></table>';
				}

				$this->content .= $content;

				$create = t3lib_div::_GP('create');

				if ($create !== null) {
					$returnUrl = rawurlencode('mod.php?M=txrecordsmanagerM1_insert');
					$editLink = 'alt_doc.php?returnUrl=' . $returnUrl . '&edit[' . $row['sqltable'] . '][' . $create . ']=new';
					// disabledFields
					$this->disableFields = implode(',', tx_recordsmanager_flexfill::getDiffFieldsFromTable($row['sqltable'], $this->currentItem['sqlfieldsinsert']));
					if ($this->currentItem['sqlfieldsinsert'] !== '') {
						$editLink .= '&recordsHide=' . $this->disableFields;
					}
					$link = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . $GLOBALS['BACK_PATH'] . $editLink;
					t3lib_utility_Http::redirect($link);
				}
			}
		}
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/recordsmanager/mod2/index.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/recordsmanager/mod2/index.php']);
}


// Make instance:
$SOBE = t3lib_div::makeInstance('tx_recordsmanager_module2');
$SOBE->init();

// Include files?
foreach ($SOBE->include_once as $INC_FILE) include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>