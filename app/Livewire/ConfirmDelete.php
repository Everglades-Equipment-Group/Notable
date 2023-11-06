<?php

namespace App\Livewire;

use LivewireUI\Modal\ModalComponent;

class ConfirmDelete extends ModalComponent
{
    public $id = '';
    public $type;
    public $verb = 'delete';
    public $message = '';

    public function mount($type, $message)
    {
        $this->type = $type;
        $this->message = $message;
    }

    public function delete()
    {
        $this->dispatch($this->verb.'-'.$this->type, id: $this->id);
        $this->closeModal();
    }

    public static function destroyOnClose(): bool
    {
        return true;
    }

    public function render()
    {
        return view('livewire.confirm-delete');
    }
}
