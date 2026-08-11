<?php
include 'config/koneksi.php';

if(isset($_POST['daftar'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $user = mysqli_real_escape_string($conn, $_POST['user']);
    $pass = mysqli_real_escape_string($conn, $_POST['pass']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    $cek = mysqli_query($conn, "SELECT * FROM users  WHERE username = '$user'");
    if(mysqli_num_rows($cek) > 0) {
        echo "<script>
        alert('Username Sudah Digunakan!');
        window.location.href='registrasi.php';
        </script>";
    } else {
        $query = "INSERT INTO users (nama_user, username, password, role) VALUES ('$nama', '$user', '$pass', '$role')";
        if(mysqli_query($conn, $query)) {
            echo"<script>
            alert('Registrasi Berhasil, Silahkan Login!');
            window.location.href='login.php';
            </script>";
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Kantin</title>
</head>
<body>
    <h2>Registrasi Aplikasi Kantin</h2>
    <form action="" method="POST">
        <label for="">Nama Lengkap: </label>
        <input type="text" name="nama" required><br><br>

        <label for="">Username: </label>
        <input type="text" name="user" required><br><br>

        <label for="">Password: </label>
        <input type="password" name="pass" required><br><br>

        <label for="">Daftar Sebagai: </label>
        <select name="role" id="" required>
            <option value="siswa">Siswa</option>
            <option value="penjual">Penjual</option>
            <option value="admin">Admin</option>
        </select><br><br>

        <button type="submit" name="daftar">Daftar</button>
    </form>

    <p>Sudah Punya Akun? <a href="login.php">Login</a></p>
    
</body>
</html>