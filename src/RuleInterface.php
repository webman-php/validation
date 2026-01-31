<?php
declare(strict_types=1);

namespace Webman\Validation;

interface RuleInterface
{
    public function setScene(string $scene): static;

    public function rules(): array;

    public function messages(): array;

    public function attributes(): array;

    public function scenes(): array;
}
