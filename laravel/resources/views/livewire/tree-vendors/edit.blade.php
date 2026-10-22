<div class="mx-auto flex w-full max-w-5xl flex-col gap-6">
    <flux:breadcrumbs>
        <flux:breadcrumbs.item :href="route('tree-vendors.index')" wire:navigate>Tree vendors</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ $vendor->name }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <div>
        <flux:heading size="xl" level="1">{{ $vendor->name }}</flux:heading>
        <flux:text class="mt-1">Tree vendor ID <span class="font-mono">{{ $vendor->id }}</span> · set this on the in-world tree vendor</flux:text>
    </div>

    <flux:card class="p-0">
        <div class="flex flex-wrap items-end justify-between gap-4 px-6 pt-5 pb-3">
            <div>
                <flux:heading size="lg">Packages</flux:heading>
                <flux:text class="text-sm">What clients can choose from at this vendor.</flux:text>
            </div>
            <form wire:submit="addPackage" class="flex items-start gap-2">
                <div class="w-64">
                    <flux:select wire:model="addPackageId" placeholder="Add a package…">
                        @foreach ($available as $package)
                            <flux:select.option :value="$package->id">{{ $package->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="addPackageId" />
                </div>
                <flux:button type="submit" icon="plus" :disabled="$available->isEmpty()">Add</flux:button>
            </form>
        </div>
        <div class="px-6 pb-2">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Package</flux:table.column>
                    <flux:table.column>Type</flux:table.column>
                    <flux:table.column align="end">Price</flux:table.column>
                    <flux:table.column>Stock</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($packages as $package)
                        <flux:table.row :key="$package->id">
                            <flux:table.cell>
                                <a href="{{ route('packages.edit', $package) }}" wire:navigate class="font-medium hover:underline">{{ $package->name }}</a>
                            </flux:table.cell>
                            <flux:table.cell>{{ $package->serverType->name ?? '' }}</flux:table.cell>
                            <flux:table.cell align="end" class="tabular-nums">L$ {{ number_format($package->cost) }} / {{ $package->days }} days</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="($ready[$package->id] ?? 0) > 0 ? 'emerald' : 'red'">{{ $ready[$package->id] ?? 0 }} ready</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                <flux:button size="xs" variant="ghost" icon="x-mark" wire:click="removePackage({{ $package->id }})" tooltip="Remove from this vendor" />
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="py-6 text-center text-zinc-500">No packages yet. Add one above.</flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </flux:card>

    <form wire:submit="save" class="flex flex-col gap-6">
        <flux:card class="flex flex-col gap-5">
            <flux:heading size="lg">Settings</flux:heading>
            <flux:input wire:model="name" label="Name" />
            <x-ui.texture-input model="texture_waiting" label="Waiting texture" description="Shown while the vendor waits for a choice." />
            <x-ui.texture-input model="texture_inuse" label="In use texture" description="Shown while someone is buying." />
            <flux:field variant="inline">
                <flux:switch wire:model="hide_soldout" />
                <flux:label>Hide packages that are sold out</flux:label>
            </flux:field>
        </flux:card>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:modal.trigger name="tree-delete">
                <flux:button variant="ghost" icon="trash" class="text-red-600!">Remove tree vendor</flux:button>
            </flux:modal.trigger>
            <div class="flex gap-2">
                <flux:button :href="route('tree-vendors.index')" variant="ghost" wire:navigate>Back</flux:button>
                <flux:button type="submit" variant="primary">Save settings</flux:button>
            </div>
        </div>
    </form>

    <flux:modal name="tree-delete" class="md:w-md">
        <div class="flex flex-col gap-5">
            <div>
                <flux:heading size="lg">Remove {{ $vendor->name }}?</flux:heading>
                <flux:text class="mt-2">In-world vendors using ID {{ $vendor->id }} will stop working. The packages themselves are not removed.</flux:text>
            </div>
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
                <flux:button variant="danger" wire:click="delete">Remove tree vendor</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
