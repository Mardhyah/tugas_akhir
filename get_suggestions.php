<?php
require_once 'config/koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['query'])) {
    $search_value = $_POST['query'];

    $suggestions = [];

    if (!empty($search_value)) {
        // Updated SQL query to handle both 'nik' and 'nama'
        $user_query = "SELECT nik, nama FROM user WHERE (nik LIKE '%$search_value%' OR nama LIKE '%$search_value%') AND role = 'Nasabah' LIMIT 5";
        $user_result = $koneksi->query($user_query); // Use $koneksi here

        if ($user_result->num_rows > 0) {
            while ($row = $user_result->fetch_assoc()) {
                $suggestions[] = ['nik' => $row['nik'], 'nama' => $row['nama']];
            }
        }
    }

    foreach ($suggestions as $suggestion) {
        echo "<div onclick=\"selectSuggestion('{$suggestion['nik']}')\">{$suggestion['nama']} ({$suggestion['nik']})</div>";
    }
}
