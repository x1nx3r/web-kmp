# Testing Penawaran Save Functionality

## Prerequisites
1. Be logged in (or the system will fallback to User ID 1)
2. Have at least one Klien with materials
3. Have at least one Supplier with materials

## Steps to Test Save Draft

1. **Navigate to**: `/penawaran/buat`

2. **Select a Client**:
   - Use the search or filter
   - Click on a client to select them

3. **Add Materials**:
   - Click "+ Tambah" button in Materials section
   - Select a material from dropdown
   - Enter quantity
   - Click "Tambah Material"
   - Repeat for at least one material

4. **Review Analysis**:
   - Check that margin analysis table shows data
   - Check that charts display
   - Verify supplier selection (radio buttons)

5. **Click "Simpan Draft"**:
   - Should redirect to `/penawaran` (riwayat page)
   - Should show success message with penawaran number

## Debugging if Save Fails

### Check Logs
```bash
cd /mnt/libraries/proyek/web-kmp
tail -50 storage/logs/laravel.log
```

### Common Issues

1. **"Tidak ada material untuk membuat penawaran"**
   - You didn't add any materials
   - Add at least one material before saving

2. **"Klien belum dipilih"**
   - You didn't select a client
   - Click on a client in the client list

3. **"Gagal menyimpan penawaran: [error message]"**
   - Check the error message for details
   - Common causes:
     - Missing BahanBakuSupplier record
     - Database constraint violation
     - Invalid supplier ID

4. **Button does nothing**
   - Check browser console for JavaScript errors
   - Check that Livewire is loading correctly
   - Verify wire:click="saveDraft" is in the button

### Manual Database Check

```bash
# Check if penawaran was created
php artisan tinker --execute="echo App\Models\Penawaran::count();"

# Check latest penawaran
php artisan tinker --execute="
\$p = App\Models\Penawaran::latest()->first();
if (\$p) {
    echo 'Latest: ' . \$p->nomor_penawaran . ' - Status: ' . \$p->status . PHP_EOL;
    echo 'Details count: ' . \$p->details()->count() . PHP_EOL;
} else {
    echo 'No penawaran found' . PHP_EOL;
}
"
```

## Expected Database Records

After successful save, you should have:

1. **1 Penawaran record** with:
   - Auto-generated nomor_penawaran (PNW-2025-XXXX)
   - Status = 'draft'
   - klien_id = selected client ID
   - Calculated totals (revenue, cost, profit, margin)

2. **N PenawaranDetail records** (where N = number of materials):
   - One for each material
   - With selected supplier
   - With calculated subtotals

3. **M PenawaranAlternativeSupplier records** (where M = total alternatives):
   - For each material, all non-selected suppliers are saved as alternatives

## Verification Query

```sql
-- Check latest penawaran with details
SELECT 
    p.nomor_penawaran,
    p.status,
    k.nama as klien,
    COUNT(pd.id) as materials_count,
    p.total_revenue,
    p.total_cost,
    p.total_profit,
    p.margin_percentage
FROM penawaran p
LEFT JOIN kliens k ON p.klien_id = k.id
LEFT JOIN penawaran_detail pd ON p.id = pd.penawaran_id
WHERE p.id = (SELECT MAX(id) FROM penawaran)
GROUP BY p.id;
```

## What to Report

If save fails, please provide:

1. Steps you took before clicking save
2. Any error messages shown on screen
3. Browser console errors (F12 → Console tab)
4. Laravel log output (last 50 lines)
5. Whether a penawaran record was created (check with query above)

## Next Steps After Successful Save

Once save works, test:
1. Edit existing draft
2. Submit for verification
3. View in riwayat page
4. Duplicate penawaran
5. Delete draft
