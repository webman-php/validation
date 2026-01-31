<?php
declare(strict_types=1);

namespace Webman\Validation\Command\ValidatorGenerator\Support;

final class ValidatorClassRenderer
{
    /**
     * @param array<string, string> $rules
     * @param array<string, string> $messages
     * @param array<string, string> $attributes
     * @param array<string, list<string>> $scenes
     */
    public function render(
        string $namespace,
        string $class,
        array $rules,
        array $messages = [],
        array $attributes = [],
        array $scenes = [],
    ): string {
        $rulesCode = PhpArrayExporter::exportForProperty($rules, 1);
        $messagesCode = PhpArrayExporter::exportForProperty($messages, 1);
        $attributesCode = PhpArrayExporter::exportForProperty($attributes, 1);
        $scenesCode = PhpArrayExporter::exportForProperty($scenes, 1);

        return <<<PHP
<?php
declare(strict_types=1);

namespace {$namespace};

use support\\validation\\Validator;

class {$class} extends Validator
{
    protected array \$rules = {$rulesCode};

    protected array \$messages = {$messagesCode};

    protected array \$attributes = {$attributesCode};

    protected array \$scenes = {$scenesCode};
}

PHP;
    }
}

