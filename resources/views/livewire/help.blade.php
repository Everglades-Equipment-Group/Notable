<?php

use function Livewire\Volt\{state, layout, mount, on};

layout('layouts.app');

state([
    'previousPage' => '',
]);

mount(function () {
    $this->previousPage = app('router')->getRoutes()->match(app('request')->create(url()->previous()))->getName();
});

?>

<div class="flex flex-col items-center py-6 px-3 bg-inherit dark:text-gray-300">
    <h1 class="text-2xl tracking-wide">Help</h1>
    <div class="w-full py-4 bg-inherit lg:w-1/2">
        <div x-data="{ openBasics: $wire.previousPage === null || $wire.previousPage === 'notifications' }"
            @close.stop="open = false"
            class="flex flex-col items-center w-full my-2 bg-inherit"
        >
            <div @click="openBasics = !openBasics"
                class="w-full text-center text-xl tracking-wide text-gray-900 bg-blue-400 rounded-full cursor-pointer"
            >
                Basics
            </div>
            <div x-show="openBasics"
                class="flex flex-col text-left w-full p-2 bg-inherit"
            >
                <div class="my-2">Notes, Records, and Events are called "resources."</div>
                <div class="my-2">
                    <span class="text-red-500 pr-1">
                        <span class="pt-px">&</span>
                        <span class="fa-solid fa-angle-right text-blue-400 pt-1 -ml-1"></span>
                    </span>
                    indicates a resource is shared BY you.
                </div>
                <div class="my-2">
                    <span class="text-red-500 pr-1">
                        <span class="fa-solid fa-angle-left text-blue-400 pt-1 -mr-1"></span>
                        <span>&</span>
                    </span>
                    indicates a resource is shared WITH you.
                </div>
                <div class="my-2">
                    <span class="relative text-blue-400 dark:text-blue-400 mr-2 pr-1 pt-1 -ml-1">
                        <svg class="inline-flex h-6 w-6"
                            stroke="currentColor"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <path class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <span class="absolute right-0 top-0 h-2 w-2 rounded-full bg-red-500"></span>
                    </span>
                    indicates unread notifications.
                </div>
                <div class="my-2">
                    <span class="fa-solid fa-sliders text-xl text-blue-400 mr-1"></span>
                    opens the control panel for the resource(s).
                </div>
                <div class="my-2">
                    <span class="fa-solid fa-file-circle-plus text-xl text-blue-400"></span>
                    <span class="fa-solid fa-folder-plus text-xl text-blue-400"></span>
                    <span class="fa-regular fa-calendar-plus text-xl text-blue-400 mr-1"></span>
                    creates a new resource.
                </div>
                <div class="my-2">
                    <span class="fa-regular fa-trash-can text-xl text-red-500 mr-1"></span>
                    deletes the resource.
                </div>
                <div class="my-2">
                    <span class="fa-solid fa-user-xmark text-xl text-red-500 mr-1"></span>
                    leaves a resource shared with you.
                </div>
                <div class="my-2">
                    <span class="fa-regular fa-clock text-blue-400 mr-1"></span>
                    time selector.
                </div>
                <div class="my-2">
                    <span class="fa-regular fa-calendar text-blue-400 mr-1"></span>
                    date selector.
                </div>
                <div class="my-2">
                    <span class="fa-solid fa-info text-gray-700 text-xl mr-1"></span>
                    shows/hides item info. color indicates item has no info.
                </div>
                <div class="my-2">
                    <span class="fa-solid fa-info text-blue-400 text-xl mr-1"></span>
                    shows/hides item info. color indicates item has info.
                </div>
                <div
                    class="flex flex-col items-center w-full my-2 bg-inherit"
                >
                    <hr class="w-full border-none h-px bg-gray-500 -mb-4 mt-6">
                    <div class="px-2 text-lg tracking-wider font-medium text-center bg-inherit">
                        Creating Resources
                    </div>
                    <div>
                        New resources can be created from the Dashboard with the 'New' button and from each resource list page with the '+' icon to the right of the page title.
                    </div>
                </div>
                <div
                    class="flex flex-col items-center w-full my-2 bg-inherit"
                >
                    <hr class="w-full border-none h-px bg-gray-500 -mb-4 mt-6">
                    <div class="px-2 text-lg tracking-wider font-medium text-center bg-inherit">
                        Deleting Resources
                    </div>
                    <div class="w-full">
                        When viewing a single resource,
                        <br>
                        if it is owned by you, the red trash can icon to the right of the resource title will delete the resource.
                        <br>
                        If the resource is shared with you, the red 'x' icon will leave the resource.
                        <br>
                        You will be prompted to confirm.
                        <br>
                        Delete and leave buttons can be hidden and shown from the 'Settings' section in the control panel of each resource.
                    </div>
                </div>
                <div
                    class="flex flex-col items-center w-full my-2 bg-inherit"
                >
                    <hr class="w-full border-none h-px bg-gray-500 -mb-4 mt-6">
                    <div class="px-2 text-lg tracking-wider font-medium text-center bg-inherit">
                        Sharing Resources
                    </div>
                    <div class="w-full">
                        In the 'Sharing' section of the control panel for each resource, add the username of the user you want to share with.
                        <br>
                        The users a resource is shared with are listed in the 'Sharing' section under 'With:'.
                    </div>
                    <div class="flex justify-between items-center w-full my-1">
                        Username
                        <div class="flex justify-between items-center border border-gray-700 rounded-full pl-1">
                            <span class="fa-solid fa-shuffle text-sm text-gray-400 p-1" title="can sort"></span>
                            <span class="fa-solid fa-check text-sm text-green-700 p-1" title="can check"></span>
                            <span class="fa-solid fa-plus text-sm text-blue-500 p-1" title="can add"></span>
                            <span class="fa-solid fa-scissors text-sm text-yellow-600 p-1" title="can edit"></span>
                            <span class="fa-solid fa-trash text-sm text-red-600 p-1" title="can delete"></span>
                            <span class="fa-solid fa-user-plus text-sm text-blue-500 p-1" title="can share"></span>
                            <span class="fa-solid fa-key text-blue-400 p-1 ml-1 border border-gray-700 rounded-full" title="access pannel"></span>
                        </div>
                    </div>
                    <div>
                        If you own the resource, the permissions each user has for it are indicated to the right of their username. This is called the access panel. Opening the access panel allows you to adjust their permissions.
                    </div>
                    <div class="w-full my-1">
                        <span class="fa-solid fa-shuffle text-sm text-gray-400 p-1" title="can sort"></span>
                        can sort note items.
                        <br>
                        <span class="fa-solid fa-check text-sm text-green-700 p-1" title="can check"></span>
                        can check note items.
                        <br>
                        <span class="fa-solid fa-plus text-sm text-blue-500 p-1" title="can add"></span>
                        can add note items or record entries.
                        <br>
                        <span class="fa-solid fa-scissors text-sm text-yellow-600 p-1" title="can edit"></span>
                        can edit the resource.
                        <br>
                        <span class="fa-solid fa-trash text-sm text-red-600 p-1" title="can delete"></span>
                        can delete note items and record entries.
                        <br>
                        <span class="fa-solid fa-user-plus text-sm text-blue-500 p-1" title="can share"></span>
                        can share the resource with others.
                        <br>
                        <span class="fa-solid fa-key text-sm text-blue-400 p-1" title="access pannel"></span>
                        the access panel icon.
                    </div>
                </div>
            </div>
        </div>
        <div x-data="{ openDashboard: $wire.previousPage === 'dashboard' }"
            @close.stop="open = false"
            class="flex flex-col items-center w-full my-2"
        >
            <div @click="openDashboard = !openDashboard"
                class="w-full text-center text-xl tracking-wide text-gray-900 bg-blue-400 rounded-full cursor-pointer"
            >
                Dashboard
            </div>
            <div x-show="openDashboard"
                class="flex flex-col text-left w-full p-2"
            >
                <div class="flex justify-between items-center h-12 px-4 border border-blue-400 rounded-lg my-2">
                    <div class="flex items-center h-full pr-5 border-r-2 border-blue-400 rounded-xl">
                        New
                    </div>
                    <div class="tracking-wider">
                        Resource Type
                    </div>
                    <div class="flex items-center h-full pl-5 border-l-2 border-blue-400 rounded-xl">
                        Sort
                    </div>
                </div>
                <div>
                    <div class="my-2">
                        Each '<span class="text-lg font-medium tracking-wide drop-shadow-[1px_1px_0px_rgba(96,165,250,1)]">New</span>' button will create a resource of the corresponding type and navigate to that new resource. The default title will be "New *Type*"
                    </div>
                    <div class="my-2">
                        Each <span class="text-lg font-medium tracking-wide drop-shadow-[1px_1px_0px_rgba(96,165,250,1)]">'Resource Type'</span> button will navigate to a page listing all resources of that type.
                    </div>
                    <div class="my-2">
                        Each <span class="text-lg font-medium tracking-wide drop-shadow-[1px_1px_0px_rgba(96,165,250,1)]">'Sort'</span> button opens a dropdown with options to sort the associated resources.
                        <br>
                        <span class="text-lg font-medium tracking-wide drop-shadow-[1px_1px_0px_rgba(96,165,250,1)]">"Alpha"</span> sorts by resource title alphabetically.
                        <br>
                        <span class="text-lg font-medium tracking-wide drop-shadow-[1px_1px_0px_rgba(96,165,250,1)]">"Chrono"</span> sorts by resource creation date, except for events, which are sorted by event start date.
                        <br>
                        Selecting the active sort option will alternate the sort order between ascending and descending.
                        <br>
                        The default sorting is by most recent.
                    </div>
                </div>
            </div>
        </div>
        <div x-data="{ openNotes: $wire.previousPage === 'notes' }"
            @close.stop="open = false"
            class="flex flex-col items-center w-full my-2"
        >
            <div @click="openNotes = !openNotes"
                class="w-full text-center text-xl tracking-wide text-gray-900 bg-blue-400 rounded-full cursor-pointer"
            >
                Notes
            </div>
            <div x-show="openNotes"
                class="flex flex-col text-left w-full p-2"
            >
                <div  class="w-full mt-2 bg-inherit">
                    New items can be input below the note's info box, where it says '<span class="font-medium tracking-wide drop-shadow-[1px_1px_0px_rgba(96,165,250,1)]">new item</span>'.
                    <br>
                    The 'new item' input can be moved to the bottom of the items list in the control panel via '<span class="font-medium tracking-wide drop-shadow-[1px_1px_0px_rgba(96,165,250,1)]">input at bottom</span>'.
                </div>
                <div class="flex items-center mt-2">
                    <span class="inline-block h-5 w-5 border border-gray-600 rounded-full mr-2"></span>
                    an item's check box.
                </div>
                <div  class="flex items-center my-2">
                    <span class="inline-block h-5 w-5 border border-green-800 bg-green-800 rounded-full mr-2"></span>
                    indicates checked.
                </div>
                <div>
                    Check boxes can be hidden and shown from the 'Settings' section in the control panel.
                    <br>
                    Items can be automatically moved to the bottom of the list when checked via '<span class="font-medium tracking-wide drop-shadow-[1px_1px_0px_rgba(96,165,250,1)]">move checked to bottom</span>' in the 'Settings' section of the control panel.
                    <br>
                    When 'move checked to bottom' is active, checked items can be restored to their original positions via '<span class="font-medium tracking-wide drop-shadow-[1px_1px_0px_rgba(96,165,250,1)]">leave checked in place</span>' in the 'Settings' section of the control panel.
                </div>
                <div class="my-2">
                    <span class="fa-solid fa-arrows-up-down mr-2 dark:text-gray-600"></span>
                    the item's handle for drag & drop sorting.
                    <br>
                    Sort by '<span class="font-medium tracking-wide drop-shadow-[1px_1px_0px_rgba(96,165,250,1)]">Draggable</span>' in the 'Sorting' section of the control panel.
                </div>
            </div>
        </div>
        <div x-data="{ openRecords: $wire.previousPage === 'records' }"
            @close.stop="open = false"
            class="flex flex-col items-center w-full my-2 bg-inherit"
        >
            <div @click="openRecords = !openRecords"
                class="w-full text-center text-xl tracking-wide text-gray-900 bg-blue-400 rounded-full cursor-pointer"
            >
                Records
            </div>
            <div x-show="openRecords"
                class="flex flex-col text-left w-full p-2 bg-inherit"
            >
                <div class="w-full mt-2 bg-inherit">
                    Below the title and info is a summary of the record;
                    <br>
                    Total, units, "of", and what is being measured.
                    <br>
                    'units' is where you enter the units of measurement for the record.
                    <br>
                    EX: oz, ounces, minutes, bottles, etc.
                    <br>
                    'measuring' is where you enter what is being measured.
                    <br>
                    EX: water, running, beer on the wall, etc.
                    <br>
                    'from' the date and time of the first entry.
                    <br>
                    'to' the date and time of the last entry.
                    <br>
                    The 'from' and 'to' dates can be changed to view and chart only entries from that date range.
                    <br>
                    These can be hidden and shown from the 'Settings' section in the control panel.
                </div>
                <div class="flex flex-col items-center mt-2 bg-inherit">
                    <hr class="w-full border-none h-px bg-gray-500 -mb-4 mt-6">
                    <div class="px-2 text-lg tracking-wider font-medium text-center bg-inherit">
                        Entries
                    </div>
                    <div class="w-full mt-1">
                        New entries can be input below the summary, where it says '<span class="font-medium tracking-wide drop-shadow-[1px_1px_0px_rgba(96,165,250,1)]">new entry</span>'.
                        <br>
                        The 'new entry' input can be moved to the bottom of the entries list in the control panel via '<span class="font-medium tracking-wide drop-shadow-[1px_1px_0px_rgba(96,165,250,1)]">input at bottom</span>'.
                        <br>
                        New entries will default to the present date and time.
                        <br>
                        The date and time can be edited where they are displayed, by typing over it or using the selector.
                        <br>
                        The date, time, and units can be hidden and shown from the 'Settings' section in the control panel.
                        <br>
                        The value of an existing entry can be edited by typing over it.
                    </div>
                </div>
                <div class="flex flex-col items-center my-2 bg-inherit">
                    <hr class="w-full border-none h-px bg-gray-500 -mb-4 mt-6">
                    <div class="px-2 text-lg tracking-wider font-medium text-center bg-inherit">
                        Charting
                    </div>
                    <div class="w-full">
                        Entries are displayed in a chart at the bottom of the page.
                        <br>
                        The chart's type and x-axis can be changed from the 'Charting' section of the control panel.
                        <br>
                        <span class="inline-block p-1 m-1 ml-0 border border-slate-600 rounded-md shadow-lg">
                            compare records
                        </span>
                        select another record to display on the chart with the current one(s).
                        <br>
                        Multiple records can be charted at once.
                    </div>
                </div>
            </div>
        </div>
        <div x-data="{ openEvents: $wire.previousPage === 'events' }"
            @close.stop="open = false"
            class="flex flex-col items-center w-full my-2 bg-inherit"
        >
            <div @click="openEvents = !openEvents"
                class="w-full text-center text-xl tracking-wide text-gray-900 bg-blue-400 rounded-full cursor-pointer"
            >
                Events
            </div>
            <div x-show="openEvents"
                class="flex flex-col w-full p-2 bg-inherit"
            >
                <div class="flex flex-col items-center my-2 bg-inherit">
                    <hr class="w-full border-none h-px bg-gray-500 -mb-4 mt-6">
                    <div class="px-2 text-lg tracking-wider font-medium text-center bg-inherit">
                        Events List Page
                    </div>
                    <div class="w-full mt-2">
                        Opens in calendar view on the present day.
                    </div>
                    <div class="flex justify-evenly items-center w-3/4 m-2">
                        <div class="fa-solid fa-angle-left text-blue-400"></div>
                        <div class="fa-solid fa-angles-left text-blue-400"></div>
                        <div class="w-3/5 tracking-wider text-center text-lg font-medium">Month Year</div>
                        <div class="fa-solid fa-angles-right text-blue-400"></div>
                        <div class="fa-solid fa-angle-right text-blue-400"></div>
                    </div>
                    <div class="w-full">
                        <span class="fa-solid fa-angle-left text-blue-400 mr-1"></span>
                        previous month.
                        <br>
                        <span class="fa-solid fa-angles-left text-blue-400 mr-1"></span>
                        previous year.
                        <br>
                        <span class="w-3/5 tracking-wider text-center text-lg font-medium mr-1 drop-shadow-[1px_1px_0px_rgba(96,165,250,1)]">'Month Year'</span>
                        scroll to specific month and year.
                        <br>
                        <span class="fa-solid fa-angles-right text-blue-400 mr-1"></span>
                        next year.
                        <br>
                        <span class="fa-solid fa-angle-right text-blue-400 mr-1"></span>
                        next month.
                    </div>
                    <div class="mt-2">
                        The selected day on the calendar is displayed larger and without a border. The present day will be selected when opening the page. Selecting another day will display that day's events in the list below the calendar.
                    </div>
                    <div class="w-full my-1">
                        <div class="my-1">
                            <span class="inline-flex flex-col items-center justify-center h-7 w-7 bg-blue-400 text-gray-900 rounded-full mr-2">#</span>
                            a solid blue day has events.
                        </div>
                        <div class="my-1">
                            <span class="inline-flex flex-col items-center justify-center h-7 w-7 border border-gray-500 rounded-full mr-2">#</span>
                            a transparent day has no events.
                        </div>
                    </div>
                    <div class="flex justify-evenly items-center w-full my-1">
                        <div class="fa-solid fa-magnifying-glass text-lg text-blue-400"></div>
                        <div class="tracking-wider text-lg font-medium">
                            Week Day
                            <span class="inline-block"> </span>
                            Month Day
                        </div>
                        <div class="fa-solid fa-location-crosshairs text-lg text-blue-400"></div>
                    </div>
                    <div class="w-full my-2">
                        <span class="fa-solid fa-magnifying-glass text-blue-400 mr-1"></span>
                        opens the event search bar.
                        <br>
                        <span class="tracking-wider font-medium mr-1 drop-shadow-[1px_1px_0px_rgba(96,165,250,1)]">
                            'Week Day
                            <span class="inline-block"> </span>
                            Month Day'
                        </span>
                        the selected day.
                        <br>
                        <span class="fa-solid fa-location-crosshairs text-blue-400 mr-1"></span>
                        returns to the present day.
                    </div>
                    <div>
                        If the selected day has events, they will be listed below the calendar. The title, start and end times, and the 'shared' symbol, if applicable, will be displayed for each event. Each is a link to view that event.
                    </div>
                </div>
                <div class="flex flex-col items-center w-full my-2 bg-inherit">
                    <hr class="w-full border-none h-px bg-gray-500 -mb-4 mt-6">
                    <div class="px-2 text-lg tracking-wider font-medium text-center bg-inherit">
                        Event View Page
                    </div>
                    <div class="flex justify-evenly items-center w-full my-2">
                        <div class="fa-regular fa-calendar-days text-lg text-blue-400"></div>
                        <div class="fa-solid fa-calendar-day text-lg text-blue-400"></div>
                        <div class="fa-regular fa-calendar-plus text-lg text-blue-400"></div>
                    </div>
                    <div class="w-full">
                        <span class="fa-regular fa-calendar-days text-blue-400 mr-1"></span>
                        navigates to the events list page.
                        <br>
                        <span class="fa-solid fa-calendar-day text-blue-400 mr-1"></span>
                        navigates to the events list page, with the current event's day selected.
                        <br>
                        <span class="fa-regular fa-calendar-plus text-blue-400 mr-1"></span>
                        creates a new event and navigates to it.
                    </div>
                    <div class="flex justify-evenly items-center w-full my-2">
                        <div class="flex flex-col justify-between items-center">
                            <div class="tracking-wide text-lg">Start</div>
                            <div>
                                mm/dd/yyyy
                                <span class="fa-regular fa-calendar text-blue-400 ml-2"></span>
                            </div>
                            <div>
                                --:-- --
                                <span class="fa-regular fa-clock text-blue-400 ml-2"></span>
                            </div>
                            <div class="px-2 py-1 mt-1 border border-blue-400 text-blue-400 rounded-md shadow-sm">
                                every
                            </div>
                        </div>
                        <div class="flex flex-col justify-between items-center">
                            <div class="tracking-wide text-lg">End</div>
                            <div>
                                mm/dd/yyyy
                                <span class="fa-regular fa-calendar text-blue-400 ml-2"></span>
                            </div>
                            <div>
                                --:-- --
                                <span class="fa-regular fa-clock text-blue-400 ml-2"></span>
                            </div>
                            <div class="px-2 py-1 mt-1 border border-blue-400 text-blue-400 rounded-md shadow-sm">
                                all day
                            </div>
                        </div>
                    </div>
                    <div class="w-full my-2">
                        <span class="mr-1 font-medium tracking-wide drop-shadow-[1px_1px_0px_rgba(96,165,250,1)]">mm/dd/yyyy</span>
                        is the date format.
                        <br>
                        The date can be typed here.
                        <br>
                        New events will default to the present day.
                        <br>
                        <span class="fa-regular fa-calendar text-blue-400 mr-1"></span>
                        opens the date selector.
                        <br>
                        <span class="mr-1 font-medium tracking-wide drop-shadow-[1px_1px_0px_rgba(96,165,250,1)]">--:-- --</span>
                        the time can be typed here.
                        <br>
                        <span class="fa-regular fa-clock text-blue-400 mr-1"></span>
                        opens the time selector.
                        <br>
                        <span class="inline-block mt-1 py-1 px-2 border border-blue-400 text-blue-400 text-sm rounded-md shadow-sm mr-1">every</span>
                        opens event recursion options.
                        <br>
                        If recursion is active, the button will be blue.
                    </div>
                    <div class="flex items-center rounded-lg my-2 border border-blue-400 overflow-hidden bg-inherit">
                        <div class="w-12 border-none text-center">
                            1
                        </div>
                        @foreach(['minute', 'hour', 'day', 'week', 'month'] as $every)
                        <div
                            wire:key="every-{{ $every }}"
                            class="border-l border-blue-400 text-blue-400 py-2 px-3"
                        >
                            {{ $every }}
                        </div>
                        @endforeach
                    </div>
                    <div class="w-full my-1">
                        These are the event recursion options.
                        <br>
                        The number on the left is the frequency for one of the intervals to the right. The selected interval will be blue.
                        <br>
                        <span class="tracking-widest font-medium text-xs">EXAMPLES:</span>
                        <br>
                        Every 1 month.
                        <br>
                        Every 3 weeks.
                        <br>
                        Every 36 hours.
                    </div>
                    <div class="w-full my-1">
                        <span class="inline-block mt-1 py-1 px-2 border border-blue-400 text-blue-400 text-sm rounded-md shadow-sm mr-1">all day</span>
                        sets event duration to all day.
                        <br>
                        The button will be blue if active.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
