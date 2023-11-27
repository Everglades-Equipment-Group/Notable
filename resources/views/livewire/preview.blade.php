<?php

use function Livewire\Volt\{state};

state(['data']);
state([
    'type' => '',
    'id' => 0,
    'sortBy' => ''
]);

$newItem = function () {
    session()->flash('id', $this->id );
    return $this->redirect($this->type.'/'.$this->id);
};

$viewItem = function ($id) {
    session()->flash('id', $id );
    return $this->redirect($this->type.'/'.$id);
};

$sort = function ($sortBy) {
    $this->sortBy == $sortBy ?
        $this->sortBy = $sortBy.'-desc' :
        $this->sortBy = $sortBy;
    $this->dispatch('sort-'.$this->type.'s', type: $this->type, sortBy: $this->sortBy);
};

$viewAll = function () {
    // session()->flash('type', $type);
    return $this->redirect($this->type.'s');
};

?>

<div class="flex flex-col items-center border border-blue-400 rounded-lg h-56 my-3 px-4 pb-4 dark:text-gray-300">
    <div class="flex items-center justify-between h-1/5 w-full dark:text-gray-300">
        <button wire:click="newItem" class="h-full pr-5 border-r-2 border-blue-400 rounded-lg">New</button>
        <div wire:click="viewAll" class="p-1 tracking-wider">
            {{ ucfirst($type) }}s
        </div>
        <div x-data="{ open: false }"
            @click.outside="open = false"
            @close.stop="open = false"
            class="relative h-full"
        >
            <button @click="open = ! open" class="h-full pl-5 border-l-2 border-blue-400 rounded-lg">Sort</button>

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
    <div class="flex flex-col justify-start items-center border border-blue-400 overflow-y-scroll py-2 bg-gray-900 rounded-lg h-4/5 w-full p-4">
        @if ($data)
        @foreach ($data as $item)
        <div class="w-full">
            <button
                wire:click="viewItem({{ $item->id }})"
                :key="{{ $item->id }}"
                class="w-full text-left my-1 flex justify-between"
            >
                <span class="w-3/5">
                    {{ $item->title }}
                </span>
                <div>
                    @if($item->users->count() > 1)
                    <span class="text-center pr-1 text-red-500">
                        @if($item->user_id == auth()->user()->id)
                            &<i class="fa-solid fa-angle-right text-blue-400"></i>
                        @else
                            <i class="fa-solid fa-angle-left text-blue-400"></i>&
                        @endif
                    </span>
                    @endif
                    @if($item->start_date)
                    <span class="text-xs text-gray-500 text-right">
                        {{ date('M d, y', strtotime($item->start_date)) }}
                    </span>
                    @endif
                </div>
            </button>
        </div>
        @endforeach
        @endif
        @if(!$data)
        <div class="h-full w-full flex justify-center items-center">
            no {{ $type }}s to display
        </div>
        @endif
    </div>
</div>
