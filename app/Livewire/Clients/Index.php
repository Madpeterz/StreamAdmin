<?php

namespace App\Livewire\Clients;

use App\Livewire\Tables\StyledTable;
use App\Models\Notice;
use App\Models\Package;
use App\Models\Rental;
use App\Models\Server;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

class Index extends StyledTable
{
    protected $model = Rental::class;

    protected string $pageTitle = 'Clients';

    protected string $pageDescription = 'Every rental, its stream and how long it has left.';

    protected int $refreshSeconds = 120;

    public function mount(): void
    {
        // dashboard cards link here with ?status=active|expiring|expired
        $status = request()->query('status');
        if (in_array($status, ['active', 'expiring', 'expired'], true)) {
            $this->setFilter('status', $status);
        }
        // the notices page links here with ?notice=<id>
        $notice = request()->query('notice');
        if (is_string($notice) && ctype_digit($notice)) {
            $this->setFilter('notice', $notice);
        }
    }

    public function configure(): void
    {
        $this->styleTable();
        $this->setPrimaryKey('id')
            ->setDefaultSort('expire_unixtime', 'asc')
            ->setSearchPlaceholder('Search avatar, rental UID or port')
            ->setAdditionalSelects(['rentals.id', 'rentals.rental_uid', 'rentals.expire_unixtime', 'rentals.avatar_id', 'rentals.stream_id'])
            ->setConfigurableAreas([
                'before-wrapper' => ['livewire.tables.page-header', [
                    'title' => $this->pageTitle,
                    'description' => $this->pageDescription,
                    'actions' => [
                        ['label' => 'Remove expired', 'icon' => 'trash', 'variant' => 'filled', 'href' => route('clients.bulk-remove')],
                        ['label' => 'Add client', 'icon' => 'plus', 'href' => route('clients.create')],
                    ],
                ]],
            ])
            ->setTableRowUrl(fn (Rental $row) => route('clients.show', $row));
    }

    public function builder(): Builder
    {
        return Rental::query()->with(['avatar', 'stream.server']);
    }

    public function columns(): array
    {
        return [
            Column::make('Client', 'avatar.avatar_name')
                ->sortable()
                ->searchable()
                ->format(fn ($value, Rental $row) => view('livewire.clients.partials.client-cell', ['rental' => $row]))
                ->html(),
            Column::make('Rental UID', 'rental_uid')
                ->searchable()
                ->collapseOnMobile()
                ->format(fn ($value) => '<span class="font-mono text-xs text-zinc-500">'.e($value).'</span>')
                ->html(),
            Column::make('Package', 'package.name')
                ->sortable()
                ->searchable()
                ->collapseOnTablet(),
            Column::make('Stream', 'stream.port')
                ->sortable()
                ->searchable()
                ->format(fn ($value, Rental $row) => e($row->stream?->server?->domain).'<span class="text-zinc-400">:'.e($value).'</span>')
                ->html(),
            Column::make('Expires', 'expire_unixtime')
                ->sortable()
                ->format(fn ($value, Rental $row) => '<span title="'.e($row->expire_unixtime->toDayDateTimeString()).'">'
                    .e($row->expire_unixtime->diffForHumans()).'</span>')
                ->html(),
            Column::make('Renewals', 'renewals')
                ->sortable()
                ->collapseOnMobile(),
            Column::make('Total paid', 'total_amount')
                ->sortable()
                ->format(fn ($value) => 'L$ '.number_format((int) $value))
                ->collapseOnMobile(),
            Column::make('Status')
                ->label(fn (Rental $row) => view('components.ui.rental-status', ['rental' => $row]))
                ->html(),
        ];
    }

    public function filters(): array
    {
        return [
            SelectFilter::make('Status', 'status')
                ->options(['' => 'Any status', 'active' => 'Active', 'expiring' => 'Expires within 24 hours', 'expired' => 'Expired'])
                ->filter(fn (Builder $query, string $value) => match ($value) {
                    'active' => $query->active(),
                    'expiring' => $query->expiringSoon(),
                    'expired' => $query->expired(),
                    default => $query,
                }),
            SelectFilter::make('Package', 'package')
                ->options(['' => 'Any package'] + Package::orderBy('name')->pluck('name', 'id')->all())
                ->filter(fn (Builder $query, string $value) => $query->where('rentals.package_id', $value)),
            SelectFilter::make('Server', 'server')
                ->options(['' => 'Any server'] + Server::orderBy('domain')->pluck('domain', 'id')->all())
                ->filter(fn (Builder $query, string $value) => $query->whereHas('stream', fn (Builder $stream) => $stream->where('server_id', $value))),
            SelectFilter::make('Notice level', 'notice')
                ->options(['' => 'Any notice level'] + Notice::orderByDesc('hours_remaining')->pluck('name', 'id')->all())
                ->filter(fn (Builder $query, string $value) => $query->where('rentals.notice_id', $value)),
        ];
    }
}
