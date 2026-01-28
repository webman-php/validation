<?php
declare(strict_types=1);

namespace Webman\Validation\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Webman\Validation\Factory\ValidationFactory;
use support\validation\Validator;
use support\validation\ValidationException;

final class TranslationTest extends TestCase
{
    public function testLocalTranslationsOverridePackage(): void
    {
        if (!class_exists(\Symfony\Component\Translation\Translator::class)) {
            $this->markTestSkipped('symfony/translation is not installed.');
        }

        validation_test_set_config([
            'translation' => [
                'path' => $this->fixturePath('translations'),
                'locale' => 'zh_CN',
                'fallback_locale' => ['zh_CN'],
            ],
        ]);
        if (function_exists('locale')) {
            locale('zh_CN');
        }
        $this->resetTranslationInstance();
        $this->resetFactory();

        try {
            Validator::make(['email' => 'bad-email'], ['email' => 'required|email'])->validate();
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            $this->assertSame('[LOCAL_ZH] email invalid.', $exception->getMessage());
        }
    }

    public function testFallbackToPackageTranslations(): void
    {
        validation_test_set_config([
            'translation' => [
                'path' => $this->fixturePath('empty'),
                'locale' => 'en',
                'fallback_locale' => ['en'],
            ],
        ]);
        if (function_exists('locale')) {
            locale('en');
        }
        $this->resetTranslationInstance();
        $this->resetFactory();

        try {
            Validator::make(['email' => 'bad-email'], ['email' => 'required|email'])->validate();
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            $this->assertSame('The email field must be a valid email address.', $exception->getMessage());
        }
    }

    private function resetFactory(): void
    {
        $property = new ReflectionProperty(ValidationFactory::class, 'factory');
        $property->setAccessible(true);
        $property->setValue(null, null);
    }

    private function resetTranslationInstance(): void
    {
        if (!class_exists(\support\Translation::class)) {
            return;
        }
        $property = new ReflectionProperty(\support\Translation::class, 'instance');
        $property->setAccessible(true);
        $property->setValue(null, []);
    }

    private function fixturePath(string $name): string
    {
        return __DIR__ . '/fixtures/' . $name;
    }
}
