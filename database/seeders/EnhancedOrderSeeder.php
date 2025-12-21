<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Klien;
use App\Models\BahanBakuKlien;
use App\Models\BahanBakuSupplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EnhancedOrderSeeder extends Seeder
{
    private const SAMPLE_PO_IMAGE_BASE64 = "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAAWgmWQ0AAAAASUVORK5CYII=";

    /**
     * Run the database seeds for the enhanced multi-supplier order system.
     */
    public function run(): void
    {
        $this->command->info("🚀 Starting Enhanced Order Seeder...");

        // Get required user
        $marketingUser =
            User::where("role", "marketing")->first() ?? User::first();

        if (!$marketingUser) {
            $this->command->error(
                "❌ Missing marketing user! Please run UserSeeder first.",
            );
            return;
        }

        $displayName = $marketingUser->name ?: "User #" . $marketingUser->id;
        $this->command->info("👤 Using marketing user: " . $displayName);

        // Create sample orders with different scenarios
        $orders = [
            $this->createLargeProductionOrder(),
            $this->createUrgentOrder(),
            $this->createRoutineOrder(),
            $this->createMultiItemOrder(),
        ];

        foreach ($orders as $orderData) {
            $this->createOrderWithAutoSuppliers($orderData, $marketingUser);
        }

        $this->command->info(
            "✅ Enhanced Order Seeder completed successfully!",
        );
        $this->command->info(
            "📊 Created " .
                count($orders) .
                " orders with auto-supplier population",
        );
    }

    private function createLargeProductionOrder(): array
    {
        return [
            "klien_id" => 2, // PT Central Proteina
            "tanggal_order" => Carbon::now()->subDays(7),
            "priority" => "tinggi",
            "status" => "dikonfirmasi",
            "catatan" => "Order besar untuk produksi Q4 - prioritas tinggi",
            "dikonfirmasi_at" => Carbon::now()->subDays(6),
            "po_number" => "PO-2025-LARGE-001",
            "po_start_date" => Carbon::now()->subDays(7),
            "po_end_date" => Carbon::now()->addDays(6),
            "details" => [
                [
                    "bahan_baku_klien_id" => 7, // Molases - PT Central Proteina
                    "primary_supplier_id" => 13,
                    "qty" => 5000,
                    "satuan" => "kg",
                    "harga_jual" => 12000,
                    "spesifikasi_khusus" =>
                        "Pengiriman drum 200L, viskositas stabil",
                    "qty_shipped" => 2200,
                    "remaining_quantity" => 2800,
                    "status" => "sebagian_dikirim",
                ],
            ],
        ];
    }

    private function createUrgentOrder(): array
    {
        return [
            "klien_id" => 5, // CJ Feed
            "tanggal_order" => Carbon::now()->subDays(2),
            "priority" => "tinggi",
            "status" => "diproses",
            "catatan" =>
                "Order urgent untuk stock emergency (mapped from legacy mendesak)",
            "dikonfirmasi_at" => Carbon::now()->subDays(1),
            "po_number" => "PO-2025-URG-002",
            "po_start_date" => Carbon::now()->subDays(2),
            "po_end_date" => Carbon::now()->addDays(2),
            "details" => [
                [
                    "bahan_baku_klien_id" => 73, // Katul - PT. Dinamika Megatama Citra
                    "primary_supplier_id" => 26,
                    "qty" => 1500,
                    "satuan" => "kg",
                    "harga_jual" => 15800,
                    "spesifikasi_khusus" => "Kualitas premium, moisture rendah",
                    "qty_shipped" => 900,
                    "remaining_quantity" => 600,
                    "status" => "sebagian_dikirim",
                ],
            ],
        ];
    }

    private function createRoutineOrder(): array
    {
        return [
            "klien_id" => 1, // PT Sreya Sewu
            "tanggal_order" => Carbon::now()->subDays(5),
            "priority" => "sedang",
            "status" => "draft",
            "catatan" =>
                "Order rutin bulanan - jadwal sedang (mapped from legacy normal)",
            "po_number" => "PO-2025-ROT-003",
            "po_start_date" => Carbon::now()->subDays(5),
            "po_end_date" => Carbon::now()->addDays(12),
            "details" => [
                [
                    "bahan_baku_klien_id" => 1, // Biji Batu - PT Sreya Sewu
                    "primary_supplier_id" => 1,
                    "qty" => 800,
                    "satuan" => "kg",
                    "harga_jual" => 13500,
                    "qty_shipped" => 0,
                    "remaining_quantity" => 800,
                ],
            ],
        ];
    }

    private function createMultiItemOrder(): array
    {
        return [
            "klien_id" => 2, // PT Central Proteina
            "tanggal_order" => Carbon::now()->subDays(3),
            "priority" => "sedang",
            "status" => "dikonfirmasi",
            "catatan" =>
                "Order campuran untuk formula pakan (mapped from legacy normal)",
            "dikonfirmasi_at" => Carbon::now()->subDays(2),
            "po_number" => "PO-2025-MIX-004",
            "po_start_date" => Carbon::now()->subDays(3),
            "po_end_date" => Carbon::now()->addDays(10),
            "details" => [
                [
                    "bahan_baku_klien_id" => 9, // Biji Batu - PT Central Proteina
                    "primary_supplier_id" => 1,
                    "qty" => 300,
                    "satuan" => "kg",
                    "harga_jual" => 12500,
                    "qty_shipped" => 300,
                    "remaining_quantity" => 0,
                    "status" => "selesai",
                ],
            ],
        ];
    }

    private function createOrderWithAutoSuppliers(array $orderData, $user): void
    {
        $this->command->info(
            "📝 Creating order for: " .
                Klien::find($orderData["klien_id"])->nama,
        );

        $poNumber = $orderData["po_number"] ?? $this->generatePoNumber("GEN");
        $poStart = $orderData["po_start_date"] ?? $orderData["tanggal_order"];
        $poEnd = $orderData["po_end_date"] ?? Carbon::now()->addDays(14);
        $poDocumentPath = $this->storeSamplePoDocument($poNumber);
        $poOriginalName = $this->makeOriginalPoFileName($poNumber);

        // Create the order
        // Surgical migration path: write into legacy enum column `priority`.
        // The seeder now uses the canonical `priority` key so data matches the post-migration schema.
        $order = Order::create([
            "klien_id" => $orderData["klien_id"],
            "created_by" => $user->id,
            "tanggal_order" => $orderData["tanggal_order"],
            "priority" => $orderData["priority"],
            "status" => $orderData["status"],
            "catatan" => $orderData["catatan"],
            "dikonfirmasi_at" => $orderData["dikonfirmasi_at"] ?? null,
            "po_number" => $poNumber,
            "po_start_date" => $poStart,
            "po_end_date" => $poEnd,
            "po_document_path" => $poDocumentPath,
            "po_document_original_name" => $poOriginalName,
        ]);

        $this->command->line("   📋 Order created: {$order->no_order}");

        // Create order details and auto-populate suppliers
        foreach ($orderData["details"] as $detailData) {
            $material = BahanBakuKlien::find(
                $detailData["bahan_baku_klien_id"],
            );

            if (!$material) {
                $this->command->warn(
                    "   ⚠️  Skipping detail - material ID {$detailData["bahan_baku_klien_id"]} tidak ditemukan",
                );
                continue;
            }

            $primarySupplier = BahanBakuSupplier::with("supplier")->find(
                $detailData["primary_supplier_id"],
            );

            if (!$primarySupplier) {
                $this->command->warn(
                    "   ⚠️  Skipping {$material->nama} - supplier ID {$detailData["primary_supplier_id"]} tidak ditemukan",
                );
                continue;
            }

            $qty = $detailData["qty"];
            $qtyShipped = $detailData["qty_shipped"] ?? 0;
            $remainingQty =
                $detailData["remaining_quantity"] ?? max(0, $qty - $qtyShipped);
            $detailStatus =
                $detailData["status"] ??
                ($remainingQty <= 0
                    ? "selesai"
                    : ($qtyShipped > 0
                        ? "sebagian_dikirim"
                        : "menunggu"));
            $totalShipped =
                $detailData["total_shipped_quantity"] ?? $qtyShipped;

            // Create order detail with new structure
            $detailAttributes = [
                "order_id" => $order->id,
                "bahan_baku_klien_id" => $material->id,
                "qty" => $qty,
                "satuan" => $detailData["satuan"],
                "harga_jual" => $detailData["harga_jual"],
                "total_harga" => $qty * $detailData["harga_jual"],
                "status" => $detailStatus,
                "qty_shipped" => $qtyShipped,
                "total_shipped_quantity" => $totalShipped,
                "remaining_quantity" => $remainingQty,
                "spesifikasi_khusus" =>
                    $detailData["spesifikasi_khusus"] ?? null,
                "catatan" => $detailData["catatan"] ?? null,
            ];

            $orderDetail = OrderDetail::create($detailAttributes);

            // 🚀 AUTO-POPULATE SUPPLIERS - This is the key feature!
            $orderDetail->populateSupplierOptions();

            // Mark seeded primary supplier as recommended when available
            $selectedSupplier = $orderDetail
                ->orderSuppliers()
                ->where("bahan_baku_supplier_id", $primarySupplier->id)
                ->first();

            if ($selectedSupplier) {
                $orderDetail
                    ->orderSuppliers()
                    ->update(["is_recommended" => false]);

                $selectedSupplier->is_recommended = true;
                $selectedSupplier->price_rank = 1;
                $selectedSupplier->save();

                $orderDetail->updateSupplierSummary();
            }

            $supplierCount = $orderDetail->orderSuppliers()->count();
            $bestMargin = $orderDetail->best_margin_percentage ?? 0;

            $this->command->line(
                "   ✅ {$material->nama}: {$supplierCount} suppliers, best margin: {$bestMargin}%",
            );
        }

        // Calculate order totals
        $order->calculateTotals();

        $totalSuppliers = $order
            ->orderSuppliers()
            ->select("order_suppliers.supplier_id")
            ->distinct()
            ->count();
        $this->command->line(
            "   💰 Total: Rp " .
                number_format($order->total_amount, 0, ",", "."),
        );
        $this->command->line("   🏪 Available suppliers: {$totalSuppliers}");
        $this->command->line("");
    }

    private function generatePoNumber(string $prefix): string
    {
        return sprintf(
            "PO-%s-%s-%s",
            strtoupper($prefix),
            Carbon::now()->format("Ymd"),
            strtoupper(Str::random(4)),
        );
    }

    private function storeSamplePoDocument(string $poNumber): string
    {
        $disk = Storage::disk("public");
        $directory = "po-documents";

        if (!$disk->exists($directory)) {
            $disk->makeDirectory($directory);
        }

        $slug = Str::slug($poNumber) ?: "po-document";
        $fileName = $slug . "-sample.png";
        $disk->put(
            $directory . "/" . $fileName,
            base64_decode(self::SAMPLE_PO_IMAGE_BASE64),
        );

        return $directory . "/" . $fileName;
    }

    private function makeOriginalPoFileName(string $poNumber): string
    {
        return "Surat PO " .
            strtoupper(str_replace("-", " ", $poNumber)) .
            ".png";
    }
}
