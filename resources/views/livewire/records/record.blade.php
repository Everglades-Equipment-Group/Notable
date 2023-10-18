<?php

use function Livewire\Volt\{on, booted, updated, mount, layout, rules, state};
use App\Models\Record;
use App\Models\RecordEntry;

state([
    'id' => session('id'),
    'record' => '',
    'title' => '',
    'info' => '',
    'units' => '',
    'measuring' => '',
    'entries' => '',
    'newEntry' => '',
    'inputAt' => '',
    'sortBy' => '',
    'sortDirection' => '',
]);

layout('layouts.app');

rules([
    'title' => 'required|string',
    'info' => 'string'
]);

$getEntries = function () {
    $this->entries = RecordEntry::where('record_id', $this->id)
                    ->orderBy($this->sortBy, $this->sortDirection)
                    ->get();
};

mount(function () {

    if ($this->id == 0) {

        auth()->user()->records()->create([
            'title' => 'New Record',
            'user_id' => auth()->user()->id
        ]);

        $this->id = Record::with('user')->latest()->first()->id;

        session()->flash('id', $this->id );
        return $this->redirect('record/'.$this->id, navigate: true);
        
    } else {
        
        $this->record = Record::where('id', $this->id)->first();
    }
        
    if ($this->record) {
        $this->id = $this->record->id;
        $this->title = $this->record->title;
        $this->info = $this->record->info;
        $this->units = $this->record->units;
        $this->measuring = $this->record->measuring;
        $this->inputAt = $this->record->input_at;
        $this->sortBy = $this->record->sort_by;
        $this->sortDirection = $this->record->sort_direction;
    }

    $this->getEntries();
});

$destroy = function (Record $record) {
    
    $this->record->delete();

    return $this->redirect('/dashboard', navigate: true);
};

$newEntry = function () {

    $this->record->recordEntries()->create([
        'amount' => $this->newEntry
    ]);

    $this->newEntry = '';

    $this->getEntries();
};

$sort = function ($sortBy) {
    if ($sortBy == $this->sortBy) {
        $this->sortDirection == 'asc' ?
            $this->sortDirection = 'desc' :
            $this->sortDirection = 'asc';
    } else {
        $this->sortDirection = 'asc';
    }

    $this->sortBy = $sortBy;
    $this->record->update([
        'sort_by' => $this->sortBy, 
        'sort_direction' => $this->sortDirection
    ]);
    $this->getEntries();
};

$toggleInputAt = function () {

    $this->inputAt == 'top' ?
        $this->inputAt = 'bottom' :
        $this->inputAt = 'top';

    $this->record->update(['input_at' => $this->inputAt]);

    $this->getEntries();
};

on(['delete-entry' => function () {
    $this->getEntries();
}]);

booted(fn () => $getEntries);
updated(['title' => fn () => $this->record->update(['title' => $this->title])]);
updated(['info' => fn () => $this->record->update(['info' => $this->info])]);
updated(['units' => fn () => $this->record->update(['units' => $this->units])]);
updated(['measuring' => fn () => $this->record->update(['measuring' => $this->measuring])]);
updated(['newEntry' => $newEntry ]);

?>

<div class="flex flex-col items-center px-3">
    <div x-data="{ open: false }"
        @click.outside="open = false"
        @close.stop="open = false"
        class="sticky top-16 w-full py-4 dark:bg-gray-900"
    >
        <div class="flex justify-between items-center px-2 mb-3">
            <button
                @click="open = ! open"
                class="h-6 w-6 border rounded-full border-blue-400 dark:text-blue-400"
                title="options"
            >#</button>
            <x-text-input 
                wire:model.change="title"
                placeholder="Title"
                class="text-xl border-none text-center focus:border"
            />
            <button
                wire:click="destroy"
                class="h-6 w-6 border rounded-full border-red-500 hover:bg-red-500 dark:text-red-500 dark:hover:text-gray-900 transition"
                title="delete record"
            >X</button>
        </div>
        <div x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="p-2 pb-5 dark:bg-gray-900 dark:text-gray-300"
            style="display: none;"
        >
            <div class="text-center text-lg tracking-wider m-2">Sorting</div>
            <div class="flex justify-between items-center">
                <button
                    wire:click="sort('created_at')"
                    class="py-1"
                >chronological</button>
                |<button
                    wire:click="sort('amount')"
                    class="py-1"
                >volumetric</button>
            </div>
            <div class="text-center text-lg tracking-wider m-2">Settings</div>
            <div class="flex flex-col justify-between">
                <button
                    wire:click="toggleInputAt"
                    class="py-1 text-left"
                >input at {{ $this->inputAt == 'top' ? 'bottom' : 'top' }}</button>
            </div>
        </div>
        <textarea
            wire:model.change="info"
            placeholder="details..."
            class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600"
        ></textarea>
        <div class="flex justify-between items-center dark:text-gray-300 mt-3">
            <x-text-input 
                wire:model.change="units"
                placeholder="units"
                class="w-1/3 border-none focus:border text-center"
            />
            <div>of</div>
            <x-text-input 
                wire:model.change="measuring"
                placeholder="measuring"
                class="w-1/3 border-none focus:border text-center"
            />
        </div>
    </div>
    <div class="dark:text-gray-300 w-full py-2">
        <div>
            @if($this->inputAt == 'top')
            <x-text-input 
                wire:model.blur="newEntry"
                placeholder="new entry"
                class="border-none focus:border"
            />
            @endif
        </div>
        <div wire:sortable="updateOrder">
        @foreach($entries as $entry)
            <livewire:records.record-entry
                wire:key="entry-{{ $entry->id }}"
                :$entry
                units="{{ $this->units }}"
                measuring="{{ $this->measuring }}"
            />
        @endforeach
        </div>
        <div>
            @if($this->inputAt == 'bottom')
            <x-text-input 
                wire:model.blur="newEntry"
                placeholder="new entry"
                class="border-none focus:border"
            />
            @endif
        </div>
    </div>
    <button wire:click="test" class="dark:text-gray-300">test</button>
</div>
