<?php
declare(strict_types=1);

namespace Webman\Validation\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Webman\Validation\Factory\ValidationFactory;
use support\validation\Validator;
use support\validation\ValidationException;

final class TranslationTest extends TestCase
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function localeEmailMessageProvider(): array
    {
        return [
            'zh_CN' => ['zh_CN', '必须是有效的邮箱地址'],
            'zh_TW' => ['zh_TW', '必須是有效的電子郵件地址'],
            'en' => ['en', 'must be a valid email address'],
            'ja' => ['ja', '有効なメールアドレスである必要があります'],
            'ko' => ['ko', '유효한 이메일 주소여야 합니다'],
            'fr' => ['fr', 'doit être une adresse e-mail valide'],
            'de' => ['de', 'muss eine gültige E-Mail-Adresse sein'],
            'es' => ['es', 'debe ser una dirección de correo electrónico válida'],
            'pt_BR' => ['pt_BR', 'deve ser um endereço de e-mail válido'],
            'ru' => ['ru', 'должно быть действительным адресом электронной почты'],
            'vi' => ['vi', 'phải là địa chỉ email hợp lệ'],
            'tr' => ['tr', 'geçerli bir e-posta adresi olmalıdır'],
            'id' => ['id', 'harus berupa alamat email yang valid'],
            'th' => ['th', 'ต้องเป็นที่อยู่อีเมลที่ถูกต้อง'],
        ];
    }

    #[DataProvider('localeEmailMessageProvider')]
    public function testValidationMessageInLocale(string $locale, string $expectedSubstring): void
    {
        $packageLangPath = dirname(__DIR__, 2) . '/resources/lang';
        if (!is_dir($packageLangPath) || !is_dir($packageLangPath . '/' . $locale)) {
            $this->markTestSkipped("Locale {$locale} not found in package resources/lang.");
        }

        validation_test_set_config([
            'translation' => [
                'path' => $packageLangPath,
                'locale' => $locale,
                'fallback_locale' => [$locale],
            ],
        ]);
        if (function_exists('locale')) {
            locale($locale);
        }
        $this->resetTranslationInstance();
        $this->resetFactory();

        try {
            Validator::make(['email' => 'bad-email'], ['email' => 'required|email'])->validate();
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString(
                $expectedSubstring,
                $exception->getMessage(),
                "Locale {$locale}: expected message to contain '{$expectedSubstring}'"
            );
        }
    }

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
