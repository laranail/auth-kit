<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;
use Simtabi\Laranail\Auth\Enums\Stack;
use Simtabi\Laranail\Auth\Services\StubRenderer;

test('render throws for path traversal with double dots', function () {
    $renderer = new StubRenderer(new Filesystem());
    $renderer->render('../../etc/passwd', Stack::Blade, []);
})->throws(InvalidArgumentException::class, 'Invalid stub name');

test('render throws for absolute path', function () {
    $renderer = new StubRenderer(new Filesystem());
    $renderer->render('/etc/passwd', Stack::Blade, []);
})->throws(InvalidArgumentException::class, 'Invalid stub name');

test('render throws for name with spaces', function () {
    $renderer = new StubRenderer(new Filesystem());
    $renderer->render('foo bar.stub', Stack::Blade, []);
})->throws(InvalidArgumentException::class, 'Invalid stub name');

test('render throws for name with special characters', function () {
    $renderer = new StubRenderer(new Filesystem());
    $renderer->render('foo@bar.stub', Stack::Blade, []);
})->throws(InvalidArgumentException::class, 'Invalid stub name');

test('render accepts valid stub name with alphanumeric dots underscores hyphens', function () {
    $renderer = new StubRenderer(new Filesystem());

    try {
        $renderer->render('valid-stub_name.test', Stack::Blade, []);
        $this->fail('Expected FileNotFoundException for non-existent stub');
    } catch (InvalidArgumentException $e) {
        $this->fail('Should not reject valid stub name: ' . $e->getMessage());
    } catch (\Illuminate\Contracts\Filesystem\FileNotFoundException) {
        // Expected — validation passed but file doesn't exist
        $this->assertTrue(true);
    } catch (\Error) {
        // base_path() not available in unit context — means validation passed
        $this->assertTrue(true);
    }
});
