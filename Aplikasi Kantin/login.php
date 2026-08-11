<?php
session_start();
include 'config/koneksi.php';

if(isset($_POST['login'])) {
    $user = mysqli_real_escape_string($conn, $_POST['user']);
    $pass = mysqli_real_escape_string($conn, $_POST['pass']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    $query = mysqli_query($conn, "SELECT * FROM users WHERE BINARY username = '$user' AND BINARY password = '$pass' AND role = '$role'");
    $cek = mysqli_num_rows($query);

    if($cek > 0) {
        $data = mysqli_fetch_assoc($query);
        $_SESSION['id_user'] = isset($data['id_user']) ? $data['id_user'] : $data['id'];
        $_SESSION['nama_user'] = $data['nama_user'];
        $_SESSION['role'] = $data['role'];

        if($data['role'] == 'penjual') {
            echo "<script>
            alert('Selamat Datang Penjual! Anda berhasil login.');
            window.location.href='kantin/dashboard.php';
            </script>";
            exit;
        } elseif ($data['role'] == 'admin') {
            echo "<script>
            alert('Selamat Datang Admin! Anda berhasil login.');
            window.location.href='admin/dashboard.php';
            </script>";
            exit;
        } elseif ($data['role'] == 'siswa') {
            echo "<script>
            alert('Selamat Datang Siswa! Anda berhasil login.');
            window.location.href='siswa/dashboard.php';
            </script>";
            exit;
        }


    } else {
        echo "<script>
        alert('Username, Password, atau Role yang Anda Pilih Tidak Cocok!');
        window.location.href='login.php';
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Kantin</title>
</head>
<body>
    <h2>Login Aplikasi Kantin</h2>
    <form action="" method="POST">
        <label for="">Username: </label>
        <input type="text" name="user" required><br><br>

        <label for="">Password: </label>
        <input type="password" name="pass" required><br><br>

        <label for="">Login Sebagai: </label><br>
        <select name="role" id="" required>
            <option value="siswa">Siswa</option>
            <option value="penjual">Penjual</option>
            <option value="admin">Admin</option>
        </select><br><br>

        <button type="submit" name="login">Login</button>
    </form>

    <p>Belum Punya Akun? <a href="registrasi.php">Daftar Akun</a></p>
</body>
</html>