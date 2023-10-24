<?php

// sort by shared (owned?)

use function Livewire\Volt\{on, state};
use App\Models\Note;
use App\Models\Record;
use App\Models\Event;
 
state([
    'notes' => fn () => auth()->user()->notes()->latest()->get(),
    'records' => fn () => auth()->user()->records()->latest()->get(),
    // 'events' => fn () => Event::with('user')->latest()->get(),
]);

$nullIfEmpty = function ($data) {
    return $data->isEmpty() ? null : $data;
};

on(['sort-notes' => function ($sortBy) {
    switch ($sortBy) {
        case 'alpha':
            $this->notes = auth()->user()->notes()->orderBy('title', 'asc')->get();
            break;
        case 'alpha-desc':
            $this->notes = auth()->user()->notes()->orderBy('title', 'desc')->get();
            break;
        case 'chrono':
            $this->notes = auth()->user()->notes()->get();
            break;
        case 'chrono-desc':
            $this->notes = auth()->user()->notes()->latest()->get();
            break;
    };
}]);

$test = function () {
    dd();
}

?>

<div>
    <div class="flex flex-col p-12 h-screen">
        <livewire:preview type="note" :data="$this->nullIfEmpty($notes)"/>
        <livewire:preview type="record" :data="$this->nullIfEmpty($records)"/>
        <livewire:preview type="event" />
    </div>
    <button wire:click="test" class="dark:text-gray-300">test</button>
</div>
