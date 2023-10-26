<?php

// indicate item checker / editor
// dissociate position from item

use function Livewire\Volt\{on, updated, mount, rules, state};
use App\Models\NoteItem;
use App\Models\User;

state([
    'item' => '',
    'title' => '',
    'info' => '',
    'checked' => false,
    'showInfo' => false,
    'creator' => '',
]);

state([
    'showChecks' => true,
    'showAllInfo' => false,
    'showDeletes' => false,
    'drag' => '',
    'can_check' => true,
    'can_edit' => false,
    'can_delete' => false,
])->reactive();

rules([
    'title' => 'required|string',
    'info' => 'string|max:255'
]);

mount(function () {
    $this->title = $this->item->title;
    $this->checked = $this->item->checked;
    $this->info = $this->item->info;
    $this->creator = $this->item->user_id;
});

$destroy = function () {
    $this->item->delete();
    $this->dispatch('delete-item');
};

$check = function () {
    if ($this->can_check) {
        $this->checked = ! $this->checked;
        $this->item->update(['checked' => $this->checked]);
        $this->dispatch('check');
    };
};

$toggleInfo = function () {
    $this->showInfo = ! $this->showInfo;
};

on(['delete-note-items' => function () {
    $this->item->delete();
    $this->dispatch('delete-item');
}]);

updated(['title' => fn () => $this->item->update(['title' => $this->title])]);
updated(['info' => fn () => $this->item->update(['info' => $this->info])]);

?>

<div wire:sortable.item="{{ $this->item->id }}"
    class="my-1"
>
    <div class="flex justify-between items-center">
        <div class="flex items-center">
            @if($this->showChecks)
            <button
                wire:click="check"
                class="{{$this->checked ? 'bg-green-800 border-green-800' : 'border-gray-600'}} h-5 w-5 text-sm border rounded-full mr-2 text-green-600"
            ></button>
            @endif
            <x-text-input 
            wire:model.change="title"
            placeholder="new item"
            class="border-none focus:border pl-1"
            disabled="{{ ! $this->can_edit }}"
            />
        </div>
        <div>
            @if(!$this->showAllInfo)
            <button
                wire:click="toggleInfo"
                class="{{ $this->info ? 'text-blue-400' : 'text-gray-700' }} fa-solid fa-info ml-5"
                title="details"
            ></button>
            @endif
            @if ($this->drag)
            <button
                wire:sortable.handle
                class="fa-solid fa-arrows-up-down ml-5 dark:text-gray-600"
                title="drag"
            ></button>
            @endif
            @if($this->can_delete && $this->showDeletes)
            <button
                wire:click="destroy"
                class="fa-regular fa-trash-can ml-6 hover:bg-red-500 dark:text-red-500 dark:hover:text-gray-900 transition"
                title="delete item"
            ></button>
            @endif
        </div>
    </div>
    @if($this->showAllInfo || $this->showInfo)
    <textarea
        wire:model.change="info"
        placeholder="details..."
        class="block w-full mt-1 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600"
        {{ $this->can_edit ? '' : 'readonly'}}
    ></textarea>
    @if(auth()->user()->id != $this->creator)
    <div class="text-sm dark:text-gray-500 pt-2 pl-1">Added by {{ User::find($this->creator)->name }}</div>
    @endif
    @endif
</div>
