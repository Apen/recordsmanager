<?php

$EM_CONF['recordsmanager'] = array(
    'title'            => 'Records management in a BE module',
    'description'      => 'Add modules to easily manage your records (insert, edit & export in be/eId) in one place.',
    'category'         => 'module',
    'version'          => '1.4.5',
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
                    'typo3' => '8.7.0-9.5.99',
                ),
            'conflicts' =>
                array(),
            'suggests'  =>
                array(),
        ),
);

