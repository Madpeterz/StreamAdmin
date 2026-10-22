<?php

namespace App\Livewire\Concerns;

use App\Models\Avatar;
use Illuminate\Support\Collection;

/**
 * Name / UID / UUID lookup behind the avatar pickers (port of the 2.0.x Avatar/Finder control).
 * The using component names the property that receives the picked avatar's UID in $avatarField.
 */
trait SearchesAvatars
{
    public string $avatarSearch = '';

    /** the public property the picked avatar UID is written to (override to use another) */
    protected function avatarField(): string
    {
        return 'avatarUid';
    }

    /** @return Collection<int, Avatar> */
    public function avatarMatches(): Collection
    {
        $search = trim($this->avatarSearch);
        if (mb_strlen($search) < 3) {
            return collect();
        }
        $like = '%'.addcslashes($search, '%_\\').'%';

        return Avatar::query()
            ->where(fn ($query) => $query->where('avatar_name', 'like', $like)
                ->orWhere('avatar_uid', 'like', $like)->orWhere('avatar_uuid', 'like', $like))
            ->orderBy('avatar_name')
            ->limit(8)
            ->get();
    }

    public function pickAvatar(string $uid): void
    {
        $this->{$this->avatarField()} = $uid;
        $this->avatarSearch = '';
    }

    public function clearAvatar(): void
    {
        $this->{$this->avatarField()} = '';
    }
}
