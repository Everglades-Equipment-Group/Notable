<?php

use function Livewire\Volt\{state};

state(['data'])->reactive();
state([
    'type' => '',
    'id' => 0,
    'sortBy' => ''
]);

$newItem = function () {
    session()->flash('id', $this->id );
    return $this->redirect($this->type.'/'.$this->id, navigate: true);
};

$viewItem = function ($id) {
    session()->flash('id', $id );
    return $this->redirect($this->type.'/'.$id, navigate: true);
};

$sort = function ($sortBy) {
    $this->sortBy == $sortBy ?
        $this->sortBy = $sortBy.'-desc' :
        $this->sortBy = $sortBy;
    $this->dispatch('sort-'.$this->type.'s', type: $this->type, sortBy: $this->sortBy);
}

?>

<div class="flex flex-col items-center border border-gray-500 rounded-lg h-1/4 my-5 px-4 pb-4 dark:text-gray-300">
    <div class="flex items-center justify-between h-1/5 w-full dark:text-gray-300">
            <button wire:click="newItem" class="p-1 pr-5 border-r border-gray-500 rounded-lg">New</button>
        <div class="p-1 tracking-wider">{{ ucfirst($type) }}s</div>

        <div x-data="{ open: false }"
            @click.outside="open = false"
            @close.stop="open = false"
            class="relative"
        >
            <button @click="open = ! open" class="p-1 pl-5 border-l border-gray-500 rounded-lg">Sort</button>

            <div x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                style="display: none;"
                @click="open = false"
                class="absolute z-10 p-2 rounded-md shadow-lg border border-t-0 border-e-0 border-gray-500 dark:bg-gray-900"
            >
                <button wire:click="sort('alpha')" class="py-1">Alpha</button>
                <button wire:click="sort('chrono')" class="py-1">Chrono</button>
            </div>
        </div>

    </div>
    <div class="flex flex-col justify-start items-center overflow-y-scroll py-2 border border-gray-500 rounded-lg h-4/5 w-full">
        @if ($data)
        @foreach ($data as $item)
            <button
                wire:click="viewItem({{ $item->id }})"
                :key="{{ $item->id }}"
                class="my-1"
            >
                {{ $item->title }}
            </button>
        @endforeach
        @endif
        @if(!$data)
        <div class="h-full w-full flex justify-center items-center">
            no {{ $type }}s to display
        </div>
        @endif
    </div>
</div>
