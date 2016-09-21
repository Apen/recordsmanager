<?php

namespace Sng\Recordsmanager\Utility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 CERDAN Yohann <cerdanyohann@yahoo.fr>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

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
        $temp = array();
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
        $tsparser = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser::class);
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
    public static function loadAndExecTS($conf, $data = array(), $table = '')
    {
        \TYPO3\CMS\Extbase\Utility\FrontendSimulatorUtility::simulateFrontendEnvironment();
        
        $tsArray = self::loadTS(array(), $conf);
        $datas = array();
        $lCobj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class);
        foreach ($tsArray as $tsKey => $tsValue) {
            if (substr($tsKey, -1) == '.') {
                $field = substr($tsKey, 0, -1);
                $lCobj->start($data, $table);
                $datas[$field] = $lCobj->cObjGetSingle($tsArray[$field], $tsValue);
            }
        }
        return $datas;
    }

    /**
     * Returns an integer from a three part version number, eg '4.12.3' -> 4012003
     *
     * @param    string $verNumberStr number on format x.x.x
     * @return   integer   Integer version of version number (where each part can count to 999)
     */
    public static function intFromVer($verNumberStr)
    {
        $verParts = explode('.', $verNumberStr);
        return intval((int)$verParts[0] . str_pad((int)$verParts[1], 3, '0', STR_PAD_LEFT) . str_pad((int)$verParts[2], 3, '0', STR_PAD_LEFT));
    }

}