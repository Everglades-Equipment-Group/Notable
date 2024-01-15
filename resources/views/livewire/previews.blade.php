<?php

// save sorting preference
// sort by shared (owned?)

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

on([
    'sort-notes' => function ($sortBy) {
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
    },
    'sort-records' => function ($sortBy) {
        switch ($sortBy) {
            case 'alpha':
                $this->records = auth()->user()->records()->orderBy('title', 'asc')->get();
                break;
            case 'alpha-desc':
                $this->records = auth()->user()->records()->orderBy('title', 'desc')->get();
                break;
            case 'chrono':
                $this->records = auth()->user()->records()->get();
                break;
            case 'chrono-desc':
                $this->records = auth()->user()->records()->latest()->get();
                break;
        };
    },
    'sort-events' => function ($sortBy) {
        switch ($sortBy) {
            case 'alpha':
                $this->events = auth()->user()->events()->orderBy('title', 'asc')->get();
                break;
            case 'alpha-desc':
                $this->events = auth()->user()->events()->orderBy('title', 'desc')->get();
                break;
            case 'chrono':
                $this->events = auth()->user()->events()->get();
                break;
            case 'chrono-desc':
                $this->events = auth()->user()->events()->latest()->get();
                break;
        };
    },
]);

$test = function () {
    dd();
}

?>

<div class="flex flex-col items-center p-5 h-max bg-inherit">
    <livewire:preview type="note" :data="$this->nullIfEmpty($notes)"/>
    <livewire:preview type="record" :data="$this->nullIfEmpty($records)"/>
    <livewire:preview type="event" :data="$this->nullIfEmpty($events)"/>
</div>