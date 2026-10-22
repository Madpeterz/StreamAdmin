<?php

namespace App\Livewire;

use App\Models\Avatar;
use App\Models\SlConfig;
use App\Models\Timezone;
use App\Services\SystemService;
use App\Support\PaymentKey;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * System settings (port of the 2.0.x Slconfig pages). Owners only. The 2.0.x list-display tweaks
 * (full client list, items per page, server column, stream grouping) are not used by this UI and
 * are left as they were.
 */
#[Title('System settings')]
class SystemSettings extends Component
{
    public string $owner_avatar_uid = '';

    public int $display_timezone_id = 11;

    public bool $new_resellers = false;

    public int $new_resellers_rate = 10;

    public bool $events_api = false;

    public bool $hud_allow_details = false;

    public bool $hud_allow_renewal = false;

    public bool $hud_allow_discord = false;

    public string $hud_discord_link = '';

    public bool $hud_allow_group = false;

    public string $hud_group_link = '';

    public bool $limit_streams = false;

    public int $max_total_streams = 100;

    public bool $limit_time = false;

    public int $max_stream_time_days = 120;

    public bool $enable_coupons = false;

    public string $ans_salt = '';

    public string $payment_key = '';

    public function mount(): void
    {
        $config = SlConfig::with('ownerAvatar')->findOrFail(1);
        $this->owner_avatar_uid = (string) $config->ownerAvatar?->avatar_uid;
        $this->fill($config->only([
            'display_timezone_id', 'new_resellers', 'new_resellers_rate', 'events_api', 'hud_allow_details', 'hud_allow_renewal',
            'hud_allow_discord', 'hud_allow_group', 'limit_streams', 'max_total_streams', 'limit_time', 'max_stream_time_days', 'enable_coupons',
        ]));
        // 2.0.x stored "Not setup yet" style placeholders in unused text settings
        $this->hud_discord_link = $config->hud_allow_discord ? (string) $config->hud_discord_link : '';
        $this->hud_group_link = $config->hud_allow_group ? (string) $config->hud_group_link : '';
        $this->ans_salt = $config->ans_salt === 'Not used' ? '' : (string) $config->ans_salt;
        $this->payment_key = strlen((string) $config->payment_key) === 23 ? (string) $config->payment_key : '';
    }

    public function save(SystemService $system): void
    {
        $data = $this->validate([
            'owner_avatar_uid' => ['required', 'string', 'max:8', 'exists:avatars,avatar_uid'],
            'display_timezone_id' => ['required', 'exists:timezones,id'],
            'new_resellers' => ['boolean'],
            'new_resellers_rate' => ['required', 'integer', 'min:1', 'max:100'],
            'events_api' => ['boolean'],
            'hud_allow_details' => ['boolean'],
            'hud_allow_renewal' => ['boolean'],
            'hud_allow_discord' => ['boolean'],
            'hud_discord_link' => [$this->hud_allow_discord ? 'required' : 'nullable', 'url', 'max:128'],
            'hud_allow_group' => ['boolean'],
            'hud_group_link' => [$this->hud_allow_group ? 'required' : 'nullable', 'string', 'max:128'],
            'limit_streams' => ['boolean'],
            'max_total_streams' => ['required', 'integer', 'min:1', 'max:2000'],
            'limit_time' => ['boolean'],
            'max_stream_time_days' => ['required', 'integer', 'min:7', 'max:365'],
            'enable_coupons' => ['boolean'],
            'ans_salt' => [$this->enable_coupons ? 'required' : 'nullable', 'string', 'max:50'],
        ], ['owner_avatar_uid.exists' => 'No avatar has that UID.'], [
            'owner_avatar_uid' => 'owner avatar UID', 'new_resellers_rate' => 'rate', 'hud_discord_link' => 'Discord link',
            'hud_group_link' => 'group link', 'ans_salt' => 'ANS salt',
        ]);
        $data['owner_avatar_id'] = Avatar::where('avatar_uid', $data['owner_avatar_uid'])->value('id');
        unset($data['owner_avatar_uid']);
        $data['hud_discord_link'] ??= '';
        $data['hud_group_link'] ??= '';
        $data['ans_salt'] = $data['ans_salt'] ?: 'Not used';

        $config = $system->saveSettings($data);
        $this->hud_allow_renewal = $config->hud_allow_renewal;
        $this->hud_allow_discord = $config->hud_allow_discord;
        $this->hud_allow_group = $config->hud_allow_group;
        Flux::toast(variant: 'success', text: 'Settings saved.');
    }

    public function reissue(SystemService $system): void
    {
        $system->reissueKeys();
        Flux::modal('reissue')->close();
        Flux::toast(variant: 'warning', text: 'Keys reissued. Update your vendors, servers and HUDs with the new codes.');
    }

    public function savePaymentKey(SystemService $system): void
    {
        $this->validate(['payment_key' => ['required', 'string', 'size:23']], [], ['payment_key' => 'key']);
        $check = PaymentKey::check($this->payment_key);
        if (! $check['ok']) {
            $this->addError('payment_key', 'That key is not usable: '.strtolower($check['status']).'.');

            return;
        }
        $system->savePaymentKey($this->payment_key);
        Flux::toast(variant: 'success', text: 'Support key saved.');
    }

    public function render()
    {
        $config = SlConfig::findOrFail(1);

        return view('livewire.system-settings', [
            'config' => $config,
            'timezones' => Timezone::orderBy('name')->get(),
            'ownerAvatar' => $this->owner_avatar_uid !== '' ? Avatar::where('avatar_uid', $this->owner_avatar_uid)->first() : null,
            'keyStatus' => PaymentKey::check($config->payment_key),
        ]);
    }
}
