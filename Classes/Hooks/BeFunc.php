<?php

namespace Sng\Recordsmanager\Hooks;

/*
 * This file is part of the "recordsmanager" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class BeFunc
 *
 * @package Sng\Recordsmanager\Hooks
 */
class BeFunc
{
    private static $dateformat;

    public function BE_postProcessValue($params)
    {
        if ($params['colConf']['type'] == 'input' && isset($params['colConf']['eval']) && $params['colConf']['eval'] == 'date') {
            if (self::$dateformat == null) {
                $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_recordsmanager_config');
                $queryBuilder
                    ->select('*')
                    ->from('tx_recordsmanager_config')
                    ->where(
                        $queryBuilder->expr()->eq('type', $queryBuilder->createNamedParameter(2, \PDO::PARAM_INT))
                    )
                    ->orderBy('sorting', 'ASC');
                $items = $queryBuilder->execute()->fetchAll();
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