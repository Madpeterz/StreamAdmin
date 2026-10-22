<?php

namespace App\Livewire\Packages;

use App\Livewire\Tables\StyledTable;
use App\Models\Package;
use App\Models\Stream;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Rappasoft\LaravelLivewireTables\Views\Column;

class Index extends StyledTable
{
    protected $model = Package::class;

    protected string $pageTitle = 'Packages';

    protected string $pageDescription = 'What vendors sell: listeners, bitrate, price and rental period.';

    protected int $refreshSeconds = 300;

    public function configure(): void
    {
        $this->styleTable();
        $this->setPrimaryKey('id')
            ->setDefaultSort('name', 'asc')
            ->setSearchPlaceholder('Search name or package UID')
            ->setAdditionalSelects(['packages.id', 'packages.package_uid', 'packages.days', 'packages.autodj', 'packages.autodj_size'])
            ->setConfigurableAreas([
                'before-wrapper' => ['livewire.tables.page-header', [
                    'title' => $this->pageTitle,
                    'description' => $this->pageDescription,
                    'actions' => [['label' => 'New package', 'icon' => 'plus', 'href' => route('packages.create')]],
                ]],
            ])
            ->setTableRowUrl(fn (Package $row) => route('packages.edit', $row));
    }

    public function builder(): Builder
    {
        return Package::query();
    }

    /** Ready/sold counts per package, one grouped query per render. */
    private ?Collection $stock = null;

    private function stockFor(Package $package): array
    {
        $this->stock ??= Stream::query()
            ->selectRaw('package_id, SUM(rental_id IS NULL AND need_work = 0) AS ready, SUM(rental_id IS NOT NULL) AS sold')
            ->groupBy('package_id')
            ->get()
            ->keyBy('package_id');
        $row = $this->stock->get($package->id);

        return ['ready' => (int) ($row->ready ?? 0), 'sold' => (int) ($row->sold ?? 0)];
    }

    public function columns(): array
    {
        return [
            Column::make('Package', 'name')
                ->sortable()
                ->searchable()
                ->format(fn ($value, Package $row) => view('livewire.packages.partials.name-cell', ['package' => $row]))
                ->html(),
            Column::make('UID', 'package_uid')->searchable()->hideIf(true),
            Column::make('Type', 'serverType.name')
                ->sortable()
                ->collapseOnMobile(),
            Column::make('Price', 'cost')
                ->sortable()
                ->format(fn ($value, Package $row) => 'L$ '.number_format((int) $value).'<span class="text-zinc-400"> / '.e($row->days).' days</span>')
                ->html(),
            Column::make('Listeners', 'listeners')
                ->sortable()
                ->collapseOnMobile(),
            Column::make('Bitrate', 'bitrate')
                ->sortable()
                ->format(fn ($value) => e($value).' kbps')
                ->collapseOnMobile(),
            Column::make('AutoDJ')
                ->label(fn (Package $row) => $row->autodj ? e($row->autodj_size).' GB' : '<span class="text-zinc-400">Off</span>')
                ->html()
                ->collapseOnTablet(),
            Column::make('Stock')
                ->label(fn (Package $row) => view('livewire.packages.partials.stock', $this->stockFor($row)))
                ->html(),
        ];
    }
}
