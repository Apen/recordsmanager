<?php

namespace Sng\Recordsmanager\Hooks;

/*
 * This file is part of the "recordsmanager" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

/**
 * Class TceForms
 *
 * @package Sng\Recordsmanager\Hooks
 */
class TceForms
{
    public function getMainFields_preProcess($table, $row, $parent)
    {
        $recordsHide = explode(',', \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('recordsHide'));
        if (count($recordsHide) > 0) {
            $parent->hiddenFieldListArr = array_merge($parent->hiddenFieldListArr, $recordsHide);
        }
    }
}