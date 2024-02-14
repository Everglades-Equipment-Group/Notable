<?php

use function Livewire\Volt\{mount, state};
use App\Models\Notification;
use App\Models\User;
use App\Models\Note;
use App\Models\Record;
use App\Models\Event;

state([
    'user' => auth()->user(),
    'notification' => '',
    'read' => '',
    'who' => '',
    'what' => '',
    'where' => '',
    'when' => '',
]);

$getResourceTitle = function () {
    switch ($this->where) {
        case 'note':
            if (Note::find($this->notification->resource_id)) {
                $this->where = Note::find($this->notification->resource_id)->title;
            };
            break;
        case 'record':
            if (Record::find($this->notification->resource_id)) {
                $this->where = Record::find($this->notification->resource_id)->title;
            };
            break;
        case 'event':
            if (Event::find($this->notification->resource_id)) {
                $this->where = Event::find($this->notification->resource_id)->title;
            };
            break;
    };
};

mount(function () {
    if ($this->notification) {
        $this->read = $this->notification->read;
        $this->who = User::find($this->notification->from_id)->name;
        $this->what = str_replace($this->user->name, 'you', $this->notification->event);
        $this->where = $this->notification->resource_type;
        $this->when = $this->notification->created_at->diffForHumans();

        $this->getResourceTitle();
    };

});

$goToResource = function () {
    // $this->notification->read = true;
    // $this->notification->save();
    session()->flash('id', $this->notification->resource_id);
    return $this->redirect($this->notification->resource_type.'/'.$this->notification->resource_id);
};

?>

<div wire:click="goToResource"
    class="{{ $this->read ? '' : 'border-x border-red-500' }} flex justify-between items-top"
>
    <div class="p-2">{{ $this->who }}</div>
    <div class="p-2 text-center">
      <div>{{ $this->what }}:</div>
      <div>{{ $this->where }}</div>
    </div>
    <div class="p-2 text-right">{{ $this->when }}</div>
</div>
