<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<footer class="bg-white border-top py-3 mt-auto">
    <div class="container-fluid text-center">
        <small class="text-muted">
            Material Request Management System
            &copy; <?= date('Y'); ?>
        </small>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Bootstrap JS -->
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

<?php
$success_message = $this->session->flashdata('success');
$error_message = $this->session->flashdata('error');
?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const notification = <?php echo json_encode(array(
        'success' => $success_message,
        'error' => $error_message
    )); ?>;

    if (notification.success) {
        Swal.fire({ icon: 'success', title: 'Berhasil', text: notification.success, timer: 2600, showConfirmButton: false });
    } else if (notification.error) {
        Swal.fire({ icon: 'error', title: 'Gagal', text: notification.error });
    }

    document.querySelectorAll('form[data-swal-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (form.dataset.swalConfirmed === 'true') return;
            event.preventDefault();
            Swal.fire({
                icon: form.dataset.swalIcon || 'warning',
                title: form.dataset.swalTitle || 'Konfirmasi tindakan',
                text: form.dataset.swalConfirm,
                showCancelButton: true,
                confirmButtonText: 'Ya, lanjutkan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#0d6efd'
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.dataset.swalConfirmed = 'true';
                    form.submit();
                }
            });
        });
    });

});
</script>

</body>
</html>
