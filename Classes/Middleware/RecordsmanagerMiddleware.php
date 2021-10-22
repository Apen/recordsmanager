<?php

declare(strict_types=1);

namespace Sng\Recordsmanager\Middleware;

/*
 * This file is part of the "recordsmanager" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sng\Recordsmanager\Controller\ExportController;
use Sng\Recordsmanager\Utility\Config;
use Sng\Recordsmanager\Utility\Query;
use TYPO3\CMS\Backend\FrontendBackendUserAuthentication;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class RecordsmanagerMiddleware implements MiddlewareInterface
{
    /**
     * Current configuration record
     *
     * @var array
     */
    protected $currentConfig = [];

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     *
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $recordsmanagerkey = $request->getParsedBody()['recordsmanagerkey'] ?? $request->getQueryParams()['recordsmanagerkey'] ?? null;

        if ($recordsmanagerkey === null) {
            return $handler->handle($request);
        }

        if (!is_object($GLOBALS['BE_USER'])) {
            $GLOBALS['BE_USER'] = GeneralUtility::makeInstance(FrontendBackendUserAuthentication::class);
            $GLOBALS['BE_USER']->start();
            $GLOBALS['BE_USER']->unpack_uc();
        }

        if (!is_object($GLOBALS['LANG'])) {
            $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageService::class);
            $GLOBALS['LANG']->init('default');
        }

        // Remove any output produced until now
        ob_clean();

        $this->setCurrentConfig($this->getConfig());
        $query = $this->buildQuery();
        if (!empty($this->currentConfig['authlogin']) && !empty($this->currentConfig['authpassword'])) {
            $userAllowed = false;
            if (!empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW']) && (($_SERVER['PHP_AUTH_USER'] === $this->currentConfig['authlogin']) &&
                    ($_SERVER['PHP_AUTH_PW'] === $this->currentConfig['authpassword']))) {
                $userAllowed = true;
            }
            if (!$userAllowed) {
                // active HTTP auth
                header('WWW-Authenticate: Basic realm="My Realm"');
                header('HTTP/1.0 401 Unauthorized');
                exit;
            }
        }

        $this->exportRecords($query, $this->getFormat());
        exit;
    }

    /**
     * Get the export format passed in URL
     *
     * @return string
     */
    public function getFormat()
    {
        $format = GeneralUtility::_GP('format');
        if (!empty($format)) {
            return (string)$format;
        }

        return 'excel';
    }

    /**
     * Get the config eid passed in URL
     *
     * @return string
     */
    public function getConfig()
    {
        $config = GeneralUtility::_GP('recordsmanagerkey');
        if (!empty($config)) {
            return (string)$config;
        }
        die('You need to specify a tx_recordsmanager_config eidkey in a config url parameter (&eidkey=x)');
    }

    /**
     * Export records if needed
     *
     * @param \Sng\Recordsmanager\Utility\Query $query
     * @param string                            $mode
     */
    public function exportRecords($query, $mode)
    {
        $pid = GeneralUtility::_GP('pid');
        if (!empty($pid)) {
            $query->setWhere($query->getWhere() . ' AND pid=' . (int)$pid);
        }
        $query->execQuery();
        $controller = GeneralUtility::makeInstance(
            ExportController::class,
            GeneralUtility::makeInstance(PageRenderer::class),
            GeneralUtility::makeInstance(IconFactory::class),
            GeneralUtility::makeInstance(FlashMessageService::class),
        );

        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');

        switch ($mode) {
            case 'xml':
                header('Content-Type: application/xml');
                $controller->exportToXML($query, true);

                break;
            case 'csv':
                $controller->exportToCSV($query, true);

                break;
            case 'excel':
                $controller->exportToEXCEL($query);

                break;
            case 'json':
                header('Content-Type: application/json');
                $this->exportToJson($query);

                break;
        }
    }

    /**
     * Export to JSON
     *
     * @param \Sng\Recordsmanager\Utility\Query $query
     */
    public function exportToJson(Query $query)
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
        $queryObject = new Query();
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
        $this->currentConfig = Config::getEidConfig($eidkey);
        if (empty($this->currentConfig)) {
            die('You need to specify a CORRECT tx_recordsmanager_config eidkey in a config url parameter (&eidkey=x)');
        }
    }
}
