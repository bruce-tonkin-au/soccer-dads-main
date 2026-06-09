<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-4">
            <x-filament::button type="submit">
                Save scoring
            </x-filament::button>
        </div>
    </form>

    <x-filament::section icon="heroicon-o-information-circle">
        <x-slot name="heading">The draw</x-slot>

        <p class="text-sm text-gray-600 dark:text-gray-400">
            The draw is done manually from a hat on Friday night. Record each entry's
            teams and players under <strong>Entries</strong> once the names have been pulled.
        </p>
    </x-filament::section>
</x-filament-panels::page>
