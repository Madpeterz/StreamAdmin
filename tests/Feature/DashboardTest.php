<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesStreamAdminData;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use CreatesStreamAdminData;
    use RefreshDatabase;

    public function test_dashboard_shows_client_counts_and_next_rentals(): void
    {
        $this->rental(hoursLeft: 200, avatar: $this->avatar('Happy Client'));
        $this->rental(hoursLeft: 5);
        $this->rental(hoursLeft: -2);

        $this->actingAs($this->staff())
            ->get('/dashboard')
            ->assertOk()
            ->assertSeeInOrder(['Active clients', '1', 'Expiring in 24 hours', '1', 'Expired', '1'])
            ->assertSee('Happy Client');
    }

    public function test_pages_show_the_build_version_and_date(): void
    {
        config(['app.version' => '3.1.4', 'app.build_date' => '2026-10-23 14:05 UTC']);

        $this->get('/login')->assertOk()->assertSee('3.1.4')->assertSee('built 2026-10-23 14:05 UTC');
        $this->actingAs($this->staff())->get('/dashboard')->assertSee('3.1.4')->assertSee('built 2026-10-23 14:05 UTC');
    }

    public function test_owner_only_sections_are_hidden_from_staff(): void
    {
        $this->actingAs($this->staff(owner: false));
        foreach (['/staff', '/system-settings', '/bans', '/audit-log', '/bot'] as $page) {
            $this->get($page)->assertForbidden();
        }
        $this->get('/dashboard')->assertDontSee(route('staff.index'))->assertSee(route('avatars.index'));
    }
}
