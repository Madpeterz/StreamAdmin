<?php

use App\Services\StaffPasswordReset;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $sl_name = '';

    public bool $sent = false;

    /**
     * Send a reset link to the staff member's avatar. The answer is the same whether or not the
     * name belongs to a staff member.
     */
    public function sendResetLink(StaffPasswordReset $resets): void
    {
        $this->validate(['sl_name' => ['required', 'string', 'min:3', 'max:125']], [], ['sl_name' => 'Second Life name']);

        $key = 'password-reset-request|'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            throw ValidationException::withMessages([
                'sl_name' => __('auth.throttle', ['seconds' => RateLimiter::availableIn($key), 'minutes' => ceil(RateLimiter::availableIn($key) / 60)]),
            ]);
        }
        RateLimiter::hit($key, 600);

        $resets->request($this->sl_name);
        $this->sent = true;
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header title="Reset your password" description="We send a reset link to your avatar in Second Life" />

    @if ($sent)
        <flux:callout icon="paper-airplane" color="emerald">
            <flux:callout.heading>Check your Second Life IMs</flux:callout.heading>
            <flux:callout.text>If that avatar has a staff account, a reset link is on its way. It works for one hour. IMs are sent by your mail server object, so it needs to be running.</flux:callout.text>
        </flux:callout>
    @else
        <form wire:submit="sendResetLink" class="flex flex-col gap-6">
            <flux:input wire:model="sl_name" label="Second Life name" description:trailing="Your avatar name; you can leave out Resident." required autofocus autocomplete="off" />
            <flux:button variant="primary" type="submit" class="w-full">Send reset link</flux:button>
        </form>
    @endif

    <div class="text-center text-sm text-zinc-500">
        <flux:link :href="route('login')" wire:navigate>Back to sign in</flux:link>
    </div>
</div>
