<?php

use function Livewire\Volt\{on, booted, updated, mount, layout, rules, state};
use App\Models\Note;
use App\Models\NoteItem;

state([
    'id' => session('id'),
    'title' => '',
    'info' => '',
    'note' => '',
    'items' => '',
    'newItem' => '',
    'direction' => '',
    'checks' => true,
    'moveChecked' => false,
    'sortBy' => ''
]);

// tells volt which layout to use
// when using as full-page component
layout('layouts.app');

rules([
    'title' => 'required|string',
    'info' => 'string'
]);

$getItems = function () {
    $sorts = [
        '' => NoteItem::where('note_id', $this->id)->get(),
    ];

    $this->direction == 'forward' ?
        $this->items = NoteItem::where('note_id', $this->id)->get() :
        $this->items = NoteItem::where('note_id', $this->id)->latest()->get();
    // switch ($this->sortBy) {
    //     case 'alpha':
    //         $this->notes = Note::with('user')->orderBy('title', 'asc')->get();
    //         break;
    //     case 'alpha-desc':
    //         $this->notes = Note::with('user')->orderBy('title', 'desc')->get();
    //         break;
    //     case 'chrono':
    //         $this->notes = Note::with('user')->get();
    //         break;
    //     case 'chrono-desc':
    //         $this->notes = Note::with('user')->latest()->get();
    //         break;
    // };
};

mount(function () {

    if ($this->id == 0) {

        auth()->user()->notes()->create([
            'title' => 'New Note',
            'user_id' => auth()->user()->id
        ]);

        $this->id = Note::with('user')->latest()->first()->id;
        // dd($this->note);

        session()->flash('id', $this->id );
        return $this->redirect('note/'.$this->id, navigate: true);
        
    } else {
        
        $this->note = Note::where('id', $this->id)->first();

        
    }
        
    if ($this->note) {
        $this->id = $this->note->id;
        $this->title = $this->note->title;
        $this->info = $this->note->info;
        $this->direction = $this->note->direction;
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

$changeDirection = function () {

    $this->direction == 'forward' ?
        $this->direction = 'reverse' :
        $this->direction = 'forward';

    $this->note->update(['direction' => $this->direction]);
    $this->getItems();
};

$toggleChecks = function () {
    $this->checks = ! $this->checks;
};

on(['delete-item' => function () {
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
            <div class="flex justify-between items-center">
                |<button
                    class="py-1"
                >alphabetical</button>
                |<button
                    class="py-1"
                >chronological</button>|
            </div>
            <div class="flex justify-between items-center">
                |<button
                    wire:click="changeDirection"
                    class="py-1"
                >{{ $this->direction }}</button>
                |<button
                    wire:click="toggleChecks"
                    class="py-1"
                >checks</button>
                |<button
                    class="py-1"
                >move checked</button>|
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
            @if($this->direction == 'reverse')
            <x-text-input 
                wire:model.blur="newItem"
                placeholder="new item"
                class="border-none focus:border"
            />
            @endif
        </div>
        <div>
        @foreach($items as $item)
            <livewire:notes.note-item :$item :key="$item->id" :checks="$this->checks"/>
        @endforeach
        </div>
        <div>
            @if($this->direction == 'forward')
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