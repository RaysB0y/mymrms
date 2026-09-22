<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$this->load->view('layout/header');
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">RFI Detail</h3>
            <p class="text-muted mb-0">
                <?php echo html_escape($rfi->rfi_number); ?>
            </p>
        </div>
        <a href="<?php echo base_url('index.php/inspector'); ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Kembali
        </a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-3">
                    <small class="text-muted">RFI Number</small>
                    <div class="fw-semibold">
                        <?php echo html_escape($rfi->rfi_number); ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <small class="text-muted">Project</small>
                    <div class="fw-semibold">
                        <?php echo html_escape($rfi->project_code); ?> -
                        <?php echo html_escape($rfi->project_name); ?>
                    </div>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Requester</small>
                    <div class="fw-semibold">
                        <?php echo html_escape($rfi->requester_name); ?>
                    </div>
                </div>
                <div class="col-md-2">
                    <small class="text-muted">Revision</small>
                    <div class="fw-semibold">
                        Rev <?php echo (int) $rfi->revision; ?>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <small class="text-muted">Status</small>
                <div>
                    <?php if ($rfi->status === 'SUBMITTED'): ?>
                        <span class="badge text-bg-primary">SUBMITTED</span>
                    <?php elseif ($rfi->status === 'REVISION_REQUIRED'): ?>
                        <span class="badge text-bg-warning">REVISION REQUIRED</span>
                    <?php elseif ($rfi->status === 'APPROVED'): ?>
                        <span class="badge text-bg-success">APPROVED</span>
                    <?php else: ?>
                        <span class="badge text-bg-secondary">
                            <?php echo html_escape($rfi->status); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-1">Pemeriksaan Material</h5>
            <small class="text-muted">
                Periksa setiap material berdasarkan jumlah yang diminta dan stock master material.
            </small>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Material</th>
                            <th>Requested</th>
                            <th>Available</th>
                            <th>Approved</th>
                            <th>Shortage</th>
                            <th>UOM</th>
                            <th>Status</th>
                            <th>Remark</th>
                            <th>Checked By</th>
                            <th>Checked At</th>
                            <?php if ($rfi->status === 'SUBMITTED'): ?>
                                <th>Action</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($items)): ?>
                            <?php foreach ($items as $no => $item): ?>
                                <?php
                                $requested_qty = (float) $item->requested_qty;
                                $available_qty = (float) $item->available_qty;
                                $approved_qty = min($requested_qty, $available_qty);
                                $shortage_qty = max($requested_qty - $available_qty, 0);
                                ?>
                                <tr>
                                    <td><?php echo $no + 1; ?></td>
                                    <td>
                                        <div class="fw-semibold">
                                            <?php echo html_escape($item->material_code); ?>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo html_escape($item->material_name); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php echo rtrim(rtrim(number_format($requested_qty, 3, '.', ''), '0'), '.'); ?>
                                    </td>
                                    <td>
                                        <span class="fw-semibold">
                                            <?php echo rtrim(rtrim(number_format($available_qty, 3, '.', ''), '0'), '.'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($item->status === 'READY'): ?>
                                            <span class="text-success fw-semibold">
                                                <?php echo rtrim(rtrim(number_format($approved_qty, 3, '.', ''), '0'), '.'); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">0</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($shortage_qty > 0): ?>
                                            <span class="text-danger fw-semibold">
                                                <?php echo rtrim(rtrim(number_format($shortage_qty, 3, '.', ''), '0'), '.'); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-success">0</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo html_escape($item->uom); ?></td>
                                    <td>
                                        <?php if ($item->status === 'PENDING'): ?>
                                            <span class="badge text-bg-secondary">PENDING</span>
                                        <?php elseif ($item->status === 'READY'): ?>
                                            <span class="badge text-bg-success">READY</span>
                                        <?php elseif ($item->status === 'NOT_READY'): ?>
                                            <span class="badge text-bg-danger">NOT READY</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($item->remark)): ?>
                                            <?php echo html_escape($item->remark); ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo !empty($item->checker_name)
                                            ? html_escape($item->checker_name)
                                            : '-'; ?>
                                    </td>
                                    <td>
                                        <?php echo !empty($item->checked_at)
                                            ? date('d-m-Y H:i:s', strtotime($item->checked_at))
                                            : '-'; ?>
                                    </td>
                                    <?php if ($rfi->status === 'SUBMITTED'): ?>
                                        <td>
                                            <?php if ($item->status === 'PENDING'): ?>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-primary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#checkModal<?php echo $item->id; ?>"
                                                >
                                                    <i class="bi bi-check2-square"></i>
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted">Selesai</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td
                                    colspan="<?php echo $rfi->status === 'SUBMITTED' ? '12' : '11'; ?>"
                                    class="text-center py-5"
                                >
                                    <i class="bi bi-inbox fs-1 text-muted"></i>
                                    <p class="text-muted mb-0 mt-2">Tidak ada material.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php if ($rfi->status === 'SUBMITTED' && !empty($items)): ?>
    <?php foreach ($items as $item): ?>
        <?php if ($item->status === 'PENDING'): ?>
            <?php
            $requested_qty = (float) $item->requested_qty;
            $available_qty = (float) $item->available_qty;
            $approved_qty = min($requested_qty, $available_qty);
            $shortage_qty = max($requested_qty - $available_qty, 0);
            ?>
            <div class="modal fade" id="checkModal<?php echo $item->id; ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Pemeriksaan Material</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <form method="post" action="<?php echo base_url('index.php/inspector/check_item/' . $item->id); ?>">
                            <?php echo csrf_field(); ?>

                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Material</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        value="<?php echo html_escape($item->material_code . ' - ' . $item->material_name); ?>"
                                        readonly
                                    >
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label text-muted">Requested</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            value="<?php echo rtrim(rtrim(number_format($requested_qty, 3, '.', ''), '0'), '.'); ?>"
                                            readonly
                                        >
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label text-muted">Available</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            value="<?php echo rtrim(rtrim(number_format($available_qty, 3, '.', ''), '0'), '.'); ?>"
                                            readonly
                                        >
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label text-muted">UOM</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            value="<?php echo html_escape($item->uom); ?>"
                                            readonly
                                        >
                                    </div>
                                </div>

                                <div class="alert alert-light border mb-3">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">Approved Quantity</small>
                                            <strong class="text-success">
                                                <?php echo rtrim(rtrim(number_format($approved_qty, 3, '.', ''), '0'), '.'); ?>
                                                <?php echo html_escape($item->uom); ?>
                                            </strong>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">Shortage</small>
                                            <strong class="<?php echo $shortage_qty > 0 ? 'text-danger' : 'text-success'; ?>">
                                                <?php echo rtrim(rtrim(number_format($shortage_qty, 3, '.', ''), '0'), '.'); ?>
                                                <?php echo html_escape($item->uom); ?>
                                            </strong>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($shortage_qty > 0): ?>
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        Stock tidak mencukupi. Jika material dinyatakan READY, sistem akan menggunakan
                                        quantity yang tersedia sebagai Approved Quantity.
                                    </div>
                                <?php endif; ?>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Status</label>
                                    <select
                                        name="status"
                                        class="form-select check-status"
                                        data-target="remark<?php echo $item->id; ?>"
                                        required
                                    >
                                        <option value="">-- Pilih Status --</option>
                                        <option value="READY">READY</option>
                                        <option value="NOT_READY">NOT READY</option>
                                    </select>
                                </div>

                                <div class="mb-3 d-none" id="remark<?php echo $item->id; ?>">
                                    <label class="form-label fw-semibold">Remark</label>
                                    <textarea
                                        name="remark"
                                        class="form-control"
                                        rows="3"
                                        placeholder="Jelaskan alasan material NOT READY"
                                    ></textarea>
                                    <div class="form-text">
                                        Remark wajib diisi jika NOT READY.
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    Batal
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-1"></i>
                                    Simpan Pemeriksaan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.check-status').forEach(function (select) {
        select.addEventListener('change', function () {
            const target = document.getElementById(this.dataset.target);

            if (!target) {
                return;
            }

            target.classList.toggle('d-none', this.value !== 'NOT_READY');

            const textarea = target.querySelector('textarea');

            if (textarea) {
                textarea.required = this.value === 'NOT_READY';

                if (this.value !== 'NOT_READY') {
                    textarea.value = '';
                }
            }
        });
    });
});
</script>

<?php $this->load->view('layout/footer'); ?>
