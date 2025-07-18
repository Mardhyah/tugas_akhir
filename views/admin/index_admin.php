<?php
$current_page = $_GET['page'] ?? '';
?>

<?php
include_once __DIR__ . '/../layouts/header.php';
include_once __DIR__ . '/../layouts/sidebar.php';
// include_once __DIR__ . '/../../fungsi.php';


function query($query)
{
    global $koneksi;
    $result = mysqli_query($koneksi, $query);

    if (!$result) {
        // Display or log the SQL error
        die("Query failed: " . mysqli_error($koneksi));
    }

    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

//hapus user
function hapusUser($id)
{
    global $koneksi;
    mysqli_query($koneksi, "DELETE FROM user WHERE id = '$id'");
    return mysqli_affected_rows($koneksi);
}

function hapususerById($id)
{
    return hapususer($id);
}

//admin.php
function getAdmin($search_nik = null)
{
    if ($search_nik) {
        return query("SELECT * FROM user WHERE role in ('admin','superadmin') AND nik LIKE '%$search_nik%' ORDER BY LENGTH(id), CAST(id AS UNSIGNED)");
    } else {
        return query("SELECT * FROM user WHERE role in ('admin','superadmin') ORDER BY LENGTH(id), CAST(id AS UNSIGNED)");
    }
}

// Logika untuk menghapus admin jika ada parameter id pada URL
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    if (hapususerById($id) > 0) {
        echo "
            <script>
                alert('Data Berhasil Dihapus');
                document.location.href ='index.php?page=admin';
            </script>
        ";
    } else {
        echo "
            <script>
                alert('Data Gagal Dihapus');
                document.location.href ='index.php?page=admin';
            </script>
        ";
    }
}



// Cek apakah form pencarian telah disubmit
$search_nik = $_GET['search_nik'] ?? null;
$query_all = getAdmin($search_nik);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <!-- My CSS -->
    <link rel="stylesheet" href="/adminhub/assets/css/style.css">

    <title>AdminHub</title>
</head>
<!-- <style>
    input[name="search_nik"] {
        width: 600px;
        /* Panjang input */
        padding: 10px 14px;
        font-size: 16px;
        border: 2px solid #ccc;
        border-radius: 8px;
        outline: none;
        transition: border-color 0.3s, box-shadow 0.3s;
        background-color: #fff;
    }

    input[name="search_nik"]:focus {
        border-color: #007bff;
        box-shadow: 0 0 6px rgba(0, 123, 255, 0.25);
    }
</style> -->

<body>

    <!-- CONTENT -->
    <section id="content">
        <!-- NAVBAR -->
        <nav>
            <i class='bx bx-menu'></i>
        </nav>
        <!-- NAVBAR -->

        <!-- MAIN -->
        <main>


            <div id="wrapper">
                <div class="head-title">
                    <div class="left">
                        <span>Halaman</span>
                        <h1>Admin</h1>
                    </div>
                </div>
                <!-- Ini Main-Content -->
                <div class="main--content">

                    <!-- Ini card-container -->
                    <div class="card--container">


                        <!-- Ini Tabel -->
                        <div class="tabular--wrapper">
                            <!-- <div class="search--wrapper">
                                <form method="GET" action="">
                                    <input type="text" name="search_nik" placeholder="Cari NIK admin..."
                                        value="<?= $search_nik ?>" pattern="\d{16}" maxlength="16"
                                        title="NIK harus terdiri dari 16 digit angka" required>
                                    <button type="submit" class="inputbtn">Cari</button>
                                </form>
                            </div> -->
                            <script>
                                document.querySelector('form').addEventListener('submit', function(e) {
                                    var nikInput = document.querySelector('input[name="search_nik"]').value;
                                    if (nikInput.length !== 16 || !/^\d+$/.test(nikInput)) {
                                        alert('NIK harus terdiri dari 16 digit angka');
                                        e.preventDefault();
                                    }
                                });
                            </script>
                            <div class="row align-items-start">
                                <div class="user--info">
                                    <a href="index.php?page=tambah_admin"><button type="button" name="button"
                                            class="inputbtn .border-right">Tambah</button></a>
                                </div>
                            </div>

                            <?php
                            if (isset($_SESSION['message'])) {
                                echo "<h4>" . $_SESSION['message'] . "</h4>";
                                unset($_SESSION['message']);
                            }
                            ?>

                            <div class="table-container">
                                <?php if (empty($query_all)) : ?>
                                    <div class="alert alert-warning">
                                        <strong>admin tidak ditemukan!</strong> Silakan coba NIK yang lain.
                                    </div>
                                <?php else : ?>
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Username</th>
                                                <th>Nama</th>
                                                <th>Email</th>
                                                <th>No Telp</th>
                                                <th>NIK</th>
                                                <th>Alamat</th>
                                                <th>Tanggal Lahir</th>
                                                <th>Jenis Kelamin</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $i = 1; ?>
                                            <?php foreach ($query_all as $row): ?>
                                                <tr>
                                                    <td><?= $row["id"]; ?></td>
                                                    <td><?= $row["username"]; ?></td>
                                                    <td><?= $row["nama"]; ?></td>
                                                    <td><?= $row["email"]; ?></td>
                                                    <td><?= $row["notelp"]; ?></td>
                                                    <td><?= $row["nik"]; ?></td>
                                                    <td><?= $row["alamat"]; ?></td>
                                                    <td><?= $row["tgl_lahir"]; ?></td>
                                                    <td><?= $row["kelamin"]; ?></td>
                                                    <td>
                                                        <li class="liaksi">
                                                            <button type="submit" name="submit"><a href="index.php?page=ubah_admin&id=<?= $row['id']; ?>" class="inputbtn6">Ubah</a>
                                                            </button>
                                                        </li>
                                                        <li class="liaksi">
                                                            <a href="index.php?page=admin&action=delete&id=<?= $row['id']; ?>" class="inputbtn7"
                                                                onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                                                        </li>

                                                    </td>
                                                </tr>
                                                <?php $i++; ?>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                        </tfoot>
                                    </table>
                                <?php endif; ?>
                            </div>
                        </div>
        </main>
        <!-- MAIN -->
    </section>
    <!-- CONTENT -->


    <script src="script.js"></script>
</body>

</html>