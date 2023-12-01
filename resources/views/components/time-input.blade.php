@props(['disabled' => false])

<div class="relative flex items-center rounded-md overflow-hidden w-auto">
    <input type="time"
        {{ $disabled ? 'disabled' : '' }}
        {!! $attributes->merge(['class' => 'p-1 border-none rounded-md shadow-sm cursor-pointer dark:bg-gray-900 dark:text-gray-300 focus:border focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600']) !!}
    >
    <span class="absolute right-3 w-fit h-5 text-center fa-regular fa-clock text-blue-400 bg-gray-900 pointer-events-none"></span>
</div>