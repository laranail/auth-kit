<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;
use Simtabi\Laranail\Auth\Actions\GetAvailableModels;

it(description: 'returns php classes from Models and models directories', closure: function () {
    $files = new Filesystem();
    $basePath = sys_get_temp_dir() . '/auth-kit-models-' . bin2hex(string: random_bytes(length: 8));

    $files->ensureDirectoryExists(path: $basePath . '/Models/Admin');
    $files->ensureDirectoryExists(path: $basePath . '/models');

    $files->put(
        path: $basePath . '/Models/User.php',
        contents: "<?php\n\nnamespace App\\Models;\n\nclass User {}\n"
    );
    $files->put(
        path: $basePath . '/Models/Admin/Member.php',
        contents: "<?php\n\nnamespace App\\Models\\Admin;\n\nclass Member {}\n"
    );
    $files->put(
        path: $basePath . '/models/Profile.php',
        contents: "<?php\n\nnamespace App\\models;\n\nclass Profile {}\n"
    );
    $files->put(
        path: $basePath . '/Models/readme.txt',
        contents: 'Ignored'
    );

    try {
        $models = new GetAvailableModels(files: $files)(basePath: $basePath);

        expect(value: $models)->toBe(expected: [
            'App\\Models\\Admin\\Member',
            'App\\Models\\User',
            'App\\models\\Profile',
        ]);
    } finally {
        $files->deleteDirectory(directory: $basePath);
    }
});
