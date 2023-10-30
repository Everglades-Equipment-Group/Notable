<?php

// indicate item checker / editor
// dissociate position from item
// make info data longer

use function Livewire\Volt\{boot, on, updated, mount, rules, state};
use App\Models\NoteItem;
use App\Models\User;

state([
    'item' => '',
    'title' => '',
    'info' => '',
    'checked' => false,
    'showInfo' => false,
    'creator' => '',
    'cols' => 20,
]);

state([
    'showChecks' => '',
    'showAllInfo' => '',
    'showDeletes' => '',
    'drag' => '',
    'can_check' => '',
    'can_edit' => '',
    'can_delete' => '',
])->reactive();

rules([
    'title' => 'required|string',
    'info' => 'string|max:255'
]);

$calcCols = function () {
    $cols = 20;
    if(!$this->showChecks) $cols += 3;
    if(!$this->showDeletes) $cols += 5;
    if(!$this->drag) $cols += 5;
    if($this->showAllInfo) $cols += 5;
    $this->cols = $cols;
    // $this->dispatch('resize');
};

mount(function () {
    $this->title = $this->item->title;
    $this->checked = $this->item->checked;
    $this->info = $this->item->info;
    $this->creator = $this->item->user_id;

    $this->calcCols();
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

updated([
    'title' => fn () => $this->item->update(['title' => $this->title]),
    'info' => fn () => $this->item->update(['info' => $this->info]),
    // 'showChecks' => $calcCols,
    // 'showDeletes' => $calcCols,
    // 'drag' => $calcCols,
]);

boot($calcCols);

?>

<div wire:sortable.item="{{ $this->item->id }}"
    class="my-1"
>
    <div class="flex justify-between items-center">
        <div class="flex items-center">
            @if($this->showChecks)
            <button
                wire:click="check"
                wire:key="check-{{ $this->item->id }}"
                class="{{$this->checked ? 'bg-green-800 border-green-800' : 'border-gray-600'}} h-5 w-5 text-sm border rounded-full mr-2 text-green-600"
            ></button>
            @endif
            <textarea
                wire:model.live="title"
                cols="{{ $this->cols }}"
                rows="{{ strlen($this->title) / ($this->cols + 5) + 1 }}"
                placeholder="new item"
                class="bg-transparent rounded-md border-none pl-1 focus:ring-gray-700 resize-none"
                {{ $this->can_edit ? '' : 'readonly'}}
            ></textarea>
        </div>
        <div>
            @if(!$this->showAllInfo)
            <button
                wire:click="toggleInfo"
                wire:key="info-button-{{ $this->item->id }}"
                class="{{ $this->info ? 'text-blue-400' : 'text-gray-700' }} fa-solid fa-info ml-5"
                title="details"
            ></button>
            @endif
            @if ($this->drag)
            <button
                wire:sortable.handle
                wire:key="drag-{{ $this->item->id }}"
                class="fa-solid fa-arrows-up-down ml-5 dark:text-gray-600"
                title="drag"
            ></button>
            @endif
            @if($this->can_delete && $this->showDeletes)
            <button
                wire:click="destroy"
                wire:key="delete-{{ $this->item->id }}"
                class="fa-regular fa-trash-can ml-6 hover:bg-red-500 dark:text-red-500 dark:hover:text-gray-900 transition"
                title="delete item"
            ></button>
            @endif
        </div>
    </div>
    @if($this->showAllInfo || $this->showInfo)
    <textarea
        wire:model.change="info"
        wire:key="info-{{ $this->item->id }}"
        rows="{{ strlen($this->info) / 40 + 1 }}"
        placeholder="details..."
        class="block w-full mt-1 border-gray-300 resize-none focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600"
        {{ $this->can_edit ? '' : 'readonly'}}
    ></textarea>
    @if(auth()->user()->id != $this->creator)
    <div wire:key="adder-{{ $this->item->id }}" class="text-sm dark:text-gray-500 pt-2 pl-1">Added by {{ User::find($this->creator)->name }}</div>
    @endif
    @endif
</div>
