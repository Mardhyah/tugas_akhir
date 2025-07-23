<?php
$page = $_GET['page'] ?? '';
$breadcrumb = [];

// Tambahkan default root 'Aplikasi'
$breadcrumb[] = ['label' => 'Aplikasi', 'link' => '', 'active' => false];

switch ($page) {
    case 'dashboard':
        // Tidak perlu breadcrumb tambahan
        break;

    case 'sampah':
        $breadcrumb[] = ['label' => 'Sampah', 'link' => '', 'active' => true];
        break;

    case 'tambah_sampah':
        $breadcrumb[] = ['label' => 'Sampah', 'link' => 'index.php?page=sampah'];
        $breadcrumb[] = ['label' => 'Tambah Sampah', 'link' => '', 'active' => true];
        break;

    case 'ubah_sampah':
        $breadcrumb[] = ['label' => 'Sampah', 'link' => 'index.php?page=sampah'];
        $breadcrumb[] = ['label' => 'Ubah Sampah', 'link' => '', 'active' => true];
        break;

    case 'manage_kategori':
        $breadcrumb[] = ['label' => 'Sampah', 'link' => 'index.php?page=sampah'];
        $breadcrumb[] = ['label' => 'Kategori Sampah', 'link' => '', 'active' => true];
        break;

    case 'nasabah':
        $breadcrumb[] = ['label' => 'Nasabah', 'link' => '', 'active' => true];
        break;

    case 'tambah_nasabah':
        $breadcrumb[] = ['label' => 'Nasabah', 'link' => 'index.php?page=nasabah'];
        $breadcrumb[] = ['label' => 'Tambah Nasabah', 'link' => '', 'active' => true];
        break;

    case 'ubah_nasabah':
        $breadcrumb[] = ['label' => 'Nasabah', 'link' => 'index.php?page=nasabah'];
        $breadcrumb[] = ['label' => 'Ubah Nasabah', 'link' => '', 'active' => true];
        break;

    case 'detail_nasabah':
        $breadcrumb[] = ['label' => 'Nasabah', 'link' => 'index.php?page=nasabah'];
        $breadcrumb[] = ['label' => 'Detail Nasabah', 'link' => '', 'active' => true];
        break;

    case 'admin':
        $breadcrumb[] = ['label' => 'Admin', 'link' => '', 'active' => true];
        break;

    case 'tambah_admin':
        $breadcrumb[] = ['label' => 'Admin', 'link' => 'index.php?page=admin'];
        $breadcrumb[] = ['label' => 'Tambah Admin', 'link' => '', 'active' => true];
        break;

    case 'ubah_admin':
        $breadcrumb[] = ['label' => 'Admin', 'link' => 'index.php?page=admin'];
        $breadcrumb[] = ['label' => 'Ubah Admin', 'link' => '', 'active' => true];
        break;

    case 'detail_user':
        $breadcrumb[] = ['label' => 'Pengguna', 'link' => '', 'active' => true];
        break;

    case 'ubah_password':
        $breadcrumb[] = ['label' => 'Pengguna', 'link' => 'index.php?page=detail_user'];
        $breadcrumb[] = ['label' => 'Ubah Password', 'link' => '', 'active' => true];
        break;

    case 'semua_transaksi':
        $breadcrumb[] = ['label' => 'Semua Transaksi', 'link' => '', 'active' => true];
        break;

    case 'rekap_transaksi':
        $breadcrumb[] = ['label' => 'Rekap Transaksi', 'link' => '', 'active' => true];
        break;


    case 'setor_sampah':
        $breadcrumb[] = ['label' => 'Transaksi', 'link' => 'index.php?page=semua_transaksi'];
        $breadcrumb[] = ['label' => 'Setor Sampah', 'link' => '', 'active' => true];
        break;

    case 'tarik_saldo':
        $breadcrumb[] = ['label' => 'Transaksi', 'link' => 'index.php?page=semua_transaksi'];
        $breadcrumb[] = ['label' => 'Tarik Saldo', 'link' => '', 'active' => true];
        break;

    case 'jual_sampah':
        $breadcrumb[] = ['label' => 'Transaksi', 'link' => 'index.php?page=semua_transaksi'];
        $breadcrumb[] = ['label' => 'Jual Sampah', 'link' => '', 'active' => true];
        break;

    case 'nota':
        $breadcrumb[] = ['label' => 'Transaksi', 'link' => 'index.php?page=semua_transaksi'];
        $breadcrumb[] = ['label' => 'Nota Transaksi', 'link' => '', 'active' => true];
        break;

    default:
        break;
}
?>

<ol class="breadcrumb">
    <?php foreach ($breadcrumb as $item): ?>
        <li class="breadcrumb-item<?= !empty($item['active']) ? ' active' : '' ?>">
            <?php if (!empty($item['link']) && empty($item['active'])): ?>
                <a href="<?= $item['link'] ?>" class="active-link"><?= $item['label'] ?></a>
            <?php else: ?>
                <?= $item['label'] ?>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ol>