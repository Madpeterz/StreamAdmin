<?php

namespace App\Livewire\Tables;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Rappasoft\LaravelLivewireTables\DataTableComponent;

/**
 * Base for list pages: a page header plus a rappasoft table restyled to match Flux.
 */
abstract class StyledTable extends DataTableComponent
{
    protected string $pageTitle = '';

    protected string $pageDescription = '';

    /** Seconds between automatic refreshes of the rows (0 = never). */
    protected int $refreshSeconds = 0;

    protected function styleTable(): void
    {
        if ($this->refreshSeconds > 0) {
            $this->setRefreshTime($this->refreshSeconds * 1000);
        }
        $this->setConfigurableAreas([
            'before-wrapper' => ['livewire.tables.page-header', [
                'title' => $this->pageTitle,
                'description' => $this->pageDescription,
            ]],
        ])
            ->setToolBarAttributes(['default-styling' => false, 'class' => 'mb-4 gap-3 md:flex md:justify-between'])
            ->setColumnSelectDisabled()
            ->setSortingPillsDisabled()
            ->setPerPageAccepted([25, 50, 100])
            ->setPerPage(25)
            ->setTableWrapperAttributes([
                'default' => false,
                'class' => 'overflow-x-auto rounded-xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-700 dark:bg-zinc-900',
            ])
            ->setTableAttributes(['default' => false, 'class' => 'min-w-full'])
            ->setTheadAttributes(['default' => false, 'class' => 'border-b border-zinc-200 bg-zinc-50/80 dark:border-zinc-700 dark:bg-zinc-800/50'])
            ->setTbodyAttributes(['default' => false, 'class' => 'divide-y divide-zinc-100 dark:divide-zinc-800'])
            ->setThAttributes(fn () => [
                'default' => false,
                'class' => 'px-4 py-3 text-left text-xs font-medium whitespace-nowrap text-zinc-500 dark:text-zinc-400',
            ])
            ->setThSortButtonAttributes(fn () => [
                'default' => false,
                'class' => 'group flex items-center gap-1 text-left text-xs font-medium whitespace-nowrap text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white',
            ])
            ->setTrAttributes(fn () => [
                'default' => false,
                'class' => 'cursor-pointer transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50',
            ])
            ->setTdAttributes(fn () => [
                'default' => false,
                'class' => 'px-4 py-3 text-sm whitespace-nowrap text-zinc-700 dark:text-zinc-300',
            ])
            ->setSearchFieldAttributes([
                'default' => false,
                'class' => 'block w-full min-w-72 rounded-lg border border-zinc-200 bg-white py-2 pr-3 pl-3 text-sm shadow-xs placeholder:text-zinc-400 focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-none dark:border-white/10 dark:bg-white/10 dark:text-white',
            ]);
    }

    public function render(): Application|Factory|View
    {
        return parent::render()->title($this->pageTitle);
    }
}
