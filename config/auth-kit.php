<?php

declare(strict_types=1);

return [
    'targets' => [
        'root' => [
            'label'             => 'Root application',
            'type'              => 'root',
            'modules_root'      => '',
            'source_path'       => 'app',
            'model_namespace'   => 'App\\Models',
            'model_path'        => 'app/Models',
            'factory_namespace' => 'Database\\Factories',
            'factory_path'      => 'database/factories',
            'migration_path'    => 'database/migrations',
        ],

        'module_app_domain' => [
            'label'                     => 'Module (app/Domain)',
            'type'                      => 'module',
            'modules_root'              => 'modules',
            'source_path'               => 'app',
            'model_namespace_pattern'   => 'Modules\\{module}\\Models',
            'model_path_pattern'        => '{module_path}/app/Models',
            'factory_namespace_pattern' => 'Modules\\{module}\\Database\\Factories',
            'factory_path_pattern'      => '{module_path}/database/factories',
            'migration_path_pattern'    => '{module_path}/database/migrations',
        ],

        'module_src' => [
            'label'                     => 'Module (src)',
            'type'                      => 'module',
            'modules_root'              => 'modules',
            'source_path'               => 'src',
            'model_namespace_pattern'   => 'Modules\\{module}\\Models',
            'model_path_pattern'        => '{module_path}/src/Models',
            'factory_namespace_pattern' => 'Modules\\{module}\\Database\\Factories',
            'factory_path_pattern'      => '{module_path}/database/factories',
            'migration_path_pattern'    => '{module_path}/database/migrations',
        ],
    ],
];
