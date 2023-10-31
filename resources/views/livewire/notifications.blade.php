<?php

use function Livewire\Volt\{dehydrate, mount, layout, state};
use App\Models\Notification;

layout('layouts.app');

state([
    'notifications' => []
]);

mount(function () {
    $this->notifications = auth()->user()->notifications()->latest()->get();
});

dehydrate(function () {
    $this->notifications->each(function ($notification) {
        $notification->read = true;
        $notification->save();
    });
});

?>

<div class="p-3 dark:text-gray-300">
    <div class="text-center text-xl tracking-wide">Notifications</div>
    <div class="py-3">
        @foreach($this->notifications as $notification)
            <livewire:notification :$notification/>
        @endforeach
    </div>
</div>
