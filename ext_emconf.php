<?php

$EM_CONF['recordsmanager'] = [
    'title' => 'Records management in a BE module',
    'description' => 'Add modules to easily manage your records (insert, edit & export in be/eId) in one place.',
    'category' => 'module',
    'version' => '1.6.9',
    'state' => 'stable',
    'uploadfolder' => false,
    'clearcacheonload' => false,
    'author' => 'CERDAN Yohann [Site-nGo]',
    'author_email' => 'cerdanyohann@yahoo.fr',
    'author_company' => '',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.99-13.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'autoload' => [
        'classmap' => [
            'Resources/Private/Php/',
        ],
        'psr-4' => [
            'Sng\\Recordsmanager\\' => 'Classes/',
        ],
    ],
];

