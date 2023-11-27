<?php

// save sorting preference
// sort by shared (owned?)
// notifcations

use function Livewire\Volt\{booted, mount, on, state};
use App\Models\Note;
use App\Models\Record;
use App\Models\Event;
 
state([
    'notes' => '',
    'records' => '',
    'events' => '',
]);

booted(function () {
    $this->notes = auth()->user()->notes()->latest()->get();
    $this->records = auth()->user()->records()->latest()->get();
    $this->events = auth()->user()->events()->latest()->get();
});

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

<div class="flex flex-col p-5 h-max">
    <livewire:preview type="note" :data="$this->nullIfEmpty($notes)"/>
    <livewire:preview type="record" :data="$this->nullIfEmpty($records)"/>
    <livewire:preview type="event" :data="$this->nullIfEmpty($events)"/>
</div>