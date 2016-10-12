<?php

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
class Tx_Recordsmanager_Eid_Index
{
    /**
     * Current configuration record
     *
     * @var array
     */
    protected $currentConfig;

    public function __construct()
    {
        require_once('typo3conf/ext/recordsmanager/Classes/Utility/Query.php');
        require_once('typo3conf/ext/recordsmanager/Classes/Utility/Config.php');
        require_once('typo3conf/ext/recordsmanager/Classes/Utility/Powermail.php');
        require_once('typo3conf/ext/recordsmanager/Classes/Utility/Misc.php');
        require_once('typo3conf/ext/recordsmanager/Classes/Controller/ExportController.php');
        $this->initTSFE();
    }

    /**
     * Exec the eid
     */
    public function main()
    {
        $this->setCurrentConfig($this->getConfig());
        $query = $this->buildQuery();
        $this->exportRecords($query, $this->getFormat());
    }

    /**
     * Get the export format passed in URL
     *
     * @return string
     */
    public function getFormat()
    {
        $format = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('format');
        if (!empty($format)) {
            return strval($format);
        } else {
            return 'excel';
        }
    }

    /**
     * Get the config eid passed in URL
     *
     * @return string
     */
    public function getConfig()
    {
        $config = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('eidkey');
        if (!empty($config)) {
            return strval($config);
        } else {
            die('You need to specify a tx_recordsmanager_config eidkey in a config url parameter (&eidkey=x)');
        }
    }

    /**
     * Export records if needed
     *
     * @param \Sng\Recordsmanager\Utility\Query $query
     */
    public function exportRecords($query, $mode)
    {
        $pid = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('pid');
        if (!empty($pid)) {
            $query->setWhere($query->getWhere() . ' AND pid=' . intval($pid));
        }
        $query->execQuery();
        switch ($mode) {
            case 'xml':
                \Sng\Recordsmanager\Controller\ExportController::exportToXML($query, true);
                break;
            case 'csv':
                \Sng\Recordsmanager\Controller\ExportController::exportToCSV($query, true);
                break;
            case 'excel':
                \Sng\Recordsmanager\Controller\ExportController::exportToEXCEL($query);
                break;
            case 'json':
                $this->exportToJson($query);
                header('Content-Type: application/json');
                break;
        }
    }

    /**
     * Export to JSON
     *
     * @param \Sng\Recordsmanager\Utility\Query $query
     */
    public function exportToJson(\Sng\Recordsmanager\Utility\Query $query)
    {
        echo json_encode($query->getRows());
    }

    /**
     * Build the query array
     *
     * @return \Sng\Recordsmanager\Utility\Query
     */
    public function buildQuery()
    {
        $queryObject = new \Sng\Recordsmanager\Utility\Query();
        $queryObject->setConfig($this->currentConfig);
        $queryObject->setExportMode(true);
        $queryObject->buildQuery();
        return $queryObject;
    }

    /**
     * Set the current config record
     *
     * @param string $eidkey
     */
    public function setCurrentConfig($eidkey)
    {
        $this->currentConfig = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', 'tx_recordsmanager_config', 'type=3 AND deleted=0 AND eidkey="' . mysqli_real_escape_string($GLOBALS['TYPO3_DB']->getDatabaseHandle(), $eidkey) . '"');
        if (empty($this->currentConfig)) {
            die('You need to specify a CORRECT tx_recordsmanager_config eidkey in a config url parameter (&eidkey=x)');
        }
    }

    /**
     * Init the TSFE array
     */
    protected function initTSFE()
    {
        $GLOBALS['TSFE'] = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController', $GLOBALS['TYPO3_CONF_VARS'], 0, 0);
        \TYPO3\CMS\Frontend\Utility\EidUtility::initTCA();
        \TYPO3\CMS\Frontend\Utility\EidUtility::initLanguage();
        $GLOBALS['TSFE']->initFEuser();
        $GLOBALS['TSFE']->set_no_cache();
        $GLOBALS['TSFE']->checkAlternativeIdMethods();
        $GLOBALS['TSFE']->determineId();
        $GLOBALS['TSFE']->initTemplate();
        $GLOBALS['TSFE']->getConfigArray();
        \TYPO3\CMS\Core\Core\Bootstrap::getInstance()->loadCachedTca();
        $GLOBALS['TSFE']->cObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer');
        $GLOBALS['TSFE']->settingLanguage();
        $GLOBALS['TSFE']->settingLocale();
    }

}

$index = new Tx_Recordsmanager_Eid_Index();
$index->main();


