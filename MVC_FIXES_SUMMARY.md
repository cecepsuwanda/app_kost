# MVC Violations Fixed - Summary Report

## Overview
This document summarizes the MVC principle violations found in the codebase where models were directly instantiating other models, and how they were fixed to follow proper MVC architecture.

## MVC Principle Violated
**Original Problem**: Models were directly creating instances of other models using `new ModelName()`, which violates the MVC principle that models should not directly depend on other models. Inter-model communication should be coordinated through controllers.

## Files Fixed

### 1. BayarModel.php
**Location**: `app/models/BayarModel.php`

**Violation Found**:
- Line 42: `$tagihanModel = new TagihanModel();`

**Fix Applied**:
- Modified `bayar()` method to accept tagihan data as parameter instead of fetching it directly
- Changed method signature from `bayar($id_tagihan, $jml_bayar)` to `bayar($id_tagihan, $jml_bayar, $tagihan_data = null)`
- Throws exception if tagihan data is not provided, forcing controller coordination

### 2. KamarPenghuniModel.php
**Location**: `app/models/KamarPenghuniModel.php`

**Violations Found**:
- Line 39: `$detailKamarPenghuniModel = new \App\Models\DetailKamarPenghuniModel();`
- Line 53: `$detailKamarPenghuniModel = new \App\Models\DetailKamarPenghuniModel();`
- Line 63: `$detailKamarPenghuniModel = new \App\Models\DetailKamarPenghuniModel();`
- Line 102: `$detailKamarPenghuniModel = new \App\Models\DetailKamarPenghuniModel();`
- Line 147: `$detailKamarPenghuniModel = new \App\Models\DetailKamarPenghuniModel();`

**Fixes Applied**:
1. **createKamarPenghuni()**: Split into two methods:
   - `createKamarPenghuni($id_kamar, $tgl_masuk)` - creates only the main record
   - `createKamarPenghuniWithDetails($id_kamar, $tgl_masuk, $penghuni_ids, $detailKamarPenghuniModel)` - uses injected model

2. **addPenghuniToKamar()**: Modified to accept DetailKamarPenghuniModel as parameter
   - From: `addPenghuniToKamar($id_kmr_penghuni, $id_penghuni, $tgl_masuk)`
   - To: `addPenghuniToKamar($id_kmr_penghuni, $id_penghuni, $tgl_masuk, $detailKamarPenghuniModel)`

3. **pindahKamar()**: Modified to accept DetailKamarPenghuniModel as parameter
   - From: `pindahKamar($id_penghuni, $id_kamar_baru, $tgl_pindah)`
   - To: `pindahKamar($id_penghuni, $id_kamar_baru, $tgl_pindah, $detailKamarPenghuniModel)`

4. **createKamarPenghuniForTransfer()**: Modified to accept DetailKamarPenghuniModel as parameter
   - From: `createKamarPenghuniForTransfer($id_kamar, $tgl_masuk_kamar, $id_penghuni, $tgl_pindah)`
   - To: `createKamarPenghuniForTransfer($id_kamar, $tgl_masuk_kamar, $id_penghuni, $tgl_pindah, $detailKamarPenghuniModel)`

5. **checkKamarCapacity()**: Modified to accept DetailKamarPenghuniModel as parameter
   - From: `checkKamarCapacity($id_kamar, $max_occupants = 2)`
   - To: `checkKamarCapacity($id_kamar, $max_occupants = 2, $detailKamarPenghuniModel = null)`

### 3. TagihanModel.php
**Location**: `app/models/TagihanModel.php`

**Violations Found**:
- Line 56: `$kamarModel = new \App\Models\KamarModel();`
- Line 57: `$detailKamarPenghuniModel = new \App\Models\DetailKamarPenghuniModel();`
- Line 58: `$barangModel = new \App\Models\BarangModel();`
- Line 139: `$kmrPenghuniModel = new \App\Models\KamarPenghuniModel();`
- Line 146: `$kamarModel = new \App\Models\KamarModel();`
- Line 153: `$barangModel = new \App\Models\BarangModel();`
- Line 154: `$detailKamarPenghuniModel = new \App\Models\DetailKamarPenghuniModel();`

**Fixes Applied**:
1. **generateTagihan()**: Replaced model instantiations with direct SQL queries
   - Eliminated dependencies on KamarModel, DetailKamarPenghuniModel, and BarangModel
   - Used JOIN queries to get required data directly from database

2. **recalculateTagihan()**: Replaced model instantiations with direct SQL queries
   - Eliminated dependencies on KamarPenghuniModel, KamarModel, DetailKamarPenghuniModel, and BarangModel
   - Used JOIN queries to get required data directly from database

## Controller Updates

### Admin.php
**Location**: `app/controllers/Admin.php`

**Updates Made**:
1. **Penghuni Creation** (Lines 107-116):
   - Added loading of DetailKamarPenghuniModel in controller
   - Updated `checkKamarCapacity()` call to include required model parameter
   - Updated `addPenghuniToKamar()` call to include required model parameter
   - Updated `createKamarPenghuni()` call to use new `createKamarPenghuniWithDetails()` method

2. **Room Transfer** (Lines 198-203):
   - Added loading of DetailKamarPenghuniModel in controller
   - Updated `pindahKamar()` call to include required model parameter

3. **Payment Processing** (Lines 410-420):
   - Added proper tagihan data retrieval in controller before calling bayar method
   - Updated `bayar()` call to include tagihan data parameter
   - Added proper error handling for missing tagihan

## Benefits of These Fixes

1. **Proper MVC Architecture**: Models no longer directly depend on other models
2. **Controller Coordination**: All inter-model communication now goes through controllers
3. **Better Testability**: Models can be tested in isolation with mocked dependencies
4. **Clearer Dependencies**: Method signatures clearly show what data/models are required
5. **Error Handling**: Clear error messages when required dependencies are not provided
6. **Performance**: Some operations now use direct SQL queries instead of multiple model calls

## Verification

All violations have been eliminated as confirmed by grep search showing no remaining `new.*Model` instantiations in model files.

The code now properly follows MVC principles where:
- Models focus on data operations
- Controllers coordinate between models
- Inter-model dependencies are explicit and managed by controllers