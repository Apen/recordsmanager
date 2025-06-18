<?php

declare(strict_types=1);

namespace Sng\Recordsmanager\ViewHelpers;

/*
 * This file is part of the "recordsmanager" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Sng\Recordsmanager\Utility\Misc;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Displays sprite icon identified by iconName key
 *
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
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('iconName', 'string', 'as', true);
        if (Misc::isTypo3V12()) {
            $this->registerArgument('size', 'integer', 'size', false, \TYPO3\CMS\Core\Imaging\Icon::SIZE_SMALL);
        } else {
            $this->registerArgument('size', 'integer', 'size', false, \TYPO3\CMS\Core\Imaging\IconSize::SMALL);
        }
    }

    /**
     * Prints sprite icon html for $iconName key
     */
    public function render(): string
    {
        $iconFactory = GeneralUtility::makeInstance(IconFactory::class);

        return (string)$iconFactory->getIcon($this->arguments['iconName'], $this->arguments['size']);
    }
}
