<?php

namespace Sng\Recordsmanager\ViewHelpers;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

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
     * Prints sprite icon html for $iconName key
     *
     * @param string $iconName
     * @param string $size
     * @return string
     */
    public function render($iconName, $size = \TYPO3\CMS\Core\Imaging\Icon::SIZE_SMALL)
    {
        $iconFactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(IconFactory::class);
        return $iconFactory->getIcon($iconName, $size);
    }

}
