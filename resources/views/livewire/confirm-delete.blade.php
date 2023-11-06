<div class="flex flex-col justify-evenly items-center h-screen bg-gray-900 text-gray-300">
    <button wire:click="delete" wire:click.outside="closeModal" class="text-red-500 p-2">
        {{ $this->message }}
    </button>
    <button>Cancel</button>
</div>
