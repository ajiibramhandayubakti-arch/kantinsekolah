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
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — E-Kantin Sekolah</title>
    <link rel="icon" href="assets/img/logo-sekolah.png">
    <link rel="stylesheet" href="assets/css/login-register.css">
</head>
<body>
    <div class="auth-wrap">

        <div class="brand">
            <img src="assets/img/logo-sekolah.png" alt="Logo Sekolah">
            <p class="eyebrow">Sistem Kantin Sekolah</p>
            <h1>E-Kantin</h1>
        </div>

        <div class="auth-card">
            <div class="auth-tabs">
                <a href="login.php" class="active">Masuk</a>
                <a href="registrasi.php">Daftar Siswa</a>
            </div>

            <div class="auth-body">
                <h2>Masuk ke akunmu</h2>
                <p class="sub">Gunakan username &amp; password yang sudah terdaftar.</p>

                <form action="" method="POST" data-loading-form data-loading-text="Memeriksa akun...">

                    <div class="field">
                        <label for="user">Username</label>
                        <div class="field-input">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <input type="text" id="user" name="user" placeholder="Masukkan username" required autocomplete="username">
                        </div>
                    </div>

                    <div class="field">
                        <label for="pass">Password</label>
                        <div class="field-input">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <input type="password" id="pass" name="pass" placeholder="Masukkan password" required autocomplete="current-password">
                            <button type="button" class="toggle-pass" data-target="pass" aria-label="Tampilkan password">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="field">
                        <label>Masuk sebagai</label>
                        <div class="role-group">
                            <input type="radio" id="role-siswa" name="role" value="siswa" checked>
                            <label for="role-siswa" class="role-card">
                                <span class="emoji">🎓</span>
                                <span class="label">Siswa</span>
                            </label>

                            <input type="radio" id="role-penjual" name="role" value="penjual">
                            <label for="role-penjual" class="role-card">
                                <span class="emoji">🍳</span>
                                <span class="label">Penjual</span>
                            </label>

                            <input type="radio" id="role-admin" name="role" value="admin">
                            <label for="role-admin" class="role-card">
                                <span class="emoji">🛡️</span>
                                <span class="label">Admin</span>
                            </label>
                        </div>
                    </div>

                    <button type="submit" name="login" class="btn-primary">
                        <span class="spinner"></span>
                        <span class="btn-label">Masuk</span>
                    </button>
                </form>
            </div>
        </div>

        <p class="switch-link">Belum punya akun? <a href="registrasi.php">Daftar Akun</a></p>
    </div>

    <script src="assets/js/login-register.js"></script>
</body>
</html>
