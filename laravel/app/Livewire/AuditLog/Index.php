<?php

namespace App\Livewire\AuditLog;

use App\Livewire\Tables\StyledTable;
use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

/**
 * Every change staff (and the system) made, newest first (port of the 2.0.x Audit log, which only
 * showed the newest 25). Owners only.
 */
class Index extends StyledTable
{
    protected $model = AuditLog::class;

    protected string $pageTitle = 'Audit log';

    protected string $pageDescription = 'Who changed what, and when.';

    protected int $refreshSeconds = 60;

    public function configure(): void
    {
        $this->styleTable();
        $this->setPrimaryKey('id')
            ->setDefaultSort('unixtime', 'desc')
            ->setSearchPlaceholder('Search record, field or value')
            ->setAdditionalSelects(['audit_logs.id', 'audit_logs.unixtime', 'audit_logs.avatar_id'])
            ->setTrAttributes(fn () => ['default' => false, 'class' => 'align-top']);
    }

    public function builder(): Builder
    {
        return AuditLog::query()->with('avatar');
    }

    private static function value(?string $value): string
    {
        return match ($value) {
            null, '' => '<span class="text-zinc-400">–</span>',
            default => '<span class="block max-w-xs truncate" title="'.e($value).'">'.e($value).'</span>',
        };
    }

    public function columns(): array
    {
        return [
            Column::make('When', 'unixtime')
                ->sortable()
                ->format(fn ($value, AuditLog $row) => '<span title="'.e($row->unixtime->toDayDateTimeString()).'">'.e($row->unixtime->diffForHumans()).'</span>')
                ->html(),
            Column::make('Area', 'store')
                ->sortable()
                ->format(fn ($value) => '<span class="rounded bg-zinc-100 px-1.5 py-0.5 text-xs font-medium dark:bg-zinc-800">'.e($value).'</span>')
                ->html(),
            Column::make('Record', 'source_id')
                ->searchable()
                ->collapseOnTablet()
                ->format(fn ($value) => '<span class="font-mono text-xs text-zinc-500">'.e($value).'</span>')
                ->html(),
            // "+++" and "---" are how 2.0.x recorded creates and removals
            Column::make('Change', 'value_name')
                ->searchable()
                ->format(fn ($value) => match ($value) {
                    '+++' => '<span class="font-medium text-emerald-600 dark:text-emerald-400">Created</span>',
                    '---' => '<span class="font-medium text-red-600 dark:text-red-400">Removed</span>',
                    default => '<span class="font-medium">'.e($value).'</span>',
                })
                ->html(),
            Column::make('Old', 'old_value')
                ->searchable()
                ->format(fn ($value) => self::value($value))
                ->html(),
            Column::make('New', 'new_value')
                ->searchable()
                ->format(fn ($value) => self::value($value))
                ->html(),
            Column::make('By', 'avatar.avatar_name')
                ->searchable()
                ->collapseOnMobile(),
        ];
    }

    public function filters(): array
    {
        return [
            SelectFilter::make('Area', 'store')
                ->options(['' => 'All areas'] + AuditLog::query()->distinct()->orderBy('store')->pluck('store', 'store')->all())
                ->filter(fn (Builder $query, string $value) => $query->where('audit_logs.store', $value)),
        ];
    }
}
