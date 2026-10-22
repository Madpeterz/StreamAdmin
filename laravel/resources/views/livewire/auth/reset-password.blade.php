<?php

use App\Services\StaffPasswordReset;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    #[Locked]
    public string $token = '';

    public string $sl_name = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(string $token): void
    {
        $this->token = $token;
    }

    public function resetPassword(StaffPasswordReset $resets): void
    {
        $this->validate([
            'sl_name' => ['required', 'string', 'min:3', 'max:125'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ], [], ['sl_name' => 'Second Life name']);

        $key = 'password-reset|'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'sl_name' => __('auth.throttle', ['seconds' => RateLimiter::availableIn($key), 'minutes' => ceil(RateLimiter::availableIn($key) / 60)]),
            ]);
        }

        if (! $resets->reset($this->sl_name, $this->token, $this->password)) {
            RateLimiter::hit($key, 600);
            $this->reset('password', 'password_confirmation');
            throw ValidationException::withMessages([
                'sl_name' => 'This reset link does not match that name, or it has expired. Ask for a new one.',
            ]);
        }

        RateLimiter::clear($key);
        session()->flash('status', 'Your password has been changed. You can sign in now.');
        $this->redirectRoute('login', navigate: true);
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header title="Choose a new password" description="Enter your Second Life name and a new password" />

    <form wire:submit="resetPassword" class="flex flex-col gap-6">
        <flux:input wire:model="sl_name" label="Second Life name" description:trailing="The avatar the reset link was sent to." required autofocus autocomplete="off" />
        <flux:input wire:model="password" label="New password" type="password" required autocomplete="new-password" viewable />
        <flux:input wire:model="password_confirmation" label="Confirm new password" type="password" required autocomplete="new-password" viewable />
        <flux:button variant="primary" type="submit" class="w-full">Set new password</flux:button>
    </form>

    <div class="text-center text-sm text-zinc-500">
        <flux:link :href="route('password.request')" wire:navigate>Ask for a new link</flux:link>
    </div>
</div>
