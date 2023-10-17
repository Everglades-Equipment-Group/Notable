<?php

use function Livewire\Volt\{on, booted, updated, mount, layout, rules, state};
use App\Models\Note;
use App\Models\NoteItem;

state([
    'id' => session('id'),
    'note' => '',
    'title' => '',
    'info' => '',
    'items' => '',
    'newItem' => '',
    'inputAt' => '',
    'showChecks' => '',
    'moveChecked' => '',
    'sortBy' => '',
    'sortDirection' => '',
]);

// tells volt which layout to use
// when using as full-page component
layout('layouts.app');

rules([
    'title' => 'required|string',
    'info' => 'string'
]);

$getItems = function () {
    $this->items = NoteItem::where('note_id', $this->id)
                    ->when($this->moveChecked, fn ($query) => $query->orderBy('checked'))
                    ->orderBy($this->sortBy, $this->sortDirection)
                    ->get();
};

mount(function () {

    if ($this->id == 0) {

        auth()->user()->notes()->create([
            'title' => 'New Note',
            'user_id' => auth()->user()->id
        ]);

        $this->id = Note::with('user')->latest()->first()->id;

        session()->flash('id', $this->id );
        return $this->redirect('note/'.$this->id, navigate: true);
        
    } else {
        
        $this->note = Note::where('id', $this->id)->first();
    }
        
    if ($this->note) {
        $this->id = $this->note->id;
        $this->title = $this->note->title;
        $this->info = $this->note->info;
        $this->inputAt = $this->note->input_at;
        $this->showChecks = $this->note->show_checks;
        $this->moveChecked = $this->note->move_checked;
        $this->sortBy = $this->note->sort_by;
        $this->sortDirection = $this->note->sort_direction;
    }

    $this->getItems();
});

$newItem = function () {

    $this->note->noteItems()->create([
        'title' => $this->newItem
    ]);

    $this->newItem = '';

    $this->getItems();
};

$destroy = function (Note $note) {
    
    // $this->authorize('delete', $note);

    $this->note->delete();

    return $this->redirect('/dashboard', navigate: true);
};

$toggleInputAt = function () {

    $this->inputAt == 'top' ?
        $this->inputAt = 'bottom' :
        $this->inputAt = 'top';

    $this->note->update(['input_at' => $this->inputAt]);

    $this->getItems();
};

$toggleChecks = function () {
    $this->showChecks = ! $this->showChecks;
    $this->note->update(['show_checks' => $this->showChecks]);
    $this->getItems();
};

$toggleMoveChecked = function () {
    $this->moveChecked = ! $this->moveChecked;
    $this->note->update(['move_checked' => $this->moveChecked]);
    $this->getItems();
};

$updateOrder = function ($items) {
    foreach ($items as $item) {
        NoteItem::find($item['value'])->update(['position' => $item['order']]);
    }

    $this->getItems();
};

$sort = function ($sortBy) {
    if ($sortBy == $this->sortBy && $sortBy != 'position') {
        $this->sortDirection == 'asc' ?
            $this->sortDirection = 'desc' :
            $this->sortDirection = 'asc';
    } else {
        $this->sortDirection = 'asc';
    }

    $this->sortBy = $sortBy;
    $this->note->update(['sort_by' => $this->sortBy, 'sort_direction' => $this->sortDirection]);
    $this->getItems();
};

on(['delete-item' => function () {
    $this->getItems();
}]);

on(['check' => function () {
    $this->getItems();
}]);

$test = fn () => dd($this->items);

booted(fn () => $getItems);
updated(['title' => fn () => $this->note->update(['title' => $this->title])]);
updated(['info' => fn () => $this->note->update(['info' => $this->info])]);
updated(['newItem' => $newItem ]);

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
            >#</button>
            <x-text-input 
                wire:model.change="title"
                placeholder="Title"
                class="text-xl border-none text-center focus:border"
            />
            <button
                wire:click="destroy"
                class="h-6 w-6 border rounded-full border-red-500 hover:bg-red-500 dark:text-red-500 dark:hover:text-gray-900 transition"
                title="delete note"
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
            <div class="text-center text-lg tracking-wider">Sorting</div>
            <div class="flex justify-between items-center">
                <button
                    wire:click="sort('title')"
                    class="py-1"
                >alphabetical</button>
                |<button
                    wire:click="sort('created_at')"
                    class="py-1"
                >chronological</button>
                |<button
                    wire:click="sort('position')"
                    class="py-1"
                >draggable</button>
            </div>
            <div class="text-center text-lg tracking-wider mt-2">Settings</div>
            <div class="flex flex-col justify-between">
                <button
                    wire:click="toggleInputAt"
                    class="py-1 text-left"
                >input at {{ $this->inputAt == 'top' ? 'bottom' : 'top' }}</button>
                <button
                    wire:click="toggleChecks"
                    class="py-1 text-left"
                >{{ $this->showChecks ? 'hide' : 'show'}} checks</button>
                <button
                    wire:click="toggleMoveChecked"
                    class="py-1 text-left"
                >move checked to bottom</button>
            </div>
        </div>
        <textarea
            wire:model.change="info"
            placeholder="details..."
            class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600"
        ></textarea>
    </div>
    <div class="dark:text-gray-300 w-full py-2">
        <div>
            @if($this->inputAt == 'top')
            <x-text-input 
                wire:model.blur="newItem"
                placeholder="new item"
                class="border-none focus:border"
            />
            @endif
        </div>
        <div wire:sortable="updateOrder">
        @foreach($items as $item)
            <livewire:notes.note-item
                wire:key="item-{{ $item->id }}"
                :$item
                :showChecks="$this->showChecks"
                drag="{{ $this->sortBy == 'position' }}"
            />
        @endforeach
        </div>
        <div>
            @if($this->inputAt == 'bottom')
            <x-text-input 
                wire:model.blur="newItem"
                placeholder="new item"
                class="border-none focus:border"
            />
            @endif
        </div>
    </div>
    <button wire:click="test" class="dark:text-gray-300">test</button>
</div>