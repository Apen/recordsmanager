<?php

namespace Sng\Recordsmanager\Eid;

/*
 * This file is part of the "recordsmanager" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Charset\CharsetConverter;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;

class Index
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
        require_once('typo3conf/ext/recordsmanager/Classes/Utility/Misc.php');
        require_once('typo3conf/ext/recordsmanager/Classes/Controller/ExportController.php');
        $this->initTSFE();
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function processRequest(ServerRequestInterface $request): ResponseInterface
    {
        $this->setCurrentConfig($this->getConfig());
        $query = $this->buildQuery();
        if (!empty($this->currentConfig['authlogin']) && !empty($this->currentConfig['authpassword'])) {
            $userAllowed = false;
            if (!empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW'])) {
                if (
                    ($_SERVER['PHP_AUTH_USER'] == $this->currentConfig['authlogin']) &&
                    ($_SERVER['PHP_AUTH_PW'] == $this->currentConfig['authpassword'])
                ) {
                    $userAllowed = true;
                }
            }
            if ($userAllowed === false) {
                // active HTTP auth
                header('WWW-Authenticate: Basic realm="My Realm"');
                header('HTTP/1.0 401 Unauthorized');
                exit;
            }
        }
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
        $controller = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Sng\Recordsmanager\Controller\ExportController');
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");
        switch ($mode) {
            case 'xml':
                $controller->exportToXML($query, true);
                break;
            case 'csv':
                $controller->exportToCSV($query, true);
                break;
            case 'excel':
                $controller->exportToEXCEL($query);
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
        $this->currentConfig = \Sng\Recordsmanager\Utility\Config::getEidConfig($eidkey);
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
        $GLOBALS['TSFE']->set_no_cache();
        $GLOBALS['TSFE']->fe_user = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication::class);
        $GLOBALS['TSFE']->fe_user->checkPid_value = 0;
        $GLOBALS['TSFE']->fe_user->start();
        $GLOBALS['TSFE']->fe_user->unpack_uc();
        $GLOBALS['TSFE']->determineId();
        $GLOBALS['TSFE']->getConfigArray();
        $GLOBALS['TSFE']->cObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer');
        $GLOBALS['TSFE']->settingLanguage();
        $GLOBALS['TSFE']->settingLocale();
        $languageService = GeneralUtility::makeInstance(LanguageService::class);
        $languageService->csConvObj = GeneralUtility::makeInstance(CharsetConverter::class);
        $GLOBALS['LANG'] = $languageService;
    }

}



