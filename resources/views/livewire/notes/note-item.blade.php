<?php

use function Livewire\Volt\{mount, state};
use App\Models\NoteItem;

state([
    'item' => '',
    'title' => '',
    'checked' => false,
]);

state([
    'showChecks' => true,
    'drag' => ''
])->reactive();

mount(function () {
    $this->title = $this->item->title;
    $this->checked = $this->item->checked;
});

$destroy = function () {
    $this->item->delete();
    $this->dispatch('delete-item');
};

$check = function () {
    $this->checked = ! $this->checked;
    $this->item->update(['checked' => $this->checked]);
    $this->dispatch('check');
};

?>

<div wire:sortable.item="{{ $this->item->id }}" class="flex justify-between items-center my-1">
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
            class="border-none focus:border"
        />
    </div>
    @if ($this->drag)
    <button wire:sortable.handle>drag</button>
    @endif
    <button
        wire:click="destroy"
        class="h-5 w-5 text-sm border rounded-full border-red-500 hover:bg-red-500 dark:text-red-500 dark:hover:text-gray-900 transition"
        title="delete item"
    >X</button>
</div>
