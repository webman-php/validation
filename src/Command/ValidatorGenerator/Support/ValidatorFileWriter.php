<?php
declare(strict_types=1);

namespace Webman\Validation\Command\ValidatorGenerator\Support;

final class ValidatorFileWriter
{
    public function write(string $file, string $content): void
    {
        $dir = \dirname($file);
        if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new \RuntimeException("Failed to create directory: {$dir}");
        }

        if (file_put_contents($file, $content) === false) {
            throw new \RuntimeException("Failed to write file: {$file}");
        }
    }
}

