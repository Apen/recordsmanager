<?php

return [
    'frontend' => [
        'recordsmanager-export' => [
            'target' => \Sng\Recordsmanager\Middleware\RecordsmanagerMiddleware::class,
            'after' => ['typo3/cms-frontend/prepare-tsfe-rendering']
        ]
    ]
];
