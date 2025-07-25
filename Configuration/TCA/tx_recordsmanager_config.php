<?php

declare(strict_types=1);

if (!defined('TYPO3')) {
    die('Access denied.');
}

$tx_recordsmanager_config = [
    'ctrl' => [
        'title' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
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
                    ['label' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.type.I.0', 'value' => 0],
                    ['label' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.type.I.1', 'value' => 1],
                    ['label' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.type.I.2', 'value' => 2],
                    ['label' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.type.I.3', 'value' => 3]
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
                    ['label' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.exportmode.I.1', 'value' => 'xml'],
                    ['label' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.exportmode.I.2', 'value' => 'csv'],
                    ['label' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.exportmode.I.3', 'value' => 'excel'],
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
        'checkpid' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang_db.xlf:tx_recordsmanager_config.checkpid',
            'config' => [
                'type' => 'check',
                'default' => '1'
            ]
        ],
    ],
    'types' => [
        '0' => ['showitem' => 'hidden,--palette--;;1,title,--palette--,type,--palette--,sqltable,--palette--,sqlfields,sqlfieldsinsert,extrawhere,extragroupby,extraorderby,extralimit,permsgroup'],
        '1' => ['showitem' => 'hidden,--palette--;;1,title,--palette--,type,--palette--,sqltable,--palette--,sqlfieldsinsert,insertdefaultpid,permsgroup'],
        '2' => ['showitem' => 'hidden,--palette--;;1,title,--palette--,type,--palette--,sqltable,--palette--,exportmode,sqlfields,extrawhere,extragroupby,extraorderby,extralimit,exportfilterfield,dateformat,permsgroup,excludefields,checkpid'],
        '3' => ['showitem' => 'hidden,--palette--;;1,title,--palette--,type,--palette--,sqltable,--palette--,eidkey,sqlfields,extrawhere,extragroupby,extraorderby,extralimit,dateformat,excludefields,hidefields,extrats,authlogin,authpassword']
    ],
    'palettes' => [
        '1' => ['showitem' => '']
    ]
];

return $tx_recordsmanager_config;
