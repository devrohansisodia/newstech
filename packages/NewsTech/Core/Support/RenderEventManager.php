<?php

namespace NewsTech\Core\Support;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;
use Stringable;

class RenderEventManager
{
    /**
     * @var array<string, array<int, array{priority:int, renderer:callable}>>
     */
    protected array $listeners = [];

    public function register(string $event, callable $renderer, int $priority = 0): void
    {
        $this->listeners[$event][] = [
            'priority' => $priority,
            'renderer' => $renderer,
        ];
    }

    /**
     * @param  array<string, mixed>|callable(array<string, mixed>): array<string, mixed>  $data
     */
    public function registerView(string $event, string $view, array|callable $data = [], int $priority = 0): void
    {
        $this->register($event, function (array $payload) use ($view, $data): View {
            $resolvedData = is_callable($data) ? $data($payload) : $data;

            return view($view, array_merge($payload, $resolvedData));
        }, $priority);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function render(string $event, array $data = []): HtmlString
    {
        $listeners = $this->listeners[$event] ?? [];

        if ($listeners === []) {
            return new HtmlString('');
        }

        usort($listeners, fn (array $left, array $right): int => $right['priority'] <=> $left['priority']);

        $output = collect($listeners)
            ->map(fn (array $listener): string => $this->stringify(app()->call($listener['renderer'], [
                'payload' => $data,
                'data' => $data,
            ])))
            ->filter(fn (string $content): bool => $content !== '')
            ->implode(PHP_EOL);

        return new HtmlString($output);
    }

    public function clear(): void
    {
        $this->listeners = [];
    }

    protected function stringify(mixed $content): string
    {
        if ($content instanceof Htmlable) {
            return $content->toHtml();
        }

        if ($content instanceof View) {
            return $content->render();
        }

        if ($content instanceof Stringable) {
            return (string) $content;
        }

        if (is_string($content)) {
            return $content;
        }

        return '';
    }
}
