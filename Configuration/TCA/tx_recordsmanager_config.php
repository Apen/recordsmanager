<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

$tx_recordsmanager_config = array(
    'ctrl'        => array(
        'title'          => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config',
        'label'          => 'title',
        'tstamp'         => 'tstamp',
        'crdate'         => 'crdate',
        'cruser_id'      => 'cruser_id',
        'adminOnly'      => 1,
        'rootLevel'      => -1,
        'type'           => 'type',
        'sortby'         => 'sorting',
        'default_sortby' => 'ORDER BY crdate',
        'delete'         => 'deleted',
        'enablecolumns'  => array(
            'disabled' => 'hidden',
        ),
        'iconfile'       => 'EXT:recordsmanager/Resources/Public/Icons/icon_tx_recordsmanager_config.gif',
    ),
    'interface'   => array(
        'showRecordFieldList' => 'hidden,title,type,sqltable,sqlfields,sqlfieldsinsert,perms_group'
    ),
    'feInterface' => $TCA['tx_recordsmanager_config']['feInterface'],
    'columns'     => array(
        'hidden'            => array(
            'exclude' => 1,
            'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
            'config'  => array(
                'type'    => 'check',
                'default' => '0'
            )
        ),
        'title'             => array(
            'exclude' => 0,
            'label'   => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.title',
            'config'  => array(
                'type' => 'input',
                'size' => '30',
            )
        ),
        'type'              => array(
            'exclude' => 0,
            'label'   => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.type',
            'config'  => array(
                'type'       => 'select',
                'renderType' => 'selectSingle',
                'size'       => '1',
                'items'      => Array(
                    Array('LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.type.I.0', 0),
                    Array('LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.type.I.1', 1),
                    Array('LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.type.I.2', 2),
                    Array('LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.type.I.3', 3)
                ),
                'default'    => 0
            )
        ),
        'sqltable'          => array(
            'exclude' => 0,
            'label'   => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.sqltable',
            'config'  => Array(
                'type'          => 'select',
                'renderType'    => 'selectSingle',
                'itemsProcFunc' => 'Sng\\Recordsmanager\\Utility\\Flexfill->getTables',
            )
        ),
        'sqlfields'         => array(
            'exclude' => 0,
            'label'   => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.sqlfields',
            'config'  => Array(
                'type'          => 'select',
                'renderType'    => 'selectMultipleSideBySide',
                'itemsProcFunc' => 'Sng\\Recordsmanager\\Utility\\Flexfill->getFields',
                'minitems'      => 0,
                'maxitems'      => 500,
                'size'          => 10,
            )
        ),
        'extrawhere'        => array(
            'exclude' => 0,
            'label'   => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.extrawhere',
            'config'  => array(
                'type' => 'text',
                'size' => '50',
            )
        ),
        'extragroupby'      => array(
            'exclude' => 0,
            'label'   => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.extragroupby',
            'config'  => array(
                'type' => 'input',
                'size' => '30',
            )
        ),
        'extrats'           => array(
            'exclude' => 0,
            'label'   => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.extrats',
            'config'  => array(
                'type' => 'text',
                'size' => '100',
            )
        ),
        'extraorderby'      => array(
            'exclude' => 0,
            'label'   => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.extraorderby',
            'config'  => array(
                'type' => 'input',
                'size' => '30',
            )
        ),
        'extralimit'        => array(
            'exclude' => 0,
            'label'   => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.extralimit',
            'config'  => array(
                'type' => 'input',
                'size' => '30',
            )
        ),
        'exportfilterfield' => array(
            'exclude' => 0,
            'label'   => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.exportfilterfield',
            'config'  => array(
                'type' => 'input',
                'size' => '30',
            )
        ),
        'exportmode'        => array(
            'exclude' => 0,
            'label'   => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.exportmode',
            'config'  => array(
                'type'       => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'items'      => Array(
                    Array('LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.exportmode.I.1', 'xml'),
                    Array('LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.exportmode.I.2', 'csv'),
                    Array('LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.exportmode.I.3', 'excel'),
                ),
                'default'    => 0,
                'minitems'   => 1,
                'maxitems'   => 10,
                'size'       => 5,
            )
        ),
        'sqlfieldsinsert'   => array(
            'exclude' => 0,
            'label'   => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.sqlfieldsinsert',
            'config'  => Array(
                'type'          => 'select',
                'renderType'    => 'selectMultipleSideBySide',
                'itemsProcFunc' => 'Sng\\Recordsmanager\\Utility\\Flexfill->getEditFields',
                'minitems'      => 0,
                'maxitems'      => 500,
                'size'          => 10,
            )
        ),
        'dateformat'        => array(
            'exclude' => 0,
            'label'   => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.dateformat',
            'config'  => array(
                'type'    => 'check',
                'default' => '0',
            )
        ),
        'permsgroup'        => array(
            'exclude' => 0,
            'label'   => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.permsgroup',
            'config'  => array(
                'type'                => 'select',
                'renderType'          => 'selectMultipleSideBySide',
                'foreign_table'       => 'be_groups',
                'foreign_table_where' => 'ORDER BY be_groups.title',
                'size'                => 10,
                'minitems'            => 0,
                'maxitems'            => 500,
            )
        ),
        'insertdefaultpid'  => array(
            'exclude' => 0,
            'label'   => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.insertdefaultpid',
            'config'  => array(
                'type' => 'input',
                'size' => '30',
            )
        ),
        'eidkey'            => array(
            'exclude' => 0,
            'label'   => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.eidkey',
            'config'  => array(
                'type' => 'input',
                'size' => '30',
            )
        ),
        'excludefields'     => array(
            'exclude' => 0,
            'label'   => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.excludefields',
            'config'  => array(
                'type' => 'input',
                'size' => '30',
            )
        ),
        'authlogin'         => array(
            'exclude' => 0,
            'label'   => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.authlogin',
            'config'  => array(
                'type' => 'input',
                'size' => '30',
            )
        ),
        'authpassword'      => array(
            'exclude' => 0,
            'label'   => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.authpassword',
            'config'  => array(
                'type' => 'input',
                'size' => '30',
            )
        ),
        'hidefields'        => array(
            'exclude' => 0,
            'label'   => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.hidefields',
            'config'  => array(
                'type' => 'input',
                'size' => '30',
            )
        ),
    ),
    'types'       => array(
        '0' => array('showitem' => 'hidden;;1;;1-1-1, title;;;;2-2-2, type;;;;2-2-2, sqltable;;;;3-3-3, sqlfields, sqlfieldsinsert, extrawhere, extragroupby, extraorderby, extralimit, permsgroup'),
        '1' => array('showitem' => 'hidden;;1;;1-1-1, title;;;;2-2-2, type;;;;2-2-2, sqltable;;;;3-3-3, sqlfieldsinsert, insertdefaultpid, permsgroup'),
        '2' => array('showitem' => 'hidden;;1;;1-1-1, title;;;;2-2-2, type;;;;2-2-2, sqltable;;;;3-3-3, exportmode, sqlfields, extrawhere, extragroupby, extraorderby, extralimit, exportfilterfield, dateformat, permsgroup, excludefields'),
        '3' => array('showitem' => 'hidden;;1;;1-1-1, title;;;;2-2-2, type;;;;2-2-2, sqltable;;;;3-3-3, eidkey, sqlfields, extrawhere, extragroupby, extraorderby, extralimit, dateformat, excludefields, hidefields, extrats, authlogin, authpassword')
    ),
    'palettes'    => array(
        '1' => array('showitem' => '')
    )
);

return $tx_recordsmanager_config;