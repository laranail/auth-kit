# Laravel Auth Kit

`laranail/auth-kit` is a Laravel scaffolding package for generating auth-ready code in configurable application targets.

The package is exposed through the `auth-kit:init` Artisan command.

## Requirements

| Version | PHP          | Laravel |
|---------|--------------|---------|
| 1.x     | 8.4.x, 8.5.x | 13.x    |

## Installation
```bash
composer require laranail/auth-kit
```
## Configuration

Publish the package config:
```bash
php artisan vendor:publish --tag=auth-kit-config --provider="Simtabi\Laranail\Auth\AuthKitServiceProvider"
```
The configuration file will be published to:
```text
config/auth-kit.php
```
Before running the scaffold command, review and update the configured `targets` to match your application structure.

Targets define where generated files should be written. Depending on your setup, you can configure targets for:

- your main application
- module-based applications
- multiple scaffold destinations

## Usage

Run the interactive scaffold command:
```bash
php artisan auth-kit:init
```
The command will guide you through the scaffold process.

### Interactive flow

The command will prompt you to:

1. Choose where to scaffold authentication files
2. Select a module if the chosen target is module-based
3. Enter the model class name
4. Review the scaffold plan
5. Confirm before files are created or replaced

Example:
```text
This command help you scaffold an authentication.

? Where would you like to scaffold?
? Which module?
? Model class name

========= Scaffolding auth on App =========
CREATE app/Models/Admin.php
CREATE database/factories/AdminFactory.php

? Continue?
```
If a generated file already exists, the command will replace it after confirmation.

## What gets generated

The scaffold plan currently generates:

- an auth-ready model class
- a corresponding model factory

For a new model, the generated model extends Laravel's `Authenticatable` base class and includes common authentication-related traits and defaults.

The generated factory includes common user attributes such as:

- `name`
- `email`
- `email_verified_at`
- `password`
- `remember_token`

## Target-based scaffolding

Scaffolding is driven by the configured target.

A target defines things such as:

- the target label shown in the prompt
- whether the target is application-based or module-based
- source paths
- model namespaces and paths
- factory namespaces and paths

This allows the same command to scaffold models into different parts of your application.

## Published stubs

You may publish the package stubs to customize generated files:
```bash
php artisan vendor:publish --tag=auth-kit-stubs --provider="Simtabi\Laranail\Auth\AuthKitServiceProvider"
```
The stubs will be published to:
```text
auth-kit-stubs
```
After publishing, you can modify the stubs to fit your project's coding style or base model conventions.

## Notes

- Enter only the model class name when prompted, not a full namespace or file path
- Module selection is only shown when the chosen target is configured as a module target
- If no modules are found for a module target, the command exits without generating files

## Testing
```bash
composer test
```

## License
MIT