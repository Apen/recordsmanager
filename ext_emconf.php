<?php

$EM_CONF['recordsmanager'] = [
    'title' => 'Records management in a BE module',
    'description' => 'Add modules to easily manage your records (insert, edit & export in be/eId) in one place.',
    'category' => 'module',
    'version' => '1.6.8',
    'state' => 'stable',
    'uploadfolder' => false,
    'clearcacheonload' => false,
    'author' => 'CERDAN Yohann [Site-nGo]',
    'author_email' => 'cerdanyohann@yahoo.fr',
    'author_company' => '',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-12.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'autoload' => [
        'psr-4' => [
            'Sng\\Recordsmanager\\' => 'Classes/',
        ],
    ],
];

