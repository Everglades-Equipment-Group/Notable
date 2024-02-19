<?php

// save sorting preference
// sort by shared (owned?)

use function Livewire\Volt\{booted, mount, on, state};

?>

<div class="flex flex-col items-center p-5 h-max bg-inherit">
    @foreach (['note', 'record', 'event'] as $type)
        <livewire:preview
            type="{{ $type }}"
            wire:key="{{ $type }}sPreview"
        />
    @endforeach
</div>