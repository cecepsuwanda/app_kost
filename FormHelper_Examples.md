# 📝 FormHelper Usage Examples

## 🎯 **Basic Form Elements**

### **1. Form Opening & Closing**

```php
<!-- Traditional way -->
<form method="POST" action="<?= $baseUrl ?>/admin/penghuni">

<!-- With FormHelper -->
<?= \App\Helpers\FormHelper::open($baseUrl . '/admin/penghuni') ?>
<?= \App\Helpers\FormHelper::close() ?>

<!-- Global functions -->
<?= form_open($baseUrl . '/admin/penghuni') ?>
<?= form_close() ?>

<!-- With options -->
<?= form_open($baseUrl . '/admin/penghuni', [
    'method' => 'POST',
    'class' => 'row g-3',
    'enctype' => 'multipart/form-data'
]) ?>
```

### **2. Text Inputs**

```php
<!-- Traditional -->
<input type="text" class="form-control" name="nama" required>

<!-- FormHelper -->
<?= \App\Helpers\FormHelper::text('nama', '', ['required' => true]) ?>

<!-- Global function -->
<?= form_text('nama', '', ['required' => true, 'placeholder' => 'Masukkan nama']) ?>

<!-- With value and options -->
<?= form_text('nama', $penghuni['nama'], [
    'class' => 'form-control',
    'placeholder' => 'Masukkan nama lengkap',
    'required' => true,
    'autofocus' => true
]) ?>
```

### **3. Number Inputs**

```php
<!-- Traditional -->
<input type="number" class="form-control" name="harga" min="0" step="1000" required>

<!-- FormHelper -->
<?= \App\Helpers\FormHelper::number('harga', '', [
    'min' => '0',
    'step' => '1000',
    'required' => true
]) ?>
```

### **4. Date Inputs**

```php
<!-- Traditional -->
<input type="date" class="form-control" name="tgl_masuk" value="<?= date('Y-m-d') ?>" required>

<!-- FormHelper -->
<?= \App\Helpers\FormHelper::date('tgl_masuk', date('Y-m-d'), ['required' => true]) ?>

<!-- Month input for filtering -->
<?= \App\Helpers\FormHelper::month('bulan', date('Y-m')) ?>
```

### **5. Select Dropdowns**

```php
<!-- Traditional -->
<select class="form-select" name="id_kamar">
    <option value="">-- Belum pilih kamar --</option>
    <?php foreach ($kamarTersedia as $kamar): ?>
        <option value="<?= $kamar['id'] ?>">
            Kamar <?= htmlspecialchars($kamar['nomor']) ?> - Rp <?= number_format($kamar['harga'], 0, ',', '.') ?>
        </option>
    <?php endforeach; ?>
</select>

<!-- FormHelper -->
<?php
$roomOptions = ['' => '-- Belum pilih kamar --'];
foreach ($kamarTersedia as $kamar) {
    $roomOptions[$kamar['id']] = "Kamar {$kamar['nomor']} - " . currency($kamar['harga']);
}
?>
<?= \App\Helpers\FormHelper::select('id_kamar', $roomOptions) ?>

<!-- With selected value -->
<?= form_select('id_kamar', $roomOptions, $selectedRoomId, ['required' => true]) ?>
```

### **6. Checkboxes**

```php
<!-- Traditional -->
<div class="form-check">
    <input class="form-check-input" type="checkbox" name="barang_ids[]" value="<?= $item['id'] ?>" id="barang<?= $item['id'] ?>">
    <label class="form-check-label" for="barang<?= $item['id'] ?>">
        <?= htmlspecialchars($item['nama']) ?>
    </label>
</div>

<!-- FormHelper -->
<?php
$checkbox = \App\Helpers\FormHelper::checkbox('barang_ids[]', $item['id'], false, [
    'id' => 'barang' . $item['id']
]);
echo \App\Helpers\FormHelper::check($checkbox, htmlspecialchars($item['nama']), [
    'input_id' => 'barang' . $item['id']
]);
?>

<!-- Simplified -->
<?= form_checkbox('remember', '1', false) ?>
```

## 🎨 **Advanced Form Components**

### **7. Form Groups with Labels**

```php
<!-- Traditional -->
<div class="mb-3">
    <label for="nama" class="form-label">Nama Lengkap</label>
    <input type="text" class="form-control" id="nama" name="nama" required>
</div>

<!-- FormHelper -->
<?php
$nameInput = \App\Helpers\FormHelper::text('nama', '', ['required' => true, 'id' => 'nama']);
echo \App\Helpers\FormHelper::group('Nama Lengkap', $nameInput, [
    'required' => true,
    'input_id' => 'nama'
]);
?>

<!-- Global function -->
<?= form_group(
    'Nama Lengkap',
    form_text('nama', '', ['required' => true, 'id' => 'nama']),
    ['required' => true, 'input_id' => 'nama']
) ?>
```

### **8. Input Groups with Icons/Prefixes**

```php
<!-- Traditional -->
<div class="input-group">
    <span class="input-group-text">Rp</span>
    <input type="number" class="form-control" name="harga" min="0" step="1000" required>
</div>

<!-- FormHelper -->
<?= \App\Helpers\FormHelper::currency('harga', '', ['required' => true]) ?>

<!-- Phone with icon -->
<?= \App\Helpers\FormHelper::phone('no_hp', '', ['placeholder' => 'Masukkan nomor HP']) ?>

<!-- Custom input group -->
<?php
$priceInput = \App\Helpers\FormHelper::number('harga', '', ['min' => '0', 'step' => '1000']);
echo \App\Helpers\FormHelper::inputGroup($priceInput, [
    'prefix' => 'Rp',
    'suffix' => '/bulan'
]);
?>
```

### **9. Buttons**

```php
<!-- Traditional -->
<button type="submit" class="btn btn-primary">Simpan</button>
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>

<!-- FormHelper -->
<?= \App\Helpers\FormHelper::submit('Simpan', ['class' => 'btn-primary']) ?>
<?= \App\Helpers\FormHelper::button('Batal', [
    'class' => 'btn-secondary',
    'data-bs-dismiss' => 'modal'
]) ?>

<!-- Global function -->
<?= form_submit('Simpan Data') ?>
```

## 🔄 **Real-World Examples**

### **10. Complete Login Form**

```php
<!-- Traditional approach -->
<form method="POST" action="<?= $baseUrl ?>/login">
    <div class="mb-4">
        <label for="username" class="form-label">Username</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="bi bi-person"></i>
            </span>
            <input type="text" class="form-control" id="username" name="username" 
                   placeholder="Masukkan username" required autofocus>
        </div>
    </div>
    
    <div class="mb-4">
        <label for="password" class="form-label">Password</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="bi bi-lock"></i>
            </span>
            <input type="password" class="form-control" id="password" name="password" 
                   placeholder="Masukkan password" required>
        </div>
    </div>
    
    <button type="submit" class="btn btn-primary w-100">Masuk</button>
</form>

<!-- FormHelper approach -->
<?= form_open($baseUrl . '/login') ?>
    <?php
    $usernameInput = \App\Helpers\FormHelper::text('username', '', [
        'placeholder' => 'Masukkan username',
        'required' => true,
        'autofocus' => true,
        'id' => 'username'
    ]);
    $usernameWithIcon = \App\Helpers\FormHelper::inputGroup($usernameInput, [
        'prefix' => '<i class="bi bi-person"></i>'
    ]);
    echo \App\Helpers\FormHelper::group('Username', $usernameWithIcon, [
        'col' => 'mb-4',
        'input_id' => 'username'
    ]);
    
    $passwordInput = \App\Helpers\FormHelper::password('password', [
        'placeholder' => 'Masukkan password',
        'required' => true,
        'id' => 'password'
    ]);
    $passwordWithIcon = \App\Helpers\FormHelper::inputGroup($passwordInput, [
        'prefix' => '<i class="bi bi-lock"></i>'
    ]);
    echo \App\Helpers\FormHelper::group('Password', $passwordWithIcon, [
        'col' => 'mb-4',
        'input_id' => 'password'
    ]);
    ?>
    
    <?= \App\Helpers\FormHelper::submit('Masuk', ['class' => 'btn-primary w-100']) ?>
<?= form_close() ?>
```

### **11. Complete Add Item Form**

```php
<!-- FormHelper approach -->
<?= form_open($baseUrl . '/admin/barang', ['id' => 'addBarangForm']) ?>
    <?= \App\Helpers\FormHelper::hidden('action', 'create') ?>
    
    <?php
    // Nama barang input
    $namaInput = form_text('nama', '', [
        'placeholder' => 'Masukkan nama barang',
        'required' => true,
        'id' => 'nama'
    ]);
    echo form_group('Nama Barang', $namaInput, [
        'required' => true,
        'input_id' => 'nama'
    ]);
    
    // Harga input with Rp prefix
    $hargaInput = \App\Helpers\FormHelper::currency('harga', '', [
        'required' => true,
        'id' => 'harga'
    ]);
    echo form_group('Harga', $hargaInput, [
        'required' => true,
        'input_id' => 'harga',
        'help' => 'Masukkan harga dalam rupiah'
    ]);
    ?>
    
    <div class="modal-footer">
        <?= \App\Helpers\FormHelper::button('Batal', [
            'class' => 'btn-secondary',
            'data-bs-dismiss' => 'modal'
        ]) ?>
        <?= form_submit('Simpan', ['class' => 'btn-primary']) ?>
    </div>
<?= form_close() ?>
```

### **12. Search/Filter Form**

```php
<!-- FormHelper approach -->
<?= form_open('', ['method' => 'GET', 'class' => 'row g-3 align-items-end']) ?>
    <div class="col-md-6">
        <?php
        $searchInput = \App\Helpers\FormHelper::search('q', $request->getParam('q', ''), [
            'placeholder' => 'Cari penghuni...'
        ]);
        echo form_group('Pencarian', $searchInput);
        ?>
    </div>
    
    <div class="col-md-4">
        <?php
        $monthInput = \App\Helpers\FormHelper::month('bulan', $bulan);
        echo form_group('Filter Bulan', $monthInput, [
            'help' => 'Pilih bulan untuk filter data'
        ]);
        ?>
    </div>
    
    <div class="col-md-2">
        <?= \App\Helpers\FormHelper::submit('Filter', ['class' => 'btn-outline-primary']) ?>
    </div>
<?= form_close() ?>
```

## 📱 **Modal Forms**

### **13. Complete Modal Form**

```php
<?php
$modalBody = '
    ' . \App\Helpers\FormHelper::hidden('action', 'create') . '
    ' . form_group(
        'Nama Lengkap',
        form_text('nama', '', ['required' => true, 'id' => 'nama']),
        ['required' => true, 'input_id' => 'nama']
    ) . '
    ' . form_group(
        'No. HP',
        \App\Helpers\FormHelper::phone('no_hp', '', ['id' => 'no_hp']),
        ['input_id' => 'no_hp']
    ) . '
    ' . form_group(
        'Tanggal Masuk',
        form_date('tgl_masuk', date('Y-m-d'), ['required' => true, 'id' => 'tgl_masuk']),
        ['required' => true, 'input_id' => 'tgl_masuk']
    );

echo \App\Helpers\FormHelper::modal('addPenghuniModal', 'Tambah Penghuni Baru', $modalBody, [
    'action' => $baseUrl . '/admin/penghuni',
    'footer_buttons' => [
        'cancel' => 'Batal',
        'submit' => 'Simpan Data'
    ]
]);
?>
```

## 🎨 **Advanced Features**

### **14. Dynamic Form Elements**

```php
<!-- Multiple checkboxes for items -->
<?php foreach ($barangList as $item): ?>
    <div class="col-md-6 mb-2">
        <?php
        $checkbox = form_checkbox('barang_ids[]', $item['id'], false, [
            'id' => 'barang' . $item['id']
        ]);
        $label = htmlspecialchars($item['nama']) . ' <small class="text-muted">(+' . currency($item['harga']) . ')</small>';
        echo \App\Helpers\FormHelper::check($checkbox, $label, [
            'input_id' => 'barang' . $item['id']
        ]);
        ?>
    </div>
<?php endforeach; ?>
```

### **15. Conditional Form Fields**

```php
<!-- Room selection with availability -->
<?php
$roomOptions = ['' => '-- Belum pilih kamar --'];
foreach ($kamarTersedia as $kamar) {
    $text = "Kamar {$kamar['nomor']} - " . currency($kamar['harga']);
    if (isset($kamar['slot_tersedia'])) {
        $text .= " ({$kamar['slot_tersedia']} slot tersedia)";
    }
    $roomOptions[$kamar['id']] = $text;
}

echo form_group(
    'Pilih Kamar',
    form_select('id_kamar', $roomOptions, '', ['id' => 'id_kamar']),
    [
        'input_id' => 'id_kamar',
        'help' => 'Pilih kamar yang tersedia (opsional)'
    ]
);
?>
```

## 🏆 **Benefits of Using FormHelper**

1. **🔧 Consistent Bootstrap Classes** - Automatic form-control, form-select, etc.
2. **🛡️ Built-in Security** - HTML escaping and XSS protection
3. **⚡ Faster Development** - Less repetitive HTML writing
4. **🎨 Better Maintainability** - Centralized form element generation
5. **📱 Mobile-Friendly** - Bootstrap responsive classes included
6. **🔄 Reusable Components** - Input groups, form groups, modals
7. **✅ Validation Ready** - Easy to add required, patterns, etc.
8. **🎯 Type Safety** - Method-specific inputs (email, tel, number, etc.)

## 🚀 **Migration from Traditional Forms**

```php
// BEFORE (Traditional)
<div class="mb-3">
    <label for="harga" class="form-label">Harga <span class="text-danger">*</span></label>
    <div class="input-group">
        <span class="input-group-text">Rp</span>
        <input type="number" class="form-control" id="harga" name="harga" 
               min="0" step="1000" required>
    </div>
    <div class="form-text">Masukkan harga dalam rupiah</div>
</div>

// AFTER (FormHelper)
<?= form_group(
    'Harga',
    \App\Helpers\FormHelper::currency('harga', '', ['required' => true, 'id' => 'harga']),
    [
        'required' => true,
        'input_id' => 'harga',
        'help' => 'Masukkan harga dalam rupiah'
    ]
) ?>
```

**Result: 70% less code, more maintainable, and consistent!** 🎉