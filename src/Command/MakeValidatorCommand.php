<?php
declare(strict_types=1);

namespace Webman\Validation\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Webman\Console\Util;
use Webman\Validation\Command\ValidatorGenerator\Illuminate\IlluminateConnectionResolver;
use Webman\Validation\Command\ValidatorGenerator\Rules\DefaultRuleInferrer;
use Webman\Validation\Command\ValidatorGenerator\Support\ExcludedColumns;
use Webman\Validation\Command\ValidatorGenerator\Support\OrmDetector;
use Webman\Validation\Command\ValidatorGenerator\Support\SchemaIntrospectorFactory;
use Webman\Validation\Command\ValidatorGenerator\ThinkOrm\ThinkOrmConnectionResolver;
use Webman\Validation\Command\ValidatorGenerator\Support\ValidatorClassRenderer;
use Webman\Validation\Command\ValidatorGenerator\Support\ValidatorFileWriter;

final class MakeValidatorCommand extends Command
{
    protected static $defaultName = 'make:validator';
    protected static $defaultDescription = 'Make validation validator class';

    protected function configure(): void
    {
        $this->addArgument(
            'name',
            InputArgument::REQUIRED,
            'Validator class name (e.g. UserValidator, admin/UserValidator)'
        );
        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Overwrite if file already exists');
        $this->addOption('table', 't', InputOption::VALUE_REQUIRED, 'Generate rules from database table (e.g. users)');
        $this->addOption('connection', 'c', InputOption::VALUE_REQUIRED, 'Database connection name');
        $this->addOption('scenes', 's', InputOption::VALUE_REQUIRED, 'Generate scenes (supported: crud)');
        $this->addOption('orm', 'o', InputOption::VALUE_REQUIRED, 'ORM to use: auto|laravel|thinkorm (default: auto)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rawName = (string)$input->getArgument('name');
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

        [$namespace, $class, $file] = $this->resolveTarget($rawName);

        if (is_file($file) && !$force) {
            $output->writeln("<error>File already exists:</error> {$file}");
            $output->writeln('Use <comment>--force</comment> to overwrite.');
            return self::FAILURE;
        }

        $rules = [];
        $messages = [];
        $attributes = [];
        $scenes = [];

        if ($scenesOption !== '' && $table === '') {
            $output->writeln('<error>Option --scenes requires --table.</error>');
            return self::FAILURE;
        }

        if ($table !== '') {
            try {
                $detector = new OrmDetector();
                $orm = $detector->resolve($ormOption);

                $resolver = match ($orm) {
                    OrmDetector::ORM_LARAVEL => new IlluminateConnectionResolver(),
                    OrmDetector::ORM_THINKORM => new ThinkOrmConnectionResolver(),
                    default => throw new \RuntimeException("Unsupported orm: {$orm}"),
                };

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
                    $output->writeln("<error>No rules inferred from table:</error> {$table}");
                    return self::FAILURE;
                }
            } catch (\Throwable $e) {
                $output->writeln('<error>Failed to generate validator from table.</error>');
                $output->writeln($e->getMessage());
                return self::FAILURE;
            }
        }

        $renderer = new ValidatorClassRenderer();
        $content = $renderer->render($namespace, $class, $rules, $messages, $attributes, $scenes);

        try {
            (new ValidatorFileWriter())->write($file, $content);
        } catch (\Throwable $e) {
            $output->writeln("<error>Failed to write file:</error> {$file}");
            $output->writeln($e->getMessage());
            return self::FAILURE;
        }

        $output->writeln("<info>Created:</info> {$file}");
        $output->writeln("<info>Class:</info> {$namespace}\\{$class}");
        if ($table !== '') {
            $output->writeln("<info>Table:</info> {$table}");
            $output->writeln('<info>Rules:</info> ' . count($rules));
            if ($scenesOption !== '') {
                $output->writeln('<info>Scenes:</info> ' . count($scenes));
            }
        }
        return self::SUCCESS;
    }

    /**
     * @return array{0:string,1:string,2:string} [namespace, class, file]
     */
    private function resolveTarget(string $name): array
    {
        $name = trim($name);
        if ($name === '') {
            throw new \InvalidArgumentException('Validator name cannot be empty.');
        }

        // Normalize separators for Windows/Unix inputs.
        $normalized = str_replace('\\', '/', $name);
        $normalized = trim($normalized, '/');

        $segments = array_values(array_filter(explode('/', $normalized), static fn (string $s): bool => $s !== ''));
        if ($segments === []) {
            throw new \InvalidArgumentException('Validator name cannot be empty.');
        }

        $classSegment = array_pop($segments);

        // Convert to PSR-friendly StudlyCase for both directory segments and class name.
        $dirSegments = array_map([$this, 'toStudly'], $segments);
        $class = $this->toStudly($classSegment);

        $namespace = 'app\\validation';
        if ($dirSegments !== []) {
            $namespace .= '\\' . implode('\\', $dirSegments);
        }

        $baseDir = app_path() . DIRECTORY_SEPARATOR . 'validation';
        $dir = $dirSegments === [] ? $baseDir : ($baseDir . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $dirSegments));
        $file = $dir . DIRECTORY_SEPARATOR . $class . '.php';

        return [$namespace, $class, $file];
    }

    private function toStudly(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            throw new \InvalidArgumentException('Name segment cannot be empty.');
        }

        // Util::nameToClass converts snake_case / kebab-case to StudlyCase.
        $studly = Util::nameToClass($name);
        if (str_contains($studly, '/')) {
            // Should never happen because we pass a single segment.
            $studly = basename(str_replace('/', DIRECTORY_SEPARATOR, $studly));
        }

        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $studly)) {
            throw new \InvalidArgumentException("Invalid name segment: {$name}");
        }

        return $studly;
    }

    // Rendering moved to ValidatorGenerator\Support\ValidatorClassRenderer
}

