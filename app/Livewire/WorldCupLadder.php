<?php

namespace App\Livewire;

use Livewire\Component;

class WorldCupLadder extends Component
{
    public function render()
    {
        return view('livewire.world-cup-ladder')
            ->extends('layouts.app')
            ->section('content');
    }
}
