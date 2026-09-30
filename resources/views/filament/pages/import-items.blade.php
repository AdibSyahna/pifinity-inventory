@section('title', 'Import Items')

<x-filament-panels::page>
    <form wire:submit="import">
        <div style="max-width: max-content">
            {{ $this->form }}
        </div>
        <br />
        <x-filament::button type="submit" class="mt-4">
            Import
        </x-filament::button>
    </form>
</x-filament-panels::page>
