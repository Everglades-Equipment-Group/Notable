<?php

use function Livewire\Volt\{on, booted, updated, mount, layout, rules, messages, state};
use App\Models\Event;
use App\Models\User;

layout('layouts.app');

state([
    'event' => '',
    'id' => '',
    'title' => '',
    'info' => '',
    'startDate' => '',
    'endDate' => '',
    'startTime' => '',
    'endTime' => '',
    'allDay' => '',
    'recurring' => '',
    'frequency' => '',
    // 'showDate' => '',
    // 'showTime' => '',
    'isOwner' => '',
    'isShared' => '',
    'creator' => '',
    'shareWith' => '',
    'can_edit' => '',
    'can_delete' => '',
    'can_share' => '',
]);

rules([
    'shareWith' => 'in:'. User::pluck('name')->implode(','),
])->messages([
    'shareWith.in' => 'User not found.',
]);

mount(function () {
    if ($this->id == 0) {

        Event::create([
            'user_id' => auth()->user()->id,
            'title' => 'New Event',
            'start_date' => now(),
        ]);

        $this->id = Event::where('user_id', auth()->user()->id)->latest()->first()->id;
        auth()->user()->events()->attach($this->id, [
            'resource_type' => 'event',
            'can_edit' => true,
            'can_delete' => true,
            'can_share' => true,
        ]);

        session()->flash('id', $this->id);
        return $this->redirect('event/' . $this->id);

    } elseif (Event::find($this->id) != null) {

        $this->event = auth()->user()->events()->where('resource_id', $this->id)->first();
    } else {

        return $this->redirect('/dashboard');
    }

    $this->title = $this->event->title;
    $this->info = $this->event->info;
    $this->startDate = $this->event->start_date;
    $this->endDate = $this->event->end_date;
    $this->startTime = $this->event->start_time;
    $this->endTime = $this->event->end_time;
    $this->allDay = $this->event->all_day;
    $this->recurring = $this->event->recurring;
    $this->frequency = $this->event->frequency;
    $this->can_edit = $this->event->pivot->can_edit;
    $this->can_delete = $this->event->pivot->can_delete;
    $this->can_share = $this->event->pivot->can_share;
    $this->isOwner = $this->event->user_id == auth()->user()->id;
    $this->isShared = $this->event->users()->count() > 1;

});

$notify = function ($event) {
    if ($this->isShared) {
        foreach($this->event->users()->where('user_id', '!=', auth()->user()->id)->get() as $user) {
            $user->notifications()->create([
                'from_id' => auth()->user()->id,
                'event' => $event,
                'resource_type' => 'event',
                'resource_id' => $this->event->id,
            ]);
        };
    };
};

$viewAll = function () {
    return $this->redirect('/events');
};

$viewThisDay = function () {
    session()->flash('viewDate', $this->startDate);
    return $this->redirect('/events');
};

$newEvent = function () {
    return $this->redirect('/event/0');
};

$destroy = function () {

    $this->notify('deleted event');

    $this->event->delete();

    return $this->redirect('/dashboard');
};

$leaveEvent = function () {
    $this->event->users()->detach(auth()->user()->id);

    $this->notify('left event');

    return $this->redirect('/dashboard');
};

$share = function () {
    $this->validate(['shareWith' => 'in:'. User::pluck('name')->implode(',')]);
    $this->event->users()->attach(User::where('name', $this->shareWith)->first()->id, ['resource_type' => 'event']);
    
    $this->notify('added '. $this->shareWith .' to event');

    $this->shareWith = '';
};

$unshare = function ($userId) {
    $this->notify('removed '. User::find($userId)->name .' from event');

    $this->event->users()->detach($userId);
};

$toggleAccess = function ($user, $access, $value) {
    $this->event->users()->updateExistingPivot($user, [$access => ! $value]);
};

$toggleRecurring = function ($every) {
    if ($this->recurring == $every) $every = null;
    if ($every) $this->notify('enabled '. $every .' recursion for event');
    if (!$every) $this->notify('disabled '. $this->recurring .' recursion for event');

    $this->recurring = $every;
    $this->event->update(['recurring' => $this->recurring]);
};

$toggleAllDay = function () {
    $this->allDay = ! $this->allDay;
    // $this->notify('toggled all day event');
    $this->event->update(['all_day' => $this->allDay]);
};

on([
    'delete-event' => $destroy,
    'leave-event' => $leaveEvent,
]);

updated([
    'title' => function () {
        $this->notify('renamed event: '. $this->event->title .' to');
        $this->event->update(['title' => $this->title]);
    },
    'info' => function () {
        $this->notify('updated info on event');
        $this->event->update(['info' => $this->info]);
    },
    'startDate' => function () {
        $this->notify('updated start date of event');
        $this->event->update(['start_date' => $this->startDate]);
    },
    'endDate' => function () {
        $this->notify('updated end date of event');
        $this->event->update(['end_date' => $this->endDate]);
    },
    'startTime' => function () {
        $this->notify('updated start time of event');
        $this->event->update(['start_time' => $this->startTime]);
    },
    'endTime' => function () {
        $this->notify('updated end time of event');
        $this->event->update(['end_time' => $this->endTime]);
    },
    'frequency' => function () {
        $this->notify('updated frequency of event');
        $this->event->update(['frequency' => $this->frequency]);
    },
]);

?>
<div class="flex flex-col items-center px-3 dark:text-gray-300 bg-inherit">
    <div x-data="{ open: false }"
        @click.outside="open = false"
        @close.stop="open = false"
        class="sticky top-16 w-full py-4 bg-inherit z-10 lg:w-1/3"
    >
        <div class="flex items-center justify-between w-full py-5 px-20">
            <button
                wire:click="viewAll"
                class="fa-regular fa-calendar-days text-2xl text-blue-400"
                title="all events"
            >
            </button>
            <button
                wire:click="viewThisDay"
                class="fa-solid fa-calendar-day text-2xl text-blue-400"
                title="events this day"
            >
            </button>
            <button
                wire:click="newEvent"
                class="fa-regular fa-calendar-plus text-2xl text-blue-400"
                title="new event"
            >
            </button>
        </div>
        <div class="flex justify-between items-center px-2 mb-3">
            <button
                @click="open = ! open"
                class="fa-solid fa-sliders text-2xl text-blue-400"
                title="options"
            ></button>
            <x-text-input 
                wire:model.change="title"
                @focus="$event.target.select()"
                placeholder="Title"
                class="text-2xl border-none text-center focus:border"
                disabled="{{ ! $this->can_edit }}"
            />
            @if($this->isOwner)
            <button
                wire:click="$dispatch('openModal', { component: 'confirm-delete', arguments: { id: {{ $this->event->id }}, type: 'event', message: 'Delete this event?' }})"
                class="fa-regular fa-trash-can text-2xl text-red-500"
                title="delete event"
            ></button>
            @else
            <button
                wire:click="$dispatch('openModal', { component: 'confirm-delete', arguments: { id: {{ $this->event->id }}, verb: 'leave', type: 'event', message: 'Leave this event?' }})"
                class="fa-solid fa-user-xmark text-2xl text-red-500"
                title="leave event"
            ></button>
            @endif
        </div>
<!-- SETTINGS ------------------------------------------------------------------------->
        <div x-show="open"
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
            <div class="w-fit px-2 text-center text-lg tracking-wider m-2 bg-inherit">Sharing</div>
            <div class="w-full flex flex-col justify-between">
                @if($this->can_share)
                <div>
                    <button
                        wire:click="share"
                        class="py-1 text-left"
                    >add </button>
                    <x-text-input
                        wire:model.blur="shareWith"
                        wire:keydown.enter="share"
                        placeholder="by name"
                        class="border-none focus:border"
                    />
                    @error('shareWith')
                    <span class="text-red-500 pl-2">{{ $message }}</span>
                    @enderror
                </div>
                @endif
                <div class="text-lg text-center tracking-wider">
                    With:
                </div>
                <div class="py-3">
                    @foreach($this->event->users as $user)
                        @if($user->id != auth()->user()->id)
                        <div x-data="{ openAccessPannel: false }"
                            @close.stop="openAccessPannel = false"
                            @click.outside="openAccessPannel = false"
                            wire:key="user-{{ $user->id }}"
                            class="py-1"
                        >
                            <div class="flex justify-between items-center">
                                <div>{{ $user->name }}</div>
                                @if($user->id == $this->event->user_id)
                                <div>owner</div>
                                @endif
                                @if($this->isOwner)
                                <div class="flex">
                                    <button
                                        @click="openAccessPannel = ! openAccessPannel"
                                        class="flex justify-between items-center border border-gray-700 rounded-full pl-1"
                                        title="access panel"
                                    
                                        @if($user->pivot->can_edit)
                                        <span class="fa-solid fa-scissors text-sm text-yellow-600 p-1" title="can edit"></span>@endif
                                        @if($user->pivot->can_delete)
                                        <span class="fa-solid fa-trash text-sm text-red-600 p-1" title="can delete"></span>@endif
                                        @if($user->pivot->can_share)
                                        <span class="fa-solid fa-user-plus text-sm text-blue-500 p-1" title="can share"></span>@endif
                                        <span class="fa-solid fa-key text-blue-400 p-1 ml-1 border border-gray-700 rounded-full" title="access pannel"></span>
                                    </button>
                                    <button
                                        wire:click="unshare({{ $user->id }})"
                                        class="fa-regular fa-trash-can ml-6 hover:bg-red-500 dark:text-red-500 dark:hover:text-gray-900 transition"
                                        title="unshare"
                                    ></button>
                                </div>
                                @endif
                            </div>
                            <div x-show="openAccessPannel"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="flex flex-col items-center py-3"
                                style="display: none;"
                            >
                                <div class="text-lg text-center tracking-wider">Can:</div>
                                <div class="py-2">
                                @foreach($user->pivot->toArray() as $key => $value)
                                    @if($key == 'can_edit' && $value || $key == 'can_delete' && $value || $key == 'can_share' && $value)
                                        <button
                                            wire:click="toggleAccess({{ $user->id }}, '{{ $key }}', {{ $value }})"
                                            wire:key="access-{{ $key }}"
                                            class="border border-gray-700 rounded-lg px-2 py-1 m-1"
                                            title="toggle {{ $key }}"
                                        >{{ str_replace('can_', '', $key) }}</button>
                                    @endif
                                @endforeach
                                </div>
                                <div class="text-lg text-center tracking-wider">Can not:</div>
                                <div class="py-2">
                                @foreach($user->pivot->toArray() as $key => $value)
                                    @if($key == 'can_edit' && !$value || $key == 'can_delete' && !$value || $key == 'can_share' && !$value)
                                        <button
                                            wire:click="toggleAccess({{ $user->id }}, '{{ $key }}', {{ $value }})"
                                            wire:key="access-{{ $key }}"
                                            class="border border-gray-700 rounded-lg px-2 py-1 m-1"
                                            title="toggle {{ $key }}"
                                        >{{ str_replace('can_', '', $key) }}</button>
                                    @endif
                                @endforeach
                                </div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
<!-- END SETTINGS --------------------------------------------------------------------->
        <textarea
            wire:model.change="info"
            placeholder="details..."
            class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:border-gray-700 bg-inherit dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600"
            {{ $this->can_edit ? '' : 'readonly' }}
        ></textarea>
    </div>
    <div x-data="{ openEvery: false }"
        @click.outside="openEvery = false"
        @close.stop="openEvery = false"
        class="w-full lg:w-1/3"
    >
        <div class="flex w-full justify-evenly">
            <div class="flex flex-col items-center justify-between">
                <div class="tracking-wide text-xl pb-1">Start</div>
                <x-date-input
                    wire:model.change="startDate"
                    disabled="{{ !$this->can_edit }}"
                />
                <x-time-input
                    wire:model.change="startTime"
                    disabled="{{ !$this->can_edit }}"
                />
                <x-text-button
                    @click="openEvery = ! openEvery"
                    class="{{ $this->recurring ? 'bg-blue-400 text-gray-300 dark:text-gray-900' : '' }}"
                >
                    every
                </x-text-button>
            </div>
            <div class="flex flex-col items-center justify-between">
                <div class="tracking-wide text-xl pb-1">End</div>
                <x-date-input
                    wire:model.change="endDate"
                    disabled="{{ !$this->can_edit }}"
                />
                <x-time-input
                    wire:model.change="endTime"
                    disabled="{{ !$this->can_edit }}"
                />
                <x-text-button
                    wire:click="toggleAllDay"
                    class="{{ $this->allDay ? 'bg-blue-400 text-gray-300 dark:text-gray-900' : '' }}"
                >
                    all day
                </x-text-button>
            </div>
        </div>
        <div x-show="openEvery"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="flex items-center rounded-lg my-3 border border-blue-400 overflow-hidden bg-inherit"
            style="display: none;"
        >
            <x-text-input
                wire:model.live="frequency"
                class="w-12 border-none text-center text-blue-400"
            />
            @foreach(['minute', 'hour', 'day', 'week', 'month'] as $every)
                <button
                    wire:click="toggleRecurring('{{ $every }}')"
                    wire:key="every-{{ $every }}"
                    class="{{ $this->recurring == $every ? 'bg-blue-400 text-gray-300 dark:text-gray-900' : '' }} border-l border-blue-400 text-blue-400 py-2 px-3"
                >
                    {{ $every }}
                </button>
            @endforeach
        </div>
    </div>
</div>