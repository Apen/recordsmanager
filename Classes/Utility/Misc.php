<?php

namespace Sng\Recordsmanager\Utility;

/*
 * This file is part of the "recordsmanager" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

class Misc
{

    /**
     * This function return an array with ###value###
     *
     * @param array  $array
     * @param string $markerPrefix
     * @return array
     */
    public static function convertToMarkerArray($array, $markerPrefix = '')
    {
        $temp = [];
        foreach ($array as $key => $val) {
            $temp[self::convertToMarker($key, $markerPrefix)] = $val;
        }
        return $temp;
    }

    /**
     * This function return a string with ###value###
     *
     * @param string $value
     * @param string $markerPrefix
     * @return string
     */
    public static function convertToMarker($value, $markerPrefix = '')
    {
        return '###' . strtoupper($markerPrefix . $value) . '###';
    }

    /**
     * Load a TS string
     *
     * @param array  $conf
     * @param string $content
     * @return array
     */
    public static function loadTS($conf, $content)
    {
        /** @var $tsparser t3lib_tsparser */
        $tsparser = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\TypoScript\\Parser\\TypoScriptParser');
        // Copy conf into existing setup
        $tsparser->setup = $conf;
        // Parse the new Typoscript
        $tsparser->parse($content);
        // Copy the resulting setup back into conf
        return $tsparser->setup;
    }

    /**
     * Load a TS string and return array of fields
     *
     * @param array $conf
     * @return array
     */
    public static function loadAndExecTS($conf, $data = [], $table = '')
    {
        $tsArray = self::loadTS([], $conf);
        $datas = [];
        $lCobj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer');
        foreach ($tsArray as $tsKey => $tsValue) {
            if (substr($tsKey, -1) == '.') {
                $field = substr($tsKey, 0, -1);
                $lCobj->start($data, $table);
                if (empty($tsValue['sngfunc'])) {
                    $datas[$field] = $lCobj->cObjGetSingle($tsArray[$field], $tsValue);
                } else {
                    $sngfuncs = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $tsValue['sngfunc']);
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
                                $value = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode($tsValue['sngfunc.']['trimexplode.']['token'], $value);
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
     * @param    string $verNumberStr number on format x.x.x
     * @return   int   Integer version of version number (where each part can count to 999)
     */
    public static function intFromVer($verNumberStr)
    {
        $verParts = explode('.', $verNumberStr);
        return (int)((int)$verParts[0] . str_pad((int)$verParts[1], 3, '0', STR_PAD_LEFT) . str_pad((int)$verParts[2], 3, '0', STR_PAD_LEFT));
    }
}
