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

/**
 * ViewHelper to check if a variable is in a list
 *
 * Example
 * <AdditionalReports:inList list="{AdditionalReports:session(index:'agenda', identifier:'dates')}" item="{eventDate.filtre}">...</AdditionalReports:inList>
 *
 * @package    TYPO3
 * @subpackage AdditionalReports
 */
class InListViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractConditionViewHelper
{

    /**
     * Renders else-child or else-argument if variable $item is in $list
     *
     * @param string $list
     * @param string $item
     * @return string
     */
    public function render($list, $item)
    {
        if (\TYPO3\CMS\Core\Utility\GeneralUtility::inList($list, $item) === true) {
            return $this->renderThenChild();
        }
        return $this->renderElseChild();
    }

}

?>