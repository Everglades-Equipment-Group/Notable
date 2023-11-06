<?php

use function Livewire\Volt\{on, booted, updated, mount, layout, rules, state};
use App\Models\Event;

layout('layouts.app');

state([
    'event' => '',
    'id' => '',
    'title' => '',
    'info' => '',
    'start' => '',
    'end' => '',
    'allDay' => '',
    'recurring' => '',
    // 'showDate' => '',
    // 'showTime' => '',
    'creator' => '',
    'can_edit' => '',
    'can_delete' => '',
    'can_share' => '',
]);

mount(function () {
    if ($this->id == 0) {

        Event::create([
            'user_id' => auth()->user()->id,
            'title' => 'New Event',
            'start' => now(),
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
    $this->start = $this->event->start;
    $this->end = $this->event->end;
    $this->allDay = $this->event->allDay;
    $this->recurring = $this->event->recurring;
    $this->can_edit = $this->event->pivot->can_edit;
    $this->can_delete = $this->event->pivot->can_delete;
    $this->can_share = $this->event->pivot->can_share;

});

?>

<div>
    <div class="flex flex-col p-12 h-screen">
        <div class="flex justify-between items-center">
            <div class="text-2xl font-bold tracking-wide">Event</div>
            <div class="flex items-center">
                <button
                    wire:click="$dispatch('openModal', { component: 'confirm-delete', arguments: { type: 'event', message: 'Delete event?' }})"
                    class="p-1 px-3 border border-red-500 rounded-lg text-red-500"
                >delete</button>
                <button
                    wire:click="$dispatch('openModal', { component: 'share', arguments: { type: 'event', id: {{ $this->id }} }})"
                    class="p-1 px-3 ml-2 border border-blue-500 rounded-lg text-blue-500"
                >share</button>
            </div>
        </div>
        <div class="flex flex-col mt-3">
            <x-text-input
                wire:model="title"
                placeholder="title"
                class="text-2xl font-bold tracking-wide"
            />
            <x-text-input
                wire:model="info"
                placeholder="info"
                class="mt-2"
            />
            <div class="flex items-center mt-2">
                <x-text-input
                    wire:model="start"
                    placeholder="start"
                    class="w-1/2"
                    type="datetime-local"
                />
                <x-text-input
                    wire:model="end"
                    placeholder="end"
                    class="w-1/2 ml-2"
                    type="datetime-local"
                />
            </div>
            <div class="flex items-center mt-2">
                <x-text-input
                    wire:model="allDay"
                    placeholder="all day"
                    class="w-1/2"
                    type="checkbox"
                />
                <x-text-input
                    wire:model="recurring"
                    placeholder="recurring"
                    class="w-1/2 ml-2"
                    type="checkbox"
                />
</div>
