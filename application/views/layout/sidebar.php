<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$role = $this->session->userdata('role');
$current_uri = uri_string();
?>

<div class="col-md-3 col-lg-2 bg-dark min-vh-100">
    <div class="p-3">
        <div class="text-white-50 small text-uppercase mb-3">Menu</div>

        <?php if ($role === 'DOCONT'): ?>

            <div class="nav nav-pills flex-column gap-1">
                <a href="<?= site_url('rfi'); ?>" class="nav-link text-white <?= strpos($current_uri, 'rfi') === 0 ? 'active' : ''; ?>">
                    <i class="bi bi-file-earmark-text me-2"></i>Material Request
                </a>
            </div>

        <?php elseif ($role === 'MATERIAL_MAN'): ?>

            <div class="nav nav-pills flex-column gap-1">
                <a href="<?= site_url('warehouse'); ?>" class="nav-link text-white <?= strpos($current_uri, 'warehouse') === 0 ? 'active' : ''; ?>">
                    <i class="bi bi-box-seam me-2"></i>Warehouse
                </a>
            </div>

        <?php elseif ($role === 'INSPECTOR'): ?>

            <div class="nav nav-pills flex-column gap-1">
                <a href="<?= site_url('inspector'); ?>" class="nav-link text-white <?= strpos($current_uri, 'inspector') === 0 ? 'active' : ''; ?>">
                    <i class="bi bi-clipboard-check me-2"></i>Inspector
                </a>
            </div>

        <?php endif; ?>

        <hr class="border-secondary">
        <div class="text-white-50 small">Login sebagai</div>
        <div class="text-white fw-semibold"><?= html_escape($this->session->userdata('name')); ?></div>
        <div class="text-white-50 small"><?= html_escape($role); ?></div>
    </div>
</div>
