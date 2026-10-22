<?php

namespace App\Livewire\Staff;

use App\Models\Avatar;
use App\Models\Staff;
use App\Services\SystemService;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Title;
use Livewire\Component;
use RuntimeException;

/**
 * Staff accounts (port of the 2.0.x Staff pages). Owners only. 2.0.x reset a staff member's
 * password to an unknown value on create and had them use password reset; here the owner sets it.
 */
#[Title('Staff')]
class Index extends Component
{
    public ?int $editingId = null;

    public string $username = '';

    public string $avatar_uid = '';

    public string $password = '';

    public function create(): void
    {
        $this->resetValidation();
        $this->reset('editingId', 'username', 'avatar_uid', 'password');
        Flux::modal('staff-form')->show();
    }

    public function edit(Staff $staff): void
    {
        $this->resetValidation();
        $this->reset('password');
        $this->editingId = $staff->id;
        $this->username = $staff->username;
        $this->avatar_uid = (string) $staff->avatar?->avatar_uid;
        Flux::modal('staff-form')->show();
    }

    public function save(SystemService $system): void
    {
        $creating = $this->editingId === null;
        $this->validate([
            'username' => ['required', 'string', 'min:5', 'max:40', Rule::unique('staff', 'username')->ignore($this->editingId)],
            'avatar_uid' => $creating ? ['required', 'string', 'max:8', Rule::exists('avatars', 'avatar_uid')] : [],
            'password' => [$creating ? 'required' : 'nullable', 'string', Password::min(8)],
        ], [
            'username.unique' => 'There is already a staff member with that username.',
            'avatar_uid.exists' => 'No avatar has that UID.',
        ], ['avatar_uid' => 'avatar UID']);

        if ($creating) {
            $avatar = Avatar::where('avatar_uid', $this->avatar_uid)->firstOrFail();
            if (Staff::where('avatar_id', $avatar->id)->exists()) {
                $this->addError('avatar_uid', $avatar->avatar_name.' already has a staff account.');

                return;
            }
            $system->createStaff($this->username, $avatar, $this->password);
            Flux::toast(variant: 'success', text: 'Staff member added. Give them their username and password.');
        } else {
            $system->updateStaff(Staff::findOrFail($this->editingId), $this->username, $this->password);
            Flux::toast(variant: 'success', text: $this->password !== '' ? 'Staff member saved and their password changed.' : 'Staff member saved.');
        }
        Flux::modal('staff-form')->close();
        $this->reset('editingId', 'username', 'avatar_uid', 'password');
    }

    public function delete(Staff $staff, SystemService $system): void
    {
        try {
            $system->deleteStaff($staff, Auth::user());
        } catch (RuntimeException $e) {
            Flux::toast(variant: 'danger', text: $e->getMessage());

            return;
        }
        Flux::toast(variant: 'success', text: 'Staff member removed.');
    }

    public function render()
    {
        return view('livewire.staff.index', [
            'staff' => Staff::with('avatar')->orderByDesc('owner_level')->orderBy('username')->get(),
            'pickedAvatar' => $this->editingId === null && $this->avatar_uid !== '' ? Avatar::where('avatar_uid', $this->avatar_uid)->first() : null,
        ]);
    }
}
