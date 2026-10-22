<?php

namespace App\View;

use Illuminate\View\FileViewFinder as BaseFileViewFinder;
use InvalidArgumentException;

/**
 * Class components that render a string (blade-icons' <x-heroicon-*> used all over the livewire
 * tables) make Laravel ask whether that rendered markup is a view name before compiling it. The
 * stock finder answers with a file_exists() for every view path and extension, and misses are not
 * cached, so each icon cost a dozen file checks per request (slow on Docker Desktop bind mounts).
 * Markup can never be a view name, so say so straight away.
 */
class FileViewFinder extends BaseFileViewFinder
{
    public function find($name)
    {
        if (str_contains($name, '<')) {
            throw new InvalidArgumentException("View [{$name}] not found.");
        }

        return parent::find($name);
    }
}
