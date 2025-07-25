<?php

declare(strict_types=1);

namespace Sng\Recordsmanager\Controller;

/*
 * This file is part of the "recordsmanager" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Sng\Recordsmanager\Utility\Config;
use Sng\Recordsmanager\Utility\Flexfill;
use Sng\Recordsmanager\Utility\Misc;
use Sng\Recordsmanager\Utility\Query;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class EditController extends AbstractController
{
    protected array $currentConfig;

    public function indexAction(int $currentPage = 1): \Psr\Http\Message\ResponseInterface
    {
        $allConfigs = Config::getAllConfigs(0);
        $this->createMenu('index', $allConfigs);

        if (empty($allConfigs)) {
            return $this->htmlResponse('');
        }

        $this->currentConfig = $allConfigs[0];
        $this->setCurrentConfig();

        $query = $this->buildQuery();
        $this->buildPagination($query, $currentPage);

        $this->moduleTemplate->assign('headers', $query->getHeaders());
        $this->moduleTemplate->assign('currentconfig', $this->currentConfig);
        $this->moduleTemplate->assign('arguments', $this->request->getArguments());
        $this->moduleTemplate->assign('menuitems', $allConfigs);
        $this->moduleTemplate->assign('returnurl', rawurlencode($this->getReturnUrl()));
        $this->moduleTemplate->assign('deleteurl', $this->getDeleteUrl());
        $this->moduleTemplate->assign('deleteurlbase', $this->getDeleteUrlBase());
        $this->moduleTemplate->assign('baseediturl', $this->getBaseEditUrl());

        $disableFields = '';
        if ($this->currentConfig['sqlfieldsinsert'] !== '') {
            $disableFields = implode(',', Flexfill::getDiffFieldsFromTable($this->currentConfig['sqltable'], $this->currentConfig['sqlfieldsinsert']));
        }

        $this->moduleTemplate->assign('disableFields', $disableFields);

        return $this->moduleTemplate->renderResponse('Edit/Index');
    }

    /**
     * Build the query array
     */
    public function buildQuery(): Query
    {
        $arguments = $this->request->getArguments();

        $queryObject = GeneralUtility::makeInstance(Query::class);
        $queryObject->setConfig($this->currentConfig);
        $queryObject->setCheckPids(false);
        $queryObject->buildQuery();

        if (!empty($arguments['orderby'])) {
            $queryObject->setOrderBy(rawurldecode($arguments['orderby']));
        }

        return $queryObject;
    }

    /**
     * Get return url
     */
    public function getReturnUrl(): string
    {
        $this->request->getArguments();

        return $this->uriBuilder->reset()->setAddQueryString(true)->uriFor();
    }

    /**
     * Get url to delete a record
     */
    public function getDeleteUrl(): string
    {
        $this->request->getArguments();
        $returnUrl = $this->getReturnUrl();
        $deleteUrl = Misc::getModuleUrl('tce_db');

        return $deleteUrl . ('&cmd["+table+"]["+id+"][delete]=1&redirect=' . rawurlencode($returnUrl) . '&prErr=1&uPT=1');
    }

    public function getDeleteUrlBase(): string
    {
        $this->request->getArguments();
        $returnUrl = $this->getReturnUrl();
        $deleteUrl = Misc::getModuleUrl('tce_db');

        return $deleteUrl . ('&redirect=' . rawurlencode($returnUrl) . '&prErr=1&uPT=1');
    }

    /**
     * Get url to edit a record
     */
    public function getBaseEditUrl(): string
    {
        return Misc::getModuleUrl('record_edit') . '&';
    }

    /**
     * Set the current config record
     */
    public function setCurrentConfig(): void
    {
        $arguments = $this->request->getArguments();
        if (!empty($arguments['menuitem'])) {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_recordsmanager_config');
            $queryBuilder
                ->select('*')
                ->from('tx_recordsmanager_config')
                ->where(
                    $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($arguments['menuitem'], \TYPO3\CMS\Core\Database\Connection::PARAM_INT))
                );
            $this->currentConfig = $queryBuilder->executeQuery()->fetchAssociative();
        }
    }
}
