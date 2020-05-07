<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$tx_recordsmanager_config = [
    'ctrl' => [
        'title' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'adminOnly' => 1,
        'rootLevel' => -1,
        'type' => 'type',
        'sortby' => 'sorting',
        'default_sortby' => 'ORDER BY crdate',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'iconfile' => 'EXT:recordsmanager/Resources/Public/Icons/icon_tx_recordsmanager_config.gif',
    ],
    'interface' => [
        'showRecordFieldList' => 'hidden,title,type,sqltable,sqlfields,sqlfieldsinsert,perms_group'
    ],
    'columns' => [
        'hidden' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
                'default' => '0'
            ]
        ],
        'title' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.title',
            'config' => [
                'type' => 'input',
                'size' => '30',
            ]
        ],
        'type' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.type',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'size' => '1',
                'items' => [
                    ['LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.type.I.0', 0],
                    ['LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.type.I.1', 1],
                    ['LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.type.I.2', 2],
                    ['LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.type.I.3', 3]
                ],
                'default' => 0
            ]
        ],
        'sqltable' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.sqltable',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'itemsProcFunc' => 'Sng\\Recordsmanager\\Utility\\Flexfill->getTables',
            ]
        ],
        'sqlfields' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.sqlfields',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'itemsProcFunc' => 'Sng\\Recordsmanager\\Utility\\Flexfill->getFields',
                'minitems' => 0,
                'maxitems' => 500,
                'size' => 10,
            ]
        ],
        'extrawhere' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.extrawhere',
            'config' => [
                'type' => 'text',
                'size' => '50',
            ]
        ],
        'extragroupby' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.extragroupby',
            'config' => [
                'type' => 'input',
                'size' => '30',
            ]
        ],
        'extrats' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.extrats',
            'config' => [
                'type' => 'text',
                'size' => '100',
            ]
        ],
        'extraorderby' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.extraorderby',
            'config' => [
                'type' => 'input',
                'size' => '30',
            ]
        ],
        'extralimit' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.extralimit',
            'config' => [
                'type' => 'input',
                'size' => '30',
            ]
        ],
        'exportfilterfield' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.exportfilterfield',
            'config' => [
                'type' => 'input',
                'size' => '30',
            ]
        ],
        'exportmode' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.exportmode',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'items' => [
                    ['LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.exportmode.I.1', 'xml'],
                    ['LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.exportmode.I.2', 'csv'],
                    ['LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.exportmode.I.3', 'excel'],
                ],
                'default' => 0,
                'minitems' => 1,
                'maxitems' => 10,
                'size' => 5,
            ]
        ],
        'sqlfieldsinsert' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.sqlfieldsinsert',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'itemsProcFunc' => 'Sng\\Recordsmanager\\Utility\\Flexfill->getEditFields',
                'minitems' => 0,
                'maxitems' => 500,
                'size' => 10,
            ]
        ],
        'dateformat' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.dateformat',
            'config' => [
                'type' => 'check',
                'default' => '0',
            ]
        ],
        'permsgroup' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.permsgroup',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'be_groups',
                'foreign_table_where' => 'ORDER BY be_groups.title',
                'size' => 10,
                'minitems' => 0,
                'maxitems' => 500,
            ]
        ],
        'insertdefaultpid' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.insertdefaultpid',
            'config' => [
                'type' => 'input',
                'size' => '30',
            ]
        ],
        'eidkey' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.eidkey',
            'config' => [
                'type' => 'input',
                'size' => '30',
            ]
        ],
        'excludefields' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.excludefields',
            'config' => [
                'type' => 'input',
                'size' => '30',
            ]
        ],
        'authlogin' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.authlogin',
            'config' => [
                'type' => 'input',
                'size' => '30',
            ]
        ],
        'authpassword' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.authpassword',
            'config' => [
                'type' => 'input',
                'size' => '30',
            ]
        ],
        'hidefields' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.hidefields',
            'config' => [
                'type' => 'input',
                'size' => '30',
            ]
        ],
    ],
    'types' => [
        '0' => ['showitem' => 'hidden;;1;;1-1-1, title;;;;2-2-2, type;;;;2-2-2, sqltable;;;;3-3-3, sqlfields, sqlfieldsinsert, extrawhere, extragroupby, extraorderby, extralimit, permsgroup'],
        '1' => ['showitem' => 'hidden;;1;;1-1-1, title;;;;2-2-2, type;;;;2-2-2, sqltable;;;;3-3-3, sqlfieldsinsert, insertdefaultpid, permsgroup'],
        '2' => ['showitem' => 'hidden;;1;;1-1-1, title;;;;2-2-2, type;;;;2-2-2, sqltable;;;;3-3-3, exportmode, sqlfields, extrawhere, extragroupby, extraorderby, extralimit, exportfilterfield, dateformat, permsgroup, excludefields'],
        '3' => ['showitem' => 'hidden;;1;;1-1-1, title;;;;2-2-2, type;;;;2-2-2, sqltable;;;;3-3-3, eidkey, sqlfields, extrawhere, extragroupby, extraorderby, extralimit, dateformat, excludefields, hidefields, extrats, authlogin, authpassword']
    ],
    'palettes' => [
        '1' => ['showitem' => '']
    ]
];

return $tx_recordsmanager_config;
