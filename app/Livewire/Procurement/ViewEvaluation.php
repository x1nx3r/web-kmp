<?php

namespace App\Livewire\Procurement;

use App\Models\Pengiriman;
use App\Models\SupplierEvaluation;
use Livewire\Component;

class ViewEvaluation extends Component
{
    public Pengiriman $pengiriman;
    public ?SupplierEvaluation $evaluation = null;
    public $evaluationDetails = [];

    public function mount($pengirimanId)
    {
        $this->pengiriman = Pengiriman::with([
            'details.bahanBakuSupplier.supplier',
            'purchasing',
        ])->findOrFail($pengirimanId);
        
        // Load the evaluation with details
        $this->evaluation = SupplierEvaluation::where('pengiriman_id', $this->pengiriman->id)
            ->with(['details', 'evaluator', 'supplier'])
            ->first();
            
        if ($this->evaluation) {
            // Group evaluation details by kriteria
            $this->evaluationDetails = $this->evaluation->details->groupBy('kriteria');
        }
    }

    public function getCriteriaStructureProperty()
    {
        return SupplierEvaluation::getCriteriaStructure();
    }

    public function render()
    {
        return view('livewire.procurement.view-evaluation');
    }
}
