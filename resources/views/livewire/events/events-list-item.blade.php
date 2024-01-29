<?php

use function Livewire\Volt\{mount, rules, updated, state};
use App\Models\Event;
use Carbon\CarbonImmutable;

state([
    'user' => auth()->user(),
    'eventsThisDay' => [],
    'viewDay' => '',
    'showDay' => true,
]);

$viewEvent = function ($id) {
    session()->flash('id', $id);
    return $this->redirect('event/'.$id);
};

$toggleShowDay = function () {
    $this->showDay = !$this->showDay;
};

?>

<div>
    <div wire:click="toggleShowDay"
        class="text-xl text-center tracking-wider bg-blue-400 rounded-full my-3 cursor-row-resize dark:text-gray-900">
        {{ date('D j M y', strtotime($this->eventsThisDay[0]->start_date)) }}
    </div>
    @if($this->showDay)
        @foreach($eventsThisDay as $event)
        <div wire:key="event-{{ $event->id }}"
            wire:click="viewEvent({{ $event->id }})"
            class="flex justify-between w-full text-left my-1 cursor-pointer"
        >
            <div class="flex w-3/5">
                <div class="">{{ $event->title }}</div>
                @if($event->users->count() > 1)
                <div class="flex pl-2 text-red-500">
                    @if($event->user_id == auth()->user()->id)
                        <span class="pt-px">&</span>
                        <span class="fa-solid fa-angle-right text-blue-400 pt-1"></span>
                    @else
                        <span class="fa-solid fa-angle-left text-blue-400"></span>
                        <span>&</span>
                    @endif
                </div>
                @endif
            </div>
            <div class="">
                <div class="flex">
                    @if($event->start_time)
                    <div class="text-right w-full">{{ date('H:i', strtotime($event->start_time)) }}</div>
                    @endif
                    @if($event->end_time)
                    <span class="px-2">-</span>
                    <div>{{ date('H:i', strtotime($event->end_time)) }}</div>
                    @endif
                </div>
                @if($event->end_date > $this->viewDay)
                    <span class="fa-solid fa-arrow-right-long text-sm text-blue-400 pr-1"></span>
                    {{ $event->end_date }}
                @endif
            </div>
        </div>
        @if(!$loop->last)
        <hr class="border-none h-px bg-gray-700 w-full">
        @endif
        @endforeach
    @endif
</div>
