<?php

use function Livewire\Volt\{Js, on, booted, updated, mount, layout, rules, state};
use App\Models\Record;
use App\Models\RecordEntry;

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
    'isShared' => '',
    'can_sort' => '',
    'can_check' => '',
    'can_add' => '',
    'can_edit' => '',
    'can_delete' => '',
    'can_share' => '',
    'chartData' => [],
    'chartType' => 'line',
]);

layout('layouts.app');

rules([
    'title' => 'required|string',
    'info' => 'string'
]);

$getEntries = function () {
    $this->entries = $this->record->entries()
                    ->orderBy($this->sortBy, $this->sortDirection)
                    ->get();

    $this->total = $this->entries->sum('amount');
    $this->chartData[strval($this->id)] = [
        'title' => $this->title,
        'data' => $this->entries->map(function ($entry) {
            return [
                'label' => $entry->created_at->format('m-d-y'),
                'y' => $entry->amount
            ];
        }),
    ];
    // dd($this->chartData);
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
        if ($this->record->entries()->first()) {
            $this->from = $this->record->entries()->oldest()->first()->created_at->format('Y-m-d\Th:i');
            $this->to = $this->record->entries()->latest()->first()->created_at->format('Y-m-d\Th:i');
        };
    };

    $this->getEntries();
});

$destroy = function (Record $record) {
    $this->record->delete();

    return $this->redirect('/dashboard', navigate: true);
};

$createNewEntry = function () {
    if ($this->newEntry) {
        $this->record->entries()->create([
            'amount' => $this->newEntry
        ]);

        $this->newEntry = '';

        $this->getEntries();
    };
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

$compare = function ($id) {
    $recordToCompare = Record::find($id);
    if (array_search($id, array_keys($this->chartData))) {
        // dd($this->chartData[$id]);
        unset($this->chartData[$id]);
    } else {
        $this->chartData[$id] = [
            'title' => $recordToCompare->title,
            'data' => $recordToCompare->entries->map(function ($entry) {
                return [
                    'label' => $entry->created_at->format('m-d-y'),
                    'y' => $entry->amount
                ];
            }),
        ];
    };
};

on(['delete-entry' => function () {
    $this->getEntries();
}]);

booted(fn () => $getEntries);
updated([
    'title' => fn () => $this->record->update(['title' => $this->title]),
    'info' => fn () => $this->record->update(['info' => $this->info]),
    'units' => fn () => $this->record->update(['units' => $this->units]),
    'measuring' => fn () => $this->record->update(['measuring' => $this->measuring]),
    // 'chartData' => fn () => $this->js('console.log($wire.chartType);'),
    // 'chartType' => fn () => $this->js('console.log("change");'),
]);
// updated(['newEntry' => $newEntry ]);

?>

<div class="flex flex-col items-center max-w-full px-3 bg-inherit">
    <div x-data="{ open: false }"
        @click.outside="open = false"
        @close.stop="open = false"
        class="sticky top-16 w-full py-4 bg-inherit z-10"
    >
        <div class="flex justify-between items-center px-2 mb-3">
            <button
                @click="open = ! open"
                class="fa-solid fa-sliders text-2xl text-blue-400"
                title="options"
            ></button>
            <x-text-input 
                wire:model.change="title"
                placeholder="Title"
                class="text-2xl border-none text-center focus:border"
                disabled="{{ ! $this->can_edit }}"
            />
            @if($this->showDeletes)
            @if($this->isOwner)
            <button
                wire:click="destroy"
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
            @if($this->can_sort)
            <hr class="w-full border-none h-px bg-gray-500 -mb-6 mt-6">
            <div class="w-fit px-2 text-center text-lg tracking-wider m-2 bg-inherit">Sorting</div>
            <div class="w-full flex justify-between items-center">
                <button
                    wire:click="sort('created_at')"
                    class="py-1"
                >chronological
                    @if($this->sortBy == 'created_at')
                    <span class="fa-arrow-{{ $this->sortDirection == 'asc' ? 'down' : 'up' }}-long fa-solid pl-1 text-blue-400"> 
                    </span>
                    @endif
                </button>
                |<button
                    wire:click="sort('amount')"
                    class="py-1"
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
            <div class="w-fit px-2 text-center text-lg tracking-wider m-2 bg-inherit">Charting</div>
            <div class="w-full flex flex-row flex-wrap justify-between">
                <button
                    wire:click="setChartType('column')"
                    class="p-1 m-1 border border-slate-600 rounded-md shadow-lg"
                >column</button>
                <button
                    wire:click="setChartType('line')"
                    class="p-1 m-1 border border-slate-600 rounded-md shadow-lg"
                >line</button>
                <button
                    wire:click="setChartType('area')"
                    class="p-1 m-1 border border-slate-600 rounded-md shadow-lg"
                >area</button>
                <button
                    wire:click="setChartType('spline')"
                    class="p-1 m-1 border border-slate-600 rounded-md shadow-lg"
                >spline</button>
                <button
                    wire:click="setChartType('splineArea')"
                    class="p-1 m-1 border border-slate-600 rounded-md shadow-lg"
                >spline area</button>
                <button
                    wire:click="setChartType('stepLine')"
                    class="p-1 m-1 border border-slate-600 rounded-md shadow-lg"
                >step line</button>
                <button
                    wire:click="setChartType('scatter')"
                    class="p-1 m-1 border border-slate-600 rounded-md shadow-lg"
                >scatter</button>
                <button
                    wire:click="setChartType('stackedColumn')"
                    class="p-1 m-1 border border-slate-600 rounded-md shadow-lg"
                >stacked column</button>
                <button
                    wire:click="setChartType('stackedArea')"
                    class="p-1 m-1 border border-slate-600 rounded-md shadow-lg"
                >stacked area</button>
                <button
                    wire:click="setChartType('stackedColumn100')"
                    class="p-1 m-1 border border-slate-600 rounded-md shadow-lg"
                >stacked column 100</button>
                <button
                    wire:click="setChartType('stackedArea100')"
                    class="p-1 m-1 border border-slate-600 rounded-md shadow-lg"
                >stacked area 100</button>
                <button
                    wire:click="setChartType('pie')"
                    class="p-1 m-1 border border-slate-600 rounded-md shadow-lg"
                >pie</button>
                <button
                    wire:click="setChartType('doughnut')"
                    class="p-1 m-1 border border-slate-600 rounded-md shadow-lg"
                >doughnut</button>
                <button
                    wire:click="setChartType('bar')"
                    class="p-1 m-1 border border-slate-600 rounded-md shadow-lg"
                >bar</button>
                <button
                    wire:click="setChartType('stacked')"
                    class="p-1 m-1 border border-slate-600 rounded-md shadow-lg"
                >stacked</button>
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
    <div class="dark:text-gray-300 w-full py-2">
        <div>
            @if($this->inputAt == 'top')
            <x-text-input 
                wire:model.blur="newEntry"
                wire:blur="createNewEntry"
                wire:keydown.enter="createNewEntry"
                placeholder="new entry"
                class="border-none focus:border"
            />
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
            @endif
        </div>
    </div>
    <div class="w-full">
        <div id="chartContainer" class="w-full h-72 mt-10"></div>
        @assets
        <script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
        @endassets
        <livewire:chart
            :key="time()"
            :chartType="$this->chartType"
            :chartData="$this->chartData"
        />
    </div>
    <div class="w-full flex justify-left pb-20 bg-inherit dark:text-slate-300">
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
                @foreach($this->user->records->where('id', '!=', $this->id) as $record)
                <button wire:click="compare({{ $record->id }})" class="bg-inherit">
                    <x-dropdown-link class="bg-inherit">
                        {{ $record->title }}
                    </x-dropdown-link>
                </button>
                @endforeach
            </x-slot>
        </x-dropdown>
    </div>
    <button wire:click="test" class="dark:text-gray-300">test</button>
</div>
