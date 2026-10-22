<?php

namespace Tests\Feature\Catalog;

use App\Livewire\Servers\Index;
use App\Models\AuditLog;
use App\Models\Server;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Support\CreatesStreamAdminData;
use Tests\TestCase;

class ServersTest extends TestCase
{
    use CreatesStreamAdminData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->staff());
    }

    public function test_page_shows_servers_with_capacity(): void
    {
        $this->rental();

        $this->get('/servers')->assertOk()->assertSee('stream.test')->assertSee('assigned of');
    }

    public function test_add_and_edit_a_server(): void
    {
        Livewire::test(Index::class)
            ->set('domain', 'new.stream.test')->set('control_panel_url', 'https://cp.new.test')->set('ip_address', '10.1.1.1')
            ->set('bandwidth', 2)->set('bandwidth_type', 'gbps')->set('total_storage', 1)->set('total_storage_type', 'tb')
            ->call('save')->assertHasNoErrors();

        $server = Server::where('domain', 'new.stream.test')->firstOrFail();
        $this->assertSame(2_000_000, $server->bandwidthKbps());
        $this->assertTrue(AuditLog::where('store', 'Server')->where('value_name', '+++')->exists());

        // 2.0.x silently ignored bandwidth/storage edits; they are saved now
        Livewire::test(Index::class)->call('edit', $server->id)->set('total_storage', 2)->call('save')->assertHasNoErrors();
        $this->assertSame(2, $server->refresh()->total_storage);
    }

    public function test_domain_and_ip_must_be_unique(): void
    {
        $this->rental();
        Server::where('domain', 'stream.test')->update(['ip_address' => '10.0.0.9']);

        Livewire::test(Index::class)
            ->set('domain', 'stream.test')->set('control_panel_url', 'https://cp.test')->set('ip_address', '10.0.0.9')
            ->call('save')->assertHasErrors(['domain', 'ip_address']);
    }

    public function test_server_with_streams_cannot_be_removed(): void
    {
        $server = $this->rental()->stream->server;

        Livewire::test(Index::class)->call('confirmDelete', $server->id)->call('delete');
        $this->assertNotNull(Server::find($server->id));

        $empty = Server::create(['domain' => 'empty.test', 'control_panel_url' => 'https://cp.empty.test']);
        Livewire::test(Index::class)->call('confirmDelete', $empty->id)->call('delete');
        $this->assertNull(Server::find($empty->id));
    }
}
