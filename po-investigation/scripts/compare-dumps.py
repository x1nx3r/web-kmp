"""
Extract order_details from SQL dumps and compare with current DB values.
Focus on January 2026 orders.

Column layout (from Jan 25 dump):
  orders: [0]id [1]no_order [2]klien_id [3]created_by [4]tanggal_order [5]catatan 
          [6]status [7]priority [8]po_number [9]po_start_date [10]po_end_date 
          [11]po_document_path [12]po_document_original_name [13]priority_calculated_at
          [14]total_amount [15]total_items [16]total_qty [17]dikonfirmasi_at
          [18]selesai_at [19]dibatalkan_at [20]alasan_pembatalan
          [21]created_at [22]updated_at [23]deleted_at ...

  order_details: [0]id [1]order_id [2]bahan_baku_klien_id [3]nama_material_po
                 [4]qty [5]satuan [6]cheapest_price [7]most_expensive_price
                 [8]recommended_price [9]harga_jual [10]total_harga
                 [11-13]margins [14]available_suppliers_count [15]recommended_supplier_id
                 [16]qty_shipped [17]total_shipped_quantity [18]remaining_quantity
                 [19]suppliers_used_count [20]supplier_options_populated [21]options_populated_at
                 [22]status [23]spesifikasi_khusus [24]catatan
                 [25]created_at [26]updated_at [27]deleted_at ...
"""
import re
import csv
import os
from datetime import datetime

DUMP_DIR = '/mnt/libraries/proyek/web-kmp/po-investigation/database-dumps'
OUTPUT_DIR = '/mnt/libraries/proyek/web-kmp/po-investigation'

def get_dump_date(filename):
    ts = int(re.search(r'(\d{10})', filename).group(1))
    return datetime.fromtimestamp(ts)

def parse_sql_values(sql_text, table_name):
    """Parse INSERT INTO `table` VALUES (...),(...) statements."""
    pattern = rf'INSERT INTO `{table_name}` VALUES\s*(.+?);'
    matches = re.findall(pattern, sql_text, re.DOTALL)
    
    rows = []
    for match in matches:
        # Parse row tuples carefully handling quoted strings
        row_strs = []
        depth = 0
        current = ''
        in_quote = False
        escape_next = False
        
        for c in match:
            if escape_next:
                current += c
                escape_next = False
                continue
            if c == '\\':
                current += c
                escape_next = True
                continue
            if c == "'" and not escape_next:
                in_quote = not in_quote
                current += c
                continue
            if not in_quote:
                if c == '(':
                    depth += 1
                    if depth == 1:
                        current = ''
                        continue
                elif c == ')':
                    depth -= 1
                    if depth == 0:
                        row_strs.append(current)
                        current = ''
                        continue
            current += c
        
        for row_str in row_strs:
            vals = []
            current_val = ''
            in_q = False
            esc = False
            for c in row_str:
                if esc:
                    current_val += c
                    esc = False
                    continue
                if c == '\\':
                    esc = True
                    current_val += c
                    continue
                if c == "'" :
                    in_q = not in_q
                    current_val += c
                    continue
                if not in_q and c == ',':
                    vals.append(current_val.strip())
                    current_val = ''
                    continue
                current_val += c
            vals.append(current_val.strip())
            rows.append(vals)
    
    return rows

def clean(v):
    v = v.strip()
    if v.upper() == 'NULL':
        return None
    if v.startswith("'") and v.endswith("'"):
        return v[1:-1].replace("\\'", "'").replace("\\\\", "\\")
    return v

def parse_dump(sql_text):
    """Parse orders and order_details from a dump."""
    order_rows = parse_sql_values(sql_text, 'orders')
    detail_rows = parse_sql_values(sql_text, 'order_details')
    
    orders = {}
    for vals in order_rows:
        try:
            oid = int(clean(vals[0]))
            orders[oid] = {
                'id': oid,
                'no_order': clean(vals[1]),
                'klien_id': clean(vals[2]),
                'tanggal_order': clean(vals[4]),
                'status': clean(vals[6]),
                'priority': clean(vals[7]),
                'po_number': clean(vals[8]),
                'total_amount': float(clean(vals[14]) or 0),
                'total_items': clean(vals[15]),
                'total_qty': float(clean(vals[16]) or 0),
                'deleted_at': clean(vals[23]),
            }
        except Exception as e:
            print(f"  Error parsing order row: {e}")
    
    details = {}
    for vals in detail_rows:
        try:
            did = int(clean(vals[0]))
            details[did] = {
                'id': did,
                'order_id': int(clean(vals[1])),
                'material': clean(vals[3]),
                'qty': float(clean(vals[4]) or 0),
                'satuan': clean(vals[5]),
                'harga_jual': float(clean(vals[9]) or 0),
                'total_harga': float(clean(vals[10]) or 0),
                'qty_shipped': float(clean(vals[16]) or 0),
                'status': clean(vals[22]),
                'deleted_at': clean(vals[27]) if len(vals) > 27 else None,
            }
        except Exception as e:
            print(f"  Error parsing detail row: {e}")
    
    return orders, details

# ============================================================
# Load dumps
# ============================================================
dump_files = sorted(os.listdir(DUMP_DIR))

# Earliest dump (Dec 28) - original values for pre-Dec orders
dec28_file = dump_files[0]
print(f"Loading {dec28_file} ({get_dump_date(dec28_file).strftime('%Y-%m-%d')})...")
with open(os.path.join(DUMP_DIR, dec28_file), 'r') as f:
    dec28_orders, dec28_details = parse_dump(f.read())
print(f"  → {len(dec28_orders)} orders, {len(dec28_details)} details")

# Jan 25 dump - should capture Jan orders before too many shipments
jan25_file = 'mysql-dump-default-1769299210.sql'
print(f"Loading {jan25_file} ({get_dump_date(jan25_file).strftime('%Y-%m-%d')})...")
with open(os.path.join(DUMP_DIR, jan25_file), 'r') as f:
    jan25_orders, jan25_details = parse_dump(f.read())
print(f"  → {len(jan25_orders)} orders, {len(jan25_details)} details")

# Feb 1 dump - for final state of Jan orders 
feb01_file = 'mysql-dump-default-1769904034.sql'
print(f"Loading {feb01_file} ({get_dump_date(feb01_file).strftime('%Y-%m-%d')})...")
with open(os.path.join(DUMP_DIR, feb01_file), 'r') as f:
    feb01_orders, feb01_details = parse_dump(f.read())
print(f"  → {len(feb01_orders)} orders, {len(feb01_details)} details")

# ============================================================
# Find January 2026 orders
# ============================================================
print("\n" + "=" * 100)
print("JANUARY 2026 ORDERS")
print("=" * 100)

# Find Jan orders from the Jan 25 dump (and any that appear in Feb 1 dump)
all_orders = {**jan25_orders, **feb01_orders}
jan_order_ids = sorted([
    oid for oid, o in all_orders.items()
    if o['tanggal_order'] and o['tanggal_order'].startswith('2026-01')
    and o['deleted_at'] is None
])

print(f"\nFound {len(jan_order_ids)} January 2026 orders")

# ============================================================
# Compare qty across dumps for each Jan order detail
# ============================================================
csv_path = os.path.join(OUTPUT_DIR, 'jan26_comparison.csv')
with open(csv_path, 'w', newline='') as f:
    w = csv.writer(f)
    w.writerow([
        'order_id', 'po_number', 'no_order', 'tanggal_order', 
        'detail_id', 'material', 'satuan', 'harga_jual',
        'qty_dec28', 'qty_jan25', 'qty_feb01',
        'total_harga_dec28', 'total_harga_jan25', 'total_harga_feb01',
        'order_total_amount_jan25', 'order_total_amount_feb01',
        'status_jan25', 'status_feb01',
    ])
    
    for oid in jan_order_ids:
        o = all_orders[oid]
        o_jan25 = jan25_orders.get(oid, {})
        o_feb01 = feb01_orders.get(oid, {})
        
        # Get details for this order from each dump
        details_for_order = set()
        for d in dec28_details.values():
            if d['order_id'] == oid and d.get('deleted_at') is None:
                details_for_order.add(d['id'])
        for d in jan25_details.values():
            if d['order_id'] == oid and d.get('deleted_at') is None:
                details_for_order.add(d['id'])
        for d in feb01_details.values():
            if d['order_id'] == oid and d.get('deleted_at') is None:
                details_for_order.add(d['id'])
        
        for did in sorted(details_for_order):
            d_dec28 = dec28_details.get(did)
            d_jan25 = jan25_details.get(did)
            d_feb01 = feb01_details.get(did)
            
            best_d = d_feb01 or d_jan25 or d_dec28
            
            w.writerow([
                oid, o.get('po_number', ''), o.get('no_order', ''), o.get('tanggal_order', ''),
                did, best_d['material'] if best_d else '', best_d['satuan'] if best_d else '',
                best_d['harga_jual'] if best_d else '',
                d_dec28['qty'] if d_dec28 else '',
                d_jan25['qty'] if d_jan25 else '',
                d_feb01['qty'] if d_feb01 else '',
                d_dec28['total_harga'] if d_dec28 else '',
                d_jan25['total_harga'] if d_jan25 else '',
                d_feb01['total_harga'] if d_feb01 else '',
                o_jan25.get('total_amount', ''),
                o_feb01.get('total_amount', ''),
                o_jan25.get('status', ''),
                o_feb01.get('status', ''),
            ])

print(f"\n✅ Written: {csv_path}")

# ============================================================
# Print summary
# ============================================================
print(f"\n{'ID':<5} {'PO Number':<22} {'Date':<12} {'#Det':<5} {'total_amount(Jan25)':<22} {'total_amount(Feb01)':<22} {'SUM detail(Jan25)':<22} {'SUM detail(Feb01)':<22}")
print('-' * 130)

gt_jan25 = 0
gt_feb01 = 0
gd_jan25 = 0
gd_feb01 = 0

for oid in jan_order_ids:
    o_jan25 = jan25_orders.get(oid)
    o_feb01 = feb01_orders.get(oid)
    
    # Sum details for this order
    det_sum_jan25 = sum(d['total_harga'] for d in jan25_details.values() if d['order_id'] == oid and d.get('deleted_at') is None)
    det_sum_feb01 = sum(d['total_harga'] for d in feb01_details.values() if d['order_id'] == oid and d.get('deleted_at') is None)
    
    det_count = len([d for d in (list(jan25_details.values()) + list(feb01_details.values())) if d['order_id'] == oid and d.get('deleted_at') is None])
    
    ta_jan25 = o_jan25['total_amount'] if o_jan25 else 0
    ta_feb01 = o_feb01['total_amount'] if o_feb01 else 0
    
    gt_jan25 += ta_jan25
    gt_feb01 += ta_feb01
    gd_jan25 += det_sum_jan25
    gd_feb01 += det_sum_feb01
    
    po = (o_jan25 or o_feb01 or {}).get('po_number', '-')
    dt = (o_jan25 or o_feb01 or {}).get('tanggal_order', '-')
    
    print(f"{oid:<5} {po:<22} {dt:<12} {det_count//2 if det_count else 0:<5} {ta_jan25:>20,.0f} {ta_feb01:>20,.0f} {det_sum_jan25:>20,.0f} {det_sum_feb01:>20,.0f}")

print('-' * 130)
print(f"{'TOTAL':<44} {'':>5} {gt_jan25:>20,.0f} {gt_feb01:>20,.0f} {gd_jan25:>20,.0f} {gd_feb01:>20,.0f}")
print(f"\n🎯 Client's number for Jan 26: 4,724,750,000")
print(f"   Dump total_amount (Jan 25): {gt_jan25:,.0f}")
print(f"   Dump total_amount (Feb 01): {gt_feb01:,.0f}")
print(f"   Detail sum (Jan 25):        {gd_jan25:,.0f}")
print(f"   Detail sum (Feb 01):        {gd_feb01:,.0f}")
