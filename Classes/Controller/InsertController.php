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
use TYPO3\CMS\Backend\Form\FormResultCompiler;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;

class InsertController extends AbstractController
{
    protected array $currentConfig = [];

    /**
     * action index
     */
    public function indexAction()
    {
        $allConfigs = Config::getAllConfigs(1);
        $this->createMenu('index', $allConfigs);

        $formResultCompiler = GeneralUtility::makeInstance(FormResultCompiler::class);
        $formResultCompiler->printNeededJSFunctions();

        if (empty($allConfigs)) {
            return null;
        }

        $this->currentConfig = $allConfigs[0];
        $this->setCurrentConfig();

        $arguments = $this->request->getArguments();

        $addWhere = ' AND ' . $GLOBALS['BE_USER']->getPagePermsClause(1) . ' ';

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($this->currentConfig['sqltable']);
        $queryBuilder->select($this->currentConfig['sqltable'] . '.pid', 'pages.title')
            ->addSelectLiteral('count(' . $this->currentConfig['sqltable'] . '.pid) as "nbrecords"')
            ->from($this->currentConfig['sqltable'])
            ->from('pages')
            ->where(
                'pages.uid=' . $this->currentConfig['sqltable'] . '.pid AND ' . $this->currentConfig['sqltable'] . '.deleted=0 ' . $addWhere
            )
            ->groupBy($this->currentConfig['sqltable'] . '.pid');
        $pids = $queryBuilder->executeQuery()->fetchAll();

        $pidsFind = [];
        $pidsAdmin = [];

        // All find PIDs
        foreach ($pids as $pid) {
            $rootlineUtility = GeneralUtility::makeInstance(RootlineUtility::class, $pid['pid']);
            $rootline = $rootlineUtility->get();
            $path = $this->getPathFromRootline($rootline, 30);
            $pidsFind[] = ['pid' => $pid['pid'], 'path' => $path, 'nbrecords' => $pid['nbrecords']];
        }

        // Admin specified PIDs
        if ($this->currentConfig['insertdefaultpid'] !== '') {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
            $queryBuilder->select('uid', 'title')
                ->from('pages')
                ->where(
                    $queryBuilder->expr()->in('uid', $queryBuilder->createNamedParameter($this->currentConfig['insertdefaultpid']))
                )
                ->andWhere(
                    '1=1 ' . $addWhere
                )
                ->orderBy('title', 'ASC');
            $pids = $queryBuilder->executeQuery()->fetchAll();
            foreach ($pids as $pid) {
                $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($this->currentConfig['sqltable']);
                $queryBuilder
                    ->select('uid')
                    ->from($this->currentConfig['sqltable'])
                    ->where(
                        $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($pid['uid'], \PDO::PARAM_INT))
                    );
                $nb = $queryBuilder->executeQuery()->rowCount();
                $rootlineUtility = GeneralUtility::makeInstance(RootlineUtility::class, $pid['uid']);
                $rootline = $rootlineUtility->get();
                $path = $this::getPathFromRootline($rootline, 30);
                $pidsAdmin[] = ['pid' => $pid['uid'], 'path' => $path, 'nbrecords' => $nb];
            }
        }

        $this->view->assign('pidsfind', $pidsFind);
        $this->view->assign('pidsadmin', $pidsAdmin);
        $this->view->assign('currentconfig', $this->currentConfig);
        $this->view->assign('arguments', $arguments);
        $this->view->assign('returnurl', $this->getReturnUrl());
        $this->view->assign('browserurl', $this->getBrowserUrl());
        $this->view->assign('baseediturl', Misc::getModuleUrl('record_edit') . '&');

        $disableFields = '';
        if ($this->currentConfig['sqlfieldsinsert'] !== '') {
            $disableFields = implode(',', Flexfill::getDiffFieldsFromTable($this->currentConfig['sqltable'], $this->currentConfig['sqlfieldsinsert']));
        }
        $this->view->assign('disableFields', $disableFields);

        $this->moduleTemplate->setContent($this->view->render());

        return $this->htmlResponseCompatibility($this->moduleTemplate->renderContent());
    }

    public function getReturnUrl(): string
    {
        return rawurlencode($GLOBALS['TYPO3_REQUEST']->getAttribute('normalizedParams')->getRequestUri());
    }

    /**
     * Creates a "path" string for the input root line array titles.
     * Used for writing statistics.
     *
     * @param array $rl  A rootline array!
     * @param int   $len The max length of each title from the rootline.
     *
     * @return string The path in the form "/page title/This is another pageti.../Another page
     *
     * @see \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController::getConfigArray()
     */
    public function getPathFromRootline(array $rl, int $len = 20): string
    {
        $path = '';
        $c = count($rl);
        for ($a = 0; $a < $c; ++$a) {
            if ($rl[$a]['uid']) {
                $path .= '/' . GeneralUtility::fixed_lgd_cs(strip_tags($rl[$a]['title']), $len);
            }
        }

        return $path;
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
                    $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($arguments['menuitem'], \PDO::PARAM_INT))
                );
            $this->currentConfig = $queryBuilder->executeQuery()->fetch();
        }
    }

    /**
     * Get url to browser pages
     */
    public function getBrowserUrl(): string
    {
        return Misc::getModuleUrl('wizard_element_browser');
    }
}
