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

class Tx_Recordsmanager_Controller_InsertController extends Tx_Extbase_MVC_Controller_ActionController
{
	protected $currentConfig;

	/**
	 * action index
	 *
	 * @return void
	 */
	public function indexAction() {
		$allConfigs = Tx_Recordsmanager_Utility_Config::getAllConfigs(1);
		$this->currentConfig = $allConfigs[0];
		$this->setCurrentConfig();
		$arguments = $this->request->getArguments();

		$temp_sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
		$addWhere = ' AND ' . $GLOBALS['BE_USER']->getPagePermsClause(1) . ' ';

		$pids = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'count(' . $this->currentConfig['sqltable'] . '.pid) as "nbrecords",' . $this->currentConfig['sqltable'] . '.pid,pages.title',
			$this->currentConfig['sqltable'] . ',pages',
			'pages.uid=' . $this->currentConfig['sqltable'] . '.pid AND ' . $this->currentConfig['sqltable'] . '.deleted=0 ' . $addWhere . 'GROUP BY ' . $this->currentConfig['sqltable'] . '.pid '
		);

		$content = '';

		$pidsFind = array();
		$pidsAdmin = array();

		// All find PIDs
		if (count($pids) > 0) {
			foreach ($pids as $pid) {
				$rootline = $temp_sys_page->getRootLine($pid['pid']);
				$path = $temp_sys_page->getPathFromRootline($rootline, 30);
				$pidsFind[] = array('pid' => $pid['pid'], 'path' => $path, 'nbrecords' => $pid['nbrecords']);
			}
		}

		// Admin specified PIDs
		if ($this->currentConfig['insertdefaultpid'] != '') {
			$pids = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
				'pages.uid,pages.title',
				'pages',
				'pages.deleted=0 AND pages.uid IN (' . $this->currentConfig['insertdefaultpid'] . ')' . $addWhere
			);
			if (count($pids) > 0) {
				foreach ($pids as $pid) {
					$nb = count($GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid', $this->currentConfig['sqltable'] . '', 'pid=' . $pid['uid'] . ' AND deleted=0 '));
					$rootline = $temp_sys_page->getRootLine($pid['uid']);
					$path = $temp_sys_page->getPathFromRootline($rootline, 30);
					$pidsAdmin[] = array('pid' => $pid['uid'], 'path' => $path, 'nbrecords' => $nb);
				}
			}
		}

		$this->view->assign('pidsfind', $pidsFind);
		$this->view->assign('pidsadmin', $pidsAdmin);
		$this->view->assign('currentconfig', $this->currentConfig);
		$this->view->assign('arguments', $arguments);
		$this->view->assign('menuitems', $allConfigs);
		$this->view->assign('returnurl', $this->getReturnUrl());

		// redirect to tce form
		if (!empty($arguments['create'])) {
			$this->redirectToForm($arguments['create']);
		}

	}

	/**
	 * Get return url
	 *
	 * @return string
	 */
	public function getReturnUrl() {
		$arguments = $this->request->getArguments();
		return $this->uriBuilder->reset()->setAddQueryString(TRUE)->uriFor();
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

	/**
	 * Redirect to the insert form with correct params
	 *
	 * @param int $id
	 */
	public function redirectToForm($id) {
		$arguments = $this->request->getArguments();
		$returnUrl = 'mod.php?M=txrecordsmanagerM1_RecordsmanagerInsert';
		if (!empty($arguments['menuitem'])) {
			$returnUrl .= '&tx_recordsmanager_txrecordsmanagerm1_recordsmanagerinsert[menuitem]=' . $arguments['menuitem'];
		}
		$editLink = 'alt_doc.php?returnUrl=' . rawurlencode($returnUrl) . '&edit[' . $this->currentConfig['sqltable'] . '][' . $id . ']=new';
		// disabledFields
		$this->disableFields = implode(',', tx_recordsmanager_flexfill::getDiffFieldsFromTable($this->currentConfig['sqltable'], $this->currentConfig['sqlfieldsinsert']));
		if ($this->currentConfig['sqlfieldsinsert'] !== '') {
			$editLink .= '&recordsHide=' . $this->disableFields;
		}
		$link = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . $GLOBALS['BACK_PATH'] . $editLink;
		t3lib_utility_Http::redirect($link);
	}


}
