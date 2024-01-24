<?php

use function Livewire\Volt\{state, mount, rules, updated};

state([
    'user' => auth()->user(),
    'record' => '',
    'showInfo' => false,
]);

mount(function () {

});

$viewRecord = function ($id) {
    session()->flash('id', $id);
    return $this->redirect('record/'.$id);
};

$toggleInfo = function () {
    $this->showInfo = !$this->showInfo;
};

?>

<div class="w-full my-2">
    <div
        class="flex justify-between w-full"
    >
        <div wire:click="viewRecord({{ $this->record->id }})"
            class="cursor-pointer"
        >{{ $this->record->title }}</div>
        <div>
            @if($this->record->users->count() > 1)
            <span class="text-center pr-1 text-red-500">
                @if($this->record->user_id == $this->user->id)
                    &<i class="fa-solid fa-angle-right text-blue-400"></i>
                @else
                    <i class="fa-solid fa-angle-left text-blue-400"></i>&
                @endif
            </span>
            @endif
            <button
                wire:click="toggleInfo"
                class="{{ $this->record->info ? 'text-blue-400' : 'text-gray-700' }} fa-solid fa-info ml-5"
                title="details"
            ></button>
        </div>
    </div>
    @if($this->showInfo)
    <div class="flex justify-between"
    >
        <div>{{ $this->record->info }}</div>
    </div>
    @endif
</div>
