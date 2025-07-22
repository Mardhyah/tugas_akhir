<?php
$current_page = $_GET['page'] ?? '';
?>

<?php
include_once __DIR__ . '/../layouts/header.php';
include_once __DIR__ . '/../layouts/sidebar.php';
include_once __DIR__ . '/../../fungsi.php';







if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle Add Category
    if (isset($_POST["add_category"])) {
        $name = $_POST['name'];

        if (addCategory($name) > 0) {
            echo "<script>
                    alert('Kategori berhasil ditambahkan');
                    document.location.href = '?page=manage_kategori';
                  </script>";
            exit;
        } else {
            echo "<script>alert('Kategori gagal ditambahkan');</script>";
        }
    }

    // Handle Delete Category
    if (isset($_POST["delete_category"])) {
        $id = $_POST['id'];

        if (deleteCategory($id) > 0) {
            echo "<script>
                    alert('Kategori berhasil dihapus');
                    document.location.href = '?page=manage_kategori';
                  </script>";
            exit;
        } else {
            echo "<script>alert('Kategori gagal dihapus');</script>";
        }
    }
}

// Fetch all categories
$categories = query("SELECT * FROM kategori_sampah ORDER BY id ASC");

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
<style>
    label {
        font-weight: 600;
        font-size: 16px;
        color: #333;
        display: block;
        margin-bottom: 0;
        /* hapus jarak antar label dan input */
    }

    input[type="text"] {
        width: 100%;
        padding: 10px 12px;
        margin: 0;
        /* hapus jarak bawah */
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 14px;
        transition: border-color 0.3s;
    }

    input[type="text"]:focus {
        border-color: #007BFF;
        outline: none;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
    }
</style>

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
            <div class="head-title">
                <div class="left">
                    <span>Halaman</span>
                    <h1>Manage Kategori</h1>
                </div>
            </div>


            <div id="wrapper">

                <!-- Ini Main-Content -->
                <div class="main--content">


                    <!-- Ini card-container -->
                    <div class="card--container">
                        <h3 class="main--title">Tambah Kategori</h3><br>
                        <form method="POST" action="">
                            <div class="container">

                                <label for="name">Nama Kategori</label>

                                <input type="text" placeholder="Masukkan Nama Kategori" name="name" required><br>

                                <button type="submit" name="add_category" class="inputbtn">Tambah</button>
                            </div>
                        </form>

                        <br>
                        <h3 class="main--title">Daftar Kategori</h3>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama Kategori</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <td><?= $category["id"]; ?></td>
                                            <td><?= $category["name"]; ?></td>
                                            <td>
                                                <form method="POST" action="" style="display:inline;">
                                                    <input type="hidden" name="id" value="<?= $category["id"]; ?>">
                                                    <button type="submit" name="delete_category" class="inputbtn7">Hapus</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>



        </main>
        <!-- MAIN -->
    </section>
    <!-- CONTENT -->


    <script src="script.js"></script>
</body>

</html>