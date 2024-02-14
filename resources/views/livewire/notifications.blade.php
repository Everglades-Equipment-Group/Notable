<?php

use function Livewire\Volt\{on, dehydrate, mount, layout, state};
use App\Models\Notification;

layout('layouts.app');

state([
    'notifications' => []
]);

$getNotifications = function () {
    $this->notifications = auth()->user()->notifications()->latest()->get();
};

mount(function () {
    $this->getNotifications();
});

dehydrate(function () {
    $this->notifications->each(function ($notification) {
        $notification->read = true;
        $notification->save();
    });
});

$clear = function () {
    auth()->user()->notifications()->delete();
    $this->getNotifications();
};

on(['delete-notifications' => $clear]);

?>

<div class="relative flex flex-col items-center p-3 dark:text-gray-300">
    <div class="lg:w-1/2">
        <div class="text-center text-xl tracking-wide">Notifications</div>
        <button
            wire:click="$dispatch('openModal', { component: 'confirm-delete', arguments: { type: 'notifications', message: 'Delete all notifications?' }})"
            class="absolute top-1 right-5 p-1 px-3 mt-2 border border-red-500 rounded-lg text-red-500"
        >clear</button>
        <div class="py-3">
            @foreach($this->notifications as $notification)
                <livewire:notification :$notification wire:key="{{ $notifications->pluck('id')->join('-') }}"/>
            @endforeach
        </div>
    </div>
</div>
