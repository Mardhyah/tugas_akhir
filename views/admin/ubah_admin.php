<?php
$current_page = $_GET['page'] ?? '';
?>

<?php
include_once __DIR__ . '/../layouts/header.php';
include_once __DIR__ . '/../layouts/sidebar.php';



function getUserById($id)
{
    global $koneksi;
    $id = (int)$id; // agar aman dari injeksi SQL

    $query = "SELECT * FROM user WHERE id = $id LIMIT 1";
    $result = mysqli_query($koneksi, $query) or die("Query gagal: " . mysqli_error($koneksi));

    if (!$result || mysqli_num_rows($result) === 0) {
        return false;
    }

    return mysqli_fetch_assoc($result);
}

function handleAdminUpdate($postData)
{
    if (updateDataUser($postData) > 0) {
        echo "
            <script>  
                alert('Data Berhasil Diperbarui');
                document.location.href ='index.php?page=admin';
            </script>
        ";
    } else {
        echo "
            <script>  
                alert('Data Gagal Diperbarui');
                document.location.href ='index.php?page=admin';
            </script>
        ";
    }
}

// Edit data nasabah/admin
function updateDataUser($data)
{
    global $koneksi;
    $id = htmlspecialchars($data["id"]);
    $username = htmlspecialchars($data["username"]);
    $nama = htmlspecialchars($data["nama"]);
    $email = htmlspecialchars($data["email"]);
    $notelp = htmlspecialchars($data["notelp"]);
    $nik = htmlspecialchars($data["nik"]);
    $alamat = htmlspecialchars($data["alamat"]);
    $tgl_lahir = htmlspecialchars($data["tgl_lahir"]);
    $kelamin = htmlspecialchars($data["kelamin"]);

    $query = "UPDATE user SET 
                username='$username',
                nama='$nama',
                email='$email',
                notelp='$notelp',
                nik='$nik',
                alamat='$alamat',
                tgl_lahir='$tgl_lahir',
                kelamin='$kelamin' 
              WHERE id='$id'";

    mysqli_query($koneksi, $query);
    return mysqli_affected_rows($koneksi); // ⬅️ penting!
}

// Get the user ID from the URL
if (!isset($_GET['id'])) {
    die("ID tidak dikirim lewat URL");
}

$id = (int)$_GET['id'];

$user = getUserById($id);

if (!$user) {
    die("Data user tidak ditemukan di database");
}

// Ketika tombol submit diklik
if (isset($_POST["submit"])) {
    handleAdminUpdate($_POST);
}




?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <!-- My CSS -->

    <link rel="stylesheet" href="../../assets/css/style.css">
    <title>BankSampah</title>
</head>
<style>
    form {
        width: 100%;
        padding: 10px 20px;
    }

    form h3 {
        font-size: 20px;
        margin-bottom: 16px;
        color: #333;
    }

    form label {
        display: block;
        font-weight: 500;
        margin-bottom: 4px;
        color: #444;
    }

    form input[type="text"],
    form input[type="date"] {
        width: 100%;
        padding: 10px 14px;
        margin-bottom: 12px;
        border: 1px solid #bbb;
        border-radius: 8px;
        font-size: 15px;
        background-color: #f9f9f9;
    }

    form input[type="text"]:focus,
    form input[type="date"]:focus {
        border-color: #007bff;
        outline: none;
        background-color: #fff;
    }

    .inputbtn {
        background-color: #007bff;
        color: white;
        padding: 10px 20px;
        font-size: 15px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        margin-top: 10px;
    }

    .inputbtn:hover {
        background-color: #0056b3;
    }
</style>

<body>

    <!-- CONTENT -->
    <section id="content">
        <!-- NAVBAR -->
        <nav>
            <i class='bx bx-menu'></i>
            <?php include_once __DIR__ . '/../layouts/breadcrumb.php'; ?>

        </nav>
        <!-- NAVBAR -->

        <!-- MAIN -->
        <main>
            <div class="head-title">
                <div class="left">
                    <span>Halaman</span>
                    <h1>Edit Nasabah</h1>
                </div>
            </div>

            <div id="wrapper">

                <!-- Ini Main-Content -->
                <div class="main--content">
                    <div class="header--wrapper">
                    </div>

                    <!-- Ini card-container -->
                    <div class="card--container">
                        <h3 class="main--title">Isi Form Berikut</h3>
                        <form method="POST" action="index.php?page=ubah_admin&id=<?= $user['id']; ?>">

                            <input type="hidden" name="page" value="design">

                            <!-- Tambahkan input hidden untuk menandai halaman -->
                            <input type="text" placeholder="Masukkan ID Project" name="id"
                                value="<?= $user["id"] ?>"><br>
                            <label for="">Username</label>
                            <input type="text" placeholder="Masukkan Nomor JO" name="username"
                                value="<?= $user["username"] ?>"><br>

                            <label for="">Nama</label>
                            <input type="text" placeholder="Masukan Tgl JO" name="nama"
                                value="<?= $user["nama"] ?>"><br>

                            <label for="">Email</label>
                            <input type="text" placeholder="Masukkan Nama Project" name="email"
                                value="<?= $user["email"] ?>"><br>

                            <label for="">NoTelp</label>
                            <input type="text" placeholder="Masukkan Nama Project" name="notelp"
                                value="<?= $user["notelp"] ?>"><br>

                            <label for="">NIK</label>
                            <input type="text" placeholder="Masukkan Kode GBJ" name="nik"
                                value="<?= $user["nik"] ?>"><br>

                            <label for="">Alamat</label>
                            <input type="text" placeholder="Masukkan Harga" name="alamat"
                                value="<?= $user["alamat"] ?>"><br>

                            <label for="">Tanggal Lahir</label>
                            <input type="date" placeholder="Masukkan Nama Panel" name="tgl_lahir"
                                value="<?= $user["tgl_lahir"] ?>"><br>

                            <label for="">Jenis Kelamin</label>
                            <input type="text" placeholder="Masukkan Tipe Jenis" name="kelamin"
                                value="<?= $user["kelamin"] ?>"><br>

                            <button type="submit" name="submit" class="inputbtn">Input</button>

                        </form>
                    </div>
                </div>
                <!-- Batas Akhir card-container -->
            </div>


        </main>
        <!-- MAIN -->
    </section>
    <!-- CONTENT -->


    <script src="script.js"></script>

</body>

</html>