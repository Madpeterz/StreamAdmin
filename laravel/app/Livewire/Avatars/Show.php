<?php

namespace App\Livewire\Avatars;

use App\Models\Avatar;
use App\Models\BotConfig;
use App\Models\SlConfig;
use App\Models\Transaction;
use App\Services\SystemService;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;
use RuntimeException;

/**
 * Add an avatar (no avatar) or see and edit one: name, SL UUID, credits (owners only), ban,
 * remove. Rules follow the 2.0.x Avatar controls.
 */
class Show extends Component
{
    public ?Avatar $avatar = null;

    public string $avatar_name = '';

    public string $avatar_uuid = '';

    public int $credits = 0;

    public function mount(?Avatar $avatar = null): void
    {
        if ($avatar?->exists) {
            $this->avatar = $avatar;
            $this->fill($avatar->only(['avatar_name', 'avatar_uuid', 'credits']));
        }
    }

    public function save(SystemService $system): void
    {
        $data = $this->validate([
            'avatar_name' => ['required', 'string', 'min:5', 'max:125'],
            'avatar_uuid' => ['required', 'uuid', Rule::unique('avatars', 'avatar_uuid')->ignore($this->avatar?->id)],
            'credits' => ['integer', 'min:0', 'max:999999'],
        ], ['avatar_uuid.unique' => 'Another avatar already has that SL UUID.'], ['avatar_name' => 'name', 'avatar_uuid' => 'SL UUID']);
        $data['avatar_uuid'] = strtolower($data['avatar_uuid']);

        if ($this->avatar === null) {
            $avatar = $system->createAvatar($data['avatar_name'], $data['avatar_uuid']);
            Flux::toast(variant: 'success', text: 'Avatar added.');
            $this->redirectRoute('avatars.show', $avatar, navigate: true);

            return;
        }
        if (! Gate::allows('owner')) {
            unset($data['credits']); // only owners change credits, as in 2.0.x
        }
        $system->updateAvatar($this->avatar, $data);
        Flux::toast(variant: 'success', text: 'Avatar saved.');
    }

    public function ban(SystemService $system): void
    {
        Gate::authorize('owner');
        try {
            $system->ban($this->avatar);
            Flux::toast(text: $this->avatar->avatar_name.' is banned.');
        } catch (RuntimeException $e) {
            Flux::toast(variant: 'danger', text: $e->getMessage());
        }
        $this->avatar->unsetRelation('ban');
    }

    public function unban(SystemService $system): void
    {
        Gate::authorize('owner');
        if ($this->avatar->ban) {
            $system->unban($this->avatar->ban);
            Flux::toast(text: $this->avatar->avatar_name.' is no longer banned.');
        }
        $this->avatar->unsetRelation('ban');
    }

    public function delete(SystemService $system): void
    {
        try {
            $system->deleteAvatar($this->avatar);
        } catch (RuntimeException $e) {
            Flux::toast(variant: 'danger', text: $e->getMessage());

            return;
        }
        Flux::toast(variant: 'success', text: 'Avatar removed.');
        $this->redirectRoute('avatars.index', navigate: true);
    }

    public function render(SystemService $system)
    {
        $roles = [];
        if ($this->avatar) {
            $this->avatar->loadMissing(['ban', 'staff', 'reseller']);
            if ($this->avatar->id === SlConfig::find(1)?->owner_avatar_id) {
                $roles[] = ['System owner', 'emerald'];
            }
            if (BotConfig::where('avatar_id', $this->avatar->id)->exists()) {
                $roles[] = ['Bot', 'violet'];
            }
            if ($this->avatar->staff) {
                $roles[] = ['Staff: '.$this->avatar->staff->username, 'sky'];
            }
            if ($this->avatar->reseller) {
                $roles[] = ['Reseller', 'amber'];
            }
        }

        return view('livewire.avatars.show', [
            'roles' => $roles,
            'rentals' => $this->avatar?->rentals()->with(['stream.server', 'package'])->orderBy('expire_unixtime')->get() ?? collect(),
            'transactions' => $this->avatar
                ? Transaction::where('avatar_id', $this->avatar->id)->orWhere('target_avatar_id', $this->avatar->id)->latest('unixtime')->limit(10)->get()
                : collect(),
            'inUse' => $this->avatar ? $system->avatarInUse($this->avatar) : null,
        ])->title($this->avatar?->avatar_name ?? 'Add avatar');
    }
}
