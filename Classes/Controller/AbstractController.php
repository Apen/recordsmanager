<?php

declare(strict_types=1);

namespace Sng\Recordsmanager\Controller;

/*
 * This file is part of the "recordsmanager" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Sng\Recordsmanager\Pagination\QueryPaginator;
use Sng\Recordsmanager\Pagination\SimplePagination;
use Sng\Recordsmanager\Utility\Query;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Beuser\Domain\Model\ModuleData;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class AbstractController extends ActionController
{
    protected ?ModuleData $moduleData = null;

    protected ?ModuleTemplate $moduleTemplate = null;

    protected ModuleTemplateFactory $moduleTemplateFactory;

    protected PageRenderer $pageRenderer;

    protected IconFactory $iconFactory;

    protected FlashMessageService $flashMessageService;

    public function __construct(
        PageRenderer $pageRenderer,
        IconFactory $iconFactory,
        FlashMessageService $flashMessageService
    ) {
        $this->pageRenderer = $pageRenderer;
        $this->iconFactory = $iconFactory;
        $this->flashMessageService = $flashMessageService;
    }

    /**
     * Init module state.
     * This isn't done within __construct() since the controller
     * object is only created once in extbase when multiple actions are called in
     * one call. When those change module state, the second action would see old state.
     */
    protected function initializeAction(): void
    {
        $this->moduleTemplate = (GeneralUtility::makeInstance(ModuleTemplateFactory::class))->create($this->request);
        $this->moduleTemplate->setTitle(LocalizationUtility::translate('LLL:EXT:beuser/Resources/Private/Language/locallang_mod.xlf:mlang_tabs_tab'));
    }

    protected function initializeView($view): void
    {
        $this->pageRenderer->addCssInlineBlock('recordsmanager', '.t3js-datetimepicker ~ .input-group-btn > label { margin-bottom: 0; }');
    }

    /**
     * @throws NoSuchArgumentException
     */
    protected function createMenu(string $action = 'index', array $allConfigs = []): void
    {
        $this->uriBuilder->setRequest($this->request);

        if ($allConfigs !== []) {
            $menu = $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
            $menu->setIdentifier('recordsmanagermenu');

            $active = array_values($allConfigs)[0]['uid'];
            if ($this->request->hasArgument('menuitem')) {
                $active = $this->request->getArgument('menuitem');
            }

            foreach ($allConfigs as $config) {
                $menu->addMenuItem(
                    $menu->makeMenuItem()
                        ->setTitle($config['title'])
                        ->setHref($this->uriBuilder->uriFor($action, ['menuitem' => $config['uid']]))
                        ->setActive((int)$active === (int)$config['uid'])
                );
            }

            $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->addMenu($menu);
        }
    }

    protected function buildPagination(Query $query, int $currentPage): void
    {
        $itemsPerPage = 10;
        if (!empty($this->settings['list']['paginate']['itemsPerPage'])) {
            $itemsPerPage = (int)$this->settings['list']['paginate']['itemsPerPage'];
        }

        $paginator = new QueryPaginator($query, $currentPage, $itemsPerPage);
        $pagination = new SimplePagination($paginator);
        if (!empty($this->settings['list']['paginate']['maximumNumberOfLinks'])) {
            $pagination->setMaximumNumberOfLinks((int)$this->settings['list']['paginate']['maximumNumberOfLinks']);
        }

        $pagination->generate();
        $this->moduleTemplate->assign('paginator', $paginator);
        $this->moduleTemplate->assign('pagination', $pagination);
    }

    /**
     * Returns a response object with either the given html string or the current rendered view as content.
     *
     * @param string|null $html
     */
    protected function htmlResponseCompatibility(?string $html = null)
    {
        return $this->responseFactory->createResponse()
            ->withHeader('Content-Type', 'text/html; charset=utf-8')
            ->withBody($this->streamFactory->createStream($html ?? $this->view->render()));
    }
}
