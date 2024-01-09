<?php

// datetime updating
// display entry details

use function Livewire\Volt\{on, updated, mount, state, rules};
use App\Models\RecordEntry;
use Carbon\CarbonImmutable;

// date_default_timezone_set("America/New_York");

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

rules([
    'amount' => 'numeric',
]);

mount(function () {
    $this->amount = $this->entry->amount;
    $this->info = $this->entry->info;
    $this->time = CarbonImmutable::parse($this->entry->created_at)->setTimezone('America/New_York')->format('H:i');
    $this->date = CarbonImmutable::parse($this->entry->created_at)->setTimezone('America/New_York')->format('Y-m-d');
});

$destroy = function () {
    $this->entry->delete();
    $this->dispatch('delete-entry');
};

$datetimeUpdated = function () {
    $this->entry->update(['created_at' => CarbonImmutable::parse($this->date.' '.$this->time.':00')->shiftTimezone('America/New_York')->setTimezone('UTC')]);
    $this->dispatch('entry-updated');
};

on(['delete-record-entries' => $destroy]);

updated([
    'amount' => function () {
        $this->validate();
        $this->entry->update(['amount' => $this->amount]);
    },
    'info' => fn () => $this->entry->update(['info' => $this->info]),
    'time' => fn () => $this->datetimeUpdated(),
    'date' => fn () => $this->datetimeUpdated(),
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
