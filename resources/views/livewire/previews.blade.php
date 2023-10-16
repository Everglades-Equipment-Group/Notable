<?php

use function Livewire\Volt\{on, state};
use App\Models\Note;
use App\Models\Record;
use App\Models\Event;
 
state([
    'notes' => fn () => Note::with('user')->latest()->get(),
    // 'records' => fn () => Record::with('user')->latest()->get(),
    // 'events' => fn () => Event::with('user')->latest()->get(),
]);

on(['sort-notes' => function ($sortBy) {
    switch ($sortBy) {
        case 'alpha':
            $this->notes = Note::with('user')->orderBy('title', 'asc')->get();
            break;
        case 'alpha-desc':
            $this->notes = Note::with('user')->orderBy('title', 'desc')->get();
            break;
        case 'chrono':
            $this->notes = Note::with('user')->get();
            break;
        case 'chrono-desc':
            $this->notes = Note::with('user')->latest()->get();
            break;
    };
}]);

$test = function () {
    dd();
}

?>

<div>
    <div class="flex flex-col p-12 h-screen">
        <livewire:preview type="note" :data="$notes"/>
        <livewire:preview type="record" />
        <livewire:preview type="event" />
    </div>
    <button wire:click="test" class="dark:text-gray-300">test</button>
</div>
