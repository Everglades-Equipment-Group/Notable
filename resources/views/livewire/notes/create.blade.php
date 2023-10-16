<?php

use function Livewire\Volt\{rules, state};

state([
    'title' => '',
    'info' => ''
]);

rules([
    'title' => 'required|string',
    'info' => 'string'
]);

$store = function () {
    $validated = $this->validate();
 
    auth()->user()->notes()->upsert(
        ['title' => $this->title, 'info' => $this->info, 'user_id' => auth()->user()->id],
        ['id'],
        ['title', 'info']
    );
 
    $this->title = '';
    $this->info = '';
};

$test = function () {
    dd(auth()->user()->id);
}

?>

<div>
    <x-page-header page="New Note"/>
    <form wire:submit="store">
        <x-text-input 
            wire:model.live="title"
            placeholder="Title"
        />
        <textarea
            wire:model.live="info"
            placeholder="{{ __('details...') }}"
            class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600"
        ></textarea>
 
        <x-input-error :messages="$errors->get('message')" class="mt-2" />
        <x-primary-button class="mt-4">{{ __('Create') }}</x-primary-button>
    </form>
    <button wire:click="test" class="dark:text-gray-300">test</button>
</div>
