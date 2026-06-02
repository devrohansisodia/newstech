<?php

namespace NewsTech\Installer\Support;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\File;

class EnvironmentFileManager
{
    public function __construct(protected Application $application) {}

    public function ensureEnvironmentFileExists(): void
    {
        $environmentPath = $this->environmentPath();

        if (File::exists($environmentPath)) {
            return;
        }

        $examplePath = $this->application->basePath('.env.example');

        if (File::exists($examplePath)) {
            File::copy($examplePath, $environmentPath);

            return;
        }

        File::put($environmentPath, '');
    }

    /**
     * @param  array<string, string|null>  $values
     */
    public function update(array $values): void
    {
        $this->ensureEnvironmentFileExists();

        $environmentPath = $this->environmentPath();
        $contents = File::get($environmentPath);

        foreach ($values as $key => $value) {
            $escapedKey = preg_quote($key, '/');
            $formattedValue = $this->formatValue($value);
            $pattern = "/^{$escapedKey}=.*$/m";

            if (preg_match($pattern, $contents) === 1) {
                $contents = preg_replace($pattern, "{$key}={$formattedValue}", $contents) ?? $contents;

                continue;
            }

            $contents = rtrim($contents).PHP_EOL."{$key}={$formattedValue}".PHP_EOL;
        }

        File::put($environmentPath, $contents);
    }

    /**
     * @return array<string, string>
     */
    public function currentValues(array $keys): array
    {
        $this->ensureEnvironmentFileExists();

        $contents = File::get($this->environmentPath());
        $resolvedValues = [];

        foreach ($keys as $key) {
            $escapedKey = preg_quote($key, '/');

            if (preg_match("/^{$escapedKey}=(.*)$/m", $contents, $matches) !== 1) {
                $resolvedValues[$key] = '';

                continue;
            }

            $resolvedValues[$key] = $this->normalizeValue($matches[1]);
        }

        return $resolvedValues;
    }

    public function environmentPath(): string
    {
        return $this->application->environmentFilePath();
    }

    protected function formatValue(?string $value): string
    {
        $resolvedValue = (string) $value;

        if ($resolvedValue === '') {
            return '';
        }

        if (preg_match('/\s|#|=|"|\'/', $resolvedValue) === 1) {
            return '"'.str_replace(['\\', '"'], ['\\\\', '\\"'], $resolvedValue).'"';
        }

        return $resolvedValue;
    }

    protected function normalizeValue(string $value): string
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            return '';
        }

        if (str_starts_with($trimmed, '"') && str_ends_with($trimmed, '"')) {
            return stripcslashes(substr($trimmed, 1, -1));
        }

        if (str_starts_with($trimmed, '\'') && str_ends_with($trimmed, '\'')) {
            return substr($trimmed, 1, -1);
        }

        return $trimmed;
    }
}
