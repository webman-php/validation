<?php
declare(strict_types=1);

namespace Webman\Validation\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Webman\Validation\Command\ValidatorGenerator\Illuminate\IlluminateConnectionResolver;
use Webman\Validation\Command\ValidatorGenerator\Rules\DefaultRuleInferrer;
use Webman\Validation\Command\ValidatorGenerator\Support\ExcludedColumns;
use Webman\Validation\Command\ValidatorGenerator\Support\OrmDetector;
use Webman\Validation\Command\ValidatorGenerator\Support\SchemaIntrospectorFactory;
use Webman\Validation\Command\ValidatorGenerator\ThinkOrm\ThinkOrmConnectionResolver;
use Webman\Validation\Command\ValidatorGenerator\Support\ValidatorClassRenderer;
use Webman\Validation\Command\ValidatorGenerator\Support\ValidatorFileWriter;
use Webman\Validation\Command\Messages;

#[AsCommand('make:validator', 'Make validation validator class.')]
final class MakeValidatorCommand extends Command
{
    protected function configure(): void
    {
        $this->setDescription($this->selectByLocale(Messages::getDescription()));

        $this->addArgument(
            'name',
            InputArgument::REQUIRED,
            $this->selectByLocale(Messages::getArgumentName())
        );
        $this->addOption(
            'plugin',
            'p',
            InputOption::VALUE_REQUIRED,
            $this->selectByLocale(Messages::getOptionPlugin())
        );
        $this->addOption(
            'path',
            'P',
            InputOption::VALUE_REQUIRED,
            $this->selectByLocale(Messages::getOptionPath())
        );
        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_NONE,
            $this->selectByLocale(Messages::getOptionForce())
        );
        $this->addOption(
            'table',
            't',
            InputOption::VALUE_REQUIRED,
            $this->selectByLocale(Messages::getOptionTable())
        );
        $this->addOption(
            'database',
            'd',
            InputOption::VALUE_REQUIRED,
            $this->selectByLocale(Messages::getOptionDatabase())
        );
        $this->addOption(
            'scenes',
            's',
            InputOption::VALUE_REQUIRED,
            $this->selectByLocale(Messages::getOptionScenes())
        );
        $this->addOption(
            'orm',
            'o',
            InputOption::VALUE_REQUIRED,
            $this->selectByLocale(Messages::getOptionOrm())
        );

        $this->setHelp($this->buildHelpText());
        $this->addUsage('UserValidator');
        $this->addUsage('admin/UserValidator');
        $this->addUsage('UserValidator -p admin');
        $this->addUsage('UserValidator -P plugin/admin/app/validation');
        $this->addUsage('UserValidator -t users -d default');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rawName = (string)$input->getArgument('name');
        $name = $this->nameToClass($rawName);
        $name = str_replace('\\', '/', $name);
        $name = trim($name, '/');

        $plugin = $this->normalizeOptionValue($input->getOption('plugin'));
        $path = $this->normalizeOptionValue($input->getOption('path'));
        $force = (bool)$input->getOption('force');
        $table = $input->getOption('table');
        $table = is_string($table) ? trim($table) : '';
        // Some Symfony Console versions parse `-t=foo` as `=foo` for short options.
        $table = ltrim($table, '=');
        $databaseOptionRaw = $input->getOption('database');
        $databaseOptionRaw = is_string($databaseOptionRaw) ? trim($databaseOptionRaw) : '';
        // Some Symfony Console versions parse `-d=foo` as `=foo` for short options.
        $databaseOptionRaw = ltrim($databaseOptionRaw, '=');
        $connectionName = $databaseOptionRaw !== '' ? $databaseOptionRaw : null;
        $scenesOption = $input->getOption('scenes');
        $scenesOption = is_string($scenesOption) ? trim($scenesOption) : '';
        // Some Symfony Console versions parse `-s=crud` as `=crud` for short options.
        $scenesOption = ltrim($scenesOption, '=');
        $ormOption = $input->getOption('orm');
        $ormOption = is_string($ormOption) ? trim($ormOption) : OrmDetector::ORM_AUTO;
        // Some Symfony Console versions parse `-o=xxx` as `=xxx` for short options.
        $ormOption = ltrim($ormOption, '=');
        if ($ormOption === '') {
            $ormOption = OrmDetector::ORM_AUTO;
        }

        if ($name === '') {
            $output->writeln($this->msg('invalid_name_empty'));
            return self::FAILURE;
        }

        if ($plugin && (str_contains($plugin, '/') || str_contains($plugin, '\\'))) {
            $output->writeln($this->msg('invalid_plugin', ['{plugin}' => $plugin]));
            return self::FAILURE;
        }

        if ($plugin && !$this->pluginExists($plugin)) {
            $output->writeln($this->msg('plugin_not_found', ['{plugin}' => $plugin]));
            return self::FAILURE;
        }

        try {
            if ($plugin || $path) {
                $resolved = $this->resolveTargetByPluginOrPath(
                    $name,
                    $plugin,
                    $path,
                    $output,
                    fn(string $p): string => $this->getPluginValidationRelativePath($p),
                    fn(string $key, array $replace = []): string => $this->msg($key, $replace)
                );
                if ($resolved === null) {
                    return self::FAILURE;
                }
                [$class, $namespace, $file] = $resolved;
            } else {
                [$namespace, $class, $file] = $this->resolveAppValidationTarget($name);
            }
        } catch (\Throwable $e) {
            $output->writeln($this->msg('invalid_name', ['{name}' => $rawName]));
            $output->writeln($this->msg('reason', ['{reason}' => $e->getMessage()]));
            return self::FAILURE;
        }

        if (is_file($file)) {
            // Ask for confirmation when overwriting in interactive mode.
            // If the environment is non-interactive, do not block on prompting.
            if ($input->isInteractive()) {
                $helper = $this->getHelper('question');
                $relative = $this->toRelativePath($file);
                $prompt = $this->msg('override_prompt', ['{path}' => $relative]);
                $question = new ConfirmationQuestion($prompt, true);
                if (!$helper->ask($input, $output, $question)) {
                    return self::SUCCESS;
                }
            } elseif (!$force) {
                // Non-interactive mode and no --force: refuse to overwrite.
                $output->writeln($this->msg('file_exists', ['{path}' => $this->toRelativePath($file)]));
                $output->writeln($this->msg('use_force'));
                return self::FAILURE;
            }
        }

        $rules = [];
        $messages = [];
        $attributes = [];
        $scenes = [];

        if ($scenesOption !== '' && $table === '') {
            $output->writeln($this->msg('scenes_requires_table'));
            return self::FAILURE;
        }

        if ($table !== '') {
            try {
                $detector = new OrmDetector();
                $orm = $detector->resolve($ormOption);

                if (!in_array($orm, [OrmDetector::ORM_LARAVEL, OrmDetector::ORM_THINKORM], true)) {
                    $output->writeln($this->msg('unsupported_orm', ['{orm}' => (string)$orm]));
                    return self::FAILURE;
                }

                $resolver = $orm === OrmDetector::ORM_THINKORM
                    ? new ThinkOrmConnectionResolver()
                    : new IlluminateConnectionResolver();

                $resolvedConnectionName = $this->resolveDatabaseConnectionNameForTable(
                    $plugin,
                    $connectionName,
                    $databaseOptionRaw,
                    $orm,
                    $output
                );
                if ($resolvedConnectionName === null) {
                    return self::FAILURE;
                }

                $connection = $resolver->resolve($resolvedConnectionName);

                $factory = new SchemaIntrospectorFactory();
                $introspector = $factory->createForDriver($connection->driverName());

                $tableDef = $introspector->introspect($connection, $table);

                $excludeColumns = match ($orm) {
                    OrmDetector::ORM_THINKORM => ExcludedColumns::defaultForThinkOrm(),
                    default => ExcludedColumns::defaultForIlluminate(),
                };

                $inferrer = new DefaultRuleInferrer();
                $result = $inferrer->infer($tableDef, [
                    'exclude_columns' => $excludeColumns,
                    'with_scenes' => $scenesOption !== '',
                    'scenes' => $scenesOption,
                ]);

                $rules = $result['rules'] ?? [];
                $attributes = $result['attributes'] ?? [];
                $scenes = $result['scenes'] ?? [];

                if ($rules === []) {
                    $output->writeln($this->msg('no_rules_from_table', ['{table}' => $table]));
                    return self::FAILURE;
                }
            } catch (\Throwable $e) {
                $output->writeln($this->msg('failed_generate_from_table', ['{table}' => $table]));
                $output->writeln($this->msg('reason', ['{reason}' => $e->getMessage()]));
                return self::FAILURE;
            }
        }

        $renderer = new ValidatorClassRenderer();
        $content = $renderer->render($namespace, $class, $rules, $messages, $attributes, $scenes);

        try {
            (new ValidatorFileWriter())->write($file, $content);
        } catch (\Throwable $e) {
            $output->writeln($this->msg('failed_write_file', ['{path}' => $this->toRelativePath($file)]));
            $output->writeln($this->msg('reason', ['{reason}' => $e->getMessage()]));
            return self::FAILURE;
        }

        $output->writeln($this->msg('created', ['{path}' => $this->toRelativePath($file)]));
        $output->writeln($this->msg('class', ['{class}' => $namespace . '\\' . $class]));
        if ($table !== '') {
            $output->writeln($this->msg('table', ['{table}' => $table]));
            $output->writeln($this->msg('rules_count', ['{count}' => (string)count($rules)]));
            if ($scenesOption !== '') {
                $output->writeln($this->msg('scenes_count', ['{count}' => (string)count($scenes)]));
            }
        }
        return self::SUCCESS;
    }

    /**
     * @return array{0:string,1:string,2:string} [namespace, class, file]
     */
    private function resolveAppValidationTarget(string $name): array
    {
        $name = trim($name);
        if ($name === '') {
            throw new \InvalidArgumentException($this->plain('invalid_name_empty_plain'));
        }

        // Normalize separators for Windows/Unix inputs.
        $normalized = str_replace('\\', '/', $name);
        $normalized = trim($normalized, '/');

        $segments = array_values(array_filter(explode('/', $normalized), static fn (string $s): bool => $s !== ''));
        if ($segments === []) {
            throw new \InvalidArgumentException($this->plain('invalid_name_empty_plain'));
        }

        $classSegment = array_pop($segments);

        // Convert to PSR-friendly StudlyCase for both directory segments and class name.
        $dirSegments = array_map([$this, 'toStudly'], $segments);
        $class = $this->toStudly($classSegment);

        $namespace = 'app\\validation';
        if ($dirSegments !== []) {
            $namespace .= '\\' . implode('\\', $dirSegments);
        }

        $validationDirName = $this->guessPath(app_path(), 'validation') ?: 'validation';
        $baseDir = app_path() . DIRECTORY_SEPARATOR . $validationDirName;
        $dir = $dirSegments === []
            ? $baseDir
            : ($baseDir . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $dirSegments));
        $file = $dir . DIRECTORY_SEPARATOR . $class . '.php';

        return [$namespace, $class, $file];
    }

    private function toStudly(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            throw new \InvalidArgumentException($this->plain('invalid_segment_empty_plain'));
        }

        $studly = $this->nameToClass($name);
        if (str_contains($studly, '/')) {
            // Should never happen because we pass a single segment.
            $studly = basename(str_replace('/', DIRECTORY_SEPARATOR, $studly));
        }

        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $studly)) {
            throw new \InvalidArgumentException($this->plain('invalid_segment_plain', ['{name}' => $name]));
        }

        return $studly;
    }

    // Rendering moved to ValidatorGenerator\Support\ValidatorClassRenderer

    /**
     * @param string $plugin
     * @return string relative path
     */
    private function getPluginValidationRelativePath(string $plugin): string
    {
        $plugin = trim($plugin);
        $appDir = base_path('plugin' . DIRECTORY_SEPARATOR . $plugin . DIRECTORY_SEPARATOR . 'app');
        $validationDir = $this->guessPath($appDir, 'validation') ?: 'validation';
        return $this->normalizeRelativePath("plugin/{$plugin}/app/{$validationDir}");
    }

    /**
     * CLI messages (bilingual).
     *
     * @param string $key
     * @param array $replace
     * @return string
     */
    private function msg(string $key, array $replace = []): string
    {
        $map = $this->selectMessageMap(Messages::getCliMessages());
        $text = $map[$key] ?? $key;
        return $replace ? strtr($text, $replace) : $text;
    }

    /**
     * Plain (no console tags) bilingual messages for exception messages.
     *
     * @param string $key
     * @param array $replace
     * @return string
     */
    private function plain(string $key, array $replace = []): string
    {
        $map = $this->selectMessageMap(Messages::getPlainMessages());
        $text = $map[$key] ?? $key;
        return $replace ? strtr($text, $replace) : $text;
    }

    /**
     * Command help text (bilingual).
     *
     * @return string
     */
    private function buildHelpText(): string
    {
        return $this->selectByLocale(Messages::getHelpText());
    }

    private function getLocale(): string
    {
        $locale = 'en';
        if (function_exists('config')) {
            $value = config('translation.locale', 'en');
            $value = is_string($value) ? trim($value) : '';
            if ($value !== '') {
                $locale = $value;
            }
        }
        return $locale;
    }

    /**
     * @param array<string, string> $localeToValue
     */
    private function selectByLocale(array $localeToValue): string
    {
        $locale = $this->getLocale();
        if (isset($localeToValue[$locale])) {
            return $localeToValue[$locale];
        }
        $lang = explode('_', $locale)[0] ?? '';
        if ($lang !== '' && isset($localeToValue[$lang])) {
            return $localeToValue[$lang];
        }
        if (isset($localeToValue['en'])) {
            return $localeToValue['en'];
        }
        if (isset($localeToValue['zh_CN'])) {
            return $localeToValue['zh_CN'];
        }
        $first = reset($localeToValue);
        return is_string($first) ? $first : '';
    }

    /**
     * @param array<string, array<string, string>> $localeToMessages
     * @return array<string, string>
     */
    private function selectMessageMap(array $localeToMessages): array
    {
        $locale = $this->getLocale();
        if (isset($localeToMessages[$locale])) {
            return $localeToMessages[$locale];
        }
        $lang = explode('_', $locale)[0] ?? '';
        if ($lang !== '' && isset($localeToMessages[$lang])) {
            return $localeToMessages[$lang];
        }
        if (isset($localeToMessages['en'])) {
            return $localeToMessages['en'];
        }
        if (isset($localeToMessages['zh_CN'])) {
            return $localeToMessages['zh_CN'];
        }
        $first = reset($localeToMessages);
        return is_array($first) ? $first : [];
    }

    private function normalizeOptionValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim((string)$value);
        $value = ltrim($value, '=');
        return $value === '' ? null : $value;
    }

    private function normalizeRelativePath(string $path): string
    {
        $path = trim($path);
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('#^\\./+#', '', $path);
        $path = trim($path, '/');
        return $path;
    }

    private function isAbsolutePath(string $path): bool
    {
        $path = trim($path);
        if ($path === '') {
            return false;
        }
        if (preg_match('/^[a-zA-Z]:[\\\\\\/]/', $path)) {
            return true;
        }
        if (str_starts_with($path, '\\\\') || str_starts_with($path, '//')) {
            return true;
        }
        return str_starts_with($path, '/') || str_starts_with($path, '\\');
    }

    private function pathsEqual(string $a, string $b): bool
    {
        $a = strtolower($this->normalizeRelativePath($a));
        $b = strtolower($this->normalizeRelativePath($b));
        return $a === $b;
    }

    private function pluginExists(string $plugin): bool
    {
        if (!function_exists('config')) {
            return false;
        }
        $cfg = config("plugin.$plugin");
        return !empty($cfg);
    }

    private function resolveDatabaseConnectionNameForTable(
        ?string $plugin,
        ?string $explicitConnection,
        string $explicitConnectionRaw,
        string $orm,
        OutputInterface $output
    ): ?string {
        if (!function_exists('config')) {
            throw new \RuntimeException($this->plain('config_not_available'));
        }

        if ($orm === OrmDetector::ORM_THINKORM) {
            $main = config('think-orm');
            if (!is_array($main) || $main === []) {
                $alt = config('thinkorm');
                $main = is_array($alt) ? $alt : [];
            }

            $mainConnections = $main['connections'] ?? null;
            $mainConnections = is_array($mainConnections) ? $mainConnections : [];
            $mainDefault = $main['default'] ?? null;
            $mainDefault = is_string($mainDefault) ? trim($mainDefault) : '';

            $usePlugin = false;
            $connections = $mainConnections;
            $defaultConnection = $mainDefault;

            if ($plugin) {
                $pluginCfg = config("plugin.$plugin.thinkorm");
                if (!is_array($pluginCfg) || $pluginCfg === []) {
                    $alt = config("plugin.$plugin.think-orm");
                    $pluginCfg = is_array($alt) ? $alt : [];
                }
                $pluginConnections = $pluginCfg['connections'] ?? null;
                if (is_array($pluginConnections) && $pluginConnections !== []) {
                    $usePlugin = true;
                    $connections = $pluginConnections;
                    $pluginDefault = config("plugin.$plugin.thinkorm.default");
                    if (!is_string($pluginDefault) || trim($pluginDefault) === '') {
                        $pluginDefault = config("plugin.$plugin.think-orm.default");
                    }
                    $defaultConnection = is_string($pluginDefault) ? trim($pluginDefault) : '';
                }
            }

            $name = $explicitConnection !== null ? trim($explicitConnection) : '';
            if ($name === '') {
                $name = trim((string)$defaultConnection);
            }
            if ($name === '') {
                throw new \RuntimeException($this->plain('database_connection_not_provided'));
            }

            if (!array_key_exists($name, $connections)) {
                if ($explicitConnection !== null && $explicitConnectionRaw !== '') {
                    $output->writeln($this->msg('database_connection_not_found', ['{connection}' => $explicitConnectionRaw]));
                    return null;
                }
                $available = implode(', ', array_keys($connections));
                throw new \RuntimeException($this->plain('thinkorm_connection_not_found', ['{name}' => $name, '{available}' => $available]));
            }

            return ($usePlugin && $plugin) ? "plugin.$plugin.$name" : $name;
        }

        $dbConfig = config('database', []);
        if (!is_array($dbConfig)) {
            throw new \RuntimeException($this->plain('database_config_invalid'));
        }
        $mainConnections = $dbConfig['connections'] ?? null;
        $mainConnections = is_array($mainConnections) ? $mainConnections : [];
        $mainDefault = $dbConfig['default'] ?? null;
        $mainDefault = is_string($mainDefault) ? trim($mainDefault) : '';

        $usePlugin = false;
        $connections = $mainConnections;
        $defaultConnection = $mainDefault;

        if ($plugin) {
            $pluginDb = config("plugin.$plugin.database");
            if (is_array($pluginDb)) {
                $pluginConnections = $pluginDb['connections'] ?? null;
                if (is_array($pluginConnections) && $pluginConnections !== []) {
                    $usePlugin = true;
                    $connections = $pluginConnections;
                    $pluginDefault = config("plugin.$plugin.database.default");
                    $defaultConnection = is_string($pluginDefault) ? trim($pluginDefault) : '';
                }
            }
        }

        $name = $explicitConnection !== null ? trim($explicitConnection) : '';
        if ($name === '') {
            $name = trim((string)$defaultConnection);
        }
        if ($name === '') {
            throw new \RuntimeException($this->plain('database_connection_not_provided'));
        }

        if (!array_key_exists($name, $connections)) {
            if ($explicitConnection !== null && $explicitConnectionRaw !== '') {
                $output->writeln($this->msg('database_connection_not_found', ['{connection}' => $explicitConnectionRaw]));
                return null;
            }
            $available = implode(', ', array_keys($connections));
            throw new \RuntimeException($this->plain('database_connection_not_found_available', ['{name}' => $name, '{available}' => $available]));
        }

        return ($usePlugin && $plugin) ? "plugin.$plugin.$name" : $name;
    }

    private function toRelativePath(string $path): string
    {
        $base = base_path();
        $baseNorm = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $base), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $pathNorm = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        if (str_starts_with(strtolower($pathNorm), strtolower($baseNorm))) {
            $rel = substr($pathNorm, strlen($baseNorm));
        } else {
            $rel = $pathNorm;
        }
        return str_replace(DIRECTORY_SEPARATOR, '/', $rel);
    }

    /**
     * @return array{0:string,1:string,2:string}|null [class, namespace, file]
     */
    private function resolveTargetByPluginOrPath(
        string $name,
        ?string $plugin,
        ?string $path,
        OutputInterface $output,
        callable $pluginDefaultPathResolver,
        callable $msg
    ): ?array {
        $pathNorm = $path ? $this->normalizeRelativePath($path) : null;
        if ($pathNorm !== null && $this->isAbsolutePath($pathNorm)) {
            $output->writeln($msg('invalid_path', ['{path}' => (string)$path]));
            return null;
        }

        $expected = null;
        if ($plugin) {
            $expected = $pluginDefaultPathResolver($plugin);
        }

        if ($plugin && $pathNorm) {
            $pluginPrefix = 'plugin/' . $plugin . '/';
            if (!str_starts_with(strtolower($pathNorm), strtolower($pluginPrefix))) {
                $output->writeln($msg('plugin_path_conflict', [
                    '{plugin}' => $plugin,
                    '{path}' => $pathNorm,
                ]));
                return null;
            }
        }

        $targetRel = $pathNorm ?: $expected;
        if (!$targetRel) {
            return null;
        }

        $targetDir = base_path($targetRel);
        $namespaceRoot = trim(str_replace('/', '\\', $targetRel), '\\');

        if (!($pos = strrpos($name, '/'))) {
            $class = ucfirst($name);
            $subPath = '';
        } else {
            $subPath = substr($name, 0, $pos);
            $class = ucfirst(substr($name, $pos + 1));
        }

        $subDir = $subPath ? str_replace('/', DIRECTORY_SEPARATOR, $subPath) . DIRECTORY_SEPARATOR : '';
        $file = $targetDir . DIRECTORY_SEPARATOR . $subDir . $class . '.php';
        $namespace = $namespaceRoot . ($subPath ? '\\' . str_replace('/', '\\', $subPath) : '');

        return [$class, $namespace, $file];
    }

    private function nameToClass(string $class): string
    {
        $class = preg_replace_callback(['/-([a-zA-Z])/', '/_([a-zA-Z])/'], function ($matches) {
            return strtoupper($matches[1]);
        }, $class);

        if (!($pos = strrpos($class, '/'))) {
            $class = ucfirst($class);
        } else {
            $path = substr($class, 0, $pos);
            $class = ucfirst(substr($class, $pos + 1));
            $class = "$path/$class";
        }
        return $class;
    }

    private function guessPath(string $basePath, string $name, bool $returnFullPath = false)
    {
        if (!is_dir($basePath)) {
            return false;
        }
        $names = explode('/', trim(strtolower($name), '/'));
        $realname = [];
        $path = $basePath;
        foreach ($names as $n) {
            $found = false;
            foreach (scandir($path) ?: [] as $tmpName) {
                if (strtolower($tmpName) === $n && is_dir($path . DIRECTORY_SEPARATOR . $tmpName)) {
                    $path = $path . DIRECTORY_SEPARATOR . $tmpName;
                    $realname[] = $tmpName;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                return false;
            }
        }
        $realname = implode(DIRECTORY_SEPARATOR, $realname);
        return $returnFullPath ? realpath($basePath . DIRECTORY_SEPARATOR . $realname) : $realname;
    }
}

