<?php

$EM_CONF[$_EXTKEY] = array(
    'title'            => 'Records management in a BE module',
    'description'      => 'Add modules to easily manage your records (insert, edit & export in be/eId) in one place.',
    'category'         => 'module',
    'version'          => '1.4.4-dev',
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
                    'typo3' => '9.5.0-9.5.99',
                ),
            'conflicts' =>
                array(),
            'suggests'  =>
                array(),
        ),
);

