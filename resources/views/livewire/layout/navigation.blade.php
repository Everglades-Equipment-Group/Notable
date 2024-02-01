<?php

use Livewire\Volt\{Component};
use App\Models\Notification;

new class extends Component
{
    public $hasNewNotifications = false;
    
    public function boot(): void
    {
        $this->hasNewNotifications = auth()->user()->notifications()->where('read', false)->exists();
    }

    public function logout(): void
    {
        auth()->guard('web')->logout();

        session()->invalidate();
        session()->regenerateToken();

        $this->redirect('/', navigate: true);
    }

    public function test(): void
    {
        auth()->user()->notifications()->create([
            'from_id' => auth()->user()->id,
            'event' => 'is testing',
            'resource_type' => 'note',
            'resource_id' => 112,
        ]);
        // dd($this->hasNewNotifications);
    }
}; ?>

<nav x-data="{ open: false }" class="sticky top-0 bg-inherit dark:bg-slate-800 border-b border-gray-100 dark:border-gray-700 z-20">
    <!-- Primary Navigation Menu -->
    <div class=" mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ route('dashboard') }}" wire:navigate>
                        <div class="flex items-center rotate-90 p-1">
                            <span class="text-3xl text-red-500"><img src="/favicon-32x32.png"  class="inline h-6 w-6" alt="&"/></span>
                            <span class="fa-solid fa-chevron-right text-lg pt-1 text-blue-400 -ml-2"></span>
                        </div>
                        <!-- <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" /> -->
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link href="notes" :active="request()->routeIs('notes')" wire:navigate>
                        {{ __('Notes') }}
                    </x-nav-link>
                    <x-nav-link href="records" :active="request()->routeIs('records')" wire:navigate>
                        {{ __('Records') }}
                    </x-nav-link>
                    <x-nav-link href="events" :active="request()->routeIs('events')" wire:navigate>
                        {{ __('Events') }}
                    </x-nav-link>
                    <x-nav-link href="notifications" :active="request()->routeIs('notifications')" wire:navigate>
                        {{ __('Notifications') }}
                    </x-nav-link>
                    <x-nav-link href="help" :active="request()->routeIs('help')" wire:navigate>
                        {{ __('Help') }}
                    </x-nav-link>
                </div>
            </div>
            <a href="{{ route('dashboard') }}" wire:navigate>
                <div class="text-blue-400 text-2xl py-4 pl-2 tracking-wider">Nota<span class="inline text-red-500"><img src="/favicon-16x16.png"  class="inline pb-px mb-px" alt="&"/></span>le</div>
            </a>
            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div x-data="{ name: '{{ auth()->user()->name }}' }" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>

                            <div class="ml-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile')" wire:navigate>
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <button wire:click="logout" class="w-full text-left">
                            <x-dropdown-link>
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </button>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-mr-1 flex items-center sm:hidden">
                <button @click="open = ! open" class="relative inline-flex items-center justify-center p-2 rounded-md text-blue-400 dark:text-blue-400 hover:text-red-500 dark:hover:text-red-500 hover:bg-slate-100 dark:hover:bg-slate-900 focus:outline-none focus:bg-slate-100 dark:focus:bg-slate-900 focus:text-red-500 dark:focus:text-blue-400 transition duration-150 ease-in-out">
                    <svg class="h-8 w-8" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    @if($this->hasNewNotifications)
                    <div class="absolute right-0 top-0 h-2 w-2 rounded-full bg-red-500"></div>
                    @endif
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link href="/notes" :active="request()->routeIs('notes')" wire:navigate>
                {{ __('Notes') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link href="/records" :active="request()->routeIs('records')" wire:navigate>
                {{ __('Records') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link href="/events" :active="request()->routeIs('events')" wire:navigate>
                {{ __('Events') }}
            </x-responsive-nav-link>
            <div class="relative flex items-center">
                <x-responsive-nav-link href="/notifications" :active="request()->routeIs('notifications')" wire:navigate class="border-x border-red-500">
                    {{ __('Notifications') }}
                </x-responsive-nav-link>
                @if($this->hasNewNotifications)
                <div class="absolute left-0 h-full w-1 bg-red-500"></div>
                @endif
            </div>
            <x-responsive-nav-link href="/help" :active="request()->routeIs('help')" wire:navigate>
                {{ __('Help') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200" x-data="{ name: '{{ auth()->user()->name }}' }" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                <div class="font-medium text-sm text-gray-500">{{ auth()->user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile')" wire:navigate>
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <button wire:click="logout" class="w-full text-left">
                    <x-responsive-nav-link>
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </button>
            </div>
        </div>
    </div>
</nav>
