<?php

use function Livewire\Volt\{mount, state};
use App\Models\RecordEntry;

state([
    'entry' => '',
    'amount' => '',
    'info' => '',
    'when' => ''
]);

state([
    'units' => '',
    'measuring' => '',
])->reactive();

mount(function () {
    $this->amount = $this->entry->amount;
    $this->info = $this->entry->info;
    $this->when = $this->entry->created_at->format('H:i d/m/y');
});

$destroy = function () {
    $this->entry->delete();
    $this->dispatch('delete-entry');
};

?>

<div class="flex justify-between items-center my-1">
    <div class="w-fit flex items-center">
        <x-text-input 
            wire:model.change="amount"
            placeholder="amount"
            class="w-1/6 text-center border-none focus:border"
        />
        <div>{{ $this->units }} of {{ $this->measuring }} at</div>
        <x-text-input 
            wire:model.change="when"
            placeholder="when"
            class="w-2/5 border-none focus:border"
        />
    </div>
    <button
        wire:click="destroy"
        class="h-5 w-5 text-sm border rounded-full border-red-500 hover:bg-red-500 dark:text-red-500 dark:hover:text-gray-900 transition"
        title="delete entry"
    >X</button>
</div>
