<?php

// datetime updating
// display entry details

use function Livewire\Volt\{on, updated, mount, state};
use App\Models\RecordEntry;

state([
    'entry' => '',
    'amount' => '',
    'info' => '',
    'time' => '',
    'date' => '',
]);

state([
    'units' => '',
    'measuring' => '',
    'showDeletes' => '',
    'showUnits' => '',
    'showTime' => '',
    'showDate' => '',
    'can_edit' => '',
    'can_delete' => '',
])->reactive();

mount(function () {
    $this->amount = $this->entry->amount;
    $this->info = $this->entry->info;
    $this->time = $this->entry->created_at->format('h:i');
    $this->date = $this->entry->created_at->format('Y-m-d');
});

$destroy = function () {
    $this->entry->delete();
    $this->dispatch('delete-entry');
};

on(['delete-record-entries' => $destroy]);

updated([
    'amount' => fn () => $this->entry->update(['amount' => $this->amount]),
    'info' => fn () => $this->entry->update(['info' => $this->info]),
    'time' => fn () => $this->entry->update(['created_at' => $this->date.' '.$this->time.':00']),
    'date' => fn () => $this->entry->update(['created_at' => $this->date.' '.$this->time.':00']),
]);

?>

<div class="flex flex-col justify-between items-center my-1">
    <div class="w-full flex items-center justify-between">
        <div class="flex items-center">
            <x-text-input 
            wire:model.change="amount"
            placeholder="amount"
            class="w-1/6 text-center border-none focus:border"
            />
            @if($this->showUnits)
            <div>{{ $this->units }} of {{ $this->measuring }}</div>
            @endif
        </div>
        @if($this->can_delete && $this->showDeletes)
        <button
            wire:click="destroy"
            class="fa-regular fa-trash-can ml-6 text-red-500"
            title="delete entry"
        ></button>
        @endif
    </div>
    <div class="flex items-center">
        @if($this->showTime)
        <x-time-input 
            wire:model.change="time"
        />
        @endif
        @if($this->showDate)
        <x-date-input 
            wire:model.change="date"
        />
        @endif
    </div>
</div>
