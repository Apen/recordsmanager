<?php

$EM_CONF[$_EXTKEY] = array(
    'title'            => 'Records management in a BE module',
    'description'      => 'Add modules to easily manage your records (insert, edit & export in be/eId) in one place.',
    'category'         => 'module',
    'version'          => '1.4.2',
    'state'            => 'stable',
    'uploadfolder'     => true,
    'createDirs'       => '',
    'clearcacheonload' => true,
    'author'           => 'CERDAN Yohann [Site-nGo]',
    'author_email'     => 'cerdanyohann@yahoo.fr',
    'author_company'   => '',
    'constraints'      =>
        array(
            'depends'   =>
                array(
                    'typo3' => '8.7.0-8.7.99',
                ),
            'conflicts' =>
                array(),
            'suggests'  =>
                array(),
        ),
);

