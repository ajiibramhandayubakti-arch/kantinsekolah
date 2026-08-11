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
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi — E-Kantin Sekolah</title>
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
                <a href="login.php">Masuk</a>
                <a href="registrasi.php" class="active">Daftar Siswa</a>
            </div>

            <div class="auth-body">
                <h2>Buat akun baru</h2>
                <p class="sub">Lengkapi data di bawah untuk mendaftar.</p>

                <form action="" method="POST" data-loading-form data-loading-text="Mendaftarkan...">

                    <div class="field">
                        <label for="nama">Nama Lengkap</label>
                        <div class="field-input">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <input type="text" id="nama" name="nama" placeholder="Nama lengkap" required autocomplete="name">
                        </div>
                    </div>

                    <div class="field">
                        <label for="user">Username</label>
                        <div class="field-input">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                            <input type="text" id="user" name="user" placeholder="Buat username" required autocomplete="username">
                        </div>
                    </div>

                    <div class="field">
                        <label for="pass">Password</label>
                        <div class="field-input">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <input type="password" id="pass" name="pass" placeholder="Buat password" required autocomplete="new-password">
                            <button type="button" class="toggle-pass" data-target="pass" aria-label="Tampilkan password">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="field">
                        <label>Daftar sebagai</label>
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

                    <button type="submit" name="daftar" class="btn-primary">
                        <span class="spinner"></span>
                        <span class="btn-label">Daftar</span>
                    </button>
                </form>
            </div>
        </div>

        <p class="switch-link">Sudah punya akun? <a href="login.php">Login</a></p>
    </div>

    <script src="assets/js/login-register.js"></script>
</body>
</html>
