<x-filament-panels::page>
    <form wire:submit="save">
        <div style="max-width: max-content">
            {{ $this->form }}
        </div>
        <br />
        <x-filament::button type="submit" class="mt-4">
            Save
        </x-filament::button>
    </form>
</x-filament-panels::page>