<?php

namespace App\Helpers;

use App\Helpers\HtmlHelper as Html;

class ViewHelper
{
    /**
     * Render room status badge
     */
    public static function roomStatusBadge($status)
    {
        return Html::statusBadge($status);
    }

    /**
     * Render payment status badge
     */
    public static function paymentStatusBadge($status)
    {
        $mapping = [
            'Lunas' => ['text' => 'Lunas', 'type' => 'success'],
            'Cicil' => ['text' => 'Cicil', 'type' => 'warning text-dark'],
            'Belum Bayar' => ['text' => 'Belum Bayar', 'type' => 'danger'],
        ];
        
        return Html::statusBadge($status, $mapping);
    }

    /**
     * Render due date status with icon
     */
    public static function dueDateStatus($status, $days = 0)
    {
        $icons = [
            'terlambat' => '<i class="bi bi-exclamation-triangle-fill me-1"></i>',
            'mendekati' => '<i class="bi bi-clock-fill me-1"></i>',
            'lunas' => '<i class="bi bi-check-circle-fill me-1"></i>',
            'normal' => ''
        ];

        $classes = [
            'terlambat' => 'text-danger fw-bold',
            'mendekati' => 'text-warning fw-bold',
            'lunas' => 'text-success',
            'normal' => 'text-muted'
        ];

        $tooltips = [
            'terlambat' => "Terlambat " . abs($days) . " hari dari tanggal masuk kamar",
            'mendekati' => $days == 0 ? "Jatuh tempo hari ini" : "Sisa {$days} hari",
            'lunas' => "Sudah lunas",
            'normal' => "Masih normal"
        ];

        $icon = $icons[$status] ?? '';
        $class = $classes[$status] ?? 'text-muted';
        $tooltip = $tooltips[$status] ?? '';

        return "<span class=\"{$class}\" title=\"{$tooltip}\">{$icon}</span>";
    }

    /**
     * Render occupant list for a room
     */
    public static function occupantList($occupants)
    {
        if (empty($occupants)) {
            return '<span class="text-muted">-</span>';
        }

        $html = '<div class="penghuni-list">';
        foreach ($occupants as $index => $occupant) {
            $borderClass = $index > 0 ? 'border-top pt-1' : '';
            $html .= "<div class=\"penghuni-item mb-1 {$borderClass}\">";
            $html .= "<strong>" . htmlspecialchars($occupant['nama']) . "</strong>";
            $html .= "<br><small class=\"text-muted\">";
            $html .= "Masuk: " . Html::date($occupant['tgl_masuk']);
            $html .= "</small>";
            if (!empty($occupant['no_ktp'])) {
                $html .= "<br><small class=\"text-muted\">";
                $html .= "KTP: " . htmlspecialchars($occupant['no_ktp']);
                $html .= "</small>";
            }
            $html .= "</div>";
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * Render belongings list grouped by occupant
     */
    public static function belongingsList($occupants)
    {
        $html = '<div class="barang-bawaan-list">';
        $hasItems = false;

        foreach ($occupants as $index => $occupant) {
            if (!empty($occupant['barang_bawaan'])) {
                $hasItems = true;
                $borderClass = $index > 0 ? 'border-top pt-2' : '';
                $html .= "<div class=\"penghuni-barang mb-2 {$borderClass}\">";
                $html .= "<small class=\"text-muted fw-bold\">" . htmlspecialchars($occupant['nama']) . ":</small>";
                $html .= '<div class="d-flex flex-wrap gap-1 mt-1">';
                
                foreach ($occupant['barang_bawaan'] as $item) {
                    $title = htmlspecialchars($item['nama_barang']) . " (+". Html::currency($item['harga_barang']) . ")";
                    $html .= "<span class=\"badge bg-warning text-dark\" style=\"font-size: 0.7rem;\" title=\"{$title}\">";
                    $html .= htmlspecialchars($item['nama_barang']);
                    $html .= "</span>";
                }
                
                $html .= '</div></div>';
            }
        }

        $html .= '</div>';

        return $hasItems ? $html : '<span class="text-muted">-</span>';
    }

    /**
     * Render room action buttons
     */
    public static function roomActionButtons($room)
    {
        $buttons = [];

        // Prepare minimal data for edit function (avoid complex nested objects)
        $editData = [
            'id' => $room['id'],
            'gedung' => $room['gedung'],
            'nomor' => $room['nomor'],
            'harga' => $room['harga']
        ];

        // Edit button
        $buttons[] = [
            'icon' => '<i class="bi bi-pencil"></i>',
            'class' => 'btn-outline-primary',
            'onclick' => "editKamar(" . json_encode($editData) . ")",
            'title' => 'Edit Kamar'
        ];

        // Delete/Lock button
        if ($room['status'] == 'kosong') {
            $buttons[] = [
                'icon' => '<i class="bi bi-trash"></i>',
                'class' => 'btn-outline-danger',
                'onclick' => "deleteKamar({$room['id']}, '" . addslashes($room['nomor']) . "')",
                'title' => 'Hapus Kamar'
            ];
        } else {
            $buttons[] = [
                'icon' => '<i class="bi bi-lock"></i>',
                'class' => 'btn-outline-secondary',
                'disabled' => true,
                'title' => 'Kamar sedang terisi'
            ];
        }

        return Html::actionButtons($buttons);
    }

    /**
     * Render occupant action buttons
     */
    public static function occupantActionButtons($occupant)
    {
        $buttons = [];

        if (!$occupant['tgl_keluar']) {
            // Edit button
            $buttons[] = [
                'type' => 'button',
                'icon' => '<i class="bi bi-pencil"></i>',
                'class' => 'btn-outline-primary',
                'onclick' => "editPenghuni(" . htmlspecialchars(json_encode($occupant)) . ")",
                'title' => 'Edit'
            ];

            // Move room button
            $buttons[] = [
                'type' => 'button',
                'icon' => '<i class="bi bi-box-arrow-right"></i>',
                'class' => 'btn-outline-warning',
                'onclick' => "pindahKamar({$occupant['id']}, '" . htmlspecialchars($occupant['nama']) . "')",
                'title' => 'Pindah Kamar'
            ];

            // Checkout button
            $buttons[] = [
                'type' => 'button',
                'icon' => '<i class="bi bi-box-arrow-left"></i>',
                'class' => 'btn-outline-danger',
                'onclick' => "checkoutPenghuni({$occupant['id']}, '" . htmlspecialchars($occupant['nama']) . "')",
                'title' => 'Checkout'
            ];
        }

        return Html::actionButtons($buttons);
    }

    /**
     * Generate room select options
     */
    public static function roomSelectOptions($rooms, $selectedId = '')
    {
        $options = ['' => '-- Belum pilih kamar --'];
        
        foreach ($rooms as $room) {
            $text = "Kamar {$room['nomor']} - " . Html::currency($room['harga']);
            if (isset($room['slot_tersedia'])) {
                $text .= " ({$room['slot_tersedia']} slot tersedia)";
            }
            $options[$room['id']] = $text;
        }

        return Html::select('id_kamar', $options, $selectedId, ['class' => 'form-select']);
    }

    /**
     * Generate belongings checkboxes
     */
    public static function belongingsCheckboxes($items, $selectedIds = [])
    {
        $html = '<div class="row">';
        
        foreach ($items as $item) {
            $checked = in_array($item['id'], $selectedIds) ? 'checked' : '';
            $price = Html::currency($item['harga']);
            
            $html .= '<div class="col-md-6 mb-2">';
            $html .= '<div class="form-check">';
            $html .= "<input class=\"form-check-input\" type=\"checkbox\" name=\"barang_ids[]\" value=\"{$item['id']}\" id=\"barang{$item['id']}\" {$checked}>";
            $html .= "<label class=\"form-check-label\" for=\"barang{$item['id']}\">";
            $html .= htmlspecialchars($item['nama']);
            $html .= " <small class=\"text-muted\">(+{$price})</small>";
            $html .= '</label>';
            $html .= '</div>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Render empty state message
     */
    public static function emptyState($icon, $title, $message, $actionButton = '')
    {
        $html = '<div class="text-center py-5">';
        $html .= "<i class=\"bi bi-{$icon} text-muted\" style=\"font-size: 4rem;\"></i>";
        $html .= "<h5 class=\"text-muted mt-3\">{$title}</h5>";
        $html .= "<p class=\"text-muted\">{$message}</p>";
        if ($actionButton) {
            $html .= $actionButton;
        }
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Generate building badge
     */
    public static function buildingBadge($buildingNumber)
    {
        return Html::badge("Gedung {$buildingNumber}", 'primary');
    }

    /**
     * Generate summary card
     */
    public static function summaryCard($title, $value, $icon, $color = 'primary')
    {
        $content = '<div class="d-flex align-items-center">';
        $content .= "<div class=\"flex-shrink-0\"><i class=\"bi bi-{$icon} text-{$color}\" style=\"font-size: 2rem;\"></i></div>";
        $content .= '<div class="flex-grow-1 ms-3">';
        $content .= "<h4 class=\"mt-2 text-{$color}\">{$value}</h4>";
        $content .= "<small>{$title}</small>";
        $content .= '</div>';
        $content .= '</div>';
        
        return Html::card('', $content);
    }
}