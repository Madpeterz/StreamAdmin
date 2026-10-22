<?php

namespace App\LegacyApi;

/**
 * The response "swap tags" of a 2.0.x endpoint: an ordered set of named values sent as a JSON
 * object. Starts as status false / "Not processed", like YAPF's ViewAjax.
 */
class Output
{
    /** @var array<string, mixed> */
    private array $tags = ['status' => false, 'message' => 'Not processed'];

    /**
     * Start over, as when a 2.0.x endpoint handed the request to another endpoint and replied
     * with that one's output: the module/area tags and anything set so far are dropped.
     */
    public function reset(): void
    {
        $this->tags = ['status' => false, 'message' => 'ready'];
    }

    public function set(string $tag, mixed $value): void
    {
        $this->tags[$tag] = $value;
    }

    public function get(string $tag): mixed
    {
        return $this->tags[$tag] ?? null;
    }

    public function ok(string $message): void
    {
        $this->set('status', true);
        $this->set('message', $message);
    }

    public function failed(string $message): void
    {
        $this->set('status', false);
        $this->set('message', $message);
    }

    /** status as YAPF read it when deciding to commit: true, 1 and "1" are success. */
    public function succeeded(): bool
    {
        return in_array($this->tags['status'] ?? false, [true, 1, '1', 'true'], true);
    }

    /**
     * api.php and hud.php send every boolean as "1" / "0"; sys.php and ans.php keep booleans.
     *
     * @return array<string, mixed>
     */
    public function toArray(bool $boolsAsStrings): array
    {
        if (! $boolsAsStrings) {
            return $this->tags;
        }

        return array_map(fn ($value) => is_bool($value) ? ($value ? '1' : '0') : $value, $this->tags);
    }
}
