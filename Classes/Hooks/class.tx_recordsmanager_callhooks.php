<?php

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

class tx_recordsmanager_callhooks
{
    private static $dateformat;

    public function getMainFields_preProcess($table, $row, $parent)
    {
        $recordsHide = explode(',', \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('recordsHide'));
        if (count($recordsHide) > 0) {
            $parent->hiddenFieldListArr = array_merge($parent->hiddenFieldListArr, $recordsHide);
        }
    }

    public function BE_postProcessValue($params)
    {
        if ($params['colConf']['type'] == 'input' && isset($params['colConf']['eval']) && $params['colConf']['eval'] == 'date') {
            if (self::$dateformat == null) {
                $items = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tx_recordsmanager_config', 'type=2 AND deleted=0 AND hidden=0', '', 'sorting');
                if (count($items)) {
                    $config = $items[0];
                    self::$dateformat = $config['dateformat'];
                } else {
                    self::$dateformat = -1;
                }
            }
            if (self::$dateformat != null) {
                // remove the parenthesis at the end of the default date format
                $params['value'] = preg_replace('/\s*\(.+\)/', '', $params['value']);
            }
        }
        return $params['value'];
    }

}
