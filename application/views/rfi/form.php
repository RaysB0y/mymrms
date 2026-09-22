<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$this->load->view('layout/header');

$is_edit = isset($rfi);
?>

<div class="container-fluid">
    <div class="mb-4">
        <h3 class="fw-bold mb-1"><?php echo $is_edit ? 'Edit Request for Information' : 'Buat Request for Information'; ?></h3>
        <p class="text-muted mb-0"><?php echo $is_edit ? 'Kelola material dan quantity RFI sebelum disubmit kepada Inspector.' : 'Buat RFI baru dan tentukan Inspector yang akan melakukan review dan validasi.'; ?></p>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <?php if (!$is_edit): ?>
                <form action="<?php echo base_url('index.php/rfi/create'); ?>" method="post">
                    <?php echo csrf_field(); ?>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">RFI Number</label>
                            <input type="text" class="form-control" value="<?php echo $rfi_number; ?>" readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Revision</label>
                            <input type="text" class="form-control" value="Rev 0" readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="project_id" class="form-label">Project</label>
                            <select name="project_id" id="project_id" class="form-select" required>
                                <option value="">Pilih Project</option>
                                <?php foreach ($projects as $project): ?>
                                    <option value="<?php echo $project->id; ?>">
                                        <?php echo $project->project_code; ?> - <?php echo $project->project_name; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="inspector_id" class="form-label">Inspector</label>
                            <select name="inspector_id" id="inspector_id" class="form-select" required>
                                <option value="">Pilih Inspector</option>
                                <?php foreach ($inspectors as $inspector): ?>
                                    <option value="<?php echo $inspector->id; ?>">
                                        <?php echo $inspector->name; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="border-top pt-3 mt-2">
                        <h5 class="fw-bold mb-1">Material</h5>
                        <p class="text-muted mb-0 small">Material dan quantity dapat ditambahkan setelah RFI dibuat.</p>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        Setelah RFI dibuat, Anda dapat menambahkan material dan quantity pada halaman edit RFI sebelum melakukan submit ke Inspector.
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="<?php echo base_url('index.php/rfi'); ?>" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Buat RFI</button>
                    </div>
                </form>
            <?php else: ?>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">RFI Number</label>
                        <input type="text" class="form-control" value="<?php echo $rfi->rfi_number; ?>" readonly>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Revision</label>
                        <input type="text" class="form-control" value="Rev <?php echo $rfi->revision; ?>" readonly>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Status</label>
                        <input type="text" class="form-control" value="<?php echo $rfi->status; ?>" readonly>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Project</label>
                        <input type="text" class="form-control" value="<?php echo $rfi->project_code; ?> - <?php echo $rfi->project_name; ?>" readonly>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Inspector</label>
                        <input type="text" class="form-control" value="<?php echo $rfi->inspector_name; ?>" readonly>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($is_edit): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold mb-1">Material Request</h5>
                        <p class="text-muted mb-0 small">Tambahkan material dan quantity yang dibutuhkan.</p>
                    </div>

                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMaterialModal">
                        <i class="bi bi-plus-lg"></i> Tambah Material
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="60">No</th>
                                <th>Material</th>
                                <th>Requested</th>
                                <th>Unit</th>
                                <th>Status</th>
                                <th>Remark</th>
                                <th width="150">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1"></i>
                                        <div class="mt-2">Belum ada material request.</div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; ?>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td>
                                            <strong><?php echo $item->material_code; ?></strong>
                                            <div class="small text-muted"><?php echo $item->material_name; ?></div>
                                        </td>
                                        <td><?php echo $item->requested_qty; ?></td>
                                        <td><?php echo $item->uom; ?></td>
                                        <td>
                                            <?php if ($item->status === 'PENDING'): ?>
                                                <span class="badge bg-secondary">PENDING</span>
                                            <?php elseif ($item->status === 'READY'): ?>
                                                <span class="badge bg-success">READY</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">NOT READY</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $item->remark ?: '-'; ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editMaterialModal<?php echo $item->id; ?>">
                                                Edit
                                            </button>

                                            <form method="post" action="<?php echo base_url('index.php/rfi/delete_item/' . $rfi->id . '/' . $item->id); ?>" class="d-inline" data-swal-confirm="Hapus material ini dari RFI?" data-swal-icon="warning">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    Hapus
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="<?php echo base_url('index.php/rfi'); ?>" class="btn btn-secondary">
                Kembali
            </a>

            <?php if (!empty($items)): ?>
                <form method="post" action="<?php echo base_url('index.php/rfi/submit/' . $rfi->id); ?>" data-swal-confirm="Submit RFI kepada Inspector?" data-swal-icon="question">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn btn-success">
                        Submit RFI
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <div class="modal fade" id="addMaterialModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="post" action="<?php echo base_url('index.php/rfi/add_item/' . $rfi->id); ?>">
                        <?php echo csrf_field(); ?>

                        <div class="modal-header">
                            <h5 class="modal-title">Tambah Material</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="material_id" class="form-label">Material</label>
                                <select name="material_id" id="material_id" class="form-select" required>
                                    <option value="">Pilih Material</option>
                                    <?php foreach ($materials as $material): ?>
                                        <option value="<?php echo $material->id; ?>">
                                            <?php echo $material->material_code; ?> - <?php echo $material->material_name; ?> (<?php echo $material->unit; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="requested_qty" class="form-label">Requested Quantity</label>
                                <input type="number" name="requested_qty" id="requested_qty" class="form-control" min="0.001" step="0.001" required>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Tambah</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php foreach ($items as $item): ?>
            <div class="modal fade" id="editMaterialModal<?php echo $item->id; ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="post" action="<?php echo base_url('index.php/rfi/update_item/' . $rfi->id . '/' . $item->id); ?>">
                            <?php echo csrf_field(); ?>

                            <div class="modal-header">
                                <h5 class="modal-title">Edit Material</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Material</label>
                                    <input type="text" class="form-control" value="<?php echo $item->material_code; ?> - <?php echo $item->material_name; ?>" readonly>
                                </div>

                                <div class="mb-3">
                                    <label for="requested_qty_<?php echo $item->id; ?>" class="form-label">Requested Quantity</label>
                                    <input type="number" name="requested_qty" id="requested_qty_<?php echo $item->id; ?>" class="form-control" min="0.001" step="0.001" value="<?php echo $item->requested_qty; ?>" required>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php $this->load->view('layout/footer'); ?>
