<?php

namespace App\Livewire\Accounting;

use Livewire\Component;

class CatatanPiutangIndex extends Component
{
    public function render()
    {
        return view('livewire.accounting.catatan-piutang-index')
            ->layout('layouts.app');
    }
}
