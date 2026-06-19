<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;
use Simtabi\Laranail\Auth\Actions\GetAvailableModels;

it(description: 'returns php classes from configured model paths', closure: function () {
    $files = new Filesystem();
    $basePath = sys_get_temp_dir() . '/auth-kit-models-' . bin2hex(string: random_bytes(length: 8));

    $originalBasePath = app()->basePath();
    app()->setBasePath($basePath);

    $files->ensureDirectoryExists(path: $basePath . '/app/Models/Admin');
    $files->ensureDirectoryExists(path: $basePath . '/app/models');
    $files->ensureDirectoryExists(path: $basePath . '/app-modules/Billing/src/Models');
    $files->ensureDirectoryExists(path: $basePath . '/modules/CRM/models');

    $files->put(
        path: $basePath . '/app/Models/User.php',
        contents: "<?php\n\nnamespace App\\Models;\n\nclass User {}\n"
    );
    $files->put(
        path: $basePath . '/app/Models/Admin/Member.php',
        contents: "<?php\n\nnamespace App\\Models\\Admin;\n\nclass Member {}\n"
    );
    $files->put(
        path: $basePath . '/app/models/Profile.php',
        contents: "<?php\n\nnamespace App\\models;\n\nclass Profile {}\n"
    );
    $files->put(
        path: $basePath . '/app-modules/Billing/src/Models/Invoice.php',
        contents: "<?php\n\nnamespace AppModules\\Billing\\Models;\n\nclass Invoice {}\n"
    );
    $files->put(
        path: $basePath . '/modules/CRM/models/Contact.php',
        contents: "<?php\n\nnamespace Modules\\CRM\\models;\n\nclass Contact {}\n"
    );
    $files->put(
        path: $basePath . '/app/Models/readme.txt',
        contents: 'Ignored'
    );

    try {
        $models = new GetAvailableModels(files: $files)();

        expect(value: $models)->toBe(expected: [
            'AppModules\Billing\Models\Invoice',
            'App\Models\Admin\Member',
            'App\Models\User',
            'App\models\Profile',
            'Modules\CRM\models\Contact',
        ]);
    } finally {
        app()->setBasePath($originalBasePath);
        $files->deleteDirectory(directory: $basePath);
    }
});
