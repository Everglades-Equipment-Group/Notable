<?php

use function Livewire\Volt\{on, booted, updated, mount, layout, rules, messages, state};
use App\Models\Record;
use App\Models\User;

state([
    'user' => '',
    'records' => '',
    'sortBy' => '',
    'sortDirection' => '',
    'total' => '',
]);

layout('layouts.app');

mount(function () {
    $this->user = auth()->user();
    $this->records = $this->user->records()->latest()->get();
    $this->total = $this->records->count();
});

$newRecord = function () {
    $record = Record::create([
        'user_id' => $this->user->id,
        'title' => 'New Record',
    ]);

    $this->user->records()->attach($record->id, [
        'resource_type' => 'record',
        'can_edit' => true,
        'can_delete' => true,
        'can_share' => true,
    ]);

    $this->records->prepend($record);

    session()->flash('id', $record->id);
    return $this->redirect('record/'.$record->id);
};

$viewRecord = function ($id) {
    session()->flash('id', $id);
    return $this->redirect('record/'.$id);
};

$sort = function ($sortBy) {
    if ($this->sortBy == $sortBy) {
        $this->sortDirection == 'asc' ?
        $this->sortDirection = 'desc' :
        $this->sortDirection = 'asc';
    } else {
        $this->sortBy = $sortBy;
        $this->sortDirection = 'asc';
    };

    $this->records = $this->user->records()->orderBy($this->sortBy, $this->sortDirection)->get();
};

?>

<div class="flex flex-col items-center px-3 pb-3 bg-inherit dark:text-gray-300">
    <div x-data="{ open: false }"
        @close.stop="open = false"
        class="sticky top-20 w-full py-4 z-10 bg-inherit lg:w-1/3"
    >
        <div class="relative flex items-center justify-center pb-1">
            <button
                @click="open = ! open"
                class="absolute left-2 fa-solid fa-sliders text-2xl text-blue-400"
                title="options"
            ></button>
            <div class="text-xl tracking-wide">Records</div>
            <button
                wire:click="newRecord"
                class="absolute right-2 fa-solid fa-folder-plus text-2xl text-blue-400"
                title="new record"
            ></button>
        </div>
<!-- SETTINGS -------------------------------------------------------------->
        <div x-show="open"
            @click.outside="open = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="flex flex-col items-center p-2 pb-5 bg-inherit dark:text-gray-300"
            style="display: none;"
        >
            <hr class="w-full border-none h-px bg-gray-500 -mb-6 mt-6">
            <div class="w-fit px-2 text-center text-lg tracking-wider m-2 bg-inherit">Total</div>
            <div class="">{{ $this->total }}</div>
            <hr class="w-full border-none h-px bg-gray-500 -mb-6 mt-6">
            <div class="w-fit px-2 text-center text-lg tracking-wider m-2 bg-inherit">Sorting</div>
            <div class="relative w-full flex justify-between items-center">
                <button
                    wire:click="sort('title')"
                    class="w-1/2 text-center py-1"
                >alphabetical
                    @if($this->sortBy == 'title')
                    <span class="fa-arrow-{{ $this->sortDirection == 'asc' ? 'down' : 'up' }}-long fa-solid pl-1 text-blue-400">
                    </span>
                    @endif
                </button>
                <span class="absolute left-1/2 -translate-x-1/2 text-gray-500">|</span>
                <button
                    wire:click="sort('created_at')"
                    class="w-1/2 text-center py-1"
                >chronological
                    @if($this->sortBy == 'created_at')
                    <span class="fa-arrow-{{ $this->sortDirection == 'asc' ? 'down' : 'up' }}-long fa-solid pl-1 text-blue-400"> 
                    </span>
                    @endif
                </button>
            </div>
            <hr class="w-full border-none h-px bg-gray-500 -mb-6 mt-6">
            <div class="w-fit px-2 text-center text-lg tracking-wider m-2 bg-inherit">Settings</div>
            <div class="w-full flex flex-col justify-between">

            </div>
        </div>
<!-- END SETTINGS ------------------------------------------------------------->
    </div>
    <div 
        class="flex flex-col items-center justify-center w-full pt-5 px-6 lg:w-1/3"
    >
    @foreach ($this->records as $record)
        <livewire:records.records-list-item
            wire:key="record-{{ $record->id }}"
            :record="$record"
        />
            
    @endforeach
    </div>
</div>