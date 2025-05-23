<?php

$extensionConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class);
$extensionConf = $extensionConfiguration->get('recordsmanager');

if (((int)$extensionConf['enabledAdd'] === 1) || ((int)$extensionConf['enabledEdit'] === 1) || ((int)$extensionConf['enabledExport'] === 1)) {
    $conf = [
        'txrecordsmanagerM1' => [
            'labels' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang.xlf:recordsmanagertitle',
            'iconIdentifier' => 'recordsmanager-main',
            'navigationComponent' => '',
	    'aliases' => ['txrecordsmanagerM1'],
	    'access' => 'user',
        ],
    ];

    if ((int)$extensionConf['enabledAdd'] === 1) {
        $conf['recordsmanager_insert'] = [
            'parent' => 'txrecordsmanagerM1',
            'extensionName' => 'recordsmanager',
            'labels' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang.xlf:inserttitle',
            'iconIdentifier' => 'recordsmanager-insert',
            'controllerActions' => [
                \Sng\Recordsmanager\Controller\InsertController::class => 'index',
            ],
	    'aliases' => ['txrecordsmanagerM1_RecordsmanagerInsert'],
	    'access' => 'user',
        ];
    }

    if ((int)$extensionConf['enabledEdit'] === 1) {
        $conf['recordsmanager_edit'] = [
            'parent' => 'txrecordsmanagerM1',
            'extensionName' => 'recordsmanager',
            'labels' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang.xlf:edittitle',
            'iconIdentifier' => 'recordsmanager-edit',
            'controllerActions' => [
                \Sng\Recordsmanager\Controller\EditController::class => 'index',
	    ],
	    'access' => 'user',
        ];
    }

    if ((int)$extensionConf['enabledExport'] === 1) {
        $conf['recordsmanager_export'] = [
            'parent' => 'txrecordsmanagerM1',
            'extensionName' => 'recordsmanager',
            'labels' => 'LLL:EXT:recordsmanager/Resources/Private/Language/locallang.xlf:exporttitle',
            'iconIdentifier' => 'recordsmanager-export',
            'controllerActions' => [
                \Sng\Recordsmanager\Controller\ExportController::class => 'index',
	    ],
	    'access' => 'user',
        ];
    }

    return $conf;
}

return [];
