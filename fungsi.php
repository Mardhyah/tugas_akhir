<?php
$koneksi = mysqli_connect("localhost", "root", "p@ssKeiCrypto", "banksampah1");

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


//detail_user.php
function checkSession()
{
    if (!isset($_SESSION['username'])) {
        header('Location: index.php?page=login');
        exit();
    }
}

function getUserData($koneksi, $username)
{
    // Query untuk mengambil data user termasuk saldo dari tabel dompet
    $query = "SELECT u.id, u.username, u.nama, u.nik, u.created_at AS tanggal_bergabung, u.role, 
                     d.uang, d.emas 
              FROM user u
              LEFT JOIN dompet d ON u.id = d.id_user
              WHERE u.username = ?";

    // Menggunakan prepared statement untuk keamanan
    $stmt = $koneksi->prepare($query);
    if (!$stmt) {
        die("Prepare statement failed: " . $koneksi->error);
    }

    // Bind parameter
    $stmt->bind_param("s", $username);
    $stmt->execute();

    // Mendapatkan hasil
    $result = $stmt->get_result();

    if ($result) {
        // Mengembalikan hasil sebagai array asosiatif
        return $result->fetch_assoc();
    } else {
        die("Error fetching data: " . $koneksi->error);
    }
}


//hapus user
function hapusUser($id)
{
    global $koneksi;
    mysqli_query($koneksi, "DELETE FROM user WHERE id = '$id'");
    return mysqli_affected_rows($koneksi);
}

//edit data nasabah/admin
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

    $query = "UPDATE user SET username='$username',nama='$nama',email='$email',notelp='$notelp',nik='$nik',alamat='$alamat',tgl_lahir='$tgl_lahir',kelamin='$kelamin' WHERE id='$id'";
    mysqli_query($koneksi, $query);
    return mysqli_affected_rows($koneksi);
}

function getUserById($id)
{
    $query = "SELECT * FROM user WHERE id=$id";
    return query($query)[0];
}

function hapususerById($id)
{
    return hapususer($id);
}

// admin.php
function getAdmin($search_nik = null)
{
    if ($search_nik) {
        return query("SELECT * FROM user WHERE role = 'admin' AND nik LIKE '%$search_nik%' ORDER BY LENGTH(id), CAST(id AS UNSIGNED)");
    } else {
        return query("SELECT * FROM user WHERE role = 'admin' ORDER BY LENGTH(id), CAST(id AS UNSIGNED)");
    }
}


//nasabah.php (cari nik dan hapus nasabah)
function getNasabah($search_nik = null)
{
    if ($search_nik) {
        return query("SELECT * FROM user WHERE role = 'nasabah' AND nik LIKE '%$search_nik%' ORDER BY LENGTH(id), CAST(id AS UNSIGNED)");
    } else {
        return query("SELECT * FROM user WHERE role = 'nasabah' ORDER BY LENGTH(id), CAST(id AS UNSIGNED)");
    }
}

//edit_nasabah.php
// Function to get user data by ID
// Function to handle the form submission
function handleNasabahUpdate($postData)
{
    if (updateDataUser($postData) > 0) {
        echo "
            <script>  
                alert('Data Berhasil Diperbarui');
                document.location.href ='nasabah.php';
            </script>
        ";
    } else {
        echo "
            <script>  
                alert('Data Gagal Diperbarui');
                document.location.href ='nasabah.php';
            </script>
        ";
    }
}

//edit_admin.php
function handleAdminUpdate($postData)
{
    if (updateDataUser($postData) > 0) {
        echo "
            <script>  
                alert('Data Berhasil Diperbarui');
                document.location.href ='admin.php';
            </script>
        ";
    } else {
        echo "
            <script>  
                alert('Data Gagal Diperbarui');
                document.location.href ='admin.php';
            </script>
        ";
    }
}



//hapus sampah
function hapusSampah($id)
{
    global $koneksi;

    // Escape string untuk mencegah SQL injection
    $id = mysqli_real_escape_string($koneksi, $id);

    // Bungkus id dengan tanda kutip
    $query = "DELETE FROM sampah WHERE id = '$id'";
    mysqli_query($koneksi, $query);

    if (mysqli_error($koneksi)) {
        die("Query Error: " . mysqli_error($koneksi));
    }

    return mysqli_affected_rows($koneksi);
}

if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id']; // JANGAN pakai (int) karena id bisa berbentuk seperti 'S021'

    if (hapusSampah($id) > 0) {
        header("Location: index.php?page=sampah&message=Data berhasil dihapus");
    } else {
        header("Location: index.php?page=sampah&message=Data gagal dihapus");
    }
    exit;
}


//edit sampah
function updatedatasampah($data)
{
    global $koneksi;
    $id = htmlspecialchars($data["id"]);
    $id_kategori = htmlspecialchars($data["id_kategori"]);
    $jenis = htmlspecialchars($data["jenis"]);
    $harga = htmlspecialchars($data["harga"]);
    $harga_pusat = htmlspecialchars($data["harga_pusat"]);
    $jumlah = htmlspecialchars($data["jumlah"]);

    $query = "UPDATE sampah SET id_kategori='$id_kategori',jenis='$jenis',harga='$harga',harga_pusat='$harga_pusat',jumlah='$jumlah' WHERE id='$id'";
    mysqli_query($koneksi, $query);
    return mysqli_affected_rows($koneksi);
}

function addCategory($name)
{
    global $koneksi;

    // Query untuk mendapatkan ID terakhir yang dimulai dengan 'KS'
    $lastId = query("SELECT id FROM kategori_sampah WHERE id LIKE 'KS%' ORDER BY id DESC LIMIT 1");
    if ($lastId) {
        // Ambil angka dari ID terakhir, tambahkan 1, dan format ulang ID baru
        $newId = 'KS' . str_pad((int) substr($lastId[0]['id'], 2) + 1, 2, '0', STR_PAD_LEFT);
    } else {
        // Jika tidak ada ID sebelumnya, mulai dengan KS01
        $newId = 'KS01';
    }

    $query = "INSERT INTO kategori_sampah (id, name) VALUES ('$newId', '$name')";
    mysqli_query($koneksi, $query);
    return mysqli_affected_rows($koneksi);
}

function deleteCategory($id)
{
    global $koneksi;
    mysqli_query($koneksi, "DELETE FROM kategori_sampah WHERE id = '$id'");
    return mysqli_affected_rows($koneksi);
}

function addWaste($jenis, $harga_pengepul, $harga_nasabah, $id_kategori)
{
    global $koneksi;

    $lastId = query("SELECT id FROM sampah WHERE id LIKE 'S%' ORDER BY id DESC LIMIT 1");
    if ($lastId) {
        $newId = 'S' . str_pad((int) substr($lastId[0]['id'], 1) + 1, 3, '0', STR_PAD_LEFT);
    } else {
        $newId = 'S001';
    }

    // ✅ Sudah tidak tertukar
    $query = "INSERT INTO sampah (id, jenis, harga, harga_pusat, id_kategori) 
              VALUES ('$newId', '$jenis', '$harga_nasabah', '$harga_pengepul', '$id_kategori')";

    mysqli_query($koneksi, $query);
    return mysqli_affected_rows($koneksi);
}

function getCategories()
{
    return query("SELECT * FROM kategori_sampah ORDER BY name ASC");
}

function handleAddWaste($postData)
{
    $jenis = $postData['jenis'];
    $harga_pengepul = $postData['harga_pengepul'];
    $harga_nasabah = $postData['harga_nasabah'];
    $kategori = $postData['kategori'];

    if (addWaste($jenis, $harga_pengepul, $harga_nasabah, $kategori) > 0) {
        echo "<script>
                    alert('Data berhasil ditambahkan');
                    document.location.href = '?page=sampah';
                  </script>";
        exit;
    } else {
        echo "<script>alert('Data gagal ditambahkan');</script>";
    }
}

function getSampahData()
{
    return query("
        SELECT 
            sampah.id, 
            kategori_sampah.name AS kategori_name, 
            sampah.jenis, 
            sampah.harga, 
            sampah.harga_pusat, 
            sampah.jumlah 
        FROM sampah 
        JOIN kategori_sampah ON sampah.id_kategori = kategori_sampah.id 
        ORDER BY kategori_name ASC, LENGTH(sampah.id), CAST(sampah.id AS UNSIGNED)
    ");
}

// Fungsi untuk mengambil harga BELI emas (dari user ke sistem)
function getCurrentGoldPriceBuy()
{
    $api_url = "https://logam-mulia-api.vercel.app/prices/hargaemas-org";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // timeout dalam detik
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo "Curl Error: " . curl_error($ch);
        curl_close($ch);
        return null;
    }

    curl_close($ch);
    $gold_data = json_decode($response, true);

    if (isset($gold_data['data'][0]['buy'])) {
        return $gold_data['data'][0]['buy']; // harga beli emas per gram (IDR)
    } else {
        echo "Data harga beli tidak ditemukan.";
        return null;
    }
}

// Fungsi untuk mengambil harga JUAL emas (ke user)
function getCurrentGoldPriceSell()
{
    $api_url = "https://logam-mulia-api.vercel.app/prices/hargaemas-org";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // timeout dalam detik
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo "Curl Error: " . curl_error($ch);
        curl_close($ch);
        return null;
    }

    curl_close($ch);
    $gold_data = json_decode($response, true);

    if (isset($gold_data['data'][0]['sell'])) {
        return $gold_data['data'][0]['sell']; // harga jual emas per gram (IDR)
    } else {
        echo "Data harga jual tidak ditemukan.";
        return null;
    }
}

//konversi duit ke emas
// Function to convert money to gold and update the user's wallet
function convertMoneyToGold($user_id, $jumlah_uang, $current_gold_price)
{
    global $koneksi;
    $jumlah_emas = $jumlah_uang / $current_gold_price;

    $update_query = "UPDATE dompet 
                     SET uang = uang - $jumlah_uang, emas = emas + $jumlah_emas 
                     WHERE id_user = $user_id";
    return $koneksi->query($update_query);
}

//konversi emas ke duit
// Function to convert money to gold and update the user's wallet
function convertGoldToMoney($user_id, $jumlah_emas, $current_gold_price)
{
    global $koneksi;
    $jumlah_uang = $jumlah_emas * $current_gold_price;

    $update_query = "UPDATE dompet 
                     SET uang = uang + $jumlah_uang, emas = emas - $jumlah_emas 
                     WHERE id_user = $user_id";
    return $koneksi->query($update_query);
}

// Fungsi untuk menarik uang
// Fungsi untuk menarik uang
function withdrawMoney($id_user, $jumlah_uang)
{
    global $koneksi;

    // Cek saldo pengguna dari tabel dompet
    $query = "SELECT uang FROM dompet WHERE id_user = ?";
    $stmt = $koneksi->prepare($query);
    if (!$stmt) {
        die("Prepare failed: " . $koneksi->error); // Penanganan kesalahan
    }
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Validasi saldo uang
    if ($user['uang'] < $jumlah_uang) {
        return false; // Saldo tidak cukup
    }

    // Kurangi saldo uang pengguna
    $new_saldo = $user['uang'] - $jumlah_uang;
    $update_query = "UPDATE dompet SET uang = ? WHERE id_user = ?";
    $update_stmt = $koneksi->prepare($update_query);
    if (!$update_stmt) {
        die("Prepare failed: " . $koneksi->error); // Penanganan kesalahan
    }
    $update_stmt->bind_param("di", $new_saldo, $id_user);

    return $update_stmt->execute();
}

// Fungsi untuk menarik emas
function withdrawGold($id_user, $jumlah_emas)
{
    global $koneksi;

    // Cek saldo pengguna dari tabel dompet
    $query = "SELECT emas FROM dompet WHERE id_user = ?";
    $stmt = $koneksi->prepare($query);
    if (!$stmt) {
        die("Prepare failed: " . $koneksi->error); // Penanganan kesalahan
    }
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Validasi saldo emas
    if ($user['emas'] < $jumlah_emas) {
        return false; // Saldo tidak cukup
    }

    // Kurangi saldo emas pengguna
    $new_saldo = $user['emas'] - $jumlah_emas;
    $update_query = "UPDATE dompet SET emas = ? WHERE id_user = ?";
    $update_stmt = $koneksi->prepare($update_query);
    if (!$update_stmt) {
        die("Prepare failed: " . $koneksi->error); // Penanganan kesalahan
    }
    $update_stmt->bind_param("di", $new_saldo, $id_user);

    return $update_stmt->execute();
}

function searchUserByNIK($nik)
{
    global $koneksi;

    // Prepare the SQL statement
    $query = "SELECT id, nik, email, username, saldo_uang, saldo_emas FROM user WHERE nik = ?";
    $stmt = $koneksi->prepare($query);

    // Check if the prepare was successful
    if (!$stmt) {
        die("Error preparing statement: " . $koneksi->error);
    }

    // Bind the parameter and execute the query
    $stmt->bind_param("s", $nik);
    $stmt->execute();

    // Get the result
    $result = $stmt->get_result();

    // Return the fetched data
    return $result->fetch_assoc();
}

function getSampahTypes()
{
    global $koneksi;
    $query = "SELECT * FROM sampah";
    $result = $koneksi->query($query);
    return $result;
}

function insertSetorSampah($user_id, $sampah_data)
{
    global $koneksi;
    $id_transaksi = uniqid();
    $total_kg = 0;
    $total_rp = 0;

    foreach ($sampah_data as $sampah) {
        $id_sampah = $sampah['id_sampah'];
        $jumlah_kg = $sampah['jumlah_kg'];
        $jumlah_rp = $sampah['jumlah_rp'];
        $total_kg += $jumlah_kg;
        $total_rp += $jumlah_rp;

        $query = "INSERT INTO setor_sampah (id_transaksi, id_sampah, jumlah_kg, jumlah_rp) VALUES (?, ?, ?, ?)";
        $stmt = $koneksi->prepare($query);
        $stmt->bind_param("ssdd", $id_transaksi, $id_sampah, $jumlah_kg, $jumlah_rp);
        $stmt->execute();
    }

    $query = "INSERT INTO transaksi (id_transaksi, id_user, jenis_transaksi, total_kg, total_rp, tanggal) VALUES (?, ?, 'setor_sampah', ?, ?, NOW())";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("ssdd", $id_transaksi, $user_id, $total_kg, $total_rp);
    return $stmt->execute();
}
