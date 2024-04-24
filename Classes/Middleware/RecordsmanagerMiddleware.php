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
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
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
     *
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $recordsmanagerkey = $request->getParsedBody()['recordsmanagerkey'] ?? $request->getQueryParams()['recordsmanagerkey'] ?? null;

        if ($recordsmanagerkey === null) {
            return $handler->handle($request);
        }

        if (!isset($GLOBALS['LANG'])) {
            $languageServiceFactory = GeneralUtility::makeInstance(
                LanguageServiceFactory::class
            );
            $request = $GLOBALS['TYPO3_REQUEST'];
            $GLOBALS['LANG'] = $languageServiceFactory->createFromSiteLanguage(
                $request->getAttribute('language')
                ?? $request->getAttribute('site')->getDefaultLanguage()
            );
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
                header('WWW-Authenticate: Basic realm="Unauthorized"');
                header('HTTP/1.0 401 Unauthorized');
                exit;
            }
        }

        $this->exportRecords($query, Config::getFormat());
        exit;
    }

    /**
     * Get the config eid passed in URL
     */
    public function getConfig(): string
    {
        $config = GeneralUtility::_GP('recordsmanagerkey');
        if (!empty($config)) {
            return (string)$config;
        }

        die('You need to specify a tx_recordsmanager_config eidkey in a config url parameter (&eidkey=x)');
    }

    /**
     * Export records if needed
     */
    public function exportRecords(Query $query, string $mode): void
    {
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
     */
    public function exportToJson(Query $query): void
    {
        echo json_encode($query->getRows(), JSON_THROW_ON_ERROR);
    }

    /**
     * Build the query array
     */
    public function buildQuery(): Query
    {
        $queryObject = new Query();
        $queryObject->setConfig($this->currentConfig);
        $queryObject->setExportMode(true);
        $queryObject->buildQuery();

        if (trim($this->currentConfig['extralimit']) === '' && (GeneralUtility::_GP('limit') ?? false)) {
            $queryObject->setLimit((int)GeneralUtility::_GP('limit'));
        }

        if (GeneralUtility::_GP('pid') ?? false) {
            $queryObject->setWhere($queryObject->getWhere() . ' AND pid=' . (int)GeneralUtility::_GP('pid'));
        }

        if (trim($this->currentConfig['exportfilterfield'] ?? '') !== '' && (GeneralUtility::_GP('start') ?? false)) {
            $queryObject->setWhere($queryObject->getWhere() . ' AND ' . $this->currentConfig['exportfilterfield'] . '>=' . (int)GeneralUtility::_GP('start'));
        }

        if (trim($this->currentConfig['exportfilterfield'] ?? '') !== '' && (GeneralUtility::_GP('end') ?? false)) {
            $queryObject->setWhere($queryObject->getWhere() . ' AND ' . $this->currentConfig['exportfilterfield'] . '<=' . (int)GeneralUtility::_GP('end'));
        }

        return $queryObject;
    }

    /**
     * Set the current config record
     */
    public function setCurrentConfig(string $eidkey): void
    {
        $this->currentConfig = Config::getEidConfig($eidkey);
        if (empty($this->currentConfig)) {
            die('You need to specify a CORRECT tx_recordsmanager_config eidkey in a config url parameter (&eidkey=x)');
        }
    }
}
