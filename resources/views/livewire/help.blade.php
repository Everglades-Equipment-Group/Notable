<?php

use function Livewire\Volt\{state, layout, mount, on};

layout('layouts.app');

?>

<div class="flex flex-col items-center py-6 px-3 bg-inherit dark:text-gray-300">
    <h1 class="text-2xl tracking-wide">Help</h1>
    <div class="w-full py-4">
        <div x-data="{ openBasics: true }"
            @close.stop="open = false"
            class="flex flex-col items-center w-full my-2"
        >
            <div @click="openBasics = !openBasics"
                class="w-full text-center text-xl tracking-wide text-gray-900 bg-blue-400 rounded-full cursor-pointer"
            >
                Basics
            </div>
            <div x-show="openBasics"
                class="flex flex-col text-left w-full p-2"
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
                <div
                    class="w-full"
                >
                    Creating
                </div>
                <div
                    class="w-full"
                >
                    Deleting
                </div>
                <div
                    class="w-full"
                >
                    Sharing
                </div>
            </div>
        </div>
        <div x-data="{ openDashboard: false }"
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
        <div x-data="{ openNotes: false }"
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
                
            </div>
        </div>
        <div x-data="{ openRecords: false }"
            @close.stop="open = false"
            class="flex flex-col items-center w-full my-2"
        >
            <div @click="openRecords = !openRecords"
                class="w-full text-center text-xl tracking-wide text-gray-900 bg-blue-400 rounded-full cursor-pointer"
            >
                Records
            </div>
            <div x-show="openRecords"
                class="flex flex-col text-left w-full p-2"
            >

            </div>
        </div>
        <div x-data="{ openEvents: false }"
            @close.stop="open = false"
            class="flex flex-col items-center w-full my-2"
        >
            <div @click="openEvents = !openEvents"
                class="w-full text-center text-xl tracking-wide text-gray-900 bg-blue-400 rounded-full cursor-pointer"
            >
                Events
            </div>
            <div x-show="openEvents"
                class="flex flex-col text-left w-full p-2"
            >

            </div>
        </div>
    </div>
</div>
