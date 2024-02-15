<?php

use function Livewire\Volt\{Js, on, booted, updated, mount, layout, rules, messages, state};
use App\Models\Record;
use App\Models\RecordEntry;
use App\Models\User;
use Carbon\CarbonImmutable;

state([
    'user' => auth()->user(),
    'isOwner' => '',
    'pivot' => '',
    'id' => session('id'),
    'record' => '',
    'title' => '',
    'info' => '',
    'units' => '',
    'measuring' => '',
    'entries' => '',
    'newEntry' => '',
    'inputAt' => '',
    'sortBy' => '',
    'sortDirection' => '',
    'total' => '',
    'from' => '',
    'to' => '',
    'showTotal' => true,
    'showTimeframe' => true,
    'showUnits' => true,
    'showTime' => true,
    'showDate' => true,
    'showDeletes' => '',
    'shareWith' => '',
    'isOwner' => '',
    'isShared' => '',
    'can_sort' => '',
    'can_check' => '',
    'can_add' => '',
    'can_edit' => '',
    'can_delete' => '',
    'can_share' => '',
    'chartData' => [],
    'chartType' => 'line',
    'chartXAxisFormat' => 'DD MMM',
    'totalEntries' => '',
]);

layout('layouts.app');

rules([
    'title' => 'required|string',
    'info' => 'string',
    'newEntry' => 'numeric',
    'shareWith' => 'in:'. User::pluck('name')->implode(','),
])->messages([
    'newEntry' => 'must be a number',
    'shareWith.in' => 'User not found.',
]);

$toZone = function ($date) {
    return CarbonImmutable::parse($date)->setTimezone('America/New_York')->format('Y-m-d\TH:i');
};

$toUTC = function ($date) {
    return CarbonImmutable::parse($date, 'America/New_York')->setTimezone('UTC')->format('Y-m-d\TH:i');
};

$getChart = function () {
    $this->chartData[strval($this->id)] = [
        'title' => $this->title,
        'total' => $this->total . ' ' . $this->units,
        'data' => $this->entries->map(function ($entry) {
            return [
                'x' => $entry->created_at,
                'y' => $entry->amount
            ];
        }),
    ];
};

$getEntries = function () {
    $this->entries = $this->record->entries()
                    ->where([
                        ['created_at', '>=', $this->toUTC($this->from)],
                        ['created_at', '<=', CarbonImmutable::parse($this->toUTC($this->to))->addMinute()],
                    ])
                    ->orderBy($this->sortBy, $this->sortDirection)
                    ->get();

    $this->total = $this->entries->sum('amount');
    $this->getChart();
};

mount(function () {
    if ($this->id == 0) {

        Record::create([
            'title' => 'New Record',
            'user_id' => auth()->user()->id
        ]);

        $this->id = Record::where('user_id', auth()->user()->id)->latest()->first()->id;
        auth()->user()->records()->attach($this->id, [
            'resource_type' => 'record',
            'can_edit' => true,
            'can_delete' => true,
            'can_share' => true
        ]);

        session()->flash('id', $this->id );
        return $this->redirect('record/'.$this->id, navigate: true);
        
    } else {
        
        $this->record = auth()->user()->records()->where('resource_id', $this->id)->first();
    }
        
    if ($this->record) {
        $this->pivot = $this->user->records()->where('resource_id', $this->id)->first()->pivot->toArray();
        $this->id = $this->record->id;
        $this->isOwner = $this->record->user_id == $this->user->id;
        $this->isShared = $this->record->users()->count() > 1;
        $this->title = $this->record->title;
        $this->info = $this->record->info;
        $this->units = $this->record->units;
        $this->measuring = $this->record->measuring;
        
        $this->inputAt = $this->pivot['input_at'];
        $this->sortBy = $this->pivot['sort_by'];
        $this->sortDirection = $this->pivot['sort_direction'];
        $this->can_sort = $this->pivot['can_sort'];
        $this->can_check = $this->pivot['can_check'];
        $this->can_add = $this->pivot['can_add'];
        $this->can_edit = $this->pivot['can_edit'];
        $this->can_delete = $this->pivot['can_delete'];
        $this->can_share = $this->pivot['can_share'];
        $this->showDeletes = $this->pivot['show_deletes'];
        $this->showUnits = $this->pivot['show_units'];
        $this->showTime = $this->pivot['show_time'];
        $this->showDate = $this->pivot['show_date'];
        $this->showTotal = $this->pivot['show_total'];
        $this->showTimeframe = $this->pivot['show_timeframe'];
        $this->totalEntries = $this->record->entries()->count();
        if ($this->record->entries()->first()) {
            $this->from = $this->toZone($this->record->entries()->oldest()->first()->created_at);
            $this->to = $this->toZone($this->record->entries()->latest()->first()->created_at);
        };
        $this->getEntries();
    };

});

$notify = function ($event) {
    if ($this->isShared) {
        foreach($this->record->users()->where('user_id', '!=', auth()->user()->id)->get() as $user) {
            $user->notifications()->create([
                'from_id' => auth()->user()->id,
                'event' => $event,
                'resource_type' => 'record',
                'resource_id' => $this->record->id,
            ]);
        };
    };
};

$destroy = function (Record $record) {
    $this->notify('deleted record');

    $this->record->delete();

    return $this->redirect('/records');
};

$createNewEntry = function () {
    $this->validate(['newEntry' => 'numeric']);

        $this->record->entries()->create([
            'amount' => $this->newEntry
        ]);
        
        $this->newEntry = '';
        
        $this->getEntries();

        $this->notify('added entry to record');
};

$sort = function ($sortBy) {
    if ($sortBy == $this->sortBy) {
        $this->sortDirection == 'asc' ?
            $this->sortDirection = 'desc' :
            $this->sortDirection = 'asc';
    } else {
        $this->sortDirection = 'asc';
    }

    $this->sortBy = $sortBy;
    $this->record->users()->updateExistingPivot($this->user->id, [
        'sort_by' => $this->sortBy, 
        'sort_direction' => $this->sortDirection
    ]);
    $this->getEntries();
};

$toggleInputAt = function () {
    $this->inputAt == 'top' ?
        $this->inputAt = 'bottom' :
        $this->inputAt = 'top';

    $this->record->users()->updateExistingPivot($this->user->id, ['input_at' => $this->inputAt]);
};

$toggleTotal = function () {
    $this->showTotal = ! $this->showTotal;
    $this->record->users()->updateExistingPivot($this->user->id, ['show_total' => $this->showTotal]);
};

$toggleTimeframe = function () {
    $this->showTimeframe = ! $this->showTimeframe;
    $this->record->users()->updateExistingPivot($this->user->id, ['show_timeframe' => $this->showTimeframe]);
};

$toggleDeletes = function () {
    $this->showDeletes = ! $this->showDeletes;
    $this->record->users()->updateExistingPivot($this->user->id, ['show_deletes' => $this->showDeletes]);
    $this->getEntries();
};

$toggleUnits = function () {
    $this->showUnits = ! $this->showUnits;
    $this->record->users()->updateExistingPivot($this->user->id, ['show_units' => $this->showUnits]);
    $this->getEntries();
};

$toggleTime = function () {
    $this->showTime = ! $this->showTime;
    $this->record->users()->updateExistingPivot($this->user->id, ['show_time' => $this->showTime]);
    $this->getEntries();
};

$toggleDate = function () {
    $this->showDate = ! $this->showDate;
    $this->record->users()->updateExistingPivot($this->user->id, ['show_date' => $this->showDate]);
    $this->getEntries();
};

$setChartType = function ($chartType) {
    $this->chartType = $chartType;
};

$setChartFormat = function ($chartFormat) {
    $this->chartXAxisFormat = $chartFormat;
};

$compare = function ($id) {
    $recordToCompare = Record::find($id);
    if (array_search($id, array_keys($this->chartData))) {
        unset($this->chartData[$id]);
    } else {
        $this->chartData[$id] = [
            'title' => $recordToCompare->title,
            'total' => $recordToCompare->entries->sum('amount') . ' ' . $recordToCompare->units,
            'data' => $recordToCompare->entries->map(function ($entry) {
                return [
                    'x' => $entry->created_at,
                    'y' => $entry->amount
                ];
            }),
        ];
    };
};

$share = function () {
    $this->validate(['shareWith' => 'in:'. User::pluck('name')->implode(',')]);
    $this->record->users()->attach(User::where('name', $this->shareWith)->first()->id, ['resource_type' => 'record']);
    
    $this->notify('added '. $this->shareWith .' to record');

    $this->shareWith = '';
};

$unshare = function ($userId) {
    $this->notify('removed '. User::find($userId)->name .' from record');

    $this->record->users()->detach($userId);
};

$leaveRecord = function () {
    $this->record->users()->detach(auth()->user()->id);

    $this->notify('left record');

    return $this->redirect('/records');
};

$toggleAccess = function ($user, $access, $value) {
    $this->record->users()->updateExistingPivot($user, [$access => ! $value]);
};

$test = function () {
    // $this->js('console.log($wire.to)');
    dd(CarbonImmutable::parse($this->to)->addMinute());
};

on([
    'delete-record' => $destroy,
    'delete-entry' => $getEntries,
    'entry-updated' => $getEntries,
    'leave-record' => $leaveRecord,
]);

booted(fn () => $getEntries);
updated([
    'title' => function () {
        $this->notify('renamed record: '. $this->record->title .' to');
        $this->record->update(['title' => $this->title]);
    },
    'info' => function () {
        $this->notify('updated info in record');
        $this->record->update(['info' => $this->info]);
    },
    'units' => function () {
        $this->notify('updated units in record');
        $this->record->update(['units' => $this->units]);
    },
    'measuring' => function () {
        $this->notify('updated measuring in record');
        $this->record->update(['measuring' => $this->measuring]);
    },
    'from' => fn () => $this->getEntries(),
    'to' => fn () => $this->getEntries(),
]);

?>

<div class="flex flex-col items-center max-w-full px-3 bg-inherit h-auto">
    <div x-data="{ open: false }"
        @click.outside="open = false"
        @close.stop="open = false"
        class="sticky top-16 w-full py-4 bg-inherit z-10 lg:w-1/3"
    >
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
            @if($this->isOwner)
            <button
                wire:click="$dispatch('openModal', { component: 'confirm-delete', arguments: { id: {{ $this->record->id }}, type: 'record', message: 'Delete this record?' }})"
                class="fa-regular fa-trash-can text-2xl text-red-500"
                title="delete record"
            ></button>
            @else
            <button
                wire:click="$dispatch('openModal', { component: 'confirm-delete', arguments: { id: {{ $this->record->id }}, verb: 'leave', type: 'record', message: 'Leave this record?' }})"
                class="fa-solid fa-user-xmark text-2xl text-red-500"
                title="leave record"
            ></button>
            @endif
            @else
            <div class="h-6 w-6"></div>
            @endif
        </div>
        <div wire:click="test"
            class="m-2 text-2xl font-medium text-red-400"
        >
            TEST
        </div>
<!-- SETTINGS ------------------------------------------------------------------------->
        <div x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="w-full flex flex-col items-center p-2 pb-5 bg-inherit dark:text-gray-300"
            style="display: none;"
        >
            <hr class="w-full border-none h-px bg-gray-500 -mb-6 mt-6">
            <div class="w-fit px-2 text-center text-lg tracking-wider m-2 bg-inherit">Total</div>
            <div class="">{{ $this->totalEntries }}</div>
            @if($this->can_sort)
            <hr class="w-full border-none h-px bg-gray-500 -mb-6 mt-6">
            <div class="w-fit px-2 text-center text-lg tracking-wider m-2 bg-inherit">Sorting</div>
            <div class="w-full flex justify-between items-center">
                <button
                    wire:click="sort('created_at')"
                    class="w-1/2 text-center py-1"
                >chronological
                    @if($this->sortBy == 'created_at')
                    <span class="fa-arrow-{{ $this->sortDirection == 'asc' ? 'down' : 'up' }}-long fa-solid pl-1 text-blue-400"> 
                    </span>
                    @endif
                </button>
                |<button
                    wire:click="sort('amount')"
                    class="w-1/2 text-center py-1"
                >volumetric
                    @if($this->sortBy == 'amount')
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
                    wire:click="toggleTotal"
                    class="py-1 text-left"
                >{{ $this->showTotal ? 'hide' : 'show' }} total</button>
                <button
                    wire:click="toggleTimeframe"
                    class="py-1 text-left"
                >{{ $this->showTimeframe ? 'hide' : 'show' }} timeframe</button>
                <button
                    wire:click="toggleUnits"
                    class="py-1 text-left"
                >{{ $this->showUnits ? 'hide' : 'show' }} units</button>
                <button
                    wire:click="toggleTime"
                    class="py-1 text-left"
                >{{ $this->showTime ? 'hide' : 'show' }} time</button>
                <button
                    wire:click="toggleDate"
                    class="py-1 text-left"
                >{{ $this->showDate ? 'hide' : 'show' }} date</button>
                <button
                    wire:click="toggleDeletes"
                    class="py-1 text-left text-red-500"
                >{{ $this->showDeletes ? 'hide' : 'show'}} delete buttons</button>
                @if($this->can_delete)
                <button
                    wire:click="$dispatch('openModal', { component: 'confirm-delete', arguments: { id: {{ $this->record->id }}, type: 'record-entries', message: 'Delete this record\'s entries?' }})"
                    class="py-1 text-left text-red-500"
                >clear all entries</button>
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
                    @foreach($this->record->users as $user)
                        @if($user->id != auth()->user()->id)
                        <div x-data="{ openAccessPannel: false }"
                            @close.stop="openAccessPannel = false"
                            @click.outside="openAccessPannel = false"
                            wire:key="user-{{ $user->id }}"
                            class="py-1"
                        >
                            <div class="flex justify-between items-center">
                                <div>{{ $user->name }}</div>
                                @if($user->id == $this->record->user_id)
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
                        wire:click="$dispatch('openModal', { component: 'confirm-delete', arguments: { id: {{ $this->record->id }}, verb: 'leave', type: 'record', message: 'Leave this record?' }})"
                        class="text-red-500 w-fit my-2"
                    >leave record</button>
                @endif
            </div>
            <hr class="w-full border-none h-px bg-gray-500 -mb-6 mt-6">
            <div class="w-fit px-2 text-center text-lg tracking-wider m-2 bg-inherit">Charting</div>
            <div class="text-lg text-center tracking-wider p-2">
                Type
            </div>
            <div class="w-full flex flex-row flex-wrap justify-between">
                @foreach([
                    'line',
                    'column',
                    'bar',
                    'pie',
                    'doughnut',
                    'spline',
                    'area',
                    'splineArea',
                    'stepLine',
                    'scatter',
                    'stacked',
                    'stackedColumn',
                    'stackedColumn100',
                    'stackedArea',
                    'stackedArea100',
                ] as $type)
                <button
                    wire:click="setChartType('{{ $type }}')"
                    wire:key="chart-type-{{ $type }}"
                    class="{{ $this->chartType == $type ? 'bg-blue-400 dark:text-slate-900' : '' }} p-1 m-1 border border-slate-600 rounded-md shadow-lg"
                >{{ $type }}</button>
                @endforeach
            </div>
            <div class="text-lg text-center tracking-wider p  -2">
                X-Axis Format
            </div>
            <div class="flex justify between items-center">
                @foreach([
                    'time' => 'DDD HH:mm',
                    'day' => 'DD MMM',
                    'month' => 'MMM YYYY',
                ] as $formatName => $format)
                <button
                    wire:click="setChartFormat('{{ $format }}')"
                    wire:key="chart-format-{{ $format }}"
                    class="{{ $this->chartXAxisFormat == $format ? 'bg-blue-400 dark:text-slate-900' : '' }} p-1 m-1 border border-slate-600 rounded-md shadow-lg"
                >{{ $formatName }}</button>
                @endforeach
            </div>
        </div>
<!-- END SETTINGS ------------------------------------------------------------->
        <textarea
            wire:model.change="info"
            placeholder="details..."
            class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm bg-inherit dark:border-gray-700 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600"
        ></textarea>
        <div class="w-full dark:text-gray-300 mt-3">
            <div class="w-full flex items-center">
                @if($this->showTotal)
                <div class="w-fit px-3">{{ $this->total }}</div>
                @endif
                <x-text-input 
                    wire:model.change="units"
                    placeholder="units"
                    class="w-1 grow border-none focus:border text-center"
                />
                <div>of</div>
                <x-text-input 
                    wire:model.change="measuring"
                    placeholder="measuring"
                    class="w-1 grow border-none focus:border text-center"
                />
            </div>
            @if($this->showTimeframe)
            <div class="w-full flex justify-between items-center">
                <div class="w-fit px-3">from</div>
                <x-text-input 
                    wire:model.change="from"
                    placeholder="from"
                    class="shrink border-none focus:border text-center"
                    type="datetime-local"
                />
            </div>
            <div class="w-full flex justify-between items-center">
                <div class="w-fit px-3">to</div>
                <x-text-input 
                    wire:model.change="to"
                    placeholder="to"
                    class="shrink border-none focus:border text-center"
                    type="datetime-local"
                />
            </div>
            @endif
        </div>
    </div>
    <div class="dark:text-gray-300 w-full py-2 lg:w-1/3">
        <div>
            @if($this->inputAt == 'top')
            <x-text-input 
                wire:model.blur="newEntry"
                wire:blur="createNewEntry"
                wire:keydown.enter="createNewEntry"
                placeholder="new entry"
                class="border-none focus:border"
            />
            @error('newEntry')
            <span class="error text-red-500 pl-2">{{ $message }}</span>
            @enderror
            @endif
        </div>
        <div>
        @foreach($entries as $entry)
            <livewire:records.record-entry
                wire:key="entry-{{ $entry->id }}"
                :$entry
                units="{{ $this->units }}"
                measuring="{{ $this->measuring }}"
                :showUnits="$this->showUnits"
                :showTime="$this->showTime"
                :showDate="$this->showDate"
                :showDeletes="$this->showDeletes"
                :can_edit="$this->can_edit"
                :can_delete="$this->can_delete"
            />
        @endforeach
        </div>
        <div>
            @if($this->inputAt == 'bottom')
            <x-text-input 
                wire:model.blur="newEntry"
                wire:blur="createNewEntry"
                wire:keydown.enter="createNewEntry"
                placeholder="new entry"
                class="border-none focus:border"
            />
            @error('newEntry') <span class="error">{{ $message }}</span> @enderror
            @endif
        </div>
    </div>
    <div class="w-full lg:w-1/3">
        <div id="chartContainer" class="w-full h-72 mt-10"></div>
        @assets
        <script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
        @endassets
        <livewire:chart
            :key="time()"
            :chartType="$this->chartType"
            :chartData="$this->chartData"
            :xAxisFormat="$this->chartXAxisFormat"
        />
    </div>
    <div class="w-full flex justify-left pb-20 bg-inherit dark:text-slate-300 lg:w-1/3">
        <x-dropdown
            align="left"
            contentClasses="bg-inherit dark:bg-slate-900 border border-slate-600 rounded-md shadow-lg"
        >
            <x-slot name="trigger">
                <button class="p-1 m-1 mb-0 border border-slate-600 rounded-md shadow-lg bg-inherit">compare records</button>
            </x-slot>
            <x-slot name="content" class="border bg-inherit">
                @if($this->user->records->count() <= 1)
                <div class="w-full flex justify-center">no records to compare</div>
                @endif
                <div class="flex flex-col h-fit">
                    @foreach($this->user->records->where('id', '!=', $this->id) as $record)
                    <button wire:click="compare({{ $record->id }})" wire:key="compare-{{ $record->id }}" class="bg-inherit">
                        <x-dropdown-link class="bg-inherit">
                            {{ $record->title }}
                        </x-dropdown-link>
                    </button>
                    @endforeach
                </div>
            </x-slot>
        </x-dropdown>
    </div>
</div>
