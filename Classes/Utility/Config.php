<?php

declare(strict_types=1);

namespace Sng\Recordsmanager\Utility;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;

/*
 * This file is part of the "recordsmanager" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class Config
{
    /**
     * Get all config of recordsmanager
     */
    public static function getAllConfigs(int $type, string $mode = 'db'): array
    {
        $items = [];
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_recordsmanager_config');
        $queryBuilder
            ->select('*')
            ->from('tx_recordsmanager_config')
            ->where(
                $queryBuilder->expr()->eq('type', $queryBuilder->createNamedParameter($type, \TYPO3\CMS\Core\Database\Connection::PARAM_INT))
            )
            ->orderBy('sorting', 'ASC');
        $allItems = $queryBuilder->executeQuery()->fetchAllAssociative();
        $usergroups = GeneralUtility::makeInstance(Context::class)->getAspect('backend.user')
            ->getGroupIds();
        if (!empty($allItems)) {
            foreach ($allItems as $row) {
                $configgroups = explode(',', $row['permsgroup']);
                $checkRights = array_intersect($usergroups, $configgroups);
                if (($GLOBALS['BE_USER']->isAdmin()) || ($checkRights !== [])) {
                    $items[] = $row;
                }
            }
        }

        return $items;
    }

    /**
     * Get a eid config of recordsmanager
     */
    public static function getEidConfig(string $eidkey): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_recordsmanager_config');
        $queryBuilder
            ->select('*')
            ->from('tx_recordsmanager_config')
            ->where(
                $queryBuilder->expr()->eq('type', $queryBuilder->createNamedParameter(3, \TYPO3\CMS\Core\Database\Connection::PARAM_INT)),
                $queryBuilder->expr()->like('eidkey', $queryBuilder->createNamedParameter($eidkey, \TYPO3\CMS\Core\Database\Connection::PARAM_STR))
            );
        $row = $queryBuilder->executeQuery()->fetchAssociative();
        if (!empty($row)) {
            return $row;
        }

        $jsonConfigs = self::loadJsonConfigs();
        if (!empty($jsonConfigs[3][$eidkey])) {
            return $jsonConfigs[3][$eidkey];
        }

        return [];
    }

    /**
     * Load all the json config
     */
    public static function loadJsonConfigs(): array
    {
        $jsonConfigs = [];
        if (!empty($GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.typoscript')->getSetupArray()['module.']['tx_recordsmanager.']['settings.']['configs_json.'])) {
            foreach ($GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.typoscript')->getSetupArray()['module.']['tx_recordsmanager.']['settings.']['configs_json.'] as $configPath) {
                $config = json_decode(GeneralUtility::getUrl($configPath), true, 512, JSON_THROW_ON_ERROR);
                if (!empty($config['extrats'])) {
                    $config['extrats'] = implode("\r\n", $config['extrats']);
                }

                if (!empty($config['eidkey'])) {
                    $jsonConfigs[$config['type']][$config['eidkey']] = $config;
                } else {
                    $jsonConfigs[$config['type']][] = $config;
                }
            }
        }

        return $jsonConfigs;
    }

    /**
     * Get formated fields names of a row
     */
    public static function getResultRowTitles(array $row, string $table): array
    {
        $tableHeader = [];
        $conf = $GLOBALS['TCA'][$table];
        foreach (array_keys($row) as $fieldName) {
            $tableHeader[$fieldName] = Misc::getLanguageService()->sL($conf['columns'][$fieldName]['label'] ?? $fieldName);
        }

        return $tableHeader;
    }

    /**
     * Process every columns of a row to convert value
     */
    public static function getResultRow(array $row, string $table, string $excludeFields = '', bool $export = false): array
    {
        $record = [];
        foreach ($row as $fieldName => $fieldValue) {
            if (!GeneralUtility::inList($excludeFields, $fieldName)) {
                $record[$fieldName] = BackendUtility::getProcessedValueExtra($table, $fieldName, $fieldValue, 0, $row['uid']);
                if (is_string($record[$fieldName]) && trim($record[$fieldName]) === 'N/A') {
                    $record[$fieldName] = '';
                }
            } else {
                if (!empty($GLOBALS['TCA'][$table]['columns'][$fieldName]) && !GeneralUtility::inList('input,check', $GLOBALS['TCA'][$table]['columns'][$fieldName]['config']['type'])) {
                    $record[$fieldName] = BackendUtility::getProcessedValue($table, $fieldName, $fieldValue, 0, 1, 1, $row['uid'], true);
                } else {
                    $record[$fieldName] = $fieldValue;
                }

                if ($GLOBALS['TCA'][$table]['columns'][$fieldName]['config']['type'] === 'input' && (($GLOBALS['TCA'][$table]['columns'][$fieldName]['config']['eval'] === 'datetime') || ($GLOBALS['TCA'][$table]['columns'][$fieldName]['config']['eval'] === 'date'))) {
                    $record[$fieldName] = $fieldValue;
                }

                if (empty($record[$fieldName])) {
                    $record[$fieldName] = $fieldValue;
                }

                if (trim((string)$record[$fieldName]) === 'N/A') {
                    $record[$fieldName] = '';
                }
            }

            if ($export) {
                // fal reference
                if (
                    (
                        ($GLOBALS['TCA'][$table]['columns'][$fieldName]['config']['type'] ?? '') === 'inline'
                        || ($GLOBALS['TCA'][$table]['columns'][$fieldName]['config']['type'] ?? '') === 'file'
                    )
                    && ($GLOBALS['TCA'][$table]['columns'][$fieldName]['config']['foreign_table'] ?? '') === 'sys_file_reference'
                ) {
                    $files = [];

                    try {
                        $files = BackendUtility::resolveFileReferences($table, $fieldName, $row);
                    } catch (FileDoesNotExistException|ResourceDoesNotExistException $e) {
                        /*
                         * We just catch the exception here
                         * Reasoning: There is nothing an editor or even admin could do
                         */
                    }

                    $newFiles = [];
                    $newFilesMetas = [];
                    foreach ($files as $file) {
                        if (GeneralUtility::inList($excludeFields, $fieldName)) {
                            $newFiles [] = $file->getUid();
                        } else {
                            $newFiles [] = GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST') . '/' . $file->getPublicUrl();
                        }

                        $properties = $file->getProperties();
                        $newFilesMetas [] = [
                            'uid' => $file->getUid(),
                            'path' => GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST') . '/' . $file->getPublicUrl(),
                            'title' => $properties['title'],
                            'description' => $properties['description'],
                            'alternative' => $properties['alternative'],
                            'link' => $properties['link'],
                        ];
                    }

                    if (!empty($newFiles)) {
                        $record[$fieldName] = implode(', ', $newFiles);
                        $record[$fieldName . '_metas'] = $newFilesMetas;
                    } else {
                        $record[$fieldName] = '';
                        $record[$fieldName . '_metas'] = '';
                    }
                }

                // rte
                if (
                    ($GLOBALS['TCA'][$table]['columns'][$fieldName]['config']['type'] ?? '') === 'text' &&
                    (!empty($GLOBALS['TCA'][$table]['columns'][$fieldName]['config']['wizards']['RTE']) || !empty($GLOBALS['TCA'][$table]['columns'][$fieldName]['config']['enableRichtext']))
                ) {
                    $lCobj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
                    $lCobj->start([], '');
                    $record[$fieldName] = $lCobj->parseFunc($record[$fieldName], [], '< lib.parseFunc_RTE');
                }
            }
        }

        return $record;
    }

    /**
     * Get the export format passed in URL
     */
    public static function getFormat(): string
    {
        $format = $GLOBALS['TYPO3_REQUEST']->getParsedBody()['format'] ?? $GLOBALS['TYPO3_REQUEST']->getQueryParams()['format'] ?? null;
        if (!empty($format)) {
            return (string)$format;
        }

        return 'excel';
    }
}
