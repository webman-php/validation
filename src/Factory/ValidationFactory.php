<?php
declare(strict_types=1);

namespace Webman\Validation\Factory;

use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use Illuminate\Validation\DatabasePresenceVerifier;
use Illuminate\Validation\DatabasePresenceVerifierInterface;
use Illuminate\Support\Facades\Facade;

final class ValidationFactory
{
    private static ?Factory $factory = null;
    private static bool $presenceVerifierBound = false;

    public static function getFactory(): Factory
    {
        if (self::$factory === null) {
            self::$factory = self::createFactory();
            self::$presenceVerifierBound = false;
        }

        if (!self::$presenceVerifierBound) {
            self::bindPresenceVerifier(self::$factory);
        }

        return self::$factory;
    }

    private static function createFactory(): Factory
    {
        $loader = self::createLoader();
        $translator = new Translator($loader, self::getLocale());

        $fallback = self::getFallbackLocale();
        if ($fallback !== '') {
            $translator->setFallback($fallback);
        }

        $factory = new Factory($translator);
        self::bindFacadeRoot($factory);
        self::bindPresenceVerifier($factory);
        return $factory;
    }

    private static function bindFacadeRoot(Factory $factory): void
    {
        if (Facade::getFacadeApplication() !== null) {
            return;
        }

        $container = new Container();
        $container->instance('validator', $factory);
        Facade::setFacadeApplication($container);
    }

    private static function bindPresenceVerifier(Factory $factory): void
    {
        if ($factory->getPresenceVerifier() instanceof DatabasePresenceVerifierInterface) {
            self::$presenceVerifierBound = true;
            return;
        }

        if (!class_exists(DatabasePresenceVerifier::class) || !class_exists(Model::class)) {
            self::$presenceVerifierBound = true;
            return;
        }

        $resolver = Model::getConnectionResolver();
        if ($resolver === null && class_exists('support\\Model')) {
            $resolver = Model::getConnectionResolver();
        }
        if ($resolver === null) {
            return;
        }

        $factory->setPresenceVerifier(new DatabasePresenceVerifier($resolver));
        self::$presenceVerifierBound = true;
    }

    private static function createLoader(): FileLoader
    {
        $filesystem = new Filesystem();

        $packagePath = self::getPackageTranslationsPath();
        $illuminatePath = self::getIlluminateTranslationsPath();
        $projectPaths = self::getProjectTranslationsPaths();

        $orderedPaths = array_values(array_filter([
            $packagePath,
            $illuminatePath,
            ...$projectPaths,
        ], fn ($path) => $path !== ''));

        $basePath = $orderedPaths[0] ?? '';
        $loader = new FileLoader($filesystem, $basePath);

        foreach ($orderedPaths as $path) {
            if ($path !== $basePath) {
                $loader->addPath($path);
            }
        }

        return $loader;
    }

    private static function getLocale(): string
    {
        if (self::canUseLocaleFunction() && function_exists('locale')) {
            $locale = locale();
            if (is_string($locale) && $locale !== '') {
                return $locale;
            }
        }

        $locale = config('translation.locale', 'en');
        return is_string($locale) && $locale !== '' ? $locale : 'en';
    }

    private static function getFallbackLocale(): string
    {
        $fallback = config('translation.fallback_locale', []);
        if (is_array($fallback)) {
            $fallback = $fallback[0] ?? '';
        }
        return is_string($fallback) ? $fallback : '';
    }

    private static function getProjectTranslationsPaths(): array
    {
        $path = config('translation.path', '');
        $paths = is_array($path) ? $path : [$path];

        $result = [];
        foreach ($paths as $item) {
            if (is_string($item) && $item !== '' && is_dir($item)) {
                $result[] = $item;
            }
        }

        return $result;
    }

    private static function canUseLocaleFunction(): bool
    {
        $path = config('translation.path', '');
        return is_string($path) && $path !== '' && is_dir($path);
    }

    private static function getPackageTranslationsPath(): string
    {
        $path = dirname(__DIR__, 2) . '/resources/lang';
        return is_dir($path) ? $path : '';
    }

    private static function getIlluminateTranslationsPath(): string
    {
        $path = dirname(__DIR__, 2) . '/vendor/illuminate/translation/lang';
        return is_dir($path) ? $path : '';
    }

}
