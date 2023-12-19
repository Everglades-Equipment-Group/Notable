@props(['disabled' => false])

<div class="relative flex items-center rounded-md overflow-hidden w-auto bg-inherit">
    <input type="date"
        {{ $disabled ? 'disabled' : '' }}
        {!! $attributes->merge(['class' => 'p-1 border-none cursor-pointer bg-inherit dark:text-gray-300 focus:border focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm']) !!}
    >
    <span class="absolute right-1 w-5 h-5 text-center fa-regular fa-calendar text-blue-400 bg-slate-100 dark:bg-slate-900 pointer-events-none"></span>
</div>
