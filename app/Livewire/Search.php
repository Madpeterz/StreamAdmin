<?php

namespace App\Livewire;

use App\Models\Avatar;
use App\Models\Rental;
use App\Models\Stream;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Find avatars, clients and streams from one box (port of the 2.0.x Search page).
 *
 * Avatars match on name, avatar UID or SL UUID; clients on rental UID, note or their avatar;
 * streams on admin username, port or stream UID, and any stream a matching client rents.
 * Clients of matching streams are included, as in 2.0.x.
 */
#[Title('Search')]
class Search extends Component
{
    public const LIMIT = 100;

    #[Url(as: 'q')]
    public string $q = '';

    public function render()
    {
        $term = trim($this->q);
        $avatars = collect();
        $rentals = collect();
        $streams = collect();

        if (mb_strlen($term) >= 3) {
            $like = '%'.addcslashes($term, '%_\\').'%';

            $avatars = Avatar::query()
                ->where(fn (Builder $query) => $query->where('avatar_uuid', 'like', $like)->orWhere('avatar_uid', 'like', $like)
                    ->orWhere('avatar_name', 'like', $like))
                ->orderBy('avatar_name')->limit(self::LIMIT)->get();

            $rentalIds = Rental::query()
                ->where(fn (Builder $query) => $query->where('rental_uid', 'like', $like)->orWhere('message', 'like', $like)
                    ->orWhereIn('avatar_id', $avatars->modelKeys()))
                ->pluck('id');

            $streams = Stream::query()->with(['server', 'rental.avatar'])
                ->where(fn (Builder $query) => $query->where('admin_username', 'like', $like)->orWhere('stream_uid', 'like', $like)
                    ->when(ctype_digit($term), fn (Builder $inner) => $inner->orWhere('port', (int) $term))
                    ->orWhereIn('rental_id', $rentalIds))
                ->orderBy('port')->limit(self::LIMIT)->get();

            $rentals = Rental::with(['avatar', 'stream.server'])
                ->where(fn (Builder $query) => $query->whereIn('id', $rentalIds)->orWhereIn('id', $streams->pluck('rental_id')->filter()))
                ->orderByDesc('expire_unixtime')->limit(self::LIMIT)->get();
        }

        return view('livewire.search', [
            'term' => $term,
            'tooShort' => $term !== '' && mb_strlen($term) < 3,
            'avatars' => $avatars,
            'rentals' => $rentals,
            'streams' => $streams,
        ]);
    }
}
