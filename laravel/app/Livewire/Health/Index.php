<?php

namespace App\Livewire\Health;

use App\Models\Region;
use App\Models\SlConfig;
use App\Models\SlObject;
use App\Services\AuditLogger;
use App\Services\ObjectHealth;
use App\Services\Scheduler;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * In-world objects: the owner's service objects, vendor uptime per region, and every object that
 * has checked in (port of the 2.0.x Health and Objects pages).
 */
#[Title('Health')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public ?int $region = null;

    #[Url(as: 'q')]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function showRegion(?int $region): void
    {
        $this->region = $region;
        $this->resetPage();
    }

    /** Objects add themselves back the next time they check in. */
    public function clearObjects(AuditLogger $audit): void
    {
        Gate::authorize('owner');
        $count = SlObject::count();
        SlObject::query()->delete();
        $audit->log('Objects', null, '---', 'all objects', (string) $count);
        Flux::toast(variant: 'success', text: "Cleared {$count} ".str('object')->plural($count).'.');
    }

    /** Pause or resume one scheduled task. */
    public function toggleTask(string $task, AuditLogger $audit): void
    {
        Gate::authorize('owner');
        abort_unless(isset(Scheduler::TASKS[$task]), 404);
        $config = SlConfig::findOrFail(1);
        $column = Scheduler::TASKS[$task]['column'];
        $config->update([$column => ! $config->{$column}]);
        $audit->logChanges('Slconfig', (string) $config->id, $config);
        Flux::toast(text: Scheduler::TASKS[$task]['label'].($config->{$column} ? ' resumed.' : ' paused.'));
    }

    public function render(ObjectHealth $health, Scheduler $scheduler)
    {
        $config = SlConfig::findOrFail(1);
        $tasks = collect(Scheduler::TASKS)->map(fn (array $spec, string $key) => [
            'key' => $key,
            'label' => $spec['label'],
            'every' => $spec['every'],
            'on' => (bool) $config->{$spec['column']},
            'last' => $scheduler->lastRun($key),
        ])->values();

        $objects = SlObject::with(['avatar', 'region'])
            ->when($this->region, fn (Builder $query) => $query->where('region_id', $this->region))
            ->when($this->search !== '', fn (Builder $query) => $query->where(fn (Builder $match) => $match
                ->where('object_name', 'like', '%'.$this->search.'%')
                ->orWhere('object_mode', 'like', '%'.$this->search.'%')
                ->orWhereHas('avatar', fn (Builder $avatar) => $avatar->where('avatar_name', 'like', '%'.$this->search.'%'))))
            ->orderByDesc('last_seen')
            ->paginate(25);

        return view('livewire.health.index', [
            'servers' => $health->serverObjects(),
            'tasks' => $tasks,
            'schedulerOn' => (bool) config('streamadmin.scheduler.enabled'),
            'vendorPercent' => $health->vendorPercent(),
            'regions' => $health->regions(),
            'objects' => $objects,
            'regionName' => $this->region ? Region::whereKey($this->region)->value('name') : null,
        ]);
    }
}
