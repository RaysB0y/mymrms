<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->view('layout/header');
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Riwayat Transaksi Material</h3>
        <p class="text-muted mb-0"><?php echo html_escape($material->material_code); ?> — <?php echo html_escape($material->material_name); ?></p>
    </div>
    <a href="<?php echo site_url('warehouse'); ?>" class="btn btn-outline-secondary">Kembali</a>
</div>
<div class="card border-0 shadow-sm"><div class="card-body p-0"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr><th>Tanggal</th><th>Tipe</th><th>Perubahan</th><th>Stok Sebelum</th><th>Stok Sesudah</th><th>Referensi</th><th>Dibuat Oleh</th></tr></thead>
        <tbody>
        <?php if (empty($transactions)): ?>
            <tr><td colspan="7" class="text-center py-5 text-muted">Belum ada transaksi.</td></tr>
        <?php else: foreach ($transactions as $transaction): ?>
            <tr>
                <td><?php echo date('d-m-Y H:i', strtotime($transaction->created_at)); ?></td>
                <td><span class="badge <?php echo $transaction->transaction_type === 'OUT' ? 'bg-danger' : 'bg-success'; ?>"><?php echo html_escape($transaction->transaction_type); ?></span></td>
                <td><?php echo html_escape($transaction->quantity); ?></td>
                <td><?php echo html_escape($transaction->quantity_before); ?></td>
                <td><?php echo html_escape($transaction->quantity_after); ?></td>
                <td><?php echo html_escape($transaction->reference_type . ' #' . $transaction->reference_id); ?></td>
                <td><?php echo html_escape($transaction->creator_name ?: '-'); ?></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div></div></div>
<?php $this->load->view('layout/footer'); ?>
