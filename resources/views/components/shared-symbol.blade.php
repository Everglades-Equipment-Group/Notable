@props(['direction'])

<span class="inline-flex items-center text-center px-1 text-red-500">
    @if($direction)
        &<i class="fa-solid fa-angle-right text-blue-400"></i>
    @else
        <i class="fa-solid fa-angle-left text-blue-400"></i>&
    @endif
</span>
