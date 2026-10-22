<?php

namespace App\Livewire\Bans;

use App\Models\Avatar;
use App\Models\Ban;
use App\Services\SystemService;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use RuntimeException;

/**
 * Banned avatars cannot rent or renew (port of the 2.0.x Banlist page). Owners only.
 */
#[Title('Bans')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    /** name, UID or SL UUID of the avatar to ban */
    public string $find = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function ban(SystemService $system, ?int $avatarId = null): void
    {
        $avatar = $avatarId ? Avatar::find($avatarId) : $this->exactMatch();
        if ($avatar === null) {
            $this->addError('find', 'No avatar matches that exactly; pick one from the list or add them on the Avatars page first.');

            return;
        }
        try {
            $system->ban($avatar);
        } catch (RuntimeException $e) {
            $this->addError('find', $e->getMessage());

            return;
        }
        $this->reset('find');
        Flux::toast(text: $avatar->avatar_name.' is banned.');
    }

    public function unban(Ban $ban, SystemService $system): void
    {
        $system->unban($ban);
        Flux::toast(text: ($ban->avatar->avatar_name ?? 'Avatar').' is no longer banned.');
    }

    private function exactMatch(): ?Avatar
    {
        $find = trim($this->find);
        if ($find === '') {
            return null;
        }
        $matches = Avatar::where('avatar_uid', $find)->orWhere('avatar_uuid', strtolower($find))->orWhere('avatar_name', $find)->limit(2)->get();

        return $matches->count() === 1 ? $matches->first() : null;
    }

    public function render()
    {
        $find = trim($this->find);

        return view('livewire.bans.index', [
            'bans' => Ban::with('avatar')
                ->when($this->search !== '', fn (Builder $query) => $query->whereHas('avatar', fn (Builder $avatar) => $avatar
                    ->where('avatar_name', 'like', '%'.$this->search.'%')
                    ->orWhere('avatar_uuid', $this->search)
                    ->orWhere('avatar_uid', $this->search)))
                ->orderByDesc('id')
                ->paginate(25),
            'suggestions' => mb_strlen($find) >= 2
                ? Avatar::whereDoesntHave('ban')
                    ->where(fn (Builder $query) => $query->where('avatar_name', 'like', '%'.$find.'%')->orWhere('avatar_uid', $find)->orWhere('avatar_uuid', strtolower($find)))
                    ->orderBy('avatar_name')->limit(6)->get()
                : collect(),
        ]);
    }
}
