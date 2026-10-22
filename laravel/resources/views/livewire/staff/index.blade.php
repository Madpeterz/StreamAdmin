<div class="mx-auto flex w-full max-w-5xl flex-col gap-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">Staff</flux:heading>
            <flux:text class="mt-1">Who can sign in to StreamAdmin. Owners can also change system settings, bans, the bot and staff.</flux:text>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="create">Add staff member</flux:button>
    </div>

    <flux:card class="p-0">
        <div class="px-6 py-2">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Username</flux:table.column>
                    <flux:table.column>Avatar</flux:table.column>
                    <flux:table.column>Access</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($staff as $member)
                        <flux:table.row :key="'staff-'.$member->id">
                            <flux:table.cell>
                                <div class="flex items-center gap-3">
                                    <flux:avatar size="sm" :name="$member->username" />
                                    <div>
                                        <div class="font-medium text-zinc-900 dark:text-white">{{ $member->username }}</div>
                                        @if ($member->is(auth()->user()))
                                            <div class="text-xs text-zinc-500">You</div>
                                        @endif
                                    </div>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($member->avatar)
                                    <a href="{{ route('avatars.show', $member->avatar) }}" wire:navigate class="hover:underline">{{ $member->avatar->avatar_name }}</a>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="$member->owner_level ? 'emerald' : 'zinc'">{{ $member->owner_level ? 'Owner' : 'Staff' }}</flux:badge>
                                @if ($member->password === null)
                                    <flux:badge size="sm" color="amber" class="ml-1" title="Still on their 2.0.x password; it is upgraded when they next sign in.">Old password</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                <flux:dropdown align="end">
                                    <flux:button size="sm" variant="ghost" icon="ellipsis-horizontal" />
                                    <flux:menu>
                                        <flux:menu.item icon="pencil-square" wire:click="edit({{ $member->id }})">Edit</flux:menu.item>
                                        @if (! $member->owner_level && ! $member->is(auth()->user()))
                                            <flux:menu.separator />
                                            <flux:menu.item icon="trash" variant="danger" wire:click="delete({{ $member->id }})" wire:confirm="Remove {{ $member->username }}? They will be signed out.">Remove</flux:menu.item>
                                        @endif
                                    </flux:menu>
                                </flux:dropdown>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>
    </flux:card>

    <flux:modal name="staff-form" class="md:w-md">
        <form wire:submit="save" class="flex flex-col gap-5">
            <flux:heading size="lg">{{ $editingId ? 'Edit staff member' : 'Add staff member' }}</flux:heading>
            <flux:input wire:model="username" label="Username" description="Used to sign in; it does not have to be their SL name." autocomplete="off" />
            @if (! $editingId)
                <flux:field>
                    <flux:label>Avatar UID</flux:label>
                    <flux:description>From the Avatars page. Each avatar can have one staff account.</flux:description>
                    <flux:input wire:model.live.debounce.400ms="avatar_uid" maxlength="8" class="font-mono" autocomplete="off" />
                    <flux:error name="avatar_uid" />
                    @if ($pickedAvatar)
                        <div class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-300">
                            <flux:avatar size="xs" :name="$pickedAvatar->avatar_name" /> {{ $pickedAvatar->avatar_name }}
                        </div>
                    @endif
                </flux:field>
            @endif
            <flux:input wire:model="password" type="password" viewable autocomplete="new-password"
                :label="$editingId ? 'New password' : 'Password'"
                :description="$editingId ? 'Leave empty to keep their password. A new one signs them out everywhere.' : 'At least 8 characters. They can change it under Settings.'" />
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">{{ $editingId ? 'Save' : 'Add staff member' }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
