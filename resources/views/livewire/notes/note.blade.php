<?php

// sort items by creator (if note->users() > 1)
// sharing input error handling
// share copy / fork
// walkthrough / legend
// swipe events
// fix bug can drag when can't sort
// fix bug when dragging item to end of list

use function Livewire\Volt\{on, booted, updated, mount, layout, rules, messages, state};
use App\Models\Note;
use App\Models\NoteItem;
use App\Models\User;

state([
    'user' => auth()->user(),
    'pivot' => '',
    'id' => session('id'),
    'note' => '',
    'items' => '',
    'title' => '',
    'info' => '',
    'newItem' => '',
    'inputAt' => '',
    'showItemInfo' => '',
    'showDeletes' => '',
    'showChecks' => '',
    'moveChecked' => '',
    'sortBy' => '',
    'sortDirection' => '',
    'shareWith' => '',
    'isOwner' => '',
    'isShared' => '',
    'can_sort' => '',
    'can_check' => '',
    'can_add' => '',
    'can_edit' => '',
    'can_delete' => '',
    'can_share' => '',
    'total' => '',
]);

// tells volt which layout to use
// when using as full-page component
layout('layouts.app');

rules([
    'title' => 'required|string',
    'info' => 'string',
    'shareWith' => 'in:'. User::pluck('name')->implode(','),
])->messages([
    'shareWith.in' => 'User not found.',
    'title.required' => 'A title is required.',
]);

$getItems = function () {
    if($this->note) {
        $this->items = $this->note->items()
                        ->when($this->moveChecked, fn ($query) => $query->orderBy('checked'))
                        ->orderBy($this->sortBy, $this->sortDirection)
                        ->get();

        $this->total = $this->note->items()->count();
    }
};

$getUsers = function () {
    return User::where('id', '!=', auth()->user()->id)->get();
};

mount(function () {
    if ($this->id == 0) {

        Note::create([
            'title' => 'New Note',
            'user_id' => auth()->user()->id
        ]);

        $this->id = Note::where('user_id', auth()->user()->id)->latest()->first()->id;
        auth()->user()->notes()->attach($this->id, [
            'resource_type' => 'note',
            'can_edit' => true,
            'can_delete' => true,
            'can_share' => true
        ]);

        session()->flash('id', $this->id );
        return $this->redirect('note/'.$this->id);
        
    } elseif (Note::find($this->id) != null) {
        
        $this->note = auth()->user()->notes()->where('resource_id', $this->id)->first();
    } else {

        return $this->redirect('/dashboard');
    }
        
    if ($this->note) {
        $this->id = $this->note->id;
        $this->isOwner = $this->note->user_id == auth()->user()->id;
        $this->isShared = $this->note->users()->count() > 1;
        $this->pivot = $this->user->notes()->where('resource_id', $this->id)->first()->pivot->toArray();
        $this->title = $this->note->title;
        $this->info = $this->note->info;
        $this->inputAt = $this->pivot['input_at'];
        $this->showChecks = $this->pivot['show_checks'];
        $this->moveChecked = $this->pivot['move_checked'];
        $this->sortBy = $this->pivot['sort_by'];
        $this->sortDirection = $this->pivot['sort_direction'];
        $this->showItemInfo = $this->pivot['show_item_info'];
        $this->can_sort = $this->pivot['can_sort'];
        $this->can_check = $this->pivot['can_check'];
        $this->can_add = $this->pivot['can_add'];
        $this->can_edit = $this->pivot['can_edit'];
        $this->can_delete = $this->pivot['can_delete'];
        $this->can_share = $this->pivot['can_share'];
        $this->showDeletes = $this->pivot['show_deletes'];
        $this->total = $this->note->items()->count();
    }

    $this->getItems();
});

$notify = function ($event) {
    if ($this->isShared) {
        // $this->note->users()->where('user_id', '!=', auth()->user()->id)->get()->each(function ($user, $event) {
        //     $user->notifications()->create([
        //         'from_id' => auth()->user()->id,
        //         'event' => $event,
        //         'resource_type' => 'note',
        //         'resource_id' => $this->note->id,
        //     ]);
        // });
        foreach($this->note->users()->where('user_id', '!=', auth()->user()->id)->get() as $user) {
            $user->notifications()->create([
                'from_id' => auth()->user()->id,
                'event' => $event,
                'resource_type' => 'note',
                'resource_id' => $this->note->id,
            ]);
        };
    };
};

$createNewItem = function () {
    if ($this->newItem) {
        $this->note->items()->create([
            'title' => $this->newItem,
            'user_id' => auth()->user()->id,
        ]);

        $this->newItem = '';

        $this->getItems();

        $this->notify('added item to note');
    };
};

$updateItem = function ($key, $value, $id) {
    $item = NoteItem::find($id)->update([$key => $value]);
    $this->getItems();
};

$destroyItem = function ($id) {
    $this->notify('deleted item from note');
    NoteItem::find($id)->delete();
    $this->getItems();
};

$destroyAllItems = function () {
    $this->notify('deleted all items from note');
    $this->note->items()->delete();
    $this->getItems();
};

$destroy = function () {
    
    // $this->authorize('delete', $note);

    $this->notify('deleted note');

    $this->note->delete();

    return $this->redirect('/notes');
};

$sort = function ($sortBy) {
    if ($sortBy == $this->sortBy && $sortBy != 'position') {
        $this->sortDirection == 'asc' ?
            $this->sortDirection = 'desc' :
            $this->sortDirection = 'asc';
    } else {
        $this->sortDirection = 'asc';
    };

    $this->sortBy = $sortBy;
    $this->note->users()->updateExistingPivot($this->user->id, [
        'sort_by' => $this->sortBy,
        'sort_direction' => $this->sortDirection,
    ]);
    $this->getItems();
};

$updateOrder = function ($items) {
    foreach ($items as $item) {
        NoteItem::find($item['value'])->update(['position' => $item['order']]);
    }

    $this->getItems();
};

$toggleInputAt = function () {
    $this->inputAt == 'top' ?
        $this->inputAt = 'bottom' :
        $this->inputAt = 'top';

    $this->note->users()->updateExistingPivot($this->user->id, ['input_at' => $this->inputAt]);
    $this->getItems();
};

$toggleDeletes = function () {
    $this->showDeletes = ! $this->showDeletes;
    $this->note->users()->updateExistingPivot($this->user->id, ['show_deletes' => $this->showDeletes]);
    $this->getItems();
};

$toggleChecks = function () {
    $this->showChecks = ! $this->showChecks;
    $this->note->users()->updateExistingPivot($this->user->id, ['show_checks' => $this->showChecks]);
    $this->getItems();
};

$toggleMoveChecked = function () {
    $this->moveChecked = ! $this->moveChecked;
    $this->note->users()->updateExistingPivot($this->user->id, ['move_checked' => $this->moveChecked]);
    $this->getItems();
};

$toggleItemInfo = function () {
    $this->showItemInfo = ! $this->showItemInfo;
    $this->note->users()->updateExistingPivot($this->user->id, ['show_item_info' => $this->showItemInfo]);
    $this->getItems();
};

$share = function () {
    $this->validate(['shareWith' => 'in:'. User::pluck('name')->implode(',')]);
    $this->note->users()->attach(User::where('name', $this->shareWith)->first()->id, ['resource_type' => 'note']);
    
    $this->notify('added '. $this->shareWith .' to note');

    $this->shareWith = '';
};

$unshare = function ($userId) {
    $this->notify('removed '. User::find($userId)->name .' from note');

    $this->note->users()->detach($userId);
};

$leaveNote = function () {
    $this->note->users()->detach(auth()->user()->id);

    $this->notify('left note');

    return $this->redirect('/notes');
};

$toggleAccess = function ($user, $access, $value) {
    $this->note->users()->updateExistingPivot($user, [$access => ! $value]);
};

on([
    'delete-note' => $destroy,
    'leave-note' => $leaveNote,
    'delete-item' => $getItems,
    'check' => $getItems,
    'resize' => $getItems,
    'delete-note-items' => $destroyAllItems,
]);

$test = fn () => dd($this->id);

updated([
    'title' => function () {
        $this->validate(['title' => 'required|string']);
        $this->notify('renamed note: '. $this->note->title .' to');
        $this->note->update(['title' => $this->title]);
    },
    'info' => function () {
        $this->notify('updated info in note');
        $this->note->update(['info' => $this->info]);
    },
]);
// updated(['newItem' => $createNewItem ]);

?>

<div class="flex flex-col items-center px-3 bg-inherit">
    <div x-data="{ open: false }"
        @click.outside="open = false"
        @close.stop="open = false"
        class="sticky top-16 w-full py-4 bg-inherit lg:w-1/3"
    >
        @error('title')
        <span class="block text-red-500 w-full text-center">{{ $message }}</span>
        @enderror
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
            @if($this->showDeletes)
            <div class="w-6">
                @if($this->isOwner)
                <button
                    wire:click="$dispatch('openModal', { component: 'confirm-delete', arguments: { id: {{ $this->note->id }}, type: 'note', message: 'Delete this note?' }})"
                    class="fa-regular fa-trash-can text-2xl text-red-500"
                    title="delete note"
                ></button>
                @else
                <button
                    wire:click="$dispatch('openModal', { component: 'confirm-delete', arguments: { id: {{ $this->note->id }}, verb: 'leave', type: 'note', message: 'Leave this note?' }})"
                    class="fa-solid fa-user-xmark text-2xl text-red-500"
                    title="leave note"
                ></button>
                @endif
            </div>
            @else
            <div class="h-6 w-6"></div>
            @endif
        </div>
<!-- SETTINGS ----------------------------------------------------->
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
            <div class="w-fit px-2 text-center text-lg tracking-wider m-2 bg-inherit">Total</div>
            <div class="">{{ $this->total }}</div>
            @if($this->can_sort)
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
                    wire:click="sort('created_at')"
                    class="py-1"
                >chronological
                    @if($this->sortBy == 'created_at')
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
            @endif
            <hr class="w-full border-none h-px bg-gray-500 -mb-6 mt-6">
            <div class="w-fit px-2 text-center text-lg tracking-wider m-2 bg-inherit">Settings</div>
            <div class="w-full flex flex-col justify-between">
                <button
                    wire:click="toggleInputAt"
                    class="py-1 text-left"
                >input at {{ $this->inputAt == 'top' ? 'bottom' : 'top' }}</button>
                <button
                    wire:click="toggleChecks"
                    class="py-1 text-left"
                >{{ $this->showChecks ? 'hide' : 'show'}} checks</button>
                <button
                    wire:click="toggleMoveChecked"
                    class="py-1 text-left"
                >{{ $this->moveChecked ? 'leave checked in place' : 'move checked to bottom' }}</button>
                <button
                    wire:click="toggleItemInfo"
                    class="py-1 text-left"
                >{{ $this->showItemInfo ? 'hide' : 'show'}} all item details</button>
                <button
                    wire:click="toggleDeletes"
                    class="py-1 text-left text-red-500"
                >{{ $this->showDeletes ? 'hide' : 'show'}} delete buttons</button>
                @if($this->can_delete)
                <button
                    wire:click="$dispatch('openModal', { component: 'confirm-delete', arguments: { id: {{ $this->note->id }}, type: 'note-items', message: 'Delete this note\'s items?' }})"
                    class="py-1 text-left text-red-500"
                >clear all items</button>
                @endif
            </div>
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
                    @foreach($this->note->users as $user)
                        @if($user->id != auth()->user()->id)
                        <div x-data="{ openAccessPannel: false }"
                            @close.stop="openAccessPannel = false"
                            @click.outside="openAccessPannel = false"
                            wire:key="user-{{ $user->id }}"
                            class="py-1"
                        >
                            <div class="flex justify-between items-center">
                                <div>{{ $user->name }}</div>
                                @if($user->id == $this->note->user_id)
                                <div>owner</div>
                                @endif
                                @if($this->isOwner)
                                <div class="flex">
                                    <button
                                        @click="openAccessPannel = ! openAccessPannel"
                                        class="flex justify-between items-center border border-gray-700 rounded-full pl-1"
                                        title="access panel"
                                    >
                                        @if($user->pivot->can_sort)
                                        <span class="fa-solid fa-shuffle text-sm text-gray-400 p-1" title="can sort"></span>@endif
                                        @if($user->pivot->can_check)
                                        <span class="fa-solid fa-check text-sm text-green-700 p-1" title="can check"></span>@endif
                                        @if($user->pivot->can_add)
                                        <span class="fa-solid fa-plus text-sm text-blue-500 p-1" title="can add"></span>@endif
                                        @if($user->pivot->can_edit)
                                        <span class="fa-solid fa-scissors text-sm text-yellow-600 p-1" title="can edit"></span>@endif
                                        @if($user->pivot->can_delete)
                                        <span class="fa-solid fa-trash text-sm text-red-600 p-1" title="can delete"></span>@endif
                                        @if($user->pivot->can_share)
                                        <span class="fa-solid fa-user-plus text-sm text-blue-500 p-1" title="can share"></span>@endif
                                        <span class="fa-solid fa-key text-blue-400 p-1 ml-1 border border-gray-700 rounded-full" title="access pannel"></span>
                                    </button>
                                    @if($this->showDeletes)
                                    <button
                                        wire:click="unshare({{ $user->id }})"
                                        class="fa-regular fa-trash-can ml-6 hover:bg-red-500 dark:text-red-500 dark:hover:text-gray-900 transition"
                                        title="unshare"
                                    ></button>
                                    @endif
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
                                    @if(str_starts_with($key, 'can_') && $value)
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
                                    @if(str_starts_with($key, 'can_') && !$value)
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
                @if(!$this->isOwner)
                    <button
                        wire:click="$dispatch('openModal', { component: 'confirm-delete', arguments: { id: {{ $this->note->id }}, verb: 'leave', type: 'note', message: 'Leave this note?' }})"
                        class="text-red-500 w-fit my-2"
                    >leave note</button>
                @endif
            </div>
        </div>
<!-- END SETTINGS -------------------------------------------------->
        <textarea
            wire:model.change="info"
            placeholder="details..."
            class="block w-full border-gray-300 bg-inherit focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:border-gray-700 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600"
            {{ $this->can_edit ? '' : 'readonly' }}
        ></textarea>
    </div>
    <div class="dark:text-gray-300 w-full p-2 lg:w-1/3">
        <div>
            @if($this->inputAt == 'top' && $this->can_add)
            <x-text-input 
                wire:model.blur="newItem"
                wire:blur="createNewItem"
                wire:keydown.enter="createNewItem"
                placeholder="new item"
                class="w-full border-none focus:border"
            />
            @endif
        </div>
        <div @if($this->sortBy == 'position') wire:sortable="updateOrder" @endif>
        @foreach($items as $item)
            <div x-data="{ openItemInfo: $wire.showItemInfo }"
                @close.stop="openItemInfo = false"
                wire:sortable.item="{{ $item->id }}"
                wire:key="item-{{ $item->id }}"
                class="my-1"
            >
                <div class="flex justify-between items-center">
                    <div class="flex items-center grow">
                        @if($this->showChecks)
                        <button
                            wire:click="updateItem('checked', {{ $item->checked ? 'false' : 'true' }}, {{ $item->id }})"
                            class="{{ $item->checked ? 'bg-green-800 border-green-800' : 'border-gray-600'}} h-5 w-5 text-sm border rounded-full mr-2 text-green-600"
                        ></button>
                        @endif
                        <textarea
                            wire:change="updateItem('title', $event.target.value, {{ $item->id }})"
                            wire:keydown.enter="updateItem('title', $event.target.value, {{ $item->id }})"
                            placeholder="new item"
                            rows="{{ 1 + (strlen($item->title) / 40) }}"
                            class="count-new-lines grow bg-transparent rounded-md border-none pl-1 focus:ring-gray-700 resize-none"
                            {{ $this->can_edit ? '' : 'readonly'}}
                        >{{ $item->title }}</textarea>
                    </div>
                    <div>
                        @if(!$this->showItemInfo)
                        <button
                            @click="openItemInfo = ! openItemInfo"
                            class="{{ $item->info ? 'text-blue-400' : 'text-gray-700' }} fa-solid fa-info ml-5"
                            title="details"
                        ></button>
                        @endif
                        @if ($this->sortBy == 'position')
                        <button wire:sortable.handle
                            class="fa-solid fa-arrows-up-down ml-5 dark:text-gray-600"
                            title="drag"
                        ></button>
                        @endif
                        @if($this->can_delete && $this->showDeletes)
                        <button
                            wire:click="destroyItem({{ $item->id }})"
                            class="fa-regular fa-trash-can ml-6 text-red-500"
                            title="delete item"
                        ></button>
                        @endif
                    </div>
                </div>
                @if(!$this->showItemInfo)
                <div x-show="openItemInfo">
                    <textarea
                        wire:change="updateItem('info', $event.target.value, {{ $item->id }})"
                        wire:keydown.enter="updateItem('info', $event.target.value, {{ $item->id }})"
                        rows="{{ strlen($this->info) / 40 + 1 }}"
                        placeholder="details..."
                        class="block w-full mt-1 border-gray-300 resize-none bg-inherit focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:border-gray-700 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600"
                        {{ $this->can_edit ? '' : 'readonly'}}
                    >{{ $item->info }}</textarea>
                    @if(auth()->user()->id != $item->user_id)
                    <div class="text-sm dark:text-gray-500 pt-2 pl-1">Added by {{ User::find($item->user_id)->name }}</div>
                    @endif
                </div>
                @endif
                @if($this->showItemInfo)
                <textarea
                    wire:change="updateItem('info', $event.target.value, {{ $item->id }})"
                    wire:keydown.enter="updateItem('info', $event.target.value, {{ $item->id }})"
                    rows="{{ strlen($this->info) / 40 + 1 }}"
                    placeholder="details..."
                    class="block w-full mt-1 border-gray-300 resize-none bg-inherit focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:border-gray-700 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600"
                    {{ $this->can_edit ? '' : 'readonly'}}
                >{{ $item->info }}</textarea>
                    @if(auth()->user()->id != $item->user_id)
                    <div class="text-sm dark:text-gray-500 pt-2 pl-1">Added by {{ User::find($item->user_id)->name }}</div>
                    @endif
                @endif
            </div>
        @endforeach
        </div>
        <div>
            @if($this->inputAt == 'bottom' && $this->can_add)
            <x-text-input 
                wire:model.blur="newItem"
                wire:blur="createNewItem"
                wire:keydown.enter="createNewItem"
                placeholder="new item"
                class="w-full border-none focus:border"
            />
            @endif
        </div>
    </div>
</div>