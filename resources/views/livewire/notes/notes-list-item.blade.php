<?php

use function Livewire\Volt\{mount, rules, updated, state};

state([
    'user' => auth()->user(),
    'note' => '',
    'showInfo' => false,
]);

mount(function () {

});

$viewNote = function ($id) {
    session()->flash('id', $id);
    return $this->redirect('note/'.$id);
};

$toggleInfo = function () {
    $this->showInfo = !$this->showInfo;
};

?>

<div class="w-full my-2"
>
    <div
        class="flex justify-between w-full"
    >
        <div wire:click="viewNote({{ $this->note->id }})"
            class="cursor-pointer"
        >{{ $this->note->title }}</div>
        <div>
            @if($this->note->users->count() > 1)
            <x-shared-symbol :direction="$this->note->user_id == $this->user->id" />
            @endif
            <button
                wire:click="toggleInfo"
                class="{{ $this->note->info ? 'text-blue-400' : 'text-gray-700' }} fa-solid fa-info ml-5"
                title="details"
            ></button>
        </div>
    </div>
    @if($this->showInfo)
    <div class="flex justify-between"
    >
        <div>{{ $this->note->info }}</div>
    </div>
    @endif
</div>
