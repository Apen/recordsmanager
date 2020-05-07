<?php

namespace Sng\Recordsmanager\Hooks;

/*
 * This file is part of the "recordsmanager" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TceForms implements FormDataProviderInterface
{

    /**
     * Add form data to result array
     *
     * @param array $result Initialized result array
     * @return array Result filled with more data
     */
    public function addData(array $result)
    {
        $recordsHide = explode(',', GeneralUtility::_GP('recordsHide'));
        if (count($recordsHide) > 0) {
            foreach ($recordsHide as $col) {
                unset($result['processedTca']['columns'][$col]);
            }
        }
        return $result;
    }
}
