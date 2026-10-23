<?php

namespace App\Livewire\Avatars;

use App\Livewire\Tables\StyledTable;
use App\Models\Avatar;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

/**
 * Every avatar StreamAdmin knows about: clients, resellers, staff and the bot.
 */
class Index extends StyledTable
{
    protected $model = Avatar::class;

    protected string $pageTitle = 'Avatars';

    protected string $pageDescription = 'Every Second Life avatar StreamAdmin has seen: clients, resellers, staff and the bot.';

    protected int $refreshSeconds = 30;

    public function configure(): void
    {
        $this->styleTable();
        $this->setConfigurableAreas([
            'before-wrapper' => ['livewire.tables.page-header', [
                'title' => $this->pageTitle,
                'description' => $this->pageDescription,
                'actions' => [['label' => 'Add avatar', 'icon' => 'plus', 'href' => route('avatars.create')]],
            ]],
        ]);
        $this->setPrimaryKey('id')
            ->setDefaultSort('last_used', 'desc')
            ->setSearchPlaceholder('Search name, UID or SL UUID')
            ->setAdditionalSelects(['avatars.id', 'avatars.avatar_uid', 'avatars.last_used'])
            ->setTableRowUrl(fn (Avatar $row) => route('avatars.show', $row));
    }

    public function builder(): Builder
    {
        return Avatar::query()->withCount('rentals')->with('ban');
    }

    public function columns(): array
    {
        return [
            Column::make('Avatar', 'avatar_name')
                ->sortable()
                ->searchable()
                ->format(fn ($value, Avatar $row) => '<div class="font-medium text-zinc-900 dark:text-white">'.e($value)
                    .($row->ban ? ' <span class="ml-1 rounded bg-red-100 px-1.5 py-0.5 text-xs font-medium text-red-700 dark:bg-red-500/20 dark:text-red-300">Banned</span>' : '')
                    .'</div><div class="font-mono text-xs text-zinc-500">'.e($row->avatar_uid).'</div>')
                ->html(),
            Column::make('UID', 'avatar_uid')->searchable()->hideIf(true),
            Column::make('SL UUID', 'avatar_uuid')
                ->searchable()
                ->collapseOnTablet()
                ->format(fn ($value) => '<span class="font-mono text-xs text-zinc-500">'.e($value).'</span>')
                ->html(),
            Column::make('Last seen', 'last_used')
                ->sortable()
                ->format(fn ($value, Avatar $row) => '<span title="'.e($row->last_used?->toDayDateTimeString()).'">'.e($row->last_used?->diffForHumans() ?? 'Never').'</span>')
                ->html(),
            Column::make('Rentals')
                ->label(fn (Avatar $row) => (string) $row->rentals_count)
                ->collapseOnMobile(),
            Column::make('Credits', 'credits')
                ->sortable()
                ->format(fn ($value) => 'L$ '.number_format((int) $value))
                ->collapseOnMobile(),
            Column::make('')
                ->label(fn (Avatar $row) => $row->ban ? '' : view('livewire.avatars.partials.message', ['avatar' => $row]))
                ->html(),
        ];
    }

    public function filters(): array
    {
        return [
            SelectFilter::make('Show', 'show')
                ->options(['' => 'Everyone', 'clients' => 'With rentals', 'banned' => 'Banned', 'credit' => 'With credit'])
                ->filter(fn (Builder $query, string $value) => match ($value) {
                    'clients' => $query->has('rentals'),
                    'banned' => $query->has('ban'),
                    'credit' => $query->where('avatars.credits', '>', 0),
                    default => $query,
                }),
        ];
    }
}
