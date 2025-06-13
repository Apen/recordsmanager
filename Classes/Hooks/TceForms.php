<?php

declare(strict_types=1);

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
     *
     * @return array Result filled with more data
     */
    public function addData(array $result): array
    {
        if (!empty($GLOBALS['TYPO3_REQUEST']->getParsedBody()['recordsHide'] ?? $GLOBALS['TYPO3_REQUEST']->getQueryParams()['recordsHide'] ?? null)) {
            $recordsHide = explode(',', $GLOBALS['TYPO3_REQUEST']->getParsedBody()['recordsHide'] ?? $GLOBALS['TYPO3_REQUEST']->getQueryParams()['recordsHide'] ?? null);
            foreach ($recordsHide as $col) {
                unset($result['processedTca']['columns'][$col]);
            }
        }

        return $result;
    }
}
