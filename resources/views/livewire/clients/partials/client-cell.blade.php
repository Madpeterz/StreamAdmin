<div class="flex items-center gap-3">
    <flux:avatar size="sm" :name="$rental->avatar->avatar_name ?? '?'" />
    <div class="min-w-0">
        <div class="font-medium text-zinc-900 dark:text-white">{{ $rental->avatar->avatar_name ?? 'Unknown avatar' }}</div>
    </div>
</div>
