<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$role = $this->session->userdata('role');
$name = $this->session->userdata('name');

if ($role === 'DOCONT') {
    $home_url = site_url('rfi');
} elseif ($role === 'INSPECTOR') {
    $home_url = site_url('inspector');
} else {
    $home_url = site_url('warehouse');
}
?>

<nav class="navbar navbar-dark bg-primary shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="<?= $home_url; ?>"><i class="bi bi-box-seam me-2"></i>Material Request</a>
        <?php if ($this->session->userdata('logged_in')): ?>
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle me-1"></i>
                    <?= html_escape($name); ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <span class="dropdown-item-text">
                            <strong><?= html_escape($name); ?></strong>
                            <br>
                            <small class="text-muted"><?= html_escape($role); ?></small>
                        </span>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?= site_url('logout'); ?>"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</nav>
