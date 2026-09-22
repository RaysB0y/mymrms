<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$this->load->view('layout/header');
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

<div class="container-fluid">
    <div class="mb-4">
        <h3 class="fw-bold mb-1">Request for Information</h3>
        <p class="text-muted mb-0">Kelola RFI yang dibuat oleh requester.</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-end mb-3">
                <a href="<?php echo base_url('index.php/rfi/create'); ?>" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Buat RFI
                </a>
            </div>

            <div class="table-responsive">
                <table id="rfiTable" class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="60">No</th>
                            <th>RFI Number</th>
                            <th>Revision</th>
                            <th>Project</th>
                            <th>Inspector</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                            <th width="100">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rfis)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1"></i>
                                    <div class="mt-2">Belum ada RFI.</div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; ?>
                            <?php foreach ($rfis as $rfi): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td>
                                        <strong><?php echo $rfi->rfi_number; ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            Rev <?php echo $rfi->revision; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong><?php echo $rfi->project_code; ?></strong>
                                        <div class="small text-muted">
                                            <?php echo $rfi->project_name; ?>
                                        </div>
                                    </td>
                                    <td><?php echo $rfi->inspector_name; ?></td>
                                    <td>
                                        <?php if ($rfi->status === 'DRAFT'): ?>
                                            <span class="badge bg-secondary">DRAFT</span>
                                        <?php elseif ($rfi->status === 'SUBMITTED'): ?>
                                            <span class="badge bg-primary">SUBMITTED</span>
                                        <?php elseif ($rfi->status === 'REVISION_REQUIRED'): ?>
                                            <span class="badge bg-warning text-dark">REVISION REQUIRED</span>
                                        <?php elseif ($rfi->status === 'APPROVED'): ?>
                                            <span class="badge bg-success">APPROVED</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo date('d-m-Y H:i', strtotime($rfi->created_at)); ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo base_url('index.php/rfi/detail/' . $rfi->id); ?>" class="btn btn-sm btn-outline-primary">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    $('#rfiTable').DataTable({
        "pageLength": 10,
        "order": [[6, "desc"]],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
        }
    });
});
</script>

<?php $this->load->view('layout/footer'); ?>
