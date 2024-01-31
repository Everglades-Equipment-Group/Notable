<?php

// save sorting preference
// sort by shared (owned?)

use function Livewire\Volt\{booted, mount, on, state};
use App\Models\Note;
use App\Models\Record;
use App\Models\Event;
 
state([
    'user' => auth()->user(),
    'notes' => '',
    'records' => '',
    'events' => '',
]);

mount(function () {
    $this->notes = $this->user->notes()->latest()->get();
    $this->records = $this->user->records()->latest()->get();
    $this->events = $this->user->events()->latest()->get();
});

$nullIfEmpty = function ($data) {
    return $data->isEmpty() ? null : $data;
};

on([
    'sort-notes' => function ($sortBy, $sortDirection) {
        $this->notes = $this->user->notes()->orderBy($sortBy, $sortDirection)->get();
    },
    'sort-records' => function ($sortBy, $sortDirection) {
        $this->records = $this->user->records()->orderBy($sortBy, $sortDirection)->get();
    },
    'sort-events' => function ($sortBy, $sortDirection) {
        $this->events = $this->user->events()->orderBy($sortBy, $sortDirection)->get();
    },
]);

$test = function () {
    dd();
}

?>

<div class="flex flex-col items-center p-5 h-max bg-inherit">
    <livewire:preview type="note" :data="$this->nullIfEmpty($notes)" wire:key="{{$this->notes->first()->id}}"/>
    <livewire:preview type="record" :data="$this->nullIfEmpty($records)" wire:key="{{$this->records->first()->id}}"/>
    <livewire:preview type="event" :data="$this->nullIfEmpty($events)" wire:key="{{$this->events->first()->id}}"/>
</div>