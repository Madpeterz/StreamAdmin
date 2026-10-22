<?php

namespace Tests\Feature\Settings;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Volt\Volt;
use Tests\Support\CreatesStreamAdminData;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use CreatesStreamAdminData;
    use RefreshDatabase;

    public function test_password_can_be_updated(): void
    {
        $staff = $this->staff();

        $this->actingAs($staff);

        $response = Volt::test('settings.password')
            ->set('current_password', 'secret-password')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('updatePassword');

        $response->assertHasNoErrors();

        $this->assertTrue(Hash::check('new-password', $staff->refresh()->password));
    }

    public function test_correct_password_must_be_provided_to_update_password(): void
    {
        $this->actingAs($this->staff());

        $response = Volt::test('settings.password')
            ->set('current_password', 'wrong-password')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('updatePassword');

        $response->assertHasErrors(['current_password']);
    }
}
