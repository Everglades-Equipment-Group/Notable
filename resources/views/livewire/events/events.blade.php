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
    'showInfo' => false,
    'sortBy' => 'start_date',
    'sortDirection' => 'desc',
    'searchBy' => null,
    'searchResults' => [],
    'total' => '',
]);

$getEvents = function () {
    $this->events = auth()->user()->events()->orderBy($this->sortBy, $this->sortDirection)->orderBy('start_time')->get();
};

// $getRecurrences = function () {
//     $recurringEvents = $this->events->where('recurring', true);
//     $recurringEvents->each(function ($event) {
//         
//     });
// };

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
        'month' => $startOfMonth->month,
        'month_name' => $startOfMonth->format('F'),
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
    if (session('viewDate')) {
        $this->viewDay = session('viewDate');
    } else {
        $this->viewDay = CarbonImmutable::now()->setTimezone('EST')->format('Y-m-d');
    };
    $this->viewEvents = $this->events->where('start_date', '=', $this->viewDay)->sortBy('start_time');
    $this->total = $this->events->count();
});

$newEvent = function () {
    Event::create([
        'user_id' => auth()->user()->id,
        'title' => 'New Event',
        'start_date' => $this->viewDay,
    ]);

    $id = Event::where('user_id', auth()->user()->id)->latest()->first()->id;
    auth()->user()->events()->attach($id, [
        'resource_type' => 'event',
        'can_edit' => true,
        'can_delete' => true,
        'can_share' => true,
    ]);

    session()->flash('id', $id);
    return $this->redirect('event/'.$id);
};

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

$toggleShowInfo = function () {
    $this->showInfo = ! $this->showInfo;
};

$dayClick = function ($day) {
    $this->viewDay = $day;
    $this->viewEvents = $this->events->where('start_date', '=', $day)->sortBy('start_time');
    // dd($day);
    // return $this->redirect('events'.$path);
};

$changeMonth = function ($direction) {
    $this->calendar = $this->buildMonth(
        CarbonImmutable::create($this->calendar['year'], $this->calendar['month'], 1)->addMonths($direction)->month,
        CarbonImmutable::create($this->calendar['year'], $this->calendar['month'], 1)->addMonths($direction)->year
    );
};

$changeYear = function ($direction) {
    $this->calendar = $this->buildMonth(
        CarbonImmutable::create($this->calendar['year'], $this->calendar['month'], 1)->addYears($direction)->month,
        CarbonImmutable::create($this->calendar['year'], $this->calendar['month'], 1)->addYears($direction)->year
    );
};

$resetViewDay = function () {
    $this->calendar = $this->buildMonth(CarbonImmutable::now()->month, CarbonImmutable::now()->year);
    $this->viewDay = CarbonImmutable::now()->setTimezone('EST')->format('Y-m-d');
    $this->viewEvents = $this->events->where('start_date', '=', CarbonImmutable::now()->setTimezone('EST')->format('Y-m-d'))->sortBy('start_time');
};

$search = function () {
    $this->searchResults = Event::where('title', 'like', '%'.$this->searchBy.'%')->get();
};

$sort = function ($by) {
    if ($this->sortBy == $by) {
        $this->sortDirection = $this->sortDirection == 'asc' ? 'desc' : 'asc';
    } else {
        $this->sortBy = $by;
        $this->sortDirection = 'asc';
    };
    $this->getEvents();
};

updated([
    'searchBy' => $search,
]);

?>

<div class="flex flex-col items-center px-3 pb-3 bg-inherit dark:text-gray-300">
    <div x-data="{ open: false }"
        @close.stop="open = false"
        class="sticky top-20 w-full py-4 z-10 bg-inherit lg:w-1/3"
    >
        <div class="relative flex items-center justify-center pb-1">
            <button
                @click="open = ! open"
                class="absolute left-2 fa-solid fa-sliders text-2xl text-blue-400"
                title="options"
            ></button>
            <div class="text-xl tracking-wide">Events</div>
            <button
                wire:click="newEvent"
                class="absolute right-2 fa-regular fa-calendar-plus text-2xl text-blue-400"
                title="new event"
            ></button>
        </div>
<!-- SETTINGS ----------------------------------------------------->
        <div x-show="open"
            @click.outside="open = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="flex flex-col items-center p-2 pb-5 bg-inherit dark:text-gray-300"
            style="display: none;"
        >
            <hr class="w-full border-none h-px bg-gray-500 -mb-6 mt-6">
            <div class="w-fit px-2 text-center text-lg tracking-wider m-2 bg-inherit">Total</div>
            <div class="">{{ $this->total }}</div>
            <hr class="w-full border-none h-px bg-gray-500 -mb-6 mt-6">
            <div class="w-fit px-2 text-center text-lg tracking-wider m-2 bg-inherit">View</div>
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
            <div class="w-fit px-2 text-center text-lg tracking-wider m-2 bg-inherit">Sorting</div>
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
                    wire:click="sort('start_date')"
                    class="py-1"
                >chronological
                    @if($this->sortBy == 'start_date')
                    <span class="fa-arrow-{{ $this->sortDirection == 'asc' ? 'down' : 'up' }}-long fa-solid pl-1 text-blue-400"> 
                    </span>
                    @endif
                </button>
            </div>
            <hr class="w-full border-none h-px bg-gray-500 -mb-6 mt-6">
            <div class="w-fit px-2 text-center text-lg tracking-wider m-2 bg-inherit">Settings</div>
            <div class="w-full flex flex-col justify-between">
                <button
                    wire:click="toggleShowInfo"
                    class="py-1 text-left dark:text-gray-300"
                >{{ $this->showInfo ? 'hide' : 'show'}} events details</button>
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
        @if($this->view == 'calendar')
        <div x-data="{ openMonthPicker: false }"
            @close.stop="openMonthPicker = false"
            class="pt-5"
        >
            <div class="flex justify-between items-center px-6">
                <button wire:click="changeMonth(-1)"
                    class="fa-solid fa-angle-left text-2xl text-blue-400"
                ></button>
                <button wire:click="changeYear(-1)"
                    class="fa-solid fa-angles-left text-2xl text-blue-400"
                ></button>
                <div @click="[
                        openMonthPicker = ! openMonthPicker,
                        $nextTick(() => {
                            $refs[
                                'month-{{ $this->calendar['month'] }}'
                            ].scrollIntoView({ block: 'center' });
                            $refs[
                                'year-{{ $this->calendar['year'] }}'
                            ].scrollIntoView({ block: 'center' });
                        })
                    ]"
                    @click.outside="openMonthPicker = false"
                    class="relative w-3/5 p-2 text-xl text-center tracking-wider cursor-pointer"
                >
                {{ $this->calendar['month_name'] }} {{ $this->calendar['year'] }}
                    <div x-show="openMonthPicker"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        style="display: none;"
                        class="absolute -bottom-2 left-0 w-full h-16 flex justify-center text-center bg-gray-900 overflow-hidden after:absolute after:bottom-0 after:left-0 after:w-full after:h-full after:z-10 after:bg-gradient-to-b after:from-gray-900 after:from-5% after:via-transparent after:via-50% after:to-gray-900 after:to-95% after:pointer-events-none"
                    >
                        <div class="flex flex-col h-full w-auto overflow-y-scroll py-5"
                        >
                            @foreach(range(1, 12) as $month)
                            <button wire:click="changeMonth({{ $month - $this->calendar['month'] }})"
                                wire:key="month-{{ $month }}"
                                x-ref="month-{{ $month }}"
                                class="text-right tracking-wider px-1"
                            >
                                {{ date('F', mktime(0, 0, 0, $month, 1)) }}
                            </button>
                            @endforeach
                        </div>
                        <div class="flex flex-col h-full w-auto overflow-y-scroll py-5">
                            @foreach(range(1900, 2100) as $year)
                            <button wire:click="changeYear({{ $year - $this->calendar['year'] }})"
                                wire:key="year-{{ $year }}"
                                x-ref="year-{{ $year }}"
                                class="text-left tracking-wider px-1"
                            >
                                {{ $year }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                </div>
                <button wire:click="changeYear(1)"
                    class="fa-solid fa-angles-right text-2xl text-blue-400"
                ></button>
                <button wire:click="changeMonth(1)"
                    class="fa-solid fa-angle-right text-2xl text-blue-400"
                ></button>
            </div>
            
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
                <tbody>
                    @foreach($this->calendar['weeks'] as $week)
                    <tr>
                        @foreach($week as $day)
                        <td class="p-1" wire:key="day-{{ $day['path'] }}">
                            <div wire:click="dayClick('{{$day['path']}}')"
                                class="{{ count($day['events']) ? 'bg-blue-400 text-gray-900' : 'border' }}
                                    {{ ! $day['withinMonth'] ? 'text-gray-500 border-gray-700' : '' }}
                                    {{ $day['path'] == $this->viewDay ? 'text-4xl border-none' : '' }}
                                    flex flex-col items-center justify-center h-10 w-10 border-gray-500 rounded-full transition cursor-pointer hover:scale-110 hover:border-white">
                                <div class="flex justify-between items-center">
                                    <div class="">{{ $day['day'] }}</div>
                                </div>
                            </div>
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div x-data="{ openSearch: false }"
                @close.stop="openSearch = false"
                @click.outside="openSearch = false"
            >
                <div class="flex justify-between items-center px-6">
                    <button @click="openSearch = ! openSearch"
                        class="fa-solid fa-magnifying-glass text-2xl text-blue-400"
                    ></button>
                    <div class="p-3 text-xl text-center tracking-wider">
                        {{ date('l d', strtotime($this->viewDay)) }}
                    </div>
                    <button wire:click="resetViewDay"
                        class="fa-solid fa-location-crosshairs text-2xl text-blue-400"
                    ></button>
                </div>
                <div x-show="openSearch"
                    @click.outside="openSearch = false"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="w-full p-2"
                    style="display: none;"
                >
                    <x-text-input
                        wire:model.live="searchBy"
                        placeholder="search events"
                        class="my-1 w-full"
                    />
                    <div class="py-1 overflow-scroll">
                        @foreach($this->searchResults as $event)
                        <div wire:key="search-{{ $event->id }}"
                            wire:click="viewEvent({{ $event->id }})"
                            class="flex justify-between w-full text-left my-2 cursor-pointer"
                        >
                            <span class="w-3/5">{{ $event->title }}</span>
                            <span>{{ $event->start_date }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    @if($this->view == 'list')
    <div class="flex flex-col items-left w-full p-2 lg:w-1/3">
        @foreach($this->events->groupBy('start_date') as $eventsThisDay)
        <div x-data="{ openDay: true }"
            @close.stop="openDay = true"
            wire:key="{{ $eventsThisDay[0]->start_date }}"
        >
            <div @click="openDay = ! openDay"
                class="text-xl text-center tracking-wider bg-blue-400 rounded-full my-3 cursor-row-resize dark:text-gray-900">
                {{ date('D j M y', strtotime($eventsThisDay[0]->start_date)) }}
            </div>
            @foreach($eventsThisDay as $event)
            <div wire:key="event-{{ $event->id }}"
                wire:click="viewEvent({{ $event->id }})"
                x-show="openDay"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                style="display: none;"
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
        </div>
        @endforeach
    </div>
    @endif
    @if($this->view == 'calendar')
    <div class="p-2 w-full lg:w-1/3">
        @foreach($this->viewEvents as $event)
            <div wire:key="{{ $event->id }}"
                wire:click="viewEvent({{ $event->id }})"
                class="flex flex-col w-full justify-between items-center text-left my-2"
            >
                <div class="flex w-full justify-between">
                    <div class="pr-4">
                        {{ $event->title }}
                        @if($event->users->count() > 1)
                        <span class="pl-1 text-red-500">
                            @if($event->user_id == auth()->user()->id)
                                &<span class="fa-solid fa-angle-right text-blue-400"></span>
                            @else
                                <span class="fa-solid fa-angle-left text-blue-400"></span>&
                            @endif
                        </span>
                        @endif
                    </div>
                    <div class="">
                        @if(! $event->all_day)
                        <div class="flex">
                            @if($event->start_time)
                            <div>{{ date('H:i', strtotime($event->start_time)) }}</div>
                            @endif
                            @if($event->start_time && $event->end_time)
                            <span class="px-2"> - </span>
                            @endif
                            @if($event->end_time)
                            <div>{{ date('H:i', strtotime($event->end_time)) }}</div>
                            @endif
                        </div>
                        @endif
                        @if($event->end_date > $this->viewDay)
                        <div>
                            <span class="fa-solid fa-arrow-right-long text-sm text-blue-400 pr-1"></span>
                            {{ $event->end_date }}
                        </div>
                        @endif
                    </div>
                </div>
                @if($event->info && $this->showInfo)
                <div class="w-full py-2">
                    {{ $event->info }}
                </div>
                @endif
            </div>
            <hr class="border-none h-px bg-gray-700 w-full">
        @endforeach
    </div>
    @endif
    @if($this->view == 'schedule')
    <div class="lg:w-1/3">
        <livewire:events.schedule :events="$this->events" />
    </div>
    @endif
</div>
