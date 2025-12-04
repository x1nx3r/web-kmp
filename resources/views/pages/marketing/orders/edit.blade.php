@section('title', 'Edit Order - Kamil Maju Persada')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">Edit Order #{{ $order->nomor_order }}</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('orders.index') }}">Order</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('orders.show', $order->id) }}">{{ $order->nomor_order }}</a>
                            </li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('orders.show', $order->id) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Form -->
    <form action="{{ route('orders.update', $order->id) }}" method="POST" id="order-form">
        @csrf
        @method('PUT')

        <!-- Basic Info Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Informasi Umum
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="klien_id" class="form-label">Klien <span class="text-danger">*</span></label>
                            <select name="klien_id" id="klien_id" class="form-select @error('klien_id') is-invalid @enderror" required>
                                <option value="">Pilih Klien</option>
                                @foreach($kliens as $klien)
                                    <option value="{{ $klien->id }}" {{ old('klien_id', $order->klien_id) == $klien->id ? 'selected' : '' }}>
                                        {{ $klien->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('klien_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="tanggal_order" class="form-label">Tanggal Order <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_order" id="tanggal_order"
                                   class="form-control @error('tanggal_order') is-invalid @enderror"
                                   value="{{ old('tanggal_order', $order->tanggal_order->format('Y-m-d')) }}" required>
                            @error('tanggal_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="priority" class="form-label">Prioritas <span class="text-danger">*</span></label>
                            <select name="priority" id="priority" class="form-select @error('priority') is-invalid @enderror" required>
                                <option value="">Pilih Prioritas</option>
                                <option value="rendah" {{ old('priority', $order->priority) == 'rendah' ? 'selected' : '' }}>Rendah</option>
                                <option value="normal" {{ old('priority', $order->priority) == 'normal' ? 'selected' : '' }}>Normal</option>
                                <option value="tinggi" {{ old('priority', $order->priority) == 'tinggi' ? 'selected' : '' }}>Tinggi</option>
                                <option value="mendesak" {{ old('priority', $order->priority) == 'mendesak' ? 'selected' : '' }}>Mendesak</option>
                            </select>
                            @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="catatan" class="form-label">Catatan</label>
                            <textarea name="catatan" id="catatan" rows="3"
                                      class="form-control @error('catatan') is-invalid @enderror"
                                      placeholder="Catatan tambahan untuk order ini">{{ old('catatan', $order->catatan) }}</textarea>
                            @error('catatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Details Card -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-list me-2"></i>
                    Detail Order
                </h6>
                <button type="button" class="btn btn-sm btn-success" id="add-detail">
                    <i class="fas fa-plus me-1"></i>
                    Tambah Item
                </button>
            </div>
            <div class="card-body">
                <div id="order-details">
                    @foreach($order->orderDetails as $index => $detail)
                        <div class="order-detail-item border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h6 class="mb-0">Item Order #<span class="item-number">{{ $index + 1 }}</span></h6>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-detail">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Material <span class="text-danger">*</span></label>
                                        <select name="order_details[{{ $index }}][bahan_baku_klien_id]" class="form-select material-select" required>
                                            <option value="">Pilih Material</option>
                                            @foreach($materials as $material)
                                                <option value="{{ $material->id }}" {{ $detail->bahan_baku_klien_id == $material->id ? 'selected' : '' }}>
                                                    {{ $material->nama }} - {{ $material->klien->nama }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Supplier <span class="text-danger">*</span></label>
                                        <select name="order_details[{{ $index }}][supplier_id]" class="form-select supplier-select" required>
                                            <option value="">Pilih Supplier</option>
                                            @foreach($suppliers as $supplier)
                                                <option value="{{ $supplier->id }}" {{ $detail->supplier_id == $supplier->id ? 'selected' : '' }}>
                                                    {{ $supplier->nama }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Qty <span class="text-danger">*</span></label>
                                        <input type="number" name="order_details[{{ $index }}][qty]" class="form-control qty-input"
                                               step="0.01" min="0.01" value="{{ $detail->qty }}" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Satuan <span class="text-danger">*</span></label>
                                        <input type="text" name="order_details[{{ $index }}][satuan]" class="form-control"
                                               placeholder="kg, ton, box, dll" value="{{ $detail->satuan }}" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Harga Supplier <span class="text-danger">*</span></label>
                                        <input type="number" name="order_details[{{ $index }}][harga_supplier]" class="form-control supplier-price"
                                               step="0.01" min="0" value="{{ $detail->harga_supplier }}" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Harga Jual <span class="text-danger">*</span></label>
                                        <input type="number" name="order_details[{{ $index }}][harga_jual]" class="form-control selling-price"
                                               step="0.01" min="0" value="{{ $detail->harga_jual }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Spesifikasi Khusus</label>
                                        <textarea name="order_details[{{ $index }}][spesifikasi_khusus]" class="form-control" rows="2"
                                                  placeholder="Spesifikasi khusus untuk item ini">{{ $detail->spesifikasi_khusus }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Catatan</label>
                                        <textarea name="order_details[{{ $index }}][catatan]" class="form-control" rows="2"
                                                  placeholder="Catatan untuk item ini">{{ $detail->catatan }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Margin Info -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="margin-info p-2 bg-light rounded">
                                        <small class="text-muted">
                                            <strong>Margin:</strong>
                                            <span class="margin-amount">Rp {{ number_format($detail->margin, 0, ',', '.') }}</span>
                                            (<span class="margin-percentage">{{ number_format($detail->margin_percentage, 1) }}%</span>)
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @error('order_details')
                    <div class="alert alert-danger mt-3">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Submit Button -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        Update Order
                    </button>
                    <a href="{{ route('orders.show', $order->id) }}" class="btn btn-secondary">
                        Batal
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Order Detail Template (same as create page) -->
<template id="order-detail-template">
    <div class="order-detail-item border rounded p-3 mb-3">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <h6 class="mb-0">Item Order #<span class="item-number">1</span></h6>
            <button type="button" class="btn btn-sm btn-outline-danger remove-detail">
                <i class="fas fa-trash"></i>
            </button>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Material <span class="text-danger">*</span></label>
                    <select name="order_details[INDEX][bahan_baku_klien_id]" class="form-select material-select" required>
                        <option value="">Pilih Material</option>
                        @foreach($materials as $material)
                            <option value="{{ $material->id }}">
                                {{ $material->nama }} - {{ $material->klien->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Supplier <span class="text-danger">*</span></label>
                    <select name="order_details[INDEX][supplier_id]" class="form-select supplier-select" required>
                        <option value="">Pilih Supplier</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-label">Qty <span class="text-danger">*</span></label>
                    <input type="number" name="order_details[INDEX][qty]" class="form-control qty-input"
                           step="0.01" min="0.01" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-label">Satuan <span class="text-danger">*</span></label>
                    <input type="text" name="order_details[INDEX][satuan]" class="form-control"
                           placeholder="kg, ton, box, dll" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-label">Harga Supplier <span class="text-danger">*</span></label>
                    <input type="number" name="order_details[INDEX][harga_supplier]" class="form-control supplier-price"
                           step="0.01" min="0" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-label">Harga Jual <span class="text-danger">*</span></label>
                    <input type="number" name="order_details[INDEX][harga_jual]" class="form-control selling-price"
                           step="0.01" min="0" required>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Spesifikasi Khusus</label>
                    <textarea name="order_details[INDEX][spesifikasi_khusus]" class="form-control" rows="2"
                              placeholder="Spesifikasi khusus untuk item ini"></textarea>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Catatan</label>
                    <textarea name="order_details[INDEX][catatan]" class="form-control" rows="2"
                              placeholder="Catatan untuk item ini"></textarea>
                </div>
            </div>
        </div>

        <!-- Margin Info -->
        <div class="row">
            <div class="col-12">
                <div class="margin-info p-2 bg-light rounded">
                    <small class="text-muted">
                        <strong>Margin:</strong>
                        <span class="margin-amount">Rp 0</span>
                        (<span class="margin-percentage">0%</span>)
                    </small>
                </div>
            </div>
        </div>
    </div>
</template>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let detailIndex = {{ count($order->orderDetails) }};

    const addDetailBtn = document.getElementById('add-detail');
    const orderDetailsContainer = document.getElementById('order-details');
    const template = document.getElementById('order-detail-template');

    // Initialize existing details
    initializeExistingDetails();

    addDetailBtn.addEventListener('click', addDetail);

    function initializeExistingDetails() {
        const existingItems = orderDetailsContainer.querySelectorAll('.order-detail-item');
        existingItems.forEach((item, index) => {
            setupDetailItemEvents(item);
            calculateMargin(item);
        });
    }

    function addDetail() {
        const clone = template.content.cloneNode(true);

        // Replace INDEX placeholder with actual index
        const html = clone.firstElementChild.outerHTML.replace(/INDEX/g, detailIndex);
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;
        const detailElement = tempDiv.firstElementChild;

        // Update item number
        detailElement.querySelector('.item-number').textContent = detailIndex + 1;

        // Setup events
        setupDetailItemEvents(detailElement);

        orderDetailsContainer.appendChild(detailElement);
        detailIndex++;
        updateItemNumbers();
    }

    function setupDetailItemEvents(detailElement) {
        // Remove button
        const removeBtn = detailElement.querySelector('.remove-detail');
        removeBtn.addEventListener('click', function() {
            if (orderDetailsContainer.children.length > 1) {
                detailElement.remove();
                updateItemNumbers();
            }
        });

        // Margin calculation listeners
        const qtyInput = detailElement.querySelector('.qty-input');
        const supplierPrice = detailElement.querySelector('.supplier-price');
        const sellingPrice = detailElement.querySelector('.selling-price');

        [qtyInput, supplierPrice, sellingPrice].forEach(input => {
            input.addEventListener('input', function() {
                calculateMargin(detailElement);
            });
        });
    }

    function updateItemNumbers() {
        const items = orderDetailsContainer.querySelectorAll('.order-detail-item');
        items.forEach((item, index) => {
            item.querySelector('.item-number').textContent = index + 1;
        });
    }

    function calculateMargin(element) {
        const qty = parseFloat(element.querySelector('.qty-input').value) || 0;
        const supplierPrice = parseFloat(element.querySelector('.supplier-price').value) || 0;
        const sellingPrice = parseFloat(element.querySelector('.selling-price').value) || 0;

        const totalCost = qty * supplierPrice;
        const totalRevenue = qty * sellingPrice;
        const margin = totalRevenue - totalCost;
        const marginPercentage = totalCost > 0 ? (margin / totalCost * 100) : 0;

        const marginAmount = element.querySelector('.margin-amount');
        const marginPercentageSpan = element.querySelector('.margin-percentage');

        marginAmount.textContent = 'Rp ' + margin.toLocaleString('id-ID');
        marginPercentageSpan.textContent = marginPercentage.toFixed(1) + '%';

        // Color coding
        const marginInfo = element.querySelector('.margin-info');
        marginInfo.className = 'margin-info p-2 rounded ';

        if (marginPercentage >= 20) {
            marginInfo.classList.add('bg-success-subtle', 'text-success');
        } else if (marginPercentage >= 10) {
            marginInfo.classList.add('bg-warning-subtle', 'text-warning');
        } else if (marginPercentage >= 0) {
            marginInfo.classList.add('bg-info-subtle', 'text-info');
        } else {
            marginInfo.classList.add('bg-danger-subtle', 'text-danger');
        }
    }
});
</script>
@endpush
@endsection
