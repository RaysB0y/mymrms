<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$this->load->view('layout/header');
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

<div class="container-fluid">
    <div class="mb-4">
        <h3 class="fw-bold mb-1">Request for Information</h3>
        <p class="text-muted mb-0">RFI yang ditujukan kepada Anda untuk dilakukan review dan validasi.</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table id="inspectorTable" class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="60">No</th>
                            <th>RFI Number</th>
                            <th>Revision</th>
                            <th>Project</th>
                            <th>Requester</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th width="100">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rfis)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1"></i>
                                    <div class="mt-2">Belum ada RFI yang ditujukan kepada Anda.</div>
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
                                    <td><?php echo $rfi->requester_name; ?></td>
                                    <td>
                                        <?php if ($rfi->status === 'SUBMITTED'): ?>
                                            <span class="badge bg-primary">SUBMITTED</span>
                                        <?php elseif ($rfi->status === 'APPROVED'): ?>
                                            <span class="badge bg-success">APPROVED</span>
                                        <?php elseif ($rfi->status === 'REVISION_REQUIRED'): ?>
                                            <span class="badge bg-warning text-dark">REVISION REQUIRED</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">
                                                <?php echo $rfi->status; ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo $rfi->submitted_at ? date('d-m-Y H:i', strtotime($rfi->submitted_at)) : '-'; ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo base_url('index.php/inspector/detail/' . $rfi->id); ?>" class="btn btn-sm btn-outline-primary">
                                            Review
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
    $('#inspectorTable').DataTable({
        "pageLength": 10,
        "order": [[6, "desc"]],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
        }
    });
});
</script>

<?php $this->load->view('layout/footer'); ?>
