<?php

namespace App\Livewire;

use LivewireUI\Modal\ModalComponent;

class HelpModal extends ModalComponent
{

    public $previousPage = '';

    public function mount()
    {
        $this->previousPage = app('router')->getRoutes()->match(app('request')->create(url()->previous()))->getName();
    }

    public function render()
    {
        return view('livewire.help-modal');
    }
}
