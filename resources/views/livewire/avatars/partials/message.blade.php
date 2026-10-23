<flux:button size="xs" variant="ghost" icon="chat-bubble-left-ellipsis" x-on:click.stop
    :href="route('outbox.compose', ['source' => 'avatar', 'uid' => $avatar->avatar_uid])" wire:navigate
    tooltip="Message {{ $avatar->avatar_name }}" />
