<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$login_error = !empty($error)
    ? $error
    : $this->session->flashdata('error');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        <?php echo isset($title)
            ? html_escape($title) . ' - Material Request'
            : 'Login - Material Request'; ?>
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">
<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-5 col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">

                    <div class="text-center mb-4">
                        <div class="fs-1 text-primary">
                            <i class="bi bi-box-seam"></i>
                        </div>

                        <h4 class="fw-bold mb-1">
                            Material Request
                        </h4>

                        <p class="text-muted mb-0">
                            Management System
                        </p>
                    </div>

                    <form
                        method="post"
                        action="<?php echo base_url('index.php/auth/login'); ?>"
                    >
                        <?php echo csrf_field(); ?>

                        <div class="mb-3">
                            <label
                                for="username"
                                class="form-label"
                            >
                                Username
                            </label>

                            <input
                                type="text"
                                id="username"
                                name="username"
                                class="form-control"
                                placeholder="Masukkan username"
                                value="<?php echo set_value('username'); ?>"
                                autocomplete="username"
                                required
                                autofocus
                            >
                        </div>

                        <div class="mb-4">
                            <label
                                for="password"
                                class="form-label"
                            >
                                Password
                            </label>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control"
                                placeholder="Masukkan password"
                                autocomplete="current-password"
                                required
                            >
                        </div>

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >
                            <i class="bi bi-box-arrow-in-right me-1"></i>
                            Login
                        </button>
                    </form>

                </div>
            </div>

            <div class="text-center mt-3">
                <small class="text-muted">
                    Material Request Management System
                </small>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php if ($login_error): ?>
<script>
Swal.fire({
    icon: 'error',
    title: 'Login gagal',
    text: <?php echo json_encode($login_error); ?>
});
</script>
<?php endif; ?>
</body>
</html>
