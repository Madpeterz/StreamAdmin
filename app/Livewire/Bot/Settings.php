<?php

namespace App\Livewire\Bot;

use App\Livewire\Concerns\SearchesAvatars;
use App\Models\Avatar;
use App\Models\BotCommand;
use App\Models\BotConfig;
use App\Services\AuditLogger;
use App\Services\BotService;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * The SL bot: which avatar it is, the shared secret, and what it does (port of the 2.0.x Bot page).
 * Owner level only.
 */
#[Title('Bot')]
class Settings extends Component
{
    use SearchesAvatars;

    public string $avatar_uid = '';

    public string $secret = '';

    public bool $notecards = false;

    public bool $ims = false;

    public bool $invites = false;

    public string $invite_group_uuid = '';

    public bool $http_mode = false;

    public string $http_url = '';

    protected function avatarField(): string
    {
        return 'avatar_uid';
    }

    public function mount(): void
    {
        $config = BotConfig::with('avatar')->findOrFail(1);
        $this->avatar_uid = (string) $config->avatar?->avatar_uid;
        $this->fill([
            'secret' => (string) $config->secret,
            'notecards' => $config->notecards,
            'ims' => $config->ims,
            'invites' => $config->invites,
            'invite_group_uuid' => (string) $config->invite_group_uuid,
            'http_mode' => $config->http_mode,
            'http_url' => (string) $config->http_url,
        ]);
    }

    public function save(AuditLogger $audit): void
    {
        $data = $this->validate([
            'avatar_uid' => ['required', 'string', 'max:8', 'exists:avatars,avatar_uid'],
            'secret' => ['required', 'string', 'min:8', 'max:30'],
            'notecards' => ['boolean'],
            'ims' => ['boolean'],
            'invites' => ['boolean'],
            'invite_group_uuid' => [$this->invites ? 'required' : 'nullable', 'uuid'],
            'http_mode' => ['boolean'],
            'http_url' => [$this->http_mode ? 'required' : 'nullable', 'url'],
        ], [
            'avatar_uid.exists' => 'No avatar has that UID.',
            'invite_group_uuid.required' => 'Enter the group UUID, or turn group invites off.',
            'http_url.required' => 'Enter the bot URL, or turn HTTP mode off.',
        ], ['avatar_uid' => 'avatar UID', 'invite_group_uuid' => 'group UUID', 'http_url' => 'bot URL']);

        $config = BotConfig::findOrFail(1);
        DB::transaction(function () use ($config, $data, $audit) {
            $config->update([
                'avatar_id' => Avatar::where('avatar_uid', $data['avatar_uid'])->value('id'),
                'secret' => $data['secret'],
                'notecards' => $data['notecards'],
                'ims' => $data['ims'],
                'invites' => $data['invites'],
                'invite_group_uuid' => $data['invite_group_uuid'] ?: null,
                'http_mode' => $data['http_mode'],
                'http_url' => $data['http_url'] ?: null,
            ]);
            $audit->logChanges('Bot', (string) $config->id, $config);
        });
        Flux::toast(variant: 'success', text: 'Bot settings saved.');
    }

    public function testHttp(BotService $bot): void
    {
        $result = $bot->testHttp();
        Flux::toast(variant: $result['ok'] ? 'success' : 'danger', text: $result['message']);
    }

    public function render()
    {
        return view('livewire.bot.settings', [
            'botAvatar' => $this->avatar_uid !== '' ? Avatar::where('avatar_uid', $this->avatar_uid)->first() : null,
            'matches' => $this->avatarMatches(),
            'queued' => BotCommand::count(),
            'httpSaved' => (bool) BotConfig::whereKey(1)->value('http_mode'),
        ]);
    }
}
