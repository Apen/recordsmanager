<?php

declare(strict_types=1);

namespace Sng\Recordsmanager\Utility;

/*
 * This file is part of the "recordsmanager" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class Misc
{
    /**
     * This function return an array with ###value###
     */
    public static function convertToMarkerArray(array $array, string $markerPrefix = ''): array
    {
        $temp = [];
        foreach ($array as $key => $val) {
            $temp[self::convertToMarker($key, $markerPrefix)] = $val;
        }

        return $temp;
    }

    /**
     * This function return a string with ###value###
     */
    public static function convertToMarker(string $value, string $markerPrefix = ''): string
    {
        return '###' . strtoupper($markerPrefix . $value) . '###';
    }

    /**
     * Load a TS string
     */
    public static function loadTS(array $conf, string $content): array
    {
        $tsparser = GeneralUtility::makeInstance(TypoScriptParser::class);
        $tsparser->setup = $conf;
        $tsparser->parse($content);

        return $tsparser->setup;
    }

    /**
     * Load a TS string and return array of fields
     */
    public static function loadAndExecTS(array $conf, array $data = [], string $table = ''): array
    {
        $tsArray = self::loadTS([], $conf);
        $datas = [];
        $lCobj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        foreach ($tsArray as $tsKey => $tsValue) {
            if (substr($tsKey, -1) === '.') {
                $field = substr($tsKey, 0, -1);
                $lCobj->start($data, $table);
                if (empty($tsValue['sngfunc'])) {
                    $datas[$field] = $lCobj->cObjGetSingle($tsArray[$field], $tsValue);
                } else {
                    $sngfuncs = GeneralUtility::trimExplode(',', $tsValue['sngfunc']);
                    $value = $lCobj->cObjGetSingle($tsArray[$field], $tsValue);
                    foreach ($sngfuncs as $sngfunc) {
                        switch ($sngfunc) {
                            case 'intval':
                                if (is_array($value)) {
                                    foreach ($value as $arrayKey => $arrayValue) {
                                        $value[$arrayKey] = (int)$arrayValue;
                                    }
                                } else {
                                    $value = (int)$value;
                                }

                                break;
                            case 'trimexplode':
                                $value = GeneralUtility::trimExplode($tsValue['sngfunc.']['trimexplode.']['token'], $value);

                                break;
                            default:
                                break;
                        }
                    }

                    $datas[$field] = $value;
                }
            }
        }

        return $datas;
    }

    /**
     * Returns an integer from a three part version number, eg '4.12.3' -> 4012003
     *
     * @param string $verNumberStr number on format x.x.x
     */
    public static function intFromVer(string $verNumberStr): int
    {
        $verParts = explode('.', $verNumberStr);

        return (int)((int)$verParts[0] . str_pad((int)$verParts[1], 3, '0', STR_PAD_LEFT) . str_pad((int)$verParts[2], 3, '0', STR_PAD_LEFT));
    }

    public static function getModuleUrl(string $moduleName, array $urlParameters = []): string
    {
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $uri = $uriBuilder->buildUriFromRoute($moduleName, $urlParameters);

        return (string)$uri;
    }

    /**
     * @return TypoScriptFrontendController|LanguageService
     */
    public static function getLanguageService()
    {
        // fe
        if (!empty($GLOBALS['TSFE'])) {
            return $GLOBALS['TSFE'];
        }

        // be
        if (!empty($GLOBALS['LANG'])) {
            return $GLOBALS['LANG'];
        }

        $LANG = GeneralUtility::makeInstance(LanguageService::class);
        $LANG->init($GLOBALS['BE_USER']->uc['lang']);

        return $LANG;
    }
}
