<?php

namespace App\Livewire\Procurement;

use App\Models\Pengiriman;
use App\Models\SupplierEvaluation;
use App\Models\SupplierEvaluationDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class EvaluateSupplier extends Component
{
    public Pengiriman $pengiriman;
    public $evaluasi = [];
    public $catatanTambahan = '';

    protected $rules = [
        'evaluasi.*.*.penilaian' => 'required|integer|min:1|max:5',
        'evaluasi.*.*.keterangan' => 'nullable|string|max:500',
    ];

    public function mount(Pengiriman $pengiriman)
    {
        $this->pengiriman = $pengiriman->load([
            'details.bahanBakuSupplier.supplier',
            'purchasing',
        ]);
        
        $this->initializeEvaluasi();
    }

    public function initializeEvaluasi()
    {
        $criteria = SupplierEvaluation::getCriteriaStructure();
        
        foreach ($criteria as $kriteria => $subKriterias) {
            foreach ($subKriterias as $subKriteria) {
                $this->evaluasi[$kriteria][$subKriteria] = [
                    'penilaian' => null,
                    'keterangan' => '',
                ];
            }
        }
    }

    public function simpanEvaluasi()
    {
        Log::info('=== SIMPAN EVALUASI STARTED ===');
        Log::info('Evaluasi data:', $this->evaluasi);
        Log::info('Catatan Tambahan:', ['catatan' => $this->catatanTambahan]);

        $this->validate();
        Log::info('Validation passed');

        try {
            // Check if evaluation already exists
            $existingEvaluation = SupplierEvaluation::where('pengiriman_id', $this->pengiriman->id)->first();
            if ($existingEvaluation) {
                Log::warning('Evaluation already exists for this pengiriman', ['pengiriman_id' => $this->pengiriman->id]);
                session()->flash('error', 'Pengiriman ini sudah dievaluasi sebelumnya!');
                return redirect()->route('orders.show', $this->pengiriman->purchase_order_id);
            }

            // Get main supplier from pengiriman details
            $supplier = $this->pengiriman->details->first()?->bahanBakuSupplier?->supplier;
            Log::info('Supplier found:', ['supplier_id' => $supplier?->id, 'supplier_nama' => $supplier?->nama]);

            // Validate supplier exists
            if (!$supplier) {
                Log::error('No supplier found for this pengiriman');
                session()->flash('error', 'Supplier tidak ditemukan untuk pengiriman ini!');
                return;
            }

            DB::beginTransaction();
            Log::info('Database transaction started');

            // Create evaluation record
            $evaluationData = [
                'pengiriman_id' => $this->pengiriman->id,
                'supplier_id' => $supplier?->id,
                'evaluated_by' => Auth::id(),
                'evaluated_at' => now(),
                'catatan_tambahan' => $this->catatanTambahan,
            ];
            Log::info('Creating evaluation with data:', $evaluationData);
            
            $evaluation = SupplierEvaluation::create($evaluationData);
            Log::info('Evaluation created with ID:', ['id' => $evaluation->id]);

            // Save evaluation details
            $detailCount = 0;
            foreach ($this->evaluasi as $kriteria => $subKriterias) {
                foreach ($subKriterias as $subKriteria => $data) {
                    // Since validation requires all penilaian, we should always have a value
                    $detailData = [
                        'supplier_evaluation_id' => $evaluation->id,
                        'kriteria' => $kriteria,
                        'sub_kriteria' => $subKriteria,
                        'penilaian' => $data['penilaian'],
                        'keterangan' => $data['keterangan'] ?? '',
                    ];
                    Log::info('Creating detail:', $detailData);
                    
                    SupplierEvaluationDetail::create($detailData);
                    $detailCount++;
                }
            }
            Log::info('Created evaluation details count:', ['count' => $detailCount]);

            // Calculate score and rating
            Log::info('Calculating score and rating...');
            $evaluation->calculateScoreAndRating();
            Log::info('Score, rating, and review calculated:', [
                'total_score' => $evaluation->total_score,
                'rating' => $evaluation->rating,
                'ulasan' => $evaluation->ulasan
            ]);

            // Update pengiriman with rating and ulasan
            Log::info('Updating pengiriman...');
            $this->pengiriman->rating = $evaluation->rating;
            $this->pengiriman->ulasan = $evaluation->ulasan;
            $this->pengiriman->save();
            Log::info('Pengiriman updated successfully');

            DB::commit();
            Log::info('Database transaction committed');

            Log::info('=== SIMPAN EVALUASI SUCCESS ===');
            session()->flash('success', 'Evaluasi supplier berhasil disimpan!');
            
            return redirect()->route('orders.show', $this->pengiriman->purchase_order_id);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Database transaction rolled back');
            Log::error('=== SIMPAN EVALUASI ERROR ===');
            Log::error('Error message: ' . $e->getMessage());
            Log::error('Error trace: ' . $e->getTraceAsString());
            
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function getCriteriaStructureProperty()
    {
        return SupplierEvaluation::getCriteriaStructure();
    }

    public function render()
    {
        return view('livewire.procurement.evaluate-supplier');
    }
}
