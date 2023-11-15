<?php

use function Livewire\Volt\{on, booted, updated, mount, layout, rules, state};
use App\Models\Event;
use Carbon\CarbonImmutable;

layout('layouts.app');

state([
    'user' => auth()->user(),
    'calendar' => '',
    'events' => '',
    'viewDay' => '',
    'viewEvents' => [],
    'view' => 'calendar',
    'showDeletes' => '',
    'sortBy' => 'start_date',
    'sortDirection' => 'asc',
    'newEvent' => '',
]);

$getEvents = function () {
    // $this->events = auth()->user()->events()->orderBy($this->sortBy, $this->sortDirection)->latest()->get();
    $this->events = auth()->user()->events()->orderBy($this->sortBy)->get();
};

$buildMonth = function ($month, $year) {
    $startOfMonth = CarbonImmutable::create($year, $month, 1);
    $startOfMonth = $startOfMonth->startOfMonth();
    $endOfMonth = $startOfMonth->endOfMonth();
    // $daysInMonth = $startOfMonth->daysInMonth;
    // $daysInMonth = $startOfMonth->diffInDays($endOfMonth);
    $startOfWeek = $startOfMonth->startOfWeek(CarbonImmutable::SUNDAY);
    $endOfWeek = $endOfMonth->endOfWeek(CarbonImmutable::SATURDAY);

    return [
        'year' => $startOfMonth->year,
        'month' => $startOfMonth->format('F'),
        'weeks' => collect($startOfWeek->toPeriod($endOfWeek)->toArray()) 
            ->map(fn ($date) => [
                'path' => $date->format('Y-m-d'),
                'day' => $date->day,
                'withinMonth' => $date->between($startOfMonth, $endOfMonth),
                'events' => $this->events->where('start_date', '=', $date->format('Y-m-d'))
            ])->chunk(7), 
    ];
};

mount(function () {
    $this->getEvents();
    $this->calendar = $this->buildMonth(CarbonImmutable::now()->month, CarbonImmutable::now()->year);
    $this->viewDay = CarbonImmutable::now()->format('Y-m-d');
    $this->viewEvents = $this->events->where('start_date', '=', CarbonImmutable::now()->format('Y-m-d'));
});

$viewEvent = function ($id) {
    return $this->redirect('event/'.$id);
};

$toggleView = function ($view) {
    $this->view = $view;
    $this->getEvents();
};

$toggleDeletes = function () {
    $this->showDeletes = ! $this->showDeletes;
    $this->getEvents();
};

$dayClick = function ($day) {
    $this->viewDay = $day;
    $this->viewEvents = $this->events->where('start_date', '=', $day);
    // dd($day);
    // return $this->redirect('events'.$path);
};

?>

<div class="flex flex-col items-center p-3 dark:text-gray-300">
    <div x-data="{ open: false }"
        @click.outside="open = false"
        @close.stop="open = false"
        class="sticky top-16 w-full py-4 dark:bg-gray-900"
    >
        <div class="relative flex items-center justify-center pb-1">
            <button
                @click="open = ! open"
                class="absolute left-2 fa-solid fa-sliders text-2xl text-blue-400"
                title="options"
            ></button>
            <div class="text-xl tracking-wide">Events</div>
        </div>
<!-- SETTINGS ----------------------------------------------------->
        <div x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="flex flex-col items-center p-2 pb-5 dark:bg-gray-900 dark:text-gray-300"
            style="display: none;"
        >
            <hr class="w-full border-none h-px bg-gray-500 -mb-6 mt-6">
            <div class="w-fit px-2 text-center text-lg tracking-wider m-2 dark:bg-gray-900">View</div>
            <div class="w-full flex justify-between items-center">
                <button
                    wire:click="toggleView('calendar')"
                    class="{{ $this->view == 'calendar' ? 'text-blue-400' : '' }} py-1"
                >calendar</button>
                <span>|</span>
                <button
                    wire:click="toggleView('list')"
                    class="{{ $this->view == 'list' ? 'text-blue-400' : '' }} py-1"
                >list</button>
                <span>|</span>
                <button
                    wire:click="toggleView('schedule')"
                    class="{{ $this->view == 'schedule' ? 'text-blue-400' : '' }} py-1"
                >schedule</button>
            </div>
            <hr class="w-full border-none h-px bg-gray-500 -mb-6 mt-6">
            <div class="w-fit px-2 text-center text-lg tracking-wider m-2 dark:bg-gray-900">Sorting</div>
            <div class="w-full flex justify-between items-center">
                <button
                    wire:click="sort('title')"
                    class="py-1"
                >alphabetical
                    @if($this->sortBy == 'title')
                    <span class="fa-arrow-{{ $this->sortDirection == 'asc' ? 'down' : 'up' }}-long fa-solid pl-1 text-blue-400">
                    </span>
                    @endif
                </button>
                <span class="text-gray-500">|</span>
                <button
                    wire:click="sort('created_at')"
                    class="py-1"
                >chronological
                    @if($this->sortBy == 'start_date')
                    <span class="fa-arrow-{{ $this->sortDirection == 'asc' ? 'down' : 'up' }}-long fa-solid pl-1 text-blue-400"> 
                    </span>
                    @endif
                </button>
                <span class="text-gray-500">|</span>
                <button
                    wire:click="sort('position')"
                    class="py-1"
                >draggable
                    @if($this->sortBy == 'position')
                        <span class="fa-arrow-{{ $this->sortDirection == 'asc' ? 'down' : 'up' }}-long fa-solid pl-1 text-blue-400">
                        </span>
                    @endif
                </button>
            </div>
            <hr class="w-full border-none h-px bg-gray-500 -mb-6 mt-6">
            <div class="w-fit px-2 text-center text-lg tracking-wider m-2 dark:bg-gray-900">Settings</div>
            <div class="w-full flex flex-col justify-between">
                <button
                    wire:click="toggleDeletes"
                    class="py-1 text-left text-red-500"
                >{{ $this->showDeletes ? 'hide' : 'show'}} delete buttons</button>
                <button
                    wire:click="$dispatch('openModal', { component: 'confirm-delete', arguments: { id: {{ '' }}, type: 'events', message: 'Delete all events?' }})"
                    class="py-1 text-left text-red-500"
                >clear all events</button>
            </div>
        </div>
<!-- END SETTINGS ------------------------------------------------->
    </div>
    <div x-data="{ open: false }"
        @click.outside="open = false"
        @close.stop="open = false"
        class="w-full"
    >
        <x-text-input
            @click="open = true"
            wire:model.change="newEvent"
            placeholder="new event"
            class="my-1 w-full"
        />
        <div x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="flex flex-col pb-2"
            style="display: none;"
        >
            <textarea
                placeholder="details..."
                rows="1"
                class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600"
            ></textarea>
            <div class="flex items-center justify between">
                <div class="w-1/6 p-2">start</div>
                <x-text-input type="date" class="my-1 grow"/>
            </div>
            <div class="flex items-center justify between">
                <div class="w-1/6 p-2">end</div>
                <x-text-input type="date" class="my-1 grow"/>
            </div>
            <div class="flex justify-between py-1">
                <button class="border border-gray-700 rounded-lg p-2">all day</button>
                <button class="border border-gray-700 rounded-lg p-2">repeat</button>
            </div>
        </div>
    </div>
    @if($this->view == 'list')
    <div class="flex flex-col items-left w-full px-2">
        @foreach($this->events as $event)
            <div wire:key="{{ $event->id }}"
                wire:click="viewEvent({{ $event->id }})"
                class="flex w-full text-left my-1"
            >
                <div>{{ $event->title }}</div>
                <span class="px-3">-</span>
                <div>{{ $event->start_date }}</div>
            </div>
        @endforeach
    </div>
    @endif
    @if($this->view == 'calendar')
    <div>
        <div class="p-2 text-xl text-center tracking-wider">
            {{ $this->calendar['month'] }} {{ $this->calendar['year'] }}
        </div>
        <div>
            <table class="m-auto text-center month">
                <thead>
                    <tr class="tracking-wider">
                        <th class="py-2">Sun</th>
                        <th class="py-2">Mon</th>
                        <th class="py-2">Tue</th>
                        <th class="py-2">Wed</th>
                        <th class="py-2">Thu</th>
                        <th class="py-2">Fri</th>
                        <th class="py-2">Sat</th>
                    </tr>
                </thead>
                <tbody class="">
                    @foreach($this->calendar['weeks'] as $week)
                    <tr>
                        @foreach($week as $day)
                        <td class="p-1" wire:key="day-{{ $day['path'] }}">
                            <div wire:click="dayClick('{{$day['path']}}')"
                                class="{{ count($day['events']) ? 'bg-blue-400 text-gray-900' : 'border' }}
                                    {{ ! $day['withinMonth'] ? 'text-gray-500 border-gray-700' : '' }}
                                    {{ $day['path'] == $this->viewDay ? 'text-4xl border-none' : '' }}
                                    flex flex-col items-center justify-center h-10 w-10 border-gray-500 rounded-full transition">
                                <div class="flex justify-between items-center">
                                    <div class="">{{ $day['day'] }}</div>
                                    <!-- <div class="p-1">{{ $day['events'] }}</div> -->
                                </div>
                            </div>
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-2 mt-1">
            <div class="text-xl text-center tracking-wider">
                {{ date('l d', strtotime($this->viewDay)) }}
            </div>
            @foreach($this->viewEvents as $event)
                <div wire:key="{{ $event->id }}"
                    wire:click="viewEvent({{ $event->id }})"
                    class="flex w-full justify-between items-center text-left my-2"
                >
                    <div>{{ $event->title }}</div>
                    <div>{{ $event->start_time }}</div>
                </div>
            @endforeach
        </div>
    </div>
    @endif
    @if($this->view == 'schedule')
    <div>
        <livewire:events.schedule :events="$this->events" />
    </div>
    @endif
</div>
