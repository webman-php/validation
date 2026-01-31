<?php
declare(strict_types=1);

namespace Webman\Validation;

use BadMethodCallException;
use Webman\Validation\Validator;

abstract class RuleBase implements RuleInterface
{
    protected string $scene = 'default';
    protected array $rules = [];
    protected array $messages = [];
    protected array $attributes = [];
    protected array $scenes = [];
    protected ?string $exceptionClass = null;

    public function __construct(string $scene = 'default')
    {
        $this->scene = $scene;
    }

    public static function scene(string $scene): static
    {
        return (new static())->setScene($scene);
    }

    public static function __callStatic(string $name, array $arguments): mixed
    {
        if ($name === 'validate') {
            $data = $arguments[0] ?? null;
            if (!is_array($data)) {
                throw new BadMethodCallException('Validation data must be an array.');
            }
            return (new static())->validate($data);
        }

        throw new BadMethodCallException("Method {$name} does not exist.");
    }

    public function setScene(string $scene): static
    {
        $this->scene = $scene;
        return $this;
    }

    public function rules(): array
    {
        return $this->filterByScene($this->rules);
    }

    public function messages(): array
    {
        return $this->messages;
    }

    public function attributes(): array
    {
        return $this->filterByScene($this->attributes);
    }

    public function scenes(): array
    {
        return $this->scenes;
    }

    public function validate(array $data): array
    {
        return Validator::make(
            $data,
            $this->rules(),
            $this->messages(),
            $this->attributes(),
            $this->exceptionClass
        )->validate();
    }

    protected function filterByScene(array $items): array
    {
        $fields = $this->scenes[$this->scene] ?? [];
        if ($fields === []) {
            return $items;
        }
        return array_intersect_key($items, array_flip($fields));
    }
}
