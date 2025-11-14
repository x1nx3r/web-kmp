# Order Flexible Suppliers Implementation Plan

## Overview
Transform the order system from fixed supplier selection to flexible supplier tracking, allowing purchasing to decide suppliers during fulfillment while marketing focuses on material requirements and client pricing.

## Current vs. Target Flow

### Current Flow (Fixed Supplier)
```
Marketing: Client → Material → Specific Supplier → Order
Purchasing: No flexibility, locked to chosen supplier
```

### Target Flow (Flexible Supplier)
```
Marketing: Client → Material → All Supplier Options → Order
Purchasing: Choose optimal supplier during fulfillment
```

## Database Changes Required

### 1. Create Alternative Suppliers Table

**Migration**: `create_order_detail_alternative_suppliers_table.php`

```php
Schema::create('order_detail_alternative_suppliers', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_detail_id')->constrained('order_details')->cascadeOnDelete();
    $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
    $table->foreignId('bahan_baku_supplier_id')->constrained('bahan_baku_supplier')->cascadeOnDelete();
    
    // Pricing at time of order creation
    $table->decimal('harga_supplier', 15, 2)->comment('Supplier price when order was created');
    $table->decimal('margin_per_unit', 12, 2)->comment('Calculated margin per unit');
    $table->decimal('margin_percentage', 5, 2)->comment('Margin percentage');
    
    // Recommendation system
    $table->boolean('is_recommended')->default(false)->comment('Marketing recommended supplier');
    $table->boolean('is_cheapest')->default(false)->comment('Cheapest option available');
    
    // Additional data
    $table->decimal('stok_available', 10, 2)->nullable()->comment('Stock level when order created');
    $table->text('notes')->nullable()->comment('Additional notes or considerations');
    
    $table->timestamps();
    
    // Indexes
    $table->index('order_detail_id');
    $table->index('supplier_id');
    $table->index(['is_recommended', 'is_cheapest']);
    
    // Unique constraint
    $table->unique(['order_detail_id', 'bahan_baku_supplier_id'], 'unique_detail_bahan_supplier');
});
```

### 2. Modify OrderDetail Table (Optional)

**Migration**: `update_order_details_for_flexible_suppliers.php`

```php
// Option A: Keep supplier_id as recommended supplier
Schema::table('order_details', function (Blueprint $table) {
    $table->boolean('supplier_locked')->default(false)->after('supplier_id')
        ->comment('Whether supplier selection is locked by purchasing');
    $table->text('supplier_selection_notes')->nullable()->after('supplier_locked')
        ->comment('Notes about supplier selection decision');
});

// Option B: Make supplier_id nullable (force purchasing to choose)
Schema::table('order_details', function (Blueprint $table) {
    $table->foreignId('supplier_id')->nullable()->change();
    $table->boolean('supplier_selected')->default(false)->after('supplier_id');
});
```

## Model Changes

### 1. Create OrderDetailAlternativeSupplier Model

**File**: `app/Models/OrderDetailAlternativeSupplier.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetailAlternativeSupplier extends Model
{
    protected $fillable = [
        'order_detail_id',
        'supplier_id', 
        'bahan_baku_supplier_id',
        'harga_supplier',
        'margin_per_unit',
        'margin_percentage',
        'is_recommended',
        'is_cheapest',
        'stok_available',
        'notes'
    ];

    protected $casts = [
        'harga_supplier' => 'decimal:2',
        'margin_per_unit' => 'decimal:2', 
        'margin_percentage' => 'decimal:2',
        'stok_available' => 'decimal:2',
        'is_recommended' => 'boolean',
        'is_cheapest' => 'boolean'
    ];

    // Relationships
    public function orderDetail()
    {
        return $this->belongsTo(OrderDetail::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function bahanBakuSupplier()
    {
        return $this->belongsTo(BahanBakuSupplier::class);
    }
}
```

### 2. Update OrderDetail Model

**File**: `app/Models/OrderDetail.php`

```php
// Add relationship
public function alternativeSuppliers()
{
    return $this->hasMany(OrderDetailAlternativeSupplier::class);
}

public function recommendedSupplier()
{
    return $this->hasOne(OrderDetailAlternativeSupplier::class)
        ->where('is_recommended', true);
}

public function cheapestSupplier()
{
    return $this->hasOne(OrderDetailAlternativeSupplier::class)
        ->where('is_cheapest', true);
}

// Business logic methods
public function getSupplierOptions()
{
    return $this->alternativeSuppliers()
        ->with(['supplier', 'bahanBakuSupplier'])
        ->orderBy('harga_supplier', 'asc')
        ->get();
}

public function lockSupplier($bahanBakuSupplierId, $notes = null)
{
    // Update main supplier_id to chosen supplier's parent
    $bahanBakuSupplier = BahanBakuSupplier::find($bahanBakuSupplierId);
    
    $this->update([
        'supplier_id' => $bahanBakuSupplier->supplier_id,
        'supplier_locked' => true,
        'supplier_selection_notes' => $notes
    ]);
}
```

## Livewire Component Changes

### 1. Update OrderCreate Component

**File**: `app/Livewire/Marketing/OrderCreate.php`

#### Remove Current Supplier Selection Logic
```php
// Remove these properties
// public $currentSupplier = null;
// public $currentHargaSupplier = 0;

// Add new properties
public $supplierOptions = [];
public $recommendedSupplier = null;
```

#### Update addOrderItem Method
```php
public function addOrderItem()
{
    $this->validate([
        'currentMaterial' => 'required',
        'currentQuantity' => 'required|numeric|min:0.01',
        'currentSatuan' => 'required|string',
        'currentHargaJual' => 'required|numeric|min:0',
    ]);
    
    $material = BahanBakuKlien::find($this->currentMaterial);
    if (!$material) {
        session()->flash('error', 'Material tidak ditemukan');
        return;
    }

    // Get all suppliers for this material
    $suppliers = $this->getAllSuppliersForMaterial($material->nama);
    
    if ($suppliers->isEmpty()) {
        session()->flash('error', 'Tidak ada supplier tersedia untuk material ini');
        return;
    }

    // Find recommended supplier (cheapest with stock)
    $recommendedSupplier = $this->findRecommendedSupplier($suppliers);
    
    // Calculate estimated costs using recommended supplier
    $estimatedCost = $recommendedSupplier['harga_per_unit'];
    $totalHpp = $this->currentQuantity * $estimatedCost;
    $totalHarga = $this->currentQuantity * $this->currentHargaJual;
    $marginPerUnit = $this->currentHargaJual - $estimatedCost;
    $totalMargin = $this->currentQuantity * $marginPerUnit;
    $marginPercentage = $this->currentHargaJual > 0 ? ($marginPerUnit / $this->currentHargaJual) * 100 : 0;
    
    // Create order detail
    $orderDetail = [
        'id' => uniqid(),
        'bahan_baku_klien_id' => $this->currentMaterial,
        'supplier_id' => $recommendedSupplier['supplier_parent_id'], // Recommended supplier
        'material_name' => $material->nama,
        'qty' => $this->currentQuantity,
        'satuan' => $this->currentSatuan,
        'harga_supplier' => $estimatedCost, // From recommended supplier
        'harga_jual' => $this->currentHargaJual,
        'total_hpp' => $totalHpp,
        'total_harga' => $totalHarga,
        'margin_per_unit' => $marginPerUnit,
        'total_margin' => $totalMargin,
        'margin_percentage' => $marginPercentage,
        'spesifikasi_khusus' => $this->currentSpesifikasi,
        'catatan' => $this->currentCatatan,
        'supplier_options' => $suppliers, // Store all options for display
        'recommended_supplier' => $recommendedSupplier
    ];
    
    $this->selectedOrderItems[] = $orderDetail;
    
    $this->updateTotals();
    $this->closeAddItemModal();
    
    session()->flash('success', 'Material berhasil ditambahkan dengan ' . $suppliers->count() . ' pilihan supplier');
}
```

#### Add Helper Methods
```php
private function getAllSuppliersForMaterial($materialName)
{
    // Use existing penawaran pattern
    $suppliers = Supplier::with([
        'bahanBakuSuppliers' => function ($q) use ($materialName) {
            $q->where('nama', 'like', '%' . $materialName . '%')
                ->whereNotNull('harga_per_satuan')
                ->orderBy('harga_per_satuan', 'asc');
        },
        'picPurchasing'
    ])->get();

    $supplierOptions = [];

    foreach ($suppliers as $supplier) {
        foreach ($supplier->bahanBakuSuppliers as $bahanBaku) {
            $supplierOptions[] = [
                'supplier_parent_id' => $supplier->id,
                'bahan_baku_supplier_id' => $bahanBaku->id,
                'supplier_name' => $supplier->nama,
                'pic_name' => $supplier->picPurchasing ? $supplier->picPurchasing->nama : null,
                'harga_per_unit' => $bahanBaku->harga_per_satuan,
                'satuan' => $bahanBaku->satuan,
                'stok' => $bahanBaku->stok,
            ];
        }
    }

    // Sort by price (cheapest first)
    usort($supplierOptions, function ($a, $b) {
        return $a['harga_per_unit'] <=> $b['harga_per_unit'];
    });

    return collect($supplierOptions);
}

private function findRecommendedSupplier($suppliers)
{
    // Find cheapest supplier with stock > 0
    $recommended = $suppliers->first(function ($supplier) {
        return $supplier['stok'] > 0;
    });
    
    // If no supplier has stock, use cheapest
    return $recommended ?: $suppliers->first();
}
```

### 2. Update Order Storage Method

**File**: `app/Livewire/Marketing/OrderCreate.php`

```php
public function saveOrder()
{
    $this->validate([
        'selectedKlien' => 'required',
        'selectedKlienCabang' => 'required',
        'tanggalOrder' => 'required|date',
        'priority' => 'required|in:rendah,normal,tinggi,mendesak',
    ]);

    if (empty($this->selectedOrderItems)) {
        session()->flash('error', 'Tambahkan minimal satu item order');
        return;
    }

    DB::beginTransaction();

    try {
        // Create main order
        $order = Order::create([
            'klien_id' => $this->selectedKlienId,
            'tanggal_order' => $this->tanggalOrder,
            'priority' => $this->priority,
            'status' => 'draft',
            'catatan' => $this->catatan,
            'total_amount' => $this->totalAmount,
            'total_margin' => $this->totalMargin,
            'user_id' => auth()->id(),
        ]);

        // Create order details and alternative suppliers
        foreach ($this->selectedOrderItems as $item) {
            $orderDetail = OrderDetail::create([
                'order_id' => $order->id,
                'bahan_baku_klien_id' => $item['bahan_baku_klien_id'],
                'supplier_id' => $item['supplier_id'], // Recommended supplier
                'qty' => $item['qty'],
                'satuan' => $item['satuan'],
                'harga_supplier' => $item['harga_supplier'],
                'total_hpp' => $item['total_hpp'],
                'harga_jual' => $item['harga_jual'],
                'total_harga' => $item['total_harga'],
                'margin_per_unit' => $item['margin_per_unit'],
                'total_margin' => $item['total_margin'],
                'margin_percentage' => $item['margin_percentage'],
                'spesifikasi_khusus' => $item['spesifikasi_khusus'],
                'catatan' => $item['catatan'],
                'status' => 'menunggu'
            ]);

            // Store all supplier alternatives
            foreach ($item['supplier_options'] as $index => $supplier) {
                $marginPerUnit = $item['harga_jual'] - $supplier['harga_per_unit'];
                $marginPercentage = $item['harga_jual'] > 0 ? ($marginPerUnit / $item['harga_jual']) * 100 : 0;
                
                OrderDetailAlternativeSupplier::create([
                    'order_detail_id' => $orderDetail->id,
                    'supplier_id' => $supplier['supplier_parent_id'],
                    'bahan_baku_supplier_id' => $supplier['bahan_baku_supplier_id'],
                    'harga_supplier' => $supplier['harga_per_unit'],
                    'margin_per_unit' => $marginPerUnit,
                    'margin_percentage' => $marginPercentage,
                    'is_recommended' => $supplier['bahan_baku_supplier_id'] == $item['recommended_supplier']['bahan_baku_supplier_id'],
                    'is_cheapest' => $index === 0, // First item is cheapest due to sorting
                    'stok_available' => $supplier['stok'],
                ]);
            }
        }

        DB::commit();

        session()->flash('success', 'Order berhasil disimpan dengan ' . count($this->selectedOrderItems) . ' item');
        
        return redirect()->route('orders.show', $order);

    } catch (\Exception $e) {
        DB::rollback();
        session()->flash('error', 'Gagal menyimpan order: ' . $e->getMessage());
    }
}
```

## View Updates

### 1. Update Items List Component

**File**: `resources/views/components/order/items-list-livewire.blade.php`

```blade
{{-- Show supplier options for each item --}}
@foreach($selectedOrderItems as $index => $item)
<div class="border rounded-lg p-4 mb-4">
    {{-- Item header --}}
    <div class="flex justify-between items-start mb-3">
        <div>
            <h4 class="font-medium text-gray-900">{{ $item['material_name'] }}</h4>
            <p class="text-sm text-gray-500">{{ $item['qty'] }} {{ $item['satuan'] }}</p>
        </div>
        <button wire:click="removeOrderItem('{{ $item['id'] }}')" 
                class="text-red-500 hover:text-red-700">
            <i class="fas fa-trash"></i>
        </button>
    </div>

    {{-- Pricing info --}}
    <div class="grid grid-cols-2 gap-4 mb-3">
        <div>
            <span class="text-sm text-gray-600">Harga Jual:</span>
            <span class="font-medium">{{ number_format($item['harga_jual']) }}/{{ $item['satuan'] }}</span>
        </div>
        <div>
            <span class="text-sm text-gray-600">Estimasi Margin:</span>
            <span class="font-medium">{{ number_format($item['margin_percentage'], 1) }}%</span>
        </div>
    </div>

    {{-- Supplier options --}}
    <div class="bg-gray-50 rounded p-3">
        <h5 class="font-medium text-gray-700 mb-2">
            Pilihan Supplier ({{ count($item['supplier_options']) }} tersedia)
        </h5>
        
        <div class="space-y-2">
            @foreach($item['supplier_options'] as $supplier)
            <div class="flex justify-between items-center text-sm">
                <div class="flex items-center">
                    @if($supplier['bahan_baku_supplier_id'] == $item['recommended_supplier']['bahan_baku_supplier_id'])
                        <i class="fas fa-star text-yellow-500 mr-2"></i>
                        <span class="font-medium">{{ $supplier['supplier_name'] }}</span>
                        <span class="ml-2 px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded">Rekomendasi</span>
                    @else
                        <i class="fas fa-building text-gray-400 mr-2"></i>
                        <span>{{ $supplier['supplier_name'] }}</span>
                    @endif
                </div>
                <div class="text-right">
                    <div class="font-medium">{{ number_format($supplier['harga_per_unit']) }}</div>
                    <div class="text-xs text-gray-500">
                        Stok: {{ number_format($supplier['stok'], 0) }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endforeach
```

### 2. Create Order Detail View

**File**: `resources/views/pages/marketing/orders/show.blade.php`

```blade
{{-- Order details with supplier alternatives --}}
@foreach($order->orderDetails as $detail)
<div class="bg-white rounded-lg border p-4 mb-4">
    <div class="flex justify-between items-start mb-4">
        <div>
            <h3 class="font-semibold">{{ $detail->bahanBakuKlien->nama }}</h3>
            <p class="text-gray-600">{{ $detail->qty }} {{ $detail->satuan }}</p>
        </div>
        
        @if(!$detail->supplier_locked)
        <span class="px-2 py-1 bg-yellow-100 text-yellow-700 text-xs rounded">
            Supplier Belum Dipilih
        </span>
        @else
        <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded">
            Supplier Terkunci
        </span>
        @endif
    </div>

    {{-- Current supplier selection --}}
    <div class="mb-4 p-3 bg-blue-50 rounded">
        <h4 class="font-medium text-blue-900 mb-2">Supplier Terpilih/Rekomendasi:</h4>
        <div class="flex justify-between">
            <span>{{ $detail->supplier->nama }}</span>
            <span class="font-medium">{{ number_format($detail->harga_supplier) }}/{{ $detail->satuan }}</span>
        </div>
    </div>

    {{-- Alternative suppliers --}}
    <div>
        <h4 class="font-medium text-gray-700 mb-2">Pilihan Supplier Alternatif:</h4>
        <div class="space-y-2">
            @foreach($detail->alternativeSuppliers as $alt)
            <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                <div class="flex items-center">
                    @if($alt->is_recommended)
                        <i class="fas fa-star text-yellow-500 mr-2"></i>
                    @elseif($alt->is_cheapest)
                        <i class="fas fa-dollar-sign text-green-500 mr-2"></i>
                    @else
                        <i class="fas fa-building text-gray-400 mr-2"></i>
                    @endif
                    
                    <div>
                        <span class="font-medium">{{ $alt->supplier->nama }}</span>
                        @if($alt->is_recommended)
                            <span class="ml-2 px-1 py-0.5 bg-yellow-100 text-yellow-700 text-xs rounded">Rekomendasi</span>
                        @endif
                        @if($alt->is_cheapest)
                            <span class="ml-2 px-1 py-0.5 bg-green-100 text-green-700 text-xs rounded">Termurah</span>
                        @endif
                    </div>
                </div>
                
                <div class="text-right">
                    <div class="font-medium">{{ number_format($alt->harga_supplier) }}</div>
                    <div class="text-xs text-gray-500">
                        Margin: {{ number_format($alt->margin_percentage, 1) }}%
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endforeach
```

## Purchasing Interface (Future Implementation)

### Order Fulfillment Page
- View all order details with supplier alternatives
- Select final supplier for each item
- Lock supplier selection
- Track fulfillment progress

### Features to Add Later
1. **Supplier Selection Interface** for purchasing team
2. **Price Comparison Tools** with real-time updates
3. **Stock Level Integration** with supplier inventory
4. **Delivery Timeline Comparison**
5. **Supplier Performance Metrics**

## Testing Plan

### 1. Unit Tests
- OrderDetailAlternativeSupplier model
- OrderDetail relationships
- Supplier option calculations

### 2. Feature Tests
- Order creation with multiple suppliers
- Supplier recommendation logic
- Data integrity constraints

### 3. Integration Tests
- Complete order flow from creation to fulfillment
- Alternative supplier data preservation
- Margin calculations accuracy

## Migration Order

1. **Create alternative suppliers table**
2. **Update OrderDetail table** (if needed)
3. **Update models and relationships**
4. **Update Livewire components**
5. **Update views and UI**
6. **Add tests**
7. **Create purchasing interface** (future)

## Benefits

### For Marketing
- ✅ Focus on client needs and pricing
- ✅ No pressure to choose "perfect" supplier
- ✅ All options preserved for reference
- ✅ Clear margin calculations

### For Purchasing  
- ✅ Full flexibility in supplier selection
- ✅ Real-time decision making
- ✅ Better negotiation position
- ✅ Optimal cost management

### For Business
- ✅ Better supplier relationships
- ✅ Improved margins through flexibility
- ✅ Risk mitigation through alternatives
- ✅ Data-driven decisions

---

*Implementation should be done incrementally, testing each component before moving to the next.*