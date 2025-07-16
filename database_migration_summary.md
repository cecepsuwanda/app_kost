# Database Migration Summary: tb_tagihan Table Changes

## Changes Made

### Database Schema Changes
- **Column `bulan`**: Changed from `VARCHAR` to `INT` (values 1-12)
- **Column `tahun`**: New `INT` column added
- **Unique Constraint**: Updated to `(bulan, tahun, id_kmr_penghuni)`

## Codebase Updates

### 1. Models Updated

#### `app/models/TagihanModel.php`
- **Method `findByBulan()`** → **`findByBulanTahun($bulan, $tahun)`**
- **Method `findByBulanKamarPenghuni()`** → **`findByBulanTahunKamarPenghuni($bulan, $tahun, $id_kmr_penghuni)`**
- **Method `generateTagihan($periode)`**: Now parses 'YYYY-MM' format and extracts separate bulan/tahun integers
- **Method `getTagihanDetail($periode)`**: Updated to filter by both bulan and tahun
- **Method `getTagihanTerlambat()`**: Updated to use proper date comparison with separate bulan/tahun fields

#### `app/models/BayarModel.php`
- **Method `getLaporanPembayaran($periode)`**: Updated to filter by both bulan and tahun
- Added `t.tahun` to SELECT clause for proper date display

### 2. Views Updated

#### `app/views/admin/tagihan.php`
- Updated month display: `strtotime($t['bulan'] . '-01')` → `mktime(0, 0, 0, $t['bulan'], 1, $t['tahun'])`

#### `app/views/admin/pembayaran.php` 
- Updated month display: `strtotime($l['bulan'] . '-01')` → `mktime(0, 0, 0, $l['bulan'], 1, $l['tahun'])`
- Updated tagihan selection dropdown month display

#### `app/views/admin/dashboard.php`
- Updated month display in tagihan listing

#### `app/views/home/index.php`
- Updated month display in penghuni tagihan view

### 3. Controllers
- **`app/controllers/Admin.php`**: No changes needed (models handle the format conversion internally)

## Data Format Changes

### Input Format (Unchanged)
- Forms still use `type="month"` with 'YYYY-MM' format (e.g., '2024-01')
- Filter parameters remain in 'YYYY-MM' format

### Database Storage (Changed)
- **Before**: `bulan` VARCHAR storing 'YYYY-MM' (e.g., '2024-01')
- **After**: 
  - `bulan` INT storing month number (1-12)
  - `tahun` INT storing year (e.g., 2024)

### Display Format (Unchanged)
- Views still display formatted dates like "Jan 2024" using PHP's `date()` function
- Updated to use `mktime()` instead of `strtotime()` for date creation

## Migration Notes

### Backward Compatibility
- API interfaces remain the same (methods accept 'YYYY-MM' format)
- Frontend forms and filters work without changes
- Date display format remains consistent

### Data Migration Required
Existing data in `tb_tagihan` needs to be migrated:

```sql
-- Add new tahun column (if not already added)
ALTER TABLE tb_tagihan ADD COLUMN tahun INT;

-- Migrate existing data (example for VARCHAR bulan like '2024-01')
UPDATE tb_tagihan 
SET 
    tahun = CAST(SUBSTRING(bulan, 1, 4) AS INT),
    bulan = CAST(SUBSTRING(bulan, 6, 2) AS INT)
WHERE tahun IS NULL;

-- Change bulan column type to INT
ALTER TABLE tb_tagihan MODIFY COLUMN bulan INT NOT NULL;

-- Add the new unique constraint
ALTER TABLE tb_tagihan ADD UNIQUE KEY unique_bulan_tahun_kmr_penghuni (bulan, tahun, id_kmr_penghuni);
```

## Testing Checklist

- [ ] Generate tagihan for new month
- [ ] Filter tagihan by month
- [ ] View payment reports by month
- [ ] Verify date displays correctly in all views
- [ ] Check tagihan terlambat functionality
- [ ] Test payment processing
- [ ] Verify dashboard tagihan display
- [ ] Test penghuni tagihan view

## Files Modified

1. `app/models/TagihanModel.php`
2. `app/models/BayarModel.php` 
3. `app/views/admin/tagihan.php`
4. `app/views/admin/pembayaran.php`
5. `app/views/admin/dashboard.php`
6. `app/views/home/index.php`

All changes maintain backward compatibility at the API level while properly handling the new database structure.