<?php

namespace Tests\Feature;

use App\Livewire\AuditLog;
use App\Livewire\Avatars;
use App\Livewire\Clients;
use App\Livewire\Coupons;
use App\Livewire\Dashboard;
use App\Livewire\Health;
use App\Livewire\Outbox;
use App\Livewire\Packages;
use App\Livewire\Reports;
use App\Livewire\Resellers;
use App\Livewire\Servers;
use App\Livewire\Streams;
use App\Livewire\Transactions;
use App\Livewire\TreeVendors;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Support\CreatesStreamAdminData;
use Tests\TestCase;

/**
 * Pages that keep themselves up to date while open, and how often.
 */
class AutoRefreshTest extends TestCase
{
    use CreatesStreamAdminData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->staff());
    }

    public function test_pages_poll_at_their_own_interval(): void
    {
        $pages = [
            [AuditLog\Index::class, 'wire:poll.60000ms'],
            [Avatars\Index::class, 'wire:poll.30000ms'],
            [Transactions\Index::class, 'wire:poll.30000ms'],
            [Packages\Index::class, 'wire:poll.300000ms'],
            [Streams\Index::class, 'wire:poll.60000ms'],
            [Clients\Index::class, 'wire:poll.120000ms'],
            [Outbox\Index::class, 'wire:poll.5s'],
            [Coupons\Index::class, 'wire:poll.60s'],
            [TreeVendors\Index::class, 'wire:poll.45s'],
            [Resellers\Index::class, 'wire:poll.60s'],
            [Servers\Index::class, 'wire:poll.120s'],
            [Reports::class, 'wire:poll.300s'],
            [Health\Index::class, 'wire:poll.15s'],
        ];
        foreach ($pages as [$component, $poll]) {
            Livewire::test($component)->assertSeeHtml($poll);
        }
    }

    public function test_dashboard_blocks_refresh_on_their_own_timers(): void
    {
        $this->rental();

        Livewire::test(Dashboard::class)
            ->assertSeeHtml('wire:poll.60s')   // the four pills and streams / servers
            ->assertSeeHtml('wire:poll.30s')   // recent transactions
            ->assertSeeHtml('wire:poll.10s')   // system health
            ->assertSeeHtml('wire:poll.300s'); // next to expire
    }
}
