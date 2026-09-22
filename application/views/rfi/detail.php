<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$this->load->view('layout/header');
?>

<div class="container-fluid">
    <div class="mb-4">
        <h3 class="fw-bold mb-1">Detail Request for Information</h3>
        <p class="text-muted mb-0">Informasi lengkap RFI, material request, review, dan riwayat revision.</p>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h4 class="fw-bold mb-1"><?php echo $rfi->rfi_number; ?></h4>
                    <span class="badge bg-secondary">Revision <?php echo $rfi->revision; ?></span>
                </div>

                <div>
                    <?php if ($rfi->status === 'DRAFT'): ?>
                        <span class="badge bg-secondary">DRAFT</span>
                    <?php elseif ($rfi->status === 'SUBMITTED'): ?>
                        <span class="badge bg-primary">SUBMITTED</span>
                    <?php elseif ($rfi->status === 'REVISION_REQUIRED'): ?>
                        <span class="badge bg-warning text-dark">REVISION REQUIRED</span>
                    <?php elseif ($rfi->status === 'APPROVED'): ?>
                        <span class="badge bg-success">APPROVED</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <small class="text-muted d-block">Project</small>
                    <strong><?php echo $rfi->project_code; ?></strong>
                    <div class="small text-muted"><?php echo $rfi->project_name; ?></div>
                </div>

                <div class="col-md-4 mb-3">
                    <small class="text-muted d-block">Requester</small>
                    <strong><?php echo $rfi->requester_name; ?></strong>
                </div>

                <div class="col-md-4 mb-3">
                    <small class="text-muted d-block">Inspector</small>
                    <strong><?php echo $rfi->inspector_name; ?></strong>
                </div>

                <div class="col-md-4 mb-3">
                    <small class="text-muted d-block">Dibuat</small>
                    <?php echo date('d-m-Y H:i', strtotime($rfi->created_at)); ?>
                </div>

                <div class="col-md-4 mb-3">
                    <small class="text-muted d-block">Disubmit</small>
                    <?php echo $rfi->submitted_at ? date('d-m-Y H:i', strtotime($rfi->submitted_at)) : '-'; ?>
                </div>

                <div class="col-md-4 mb-3">
                    <small class="text-muted d-block">Approved</small>
                    <?php echo $rfi->approved_at ? date('d-m-Y H:i', strtotime($rfi->approved_at)) : '-'; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($rfi->status === 'REVISION_REQUIRED'): ?>
        <div class="alert alert-warning d-flex justify-content-between align-items-center">
            <div>
                <strong>RFI membutuhkan revisi.</strong>
                <div class="small">Silakan periksa remark dari Inspector sebelum melakukan revision.</div>
            </div>

            <form method="post" action="<?php echo base_url('index.php/rfi/revise/' . $rfi->id); ?>" class="d-inline" data-swal-confirm="RFI akan dibuat menjadi revision baru. Lanjutkan?">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-warning">
                    Revisi RFI
                </button>
            </form>
        </div>
    <?php endif; ?>

    <?php if ($rfi->status === 'DRAFT'): ?>
        <div class="d-flex justify-content-end mb-3">
            <a href="<?php echo base_url('index.php/rfi/edit/' . $rfi->id); ?>" class="btn btn-primary">
                Edit RFI
            </a>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Material Request</h5>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="60">No</th>
                            <th>Material</th>
                            <th>Requested</th>
                            <th>Approved</th>
                            <th>Unit</th>
                            <th>Status</th>
                            <th>Remark</th>
                            <th>Checked By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    Belum ada material request.
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
                                    <td><?php echo $item->approved_qty; ?></td>
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
                                        <?php echo $item->checker_name ?: '-'; ?>
                                        <?php if ($item->checked_at): ?>
                                            <div class="small text-muted">
                                                <?php echo date('d-m-Y H:i', strtotime($item->checked_at)); ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Revision History</h5>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="100">Revision</th>
                            <th>Reason</th>
                            <th>Dibuat Oleh</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($revisions)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    Belum ada revision history.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($revisions as $revision): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary">
                                            Rev <?php echo $revision->revision; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $revision->reason ?: '-'; ?></td>
                                    <td><?php echo $revision->creator_name; ?></td>
                                    <td><?php echo date('d-m-Y H:i', strtotime($revision->created_at)); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Inspector Review History</h5>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="100">Revision</th>
                            <th>Decision</th>
                            <th>Inspector</th>
                            <th>Remark</th>
                            <th>Action At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($approvals)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    Belum ada hasil review Inspector.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($approvals as $approval): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary">
                                            Rev <?php echo $approval->revision; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($approval->decision === 'APPROVED'): ?>
                                            <span class="badge bg-success">APPROVED</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">REVISION REQUIRED</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $approval->inspector_name; ?></td>
                                    <td><?php echo $approval->remark ?: '-'; ?></td>
                                    <td><?php echo date('d-m-Y H:i', strtotime($approval->action_at)); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <a href="<?php echo base_url('index.php/rfi'); ?>" class="btn btn-secondary">
            Kembali
        </a>
    </div>
</div>

<?php $this->load->view('layout/footer'); ?>
