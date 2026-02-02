<?php
declare(strict_types=1);

namespace Webman\Validation;

use Illuminate\Support\MessageBag;
use Illuminate\Validation\Validator as IlluminateValidator;
use InvalidArgumentException;
use support\Container;
use Throwable;
use Webman\Validation\Factory\ValidationFactory;

class Validator
{
    public static function make(
        array $data,
        ?array $rules = null,
        ?array $messages = null,
        ?array $attributes = null
    ): static {
        /** @var static $instance */
        $instance = Container::make(static::class);
        $instance->data = $data;

        $instance->rules = $rules ?? $instance->rules;
        $instance->messages = $messages ?? $instance->messages;
        $instance->attributes = $attributes ?? $instance->attributes;

        if ($instance->rules === []) {
            throw new InvalidArgumentException('Validation rules cannot be empty.');
        }

        return $instance;
    }

    protected array $rules = [];
    protected array $messages = [];
    protected array $attributes = [];
    protected array $scenes = [];

    protected array $data = [];
    protected ?string $scene = null;
    private ?IlluminateValidator $validator = null;

    public function withScene(string $scene): static
    {
        $clone = clone $this;
        $clone->scene = $scene;
        $clone->validator = null;
        return $clone;
    }

    public function validate(): array
    {
        $validator = $this->toIlluminate();
        if ($validator->fails()) {
            $exceptionClass = $this->resolveExceptionClass();
            $message = $validator->errors()->first() ?: 'Validation failed';
            throw new $exceptionClass($message);
        }
        return $validator->validated();
    }

    public function passes(): bool
    {
        return $this->toIlluminate()->passes();
    }

    public function fails(): bool
    {
        return $this->toIlluminate()->fails();
    }

    public function errors(): MessageBag
    {
        return $this->toIlluminate()->errors();
    }

    public function validated(): array
    {
        return $this->toIlluminate()->validated();
    }

    public function toIlluminate(): IlluminateValidator
    {
        if ($this->validator !== null) {
            return $this->validator;
        }

        $factory = ValidationFactory::getFactory();
        $this->validator = $factory->make(
            $this->data(),
            $this->rules(),
            $this->messages(),
            $this->attributes()
        );
        return $this->validator;
    }

    /**
     * Override this in subclasses to build validation rules dynamically.
     */
    public function rules(): array
    {
        return $this->resolveRules();
    }

    /**
     * Override this in subclasses to build custom messages dynamically.
     */
    public function messages(): array
    {
        return $this->messages;
    }

    /**
     * Override this in subclasses to build custom attribute names dynamically.
     */
    public function attributes(): array
    {
        return $this->attributes;
    }

    /**
     * Expose incoming validation data for subclasses.
     */
    public function data(): array
    {
        return $this->data;
    }

    /**
     * Expose current scene for subclasses.
     */
    protected function scene(): ?string
    {
        return $this->scene;
    }

    public function __call(string $name, array $arguments): mixed
    {
        $validator = $this->toIlluminate();
        if (!method_exists($validator, $name)) {
            throw new InvalidArgumentException("Validator method not found: {$name}");
        }
        return $validator->{$name}(...$arguments);
    }

    private function resolveRules(): array
    {
        $scene = $this->scene;
        if ($scene === null) {
            return $this->rules;
        }

        if (!array_key_exists($scene, $this->scenes)) {
            throw new InvalidArgumentException("Validation scene not defined: {$scene}");
        }

        $fields = $this->scenes[$scene];
        if (!is_array($fields) || $fields === []) {
            throw new InvalidArgumentException("Validation scene has no fields: {$scene}");
        }

        $rules = array_intersect_key($this->rules, array_flip($fields));
        if ($rules === []) {
            throw new InvalidArgumentException("Validation rules not found for scene: {$scene}");
        }

        return $rules;
    }

    private function resolveExceptionClass(): string
    {
        $exceptionClass = config(
            'plugin.webman.validation.app.exception',
            \support\validation\ValidationException::class
        );

        if (!is_string($exceptionClass) || $exceptionClass === '') {
            throw new InvalidArgumentException('Validation exception must be a non-empty class string.');
        }
        if (!class_exists($exceptionClass)) {
            throw new InvalidArgumentException("Validation exception class not found: {$exceptionClass}");
        }
        if (!is_subclass_of($exceptionClass, Throwable::class)) {
            throw new InvalidArgumentException("Validation exception must implement Throwable: {$exceptionClass}");
        }

        return $exceptionClass;
    }
}
