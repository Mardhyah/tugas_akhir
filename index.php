<?php
session_start();
require_once 'config/koneksi.php';

// ambil halaman yang diminta
$page = $_GET['page'] ?? 'dashboard'; // kalau kosong, ke dashboard

// router sederhana
switch ($page) {
    case 'dashboard':
        include 'views/dashboard.php';
        break;
    case 'sampah':
        include 'views/sampah/index_sampah.php';
        break;
    case 'semua_transaksi':
        include 'views/transaksi/semua_transaksi.php';
        break;
    case 'rekap_transaksi':
        include 'views/transaksi/rekap_transaksi.php';
        break;
    case 'admin':
        include 'views/admin/index_admin.php';
        break;
    case 'tambah_admin':
        include 'views/admin/tambah_admin.php';
        break;
    case 'ubah_admin':
        include 'views/admin/ubah_admin.php';
        break;
    case 'nasabah':
        include 'views/nasabah/index_nasabah.php';
        break;
    case 'detail_nasabah':
        include 'views/nasabah/detail_nasabah.php';
        break;
    case 'ubah_nasabah':
        include 'views/nasabah/ubah_nasabah.php';
        break;
    case 'tambah_nasabah':
        include 'views/nasabah/tambah_nasabah.php';
        break;
    case 'detail_user':
        include 'views/detail_user/index_detail.php';
        break;
    case 'ubah_password':
        include 'views/detail_user/ubah_password.php';
        break;
    case 'tambah_sampah':
        include 'views/sampah/tambah_sampah.php'; // misalnya file kamu ada di views/sampah/
        break;
    case 'ubah_sampah':
        include 'views/sampah/ubah_sampah.php'; // misalnya file kamu ada di views/sampah/
        break;
    case 'manage_kategori':
        include 'views/sampah/manage_kategori.php'; // misalnya file kamu ada di views/sampah/
        break;

    case 'setor_sampah':
        include 'views/transaksi/setor_sampah.php';
        break;
    case 'tarik_saldo':
        include 'views/transaksi/tarik_saldo.php';
        break;
    case 'jual_sampah':
        include 'views/transaksi/jual_sampah.php';
        break;
    case 'nota':
        include 'views/transaksi/nota.php';
        break;

    case 'login':
        include 'views/auth/login.php';
        break;
    case 'register_nasabah':
        include 'views/auth/register_nasabah.php';
        break;
    case 'logout':
        include 'views/auth/logout.php';
        break;


    default:
        echo "<h1>404 Page Not Found</h1>";
        break;
}
