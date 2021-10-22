<?php

$EM_CONF['recordsmanager'] = [
    'title' => 'Records management in a BE module',
    'description' => 'Add modules to easily manage your records (insert, edit & export in be/eId) in one place.',
    'category' => 'module',
    'version' => '1.6.0-dev',
    'state' => 'stable',
    'createDirs' => '',
    'clearcacheonload' => true,
    'author' => 'CERDAN Yohann [Site-nGo]',
    'author_email' => 'cerdanyohann@yahoo.fr',
    'author_company' => '',
    'constraints' =>
        [
            'depends' =>
                [
                    'typo3' => '10.4.0-11.5.99',
                ],
            'conflicts' =>
                [],
            'suggests' =>
                [],
        ],
];
