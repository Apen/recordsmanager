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

class Tx_Recordsmanager_Controller_EditController extends Tx_Extbase_MVC_Controller_ActionController
{
	protected $currentConfig;

	/**
	 * action index
	 *
	 * @return void
	 */
	public function indexAction() {
		$allConfigs = Tx_Recordsmanager_Utility_Config::getAllConfigs(0);
		$this->currentConfig = $allConfigs[0];
		$this->setCurrentConfig();

		$query = $this->buildQuery();
		$query->execQuery();

		$this->view->assign('headers', $query->getHeaders());
		$this->view->assign('rows', $query->getRows());
		$this->view->assign('currentconfig', $this->currentConfig);
		$this->view->assign('arguments', $this->request->getArguments());
		$this->view->assign('menuitems', $allConfigs);
		$this->view->assign('returnurl', rawurlencode($this->getReturnUrl()));
		$this->view->assign('deleteurl', $this->getDeleteUrl());
		$this->view->assign('disableFields', implode(',', tx_recordsmanager_flexfill::getDiffFieldsFromTable($this->currentConfig['sqltable'], $this->currentConfig['sqlfieldsinsert'])));
	}

	/**
	 * Build the query array
	 *
	 * @return Tx_Recordsmanager_Utility_Query
	 */
	public function buildQuery() {
		$arguments = $this->request->getArguments();

		$queryObject = new Tx_Recordsmanager_Utility_Query();
		$queryObject->setConfig($this->currentConfig);
		$queryObject->buildQuery();

		if (!empty($arguments['orderby'])) {
			$queryObject->setOrderBy(rawurldecode($arguments['orderby']));
		}

		return $queryObject;
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
	 * Get url to delete a record
	 *
	 * @return string
	 */
	public function getDeleteUrl() {
		$arguments = $this->request->getArguments();
		$returnUrl = $this->getReturnUrl();
		$deleteUrl = 'tce_db.php?cmd["+table+"]["+id+"][delete]=1&redirect=' . rawurlencode($returnUrl) . '&vC=' . $GLOBALS['BE_USER']->veriCode() . '&prErr=1&uPT=1';
		if (t3lib_div::int_from_ver(TYPO3_version) >= 4005000) {
			$deleteUrl .= t3lib_BEfunc::getUrlToken('tceAction');
		}
		return $deleteUrl;
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

