<?php

namespace Sng\Recordsmanager\ViewHelpers;

/*
 * This file is part of the "recordsmanager" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Displays sprite icon identified by iconName key
 *
 * @author Felix Kopp <felix-source@phorax.com>
 * @internal
 */
class SpriteManagerIconViewHelper extends AbstractViewHelper
{

    /**
     * Plain HTML should be returned, no output escaping allowed
     *
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Initialize arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('iconName', 'string', 'as', true);
        $this->registerArgument('size', 'integer', 'size', false, Icon::SIZE_SMALL);
    }

    /**
     * Prints sprite icon html for $iconName key
     *
     * @return string
     */
    public function render()
    {
        $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        return $iconFactory->getIcon($this->arguments['iconName'], $this->arguments['size']);
    }
}
