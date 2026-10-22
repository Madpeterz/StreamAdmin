<?php

namespace App\Livewire\Streams;

use App\Livewire\Tables\StyledTable;
use App\Models\Package;
use App\Models\Server;
use App\Models\Stream;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

class Index extends StyledTable
{
    protected $model = Stream::class;

    protected string $pageTitle = 'Streams';

    protected string $pageDescription = 'Every port on every server, and who is renting it.';

    protected int $refreshSeconds = 60;

    public function mount(): void
    {
        // dashboard, server and package pages link here with ?status=, ?server= and ?package=
        $status = request()->query('status');
        if (in_array($status, ['ready', 'needs-work', 'sold'], true)) {
            $this->setFilter('status', $status);
        }
        foreach (['server', 'package'] as $filter) {
            if (ctype_digit((string) request()->query($filter))) {
                $this->setFilter($filter, (string) request()->query($filter));
            }
        }
    }

    public function configure(): void
    {
        $this->styleTable();
        $needsWork = Stream::whereNull('rental_id')->where('need_work', true)->count();
        $this->setPrimaryKey('id')
            ->setDefaultSort('port', 'asc')
            ->setSearchPlaceholder('Search port, stream UID or client')
            ->setAdditionalSelects(['streams.id', 'streams.stream_uid', 'streams.rental_id', 'streams.need_work', 'streams.server_id'])
            ->setConfigurableAreas([
                'before-wrapper' => ['livewire.tables.page-header', [
                    'title' => $this->pageTitle,
                    'description' => $this->pageDescription,
                    'actions' => [
                        ['label' => 'Needs work', 'icon' => 'wrench-screwdriver', 'variant' => 'filled', 'href' => route('streams.needs-work'), 'badge' => $needsWork ?: null],
                        ['label' => 'Add stream', 'icon' => 'plus', 'href' => route('streams.create')],
                    ],
                ]],
            ])
            ->setTableRowUrl(fn (Stream $row) => route('streams.edit', $row));
    }

    public function builder(): Builder
    {
        return Stream::query()->with(['server', 'rental.avatar']);
    }

    public function columns(): array
    {
        return [
            Column::make('Port', 'port')
                ->sortable()
                ->searchable()
                ->format(fn ($value, Stream $row) => '<div class="font-medium tabular-nums text-zinc-900 dark:text-white">'
                    .e($row->server?->domain).'<span class="text-zinc-400">:'.e($value).'</span></div>'
                    .'<div class="font-mono text-xs text-zinc-500">'.e($row->stream_uid).'</div>')
                ->html(),
            Column::make('UID', 'stream_uid')->searchable()->hideIf(true),
            Column::make('Package', 'package.name')
                ->sortable()
                ->searchable()
                ->collapseOnMobile(),
            Column::make('Mount', 'mountpoint')
                ->collapseOnTablet(),
            Column::make('Status')
                ->label(fn (Stream $row) => view('livewire.streams.partials.status', ['stream' => $row]))
                ->html(),
            Column::make('Client')
                ->label(fn (Stream $row) => $row->rental
                    ? '<a href="'.e(route('clients.show', $row->rental)).'" wire:navigate class="text-zinc-900 hover:underline dark:text-white" onclick="event.stopPropagation()">'
                        .e($row->rental->avatar->avatar_name ?? 'Unknown avatar').'</a>'
                    : '<span class="text-zinc-400">—</span>')
                ->html(),
        ];
    }

    public function filters(): array
    {
        return [
            SelectFilter::make('Status', 'status')
                ->options(['' => 'Any status', 'ready' => 'Ready to sell', 'needs-work' => 'Needs work', 'sold' => 'Sold'])
                ->filter(fn (Builder $query, string $value) => match ($value) {
                    'ready' => $query->whereNull('streams.rental_id')->where('streams.need_work', false),
                    'needs-work' => $query->whereNull('streams.rental_id')->where('streams.need_work', true),
                    'sold' => $query->whereNotNull('streams.rental_id'),
                    default => $query,
                }),
            SelectFilter::make('Server', 'server')
                ->options(['' => 'Any server'] + Server::orderBy('domain')->pluck('domain', 'id')->all())
                ->filter(fn (Builder $query, string $value) => $query->where('streams.server_id', $value)),
            SelectFilter::make('Package', 'package')
                ->options(['' => 'Any package'] + Package::orderBy('name')->pluck('name', 'id')->all())
                ->filter(fn (Builder $query, string $value) => $query->where('streams.package_id', $value)),
        ];
    }
}
