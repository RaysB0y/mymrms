<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$this->load->view('layout/header');
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

<div class="container-fluid">
    <div class="mb-4">
        <h3 class="fw-bold mb-1">Master Material</h3>
        <p class="text-muted mb-0">Kelola data material dan stock warehouse.</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-end mb-3">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createMaterialModal">
                    <i class="bi bi-plus-lg"></i> Tambah Material
                </button>
            </div>

            <div class="table-responsive">
                <table id="warehouseTable" class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="60">No</th>
                            <th>Kode Material</th>
                            <th>Nama Material</th>
                            <th>Unit</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th width="220">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($materials)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1"></i>
                                    <div class="mt-2">Belum ada data material.</div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; ?>
                            <?php foreach ($materials as $material): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><strong><?php echo $material->material_code; ?></strong></td>
                                    <td><?php echo $material->material_name; ?></td>
                                    <td><?php echo $material->unit; ?></td>
                                    <td><strong><?php echo $material->quantity; ?></strong></td>
                                    <td>
                                        <?php if ($material->is_active): ?>
                                            <span class="badge bg-success">ACTIVE</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">INACTIVE</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editMaterialModal<?php echo $material->id; ?>">
                                                Edit
                                            </button>

                                            <a href="<?php echo base_url('index.php/warehouse/transactions/' . $material->id); ?>" class="btn btn-sm btn-outline-info">
                                                Transaksi
                                            </a>

                                            <?php if ($material->is_active): ?>
                                                <form action="<?php echo base_url('index.php/warehouse/delete/' . $material->id); ?>" method="post" data-swal-confirm="Nonaktifkan material ini?">
                                                    <?php echo csrf_field(); ?>
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Nonaktifkan</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>

                                <div class="modal fade" id="editMaterialModal<?php echo $material->id; ?>" tabindex="-1" aria-labelledby="editMaterialModalLabel<?php echo $material->id; ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <div>
                                                    <h5 class="modal-title fw-bold" id="editMaterialModalLabel<?php echo $material->id; ?>">Edit Material</h5>
                                                    <small class="text-muted">Perbarui data master material.</small>
                                                </div>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>

                                            <form action="<?php echo base_url('index.php/warehouse/edit/' . $material->id); ?>" method="post">
                                                <?php echo csrf_field(); ?>

                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Kode Material</label>
                                                            <input type="text" class="form-control" value="<?php echo html_escape($material->material_code); ?>" readonly>
                                                            <div class="form-text">Kode dibuat otomatis dan tidak dapat diubah.</div>
                                                        </div>

                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Nama Material</label>
                                                            <input type="text" name="material_name" class="form-control" value="<?php echo $material->material_name; ?>" required>
                                                        </div>

                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">UoM / Unit</label>
                                                            <select name="unit" class="form-select" required>
                                                                <?php foreach ($standard_uoms as $uom): ?>
                                                                    <option value="<?php echo $uom; ?>" <?php echo $material->unit === $uom ? 'selected' : ''; ?>><?php echo $uom; ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>

                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Quantity</label>
                                                            <input type="number" name="quantity" class="form-control" min="0" step="0.001" value="<?php echo $material->quantity; ?>" required>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="createMaterialModal" tabindex="-1" aria-labelledby="createMaterialModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold" id="createMaterialModalLabel">Tambah Material</h5>
                    <small class="text-muted">Tambahkan material baru ke master material.</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="<?php echo base_url('index.php/warehouse/create'); ?>" method="post">
                <?php echo csrf_field(); ?>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="material_code" class="form-label">Kode Material</label>
                            <input type="text" id="material_code" class="form-control" value="<?php echo html_escape($next_material_code); ?>" readonly>
                            <div class="form-text">Kode dibuat otomatis saat material disimpan.</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="material_name" class="form-label">Nama Material</label>
                            <input type="text" name="material_name" id="material_name" class="form-control" placeholder="Contoh: Cement" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="unit" class="form-label">UoM / Unit</label>
                            <select name="unit" id="unit" class="form-select" required>
                                <option value="">Pilih UoM standar</option>
                                <?php foreach ($standard_uoms as $uom): ?>
                                    <option value="<?php echo $uom; ?>"><?php echo $uom; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="quantity" class="form-label">Quantity</label>
                            <input type="number" name="quantity" id="quantity" class="form-control" min="0" step="0.001" value="0" required>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Material</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    $('#warehouseTable').DataTable({
        "pageLength": 10,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
        }
    });
});
</script>

<?php $this->load->view('layout/footer'); ?>
