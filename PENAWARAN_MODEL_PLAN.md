# Penawaran System - Database Design & Implementation Plan

## 📋 Overview
The Penawaran (Quotation) system manages quotations sent to clients, tracking materials, suppliers, pricing, margins, and approval workflow.

---

## 🗄️ Database Tables

### 1. **`penawaran` (Main Quotation Table)**

**Purpose:** Store main quotation information

**Columns:**
```php
- id (bigint, primary key, auto_increment)
- nomor_penawaran (string, unique) // e.g., PNW-2025-001
- klien_id (bigint, foreign key → klien.id)
- tanggal (date) // Quotation date
- status (enum) // ['draft', 'menunggu_verifikasi', 'disetujui', 'ditolak', 'expired']
- total_revenue (decimal 15,2) // Total client prices
- total_cost (decimal 15,2) // Total supplier costs
- total_profit (decimal 15,2) // total_revenue - total_cost
- margin_percentage (decimal 5,2) // (total_profit / total_revenue) * 100
- notes (text, nullable) // Additional notes
- created_by (bigint, foreign key → users.id) // Marketing user who created
- verified_by (bigint, nullable, foreign key → users.id) // Manager who verified
- verified_at (timestamp, nullable)
- rejected_reason (text, nullable)
- expired_at (date, nullable) // Quotation validity date
- created_at (timestamp)
- updated_at (timestamp)
- deleted_at (timestamp, nullable) // Soft delete
```

**Indexes:**
- `nomor_penawaran` (unique)
- `klien_id` (foreign key)
- `status` (for filtering)
- `tanggal` (for sorting)
- `created_by`, `verified_by` (foreign keys)

---

### 2. **`penawaran_detail` (Quotation Line Items)**

**Purpose:** Store individual material entries in a quotation
**Note:** Each material can have its own supplier - one quotation can have materials from multiple different suppliers

**Columns:**
```php
- id (bigint, primary key, auto_increment)
- penawaran_id (bigint, foreign key → penawaran.id, cascade on delete)
- bahan_baku_klien_id (bigint, foreign key → bahan_baku_klien.id)
- supplier_id (bigint, foreign key → supplier.id) // Selected supplier for THIS material
- bahan_baku_supplier_id (bigint, foreign key → bahan_baku_supplier.id) // Specific supplier material
- quantity (decimal 10,2) // Amount ordered
- satuan (string) // Unit (copied from bahan_baku_klien for reference)
- nama_material (string) // Material name (copied for reference)
- harga_klien (decimal 15,2) // Client price per unit
- harga_supplier (decimal 15,2) // Supplier price per unit (at time of quotation)
- is_custom_price (boolean, default false) // If custom client price was used
- subtotal_revenue (decimal 15,2) // quantity * harga_klien
- subtotal_cost (decimal 15,2) // quantity * harga_supplier
- subtotal_profit (decimal 15,2) // subtotal_revenue - subtotal_cost
- margin_percentage (decimal 5,2) // (subtotal_profit / subtotal_revenue) * 100
- notes (text, nullable) // Item-specific notes
- created_at (timestamp)
- updated_at (timestamp)
```

**Indexes:**
- `penawaran_id` (foreign key)
- `bahan_baku_klien_id` (foreign key)
- `supplier_id` (foreign key)
- `bahan_baku_supplier_id` (foreign key)

**Important Design Notes:**
- Each row represents ONE material from ONE supplier
- A quotation with 5 materials from 3 different suppliers = 5 detail rows
- Material A from Supplier X = 1 row
- Material B from Supplier X = 1 row (same supplier, different material)
- Material C from Supplier Y = 1 row (different supplier)
- This allows complete flexibility in supplier selection per material

---

### 3. **`penawaran_alternative_suppliers` (Optional - for tracking alternatives)**

**Purpose:** Track alternative supplier options considered for each material

**Columns:**
```php
- id (bigint, primary key, auto_increment)
- penawaran_detail_id (bigint, foreign key → penawaran_detail.id, cascade on delete)
- supplier_id (bigint, foreign key → supplier.id)
- harga_supplier (decimal 15,2)
- is_selected (boolean, default false) // If this was the chosen supplier
- notes (text, nullable)
- created_at (timestamp)
- updated_at (timestamp)
```

**Indexes:**
- `penawaran_detail_id` (foreign key)
- `supplier_id` (foreign key)

---

## 🏗️ Model Relationships

### **Penawaran Model**
```php
namespace App\Models;

class Penawaran extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'penawaran';
    
    // Relationships
    public function klien(): BelongsTo
    public function details(): HasMany (→ PenawaranDetail)
    public function createdBy(): BelongsTo (→ User)
    public function verifiedBy(): BelongsTo (→ User)
    
    // Scopes
    public function scopeByStatus($query, $status)
    public function scopeByKlien($query, $klienId)
    public function scopeRecent($query)
    public function scopePending($query) // status = 'menunggu_verifikasi'
    public function scopeApproved($query) // status = 'disetujui'
    
    // Accessors
    public function getFormattedTanggalAttribute()
    public function getStatusBadgeAttribute()
    public function getTotalMaterialsAttribute()
    
    // Methods
    public function calculateTotals(): void
    public function generateNomorPenawaran(): string
    public function submitForVerification(): bool
    public function approve(User $user): bool
    public function reject(User $user, string $reason): bool
    public function getUniqueSuppliers(): Collection // Get all unique suppliers in this quotation
    public function getSuppliersCountAttribute(): int // Count of unique suppliers
}
```

### **PenawaranDetail Model**
```php
namespace App\Models;

class PenawaranDetail extends Model
{
    protected $table = 'penawaran_detail';
    
    // Relationships
    public function penawaran(): BelongsTo
    public function bahanBakuKlien(): BelongsTo
    public function supplier(): BelongsTo // The selected supplier for this material
    public function bahanBakuSupplier(): BelongsTo // The specific supplier material entry
    public function alternativeSuppliers(): HasMany (→ PenawaranAlternativeSupplier)
    
    // Accessors
    public function getSupplierNameAttribute(): string
    public function getMaterialNameAttribute(): string
    public function getSupplierPicAttribute(): ?User
    
    // Methods
    public function calculateSubtotals(): void
    public function calculateMargin(): float
}
```

### **PenawaranAlternativeSupplier Model**
```php
namespace App\Models;

class PenawaranAlternativeSupplier extends Model
{
    protected $table = 'penawaran_alternative_suppliers';
    
    // Relationships
    public function penawaranDetail(): BelongsTo
    public function supplier(): BelongsTo
}
```

---

## 🔗 Relationship Diagram

```
User (Marketing)
  ↓ created_by
Penawaran ←─ Klien
  ↓ has many
PenawaranDetail
  ├─→ BahanBakuKlien (material info)
  ├─→ Supplier (chosen supplier)
  └─→ PenawaranAlternativeSupplier (alternative options)
        └─→ Supplier

User (Manager)
  ↓ verified_by
Penawaran
```

---

## 🎮 Controller Structure

### **PenawaranController**
```php
namespace App\Http\Controllers\Marketing;

class PenawaranController extends Controller
{
    // Display methods
    public function index() // List all quotations (riwayat)
    public function create() // Show create form (analisis)
    public function show(Penawaran $penawaran) // View single quotation
    public function edit(Penawaran $penawaran) // Edit draft quotation
    
    // CRUD operations
    public function store(Request $request) // Save new quotation
    public function update(Request $request, Penawaran $penawaran) // Update draft
    public function destroy(Penawaran $penawaran) // Soft delete
    
    // Workflow actions
    public function submit(Penawaran $penawaran) // Submit for verification
    public function approve(Penawaran $penawaran) // Approve quotation
    public function reject(Request $request, Penawaran $penawaran) // Reject with reason
    
    // Export/Print
    public function exportPdf(Penawaran $penawaran) // Generate PDF
    public function exportExcel(Penawaran $penawaran) // Generate Excel
    
    // API endpoints for Livewire
    public function calculateMargin(Request $request) // Calculate on-the-fly
    public function getSupplierPrices(Request $request) // Get supplier options
}
```

---

## 📝 Migration Files to Create

1. **`2025_10_11_000001_create_penawaran_table.php`**
2. **`2025_10_11_000002_create_penawaran_detail_table.php`**
3. **`2025_10_11_000003_create_penawaran_alternative_suppliers_table.php`**

---

## 🔄 Workflow States

```
[Draft] 
  ↓ submit()
[Menunggu Verifikasi]
  ↓ approve() / reject()
[Disetujui] / [Ditolak]
  ↓ (automatic after expiry date)
[Expired]
```

**Status Enum Values:**
- `draft` - Being created, can be edited
- `menunggu_verifikasi` - Submitted, awaiting manager approval
- `disetujui` - Approved by manager
- `ditolak` - Rejected by manager
- `expired` - Past expiry date

---

## 🧮 Business Logic

### **Multi-Supplier Handling:**

**Scenario Example:**
```
Quotation PNW-2025-001 for Klien "PT Maju"
├─ Detail 1: Material A (100 kg) → Supplier X @ Rp 5000
├─ Detail 2: Material B (50 pcs) → Supplier X @ Rp 3000
├─ Detail 3: Material C (200 m) → Supplier Y @ Rp 8000
├─ Detail 4: Material D (75 kg) → Supplier Z @ Rp 6000
└─ Detail 5: Material E (30 pcs) → Supplier Y @ Rp 4500

Result: 5 materials from 3 different suppliers in ONE quotation
Suppliers: X (2 materials), Y (2 materials), Z (1 material)
```

**Database Representation:**
- 1 row in `penawaran` table (the quotation)
- 5 rows in `penawaran_detail` table (each material + supplier pair)
- Each detail row has its own `supplier_id` and `bahan_baku_supplier_id`
- Alternative suppliers tracked separately in `penawaran_alternative_suppliers`

**UI Considerations:**
- Group materials by supplier in display
- Show "3 Suppliers" badge on quotation card
- Supplier breakdown: "Supplier X (2 items), Supplier Y (2 items), Supplier Z (1 item)"
- Allow filtering/sorting by supplier in detail view

### **Automatic Calculations:**
1. **PenawaranDetail level:**
   - `subtotal_revenue = quantity × harga_klien`
   - `subtotal_cost = quantity × harga_supplier`
   - `subtotal_profit = subtotal_revenue - subtotal_cost`
   - `margin_percentage = (subtotal_profit / subtotal_revenue) × 100`

2. **Penawaran level (sum of all details):**
   - `total_revenue = SUM(details.subtotal_revenue)`
   - `total_cost = SUM(details.subtotal_cost)`
   - `total_profit = total_revenue - total_cost`
   - `margin_percentage = (total_profit / total_revenue) × 100`

### **Nomor Penawaran Generation:**
Format: `PNW-{YEAR}-{SEQUENCE}`
- Example: `PNW-2025-001`, `PNW-2025-002`
- Sequence resets annually

### **Validation Rules:**
- Minimum 1 material required
- All materials must have supplier selected
- Quantities must be > 0
- Client prices must be ≥ supplier prices (warning, not error)
- Margin should typically be 10-30% (warning if outside)

---

## 🔐 Permissions & Access Control

**Roles:**
- **Marketing** - Can create, edit (draft), submit quotations
- **Manager** - Can approve/reject quotations
- **Admin** - Full access

**Rules:**
- Only creator can edit draft quotations
- Only manager can approve/reject
- Cannot edit after approval
- Can view history after approval/rejection

---

## 📊 Additional Features to Consider

### **Phase 1 (Essential):**
- ✅ Create quotation with materials
- ✅ Calculate margins automatically
- ✅ Save as draft
- ✅ Submit for verification
- ✅ Approve/reject workflow
- ✅ View history

### **Phase 2 (Enhancement):**
- 📧 Email notifications on approval/rejection
- 📄 PDF export with company branding
- 📈 Margin analytics dashboard
- 🔄 Clone existing quotation
- 📝 Quotation templates
- 💬 Comments/notes thread

### **Phase 3 (Advanced):**
- 🔗 **Convert to Purchase Order(s)** - Split by supplier automatically
  - One approved quotation → Multiple POs (one per supplier)
  - PNW-2025-001 with 3 suppliers → PO-001 (Supplier X), PO-002 (Supplier Y), PO-003 (Supplier Z)
  - Maintain reference back to original quotation
- 📊 Supplier performance tracking
- 🎯 Target margin recommendations  
- 📅 Expiry date reminders
- 🔍 Advanced search & filters (by supplier, date range, margin %)
- 📈 Profitability reports
- 📦 Delivery tracking per supplier

---

## 🚀 Implementation Steps

### **Step 1: Database Setup**
1. Create migration files
2. Define foreign key constraints
3. Add indexes for performance
4. Run migrations

### **Step 2: Models**
1. Create Penawaran model with relationships
2. Create PenawaranDetail model
3. Create PenawaranAlternativeSupplier model
4. Add mutators and accessors
5. Create factories for testing

### **Step 3: Update Existing Models**
1. Add `penawaran` relationship to Klien
2. Add `penawaranDetails` to BahanBakuKlien
3. Add `penawaranDetails` to Supplier

### **Step 4: Controllers & Routes**
1. Create PenawaranController
2. Define RESTful routes
3. Add API endpoints for Livewire

### **Step 5: Update Livewire Components**
1. Modify Penawaran.php to save to database
2. Update RiwayatPenawaran.php to read from database
3. Remove dummy data
4. Add validation rules

### **Step 6: Testing**
1. Unit tests for models
2. Feature tests for workflows
3. Browser tests for UI

### **Step 7: Seeders (Optional)**
1. Create sample quotations for testing
2. Different statuses and scenarios

---

## 📈 Data Flow Example

### Creating a Quotation:
```
1. User selects Klien → sets $selectedKlien
2. User adds Materials → adds to $selectedMaterials[]
   - Each material shows all available suppliers
   - User selects preferred supplier for EACH material independently
   - Material A → Supplier X selected
   - Material B → Supplier Y selected  
   - Material C → Supplier X selected (same supplier, different material)
3. User sets quantities → calculates on-the-fly
4. System fetches supplier prices from bahan_baku_supplier
5. User can override with custom client prices
6. User clicks "Buat Penawaran" →
   a. Create Penawaran record (status: 'draft')
   b. Generate nomor_penawaran
   c. Create PenawaranDetail for each material with its selected supplier
      - Detail 1: material_id=1, supplier_id=5, bahan_baku_supplier_id=12
      - Detail 2: material_id=2, supplier_id=3, bahan_baku_supplier_id=8
      - Detail 3: material_id=3, supplier_id=5, bahan_baku_supplier_id=15 (same supplier as detail 1)
   d. Calculate and store all totals (per detail + penawaran total)
   e. Save alternative suppliers for each material
7. System groups materials by supplier for PO generation later
8. Redirect to quotation detail page
9. User can submit for verification
```

---

## 🎯 Key Considerations

1. **Multi-Supplier Architecture:**
   - Each material independently selects its supplier
   - One quotation can have 1 to N suppliers
   - Details table stores supplier_id per row (not at penawaran level)
   - Alternative suppliers tracked per material, not per quotation
   - PO generation will need to split by supplier later
   - Delivery coordination may require multiple shipments

2. **Performance:**
   - Eager load relationships when listing: `with(['details.supplier', 'details.bahanBakuKlien'])`
   - Cache expensive calculations
   - Index frequently queried columns (supplier_id in details table)
   - Consider adding computed columns for supplier counts

3. **Data Integrity:**
   - Use database transactions for multi-table operations
   - Soft delete for audit trail
   - Log status changes
   - Validate supplier_id matches bahan_baku_supplier.supplier_id

4. **User Experience:**
   - Real-time margin calculations
   - Clear status indicators
   - Easy navigation between related records
   - Group materials by supplier in display views
   - Show supplier summary: "3 Suppliers: X (2 items), Y (2 items), Z (1 item)"
   - Allow quick filtering by supplier

5. **Business Rules:**
   - Configurable minimum margins
   - Approval workflow flexibility
   - Expiry date handling

---

## 📚 Related Models Reference

**Existing Models to Reference:**
- `Klien` - Client information
- `BahanBakuKlien` - Client materials with approved prices
- `Supplier` - Supplier information
- `BahanBakuSupplier` - Supplier materials with prices
- `RiwayatHargaBahanBaku` - Price history for supplier materials
- `RiwayatHargaKlien` - Price history for client materials
- `User` - Marketing staff and managers

---

This plan provides a complete foundation for implementing the Penawaran system with proper database design, relationships, and business logic.
