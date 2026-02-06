<?php
declare(strict_types=1);

namespace Webman\Validation\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Webman\Console\Commands\Concerns\MakeCommandHelpers;
use Webman\Console\Util;
use Webman\Validation\Command\ValidatorGenerator\Illuminate\IlluminateConnectionResolver;
use Webman\Validation\Command\ValidatorGenerator\Rules\DefaultRuleInferrer;
use Webman\Validation\Command\ValidatorGenerator\Support\ExcludedColumns;
use Webman\Validation\Command\ValidatorGenerator\Support\OrmDetector;
use Webman\Validation\Command\ValidatorGenerator\Support\SchemaIntrospectorFactory;
use Webman\Validation\Command\ValidatorGenerator\ThinkOrm\ThinkOrmConnectionResolver;
use Webman\Validation\Command\ValidatorGenerator\Support\ValidatorClassRenderer;
use Webman\Validation\Command\ValidatorGenerator\Support\ValidatorFileWriter;

#[AsCommand('make:validator', 'Make validation validator class.')]
final class MakeValidatorCommand extends Command
{
    use MakeCommandHelpers;

    protected function configure(): void
    {
        $this->setDescription($this->isZhLocale() ? '生成验证器类' : 'Make validation validator class');

        $this->addArgument(
            'name',
            InputArgument::REQUIRED,
            $this->isZhLocale()
                ? '验证器类名（例如：UserValidator、admin/UserValidator）'
                : 'Validator class name (e.g. UserValidator, admin/UserValidator)'
        );
        $this->addOption(
            'plugin',
            'p',
            InputOption::VALUE_REQUIRED,
            $this->isZhLocale() ? '插件名（plugin/ 下的目录名），例如：admin' : 'Plugin name under plugin/. e.g. admin'
        );
        $this->addOption(
            'path',
            'P',
            InputOption::VALUE_REQUIRED,
            $this->isZhLocale()
                ? '目标目录（相对项目根目录），例如：plugin/admin/app/validation'
                : 'Target directory (relative to base path). e.g. plugin/admin/app/validation'
        );
        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_NONE,
            $this->isZhLocale() ? '文件已存在时强制覆盖' : 'Overwrite if file already exists'
        );
        $this->addOption(
            'table',
            't',
            InputOption::VALUE_REQUIRED,
            $this->isZhLocale() ? '从数据库表推断并生成规则（例如：users）' : 'Generate rules from database table (e.g. users)'
        );
        $this->addOption(
            'connection',
            'c',
            InputOption::VALUE_REQUIRED,
            $this->isZhLocale() ? '数据库连接名' : 'Database connection name'
        );
        $this->addOption(
            'scenes',
            's',
            InputOption::VALUE_REQUIRED,
            $this->isZhLocale() ? '生成场景（支持：crud）' : 'Generate scenes (supported: crud)'
        );
        $this->addOption(
            'orm',
            'o',
            InputOption::VALUE_REQUIRED,
            $this->isZhLocale()
                ? '使用的 ORM：auto|laravel|thinkorm（默认：auto）'
                : 'ORM to use: auto|laravel|thinkorm (default: auto)'
        );

        $this->setHelp($this->buildHelpText());
        $this->addUsage('UserValidator');
        $this->addUsage('admin/UserValidator');
        $this->addUsage('UserValidator -p admin');
        $this->addUsage('UserValidator -P plugin/admin/app/validation');
        $this->addUsage('UserValidator -t users -c default');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rawName = (string)$input->getArgument('name');
        $name = Util::nameToClass($rawName);
        $name = str_replace('\\', '/', $name);
        $name = trim($name, '/');

        $plugin = $this->normalizeOptionValue($input->getOption('plugin'));
        $path = $this->normalizeOptionValue($input->getOption('path'));
        $force = (bool)$input->getOption('force');
        $table = $input->getOption('table');
        $table = is_string($table) ? trim($table) : '';
        // Some Symfony Console versions parse `-t=foo` as `=foo` for short options.
        $table = ltrim($table, '=');
        $connectionName = $input->getOption('connection');
        $connectionName = is_string($connectionName) ? trim($connectionName) : '';
        // Some Symfony Console versions parse `-c=foo` as `=foo` for short options.
        $connectionName = ltrim($connectionName, '=');
        $connectionName = $connectionName !== '' ? $connectionName : null;
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

        if (is_file($file) && !$force) {
            $output->writeln($this->msg('file_exists', ['{path}' => $this->toRelativePath($file)]));
            $output->writeln($this->msg('use_force'));
            return self::FAILURE;
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

                $connection = $resolver->resolve($connectionName);

                $factory = new SchemaIntrospectorFactory();
                $introspector = $factory->createForDriver($connection->driverName());

                $tableDef = $introspector->introspect($connection, $table);

                $excludeColumns = match ($orm) {
                    OrmDetector::ORM_LARAVEL => ExcludedColumns::defaultForIlluminate(),
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

        $validationDirName = Util::guessPath(app_path(), 'validation') ?: 'validation';
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

        // Util::nameToClass converts snake_case / kebab-case to StudlyCase.
        $studly = Util::nameToClass($name);
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
        $validationDir = Util::guessPath($appDir, 'validation') ?: 'validation';
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
        $zh = [
            'invalid_name_empty' => '<error>验证器类名不能为空。</error>',
            'invalid_name' => '<error>验证器类名无效：{name}</error>',
            'invalid_plugin' => '<error>插件名无效：{plugin}。`--plugin/-p` 只能是 plugin/ 目录下的目录名，不能包含 / 或 \\。</error>',
            'plugin_path_conflict' => "<error>`--plugin/-p` 与 `--path/-P` 同时指定且不一致。\n期望路径：{expected}\n实际路径：{actual}\n请二选一或保持一致。</error>",
            'invalid_path' => '<error>路径无效：{path}。`--path/-P` 必须是相对路径（相对于项目根目录），不能是绝对路径。</error>',
            'file_exists' => '<error>文件已存在：</error> {path}',
            'use_force' => '使用 <comment>--force/-f</comment> 强制覆盖。',
            'scenes_requires_table' => '<error>选项 --scenes 需要同时指定 --table。</error>',
            'unsupported_orm' => '<error>不支持的 ORM：{orm}（支持：auto/laravel/thinkorm）。</error>',
            'no_rules_from_table' => '<error>无法从数据表推断出规则：</error> {table}',
            'failed_generate_from_table' => '<error>从数据表生成验证器失败：</error> {table}',
            'failed_write_file' => '<error>写入文件失败：</error> {path}',
            'reason' => '<comment>原因：</comment> {reason}',
            'created' => '<info>已创建：</info> {path}',
            'class' => '<info>类：</info> {class}',
            'table' => '<info>数据表：</info> {table}',
            'rules_count' => '<info>规则数：</info> {count}',
            'scenes_count' => '<info>场景数：</info> {count}',
        ];

        $en = [
            'invalid_name_empty' => '<error>Validator name cannot be empty.</error>',
            'invalid_name' => '<error>Invalid validator name: {name}</error>',
            'invalid_plugin' => '<error>Invalid plugin name: {plugin}. `--plugin/-p` must be a directory name under plugin/ and must not contain / or \\.</error>',
            'plugin_path_conflict' => "<error>`--plugin/-p` and `--path/-P` are both provided but inconsistent.\nExpected: {expected}\nActual: {actual}\nPlease provide only one, or make them identical.</error>",
            'invalid_path' => '<error>Invalid path: {path}. `--path/-P` must be a relative path (to project root) and must not be an absolute path.</error>',
            'file_exists' => '<error>File already exists:</error> {path}',
            'use_force' => 'Use <comment>--force/-f</comment> to overwrite.',
            'scenes_requires_table' => '<error>Option --scenes requires --table.</error>',
            'unsupported_orm' => '<error>Unsupported ORM: {orm} (supported: auto/laravel/thinkorm).</error>',
            'no_rules_from_table' => '<error>No rules inferred from table:</error> {table}',
            'failed_generate_from_table' => '<error>Failed to generate validator from table:</error> {table}',
            'failed_write_file' => '<error>Failed to write file:</error> {path}',
            'reason' => '<comment>Reason:</comment> {reason}',
            'created' => '<info>Created:</info> {path}',
            'class' => '<info>Class:</info> {class}',
            'table' => '<info>Table:</info> {table}',
            'rules_count' => '<info>Rules:</info> {count}',
            'scenes_count' => '<info>Scenes:</info> {count}',
        ];

        $map = $this->isZhLocale() ? $zh : $en;
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
        $zh = [
            'invalid_name_empty_plain' => '验证器类名不能为空。',
            'invalid_segment_empty_plain' => '名称段不能为空。',
            'invalid_segment_plain' => '名称段无效：{name}',
        ];
        $en = [
            'invalid_name_empty_plain' => 'Validator name cannot be empty.',
            'invalid_segment_empty_plain' => 'Name segment cannot be empty.',
            'invalid_segment_plain' => 'Invalid name segment: {name}',
        ];
        $map = $this->isZhLocale() ? $zh : $en;
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
        if ($this->isZhLocale()) {
            return <<<'EOF'
生成验证器类文件（默认在 app/validation 下）。

推荐用法：
  php webman make:validator UserValidator
  php webman make:validator admin/UserValidator
  php webman make:validator UserValidator -p admin
  php webman make:validator UserValidator -P plugin/admin/app/validation

说明：
  - 默认生成到 app/validation（大小写以现有目录为准）。
  - 使用 -p/--plugin 时默认生成到 plugin/<plugin>/app/validation。
  - 使用 -P/--path 时生成到指定相对目录（相对于项目根目录）。
  - 文件已存在时默认拒绝覆盖；使用 -f/--force 可强制覆盖。
  - 使用 -t/--table 可从数据库表推断规则；如需生成场景请同时指定 -s/--scenes（例如 crud）。
EOF;
        }

        return <<<'EOF'
Generate a validator class file (default under app/validation).

Recommended:
  php webman make:validator UserValidator
  php webman make:validator admin/UserValidator
  php webman make:validator UserValidator -p admin
  php webman make:validator UserValidator -P plugin/admin/app/validation

Notes:
  - By default, it generates under app/validation (case depends on existing directory).
  - With -p/--plugin, it generates under plugin/<plugin>/app/validation by default.
  - With -P/--path, it generates under the specified relative directory (to project root).
  - If the file already exists, it refuses to overwrite by default; use -f/--force to overwrite.
  - With -t/--table, it infers rules from a database table; to generate scenes, also provide -s/--scenes (e.g. crud).
EOF;
    }
}

