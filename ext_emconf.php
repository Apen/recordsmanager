<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "recordsmanager".
 *
 * Auto generated 16-11-2021 15:06
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
    'title' => 'Records management in a BE module',
    'description' => 'Add modules to easily manage your records (insert, edit & export in be/eId) in one place.',
    'category' => 'module',
    'version' => '1.6.7',
    'state' => 'stable',
    'clearCacheOnLoad' => true,
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
        [
        ],
        'suggests' =>
        [
        ],
    ],
];
