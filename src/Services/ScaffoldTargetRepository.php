<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Services;

use Simtabi\Laranail\Auth\Exceptions\InvalidScaffoldConfigException;

final class ScaffoldTargetRepository
{
    private const REQUIRED_ROOT_KEYS = [
        'label',
        'type',
        'modules_root',
        'source_path',
        'model_namespace',
        'model_path',
        'factory_namespace',
        'factory_path',
        'migration_path',
    ];

    private const REQUIRED_MODULE_KEYS = [
        'label',
        'type',
        'modules_root',
        'source_path',
        'model_namespace_pattern',
        'model_path_pattern',
        'factory_namespace_pattern',
        'factory_path_pattern',
        'migration_path_pattern',
    ];

    /**
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        $targets = config(key: 'auth-kit.targets', default: []);

        $this->validate(targets: $targets);

        return $targets;
    }

    /**
     * @return array<string, mixed>
     */
    public function find(string $key): array
    {
        $targets = $this->all();

        if (! isset($targets[$key])) {
            throw InvalidScaffoldConfigException::invalidTargetKey(key: $key);
        }

        return $targets[$key];
    }

    /**
     * @return array<string, string>
     */
    public function labels(): array
    {
        $labels = [];

        foreach ($this->all() as $key => $target) {
            $labels[$key] = $target['label'] ?? $key;
        }

        return $labels;
    }

    /**
     * @param  array<string, array<string, mixed>>  $targets
     */
    private function validate(array $targets): void
    {
        if ($targets === []) {
            throw InvalidScaffoldConfigException::noTargets();
        }

        $usedLabels = [];

        foreach ($targets as $key => $target) {
            $type = $target['type'] ?? null;

            if ($type === null) {
                throw InvalidScaffoldConfigException::missingRequiredKey(targetKey: $key, key: 'type');
            }

            $requiredKeys = $type === 'module' ? self::REQUIRED_MODULE_KEYS : self::REQUIRED_ROOT_KEYS;

            foreach ($requiredKeys as $requiredKey) {
                if (! array_key_exists(key: $requiredKey, array: $target)) {
                    throw InvalidScaffoldConfigException::missingRequiredKey(targetKey: $key, key: $requiredKey);
                }
            }

            if ($type === 'module' && (isset($target['modules_root']) && $target['modules_root'] === '')) {
                throw InvalidScaffoldConfigException::moduleTargetMissingModulesRoot(key: $key);
            }

            $label = $target['label'] ?? '';

            if (isset($usedLabels[$label])) {
                throw InvalidScaffoldConfigException::duplicateLabel(label: $label, keyA: $usedLabels[$label], keyB: $key);
            }

            $usedLabels[$label] = $key;
        }
    }
}
