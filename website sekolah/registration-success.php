<?php
session_start();
if (!isset($_SESSION['registration_id'])) {
    header('Location: pendaftaran.php');
    exit;
}

$registration_id = $_SESSION['registration_id'];
unset($_SESSION['registration_id']); // Clear the session after displaying
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Berhasil - Sekolah Unggulan</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
            <div class="text-center">
                <div class="mb-6">
                    <i class="fas fa-check-circle text-6xl text-green-500"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mb-4">Pendaftaran Berhasil!</h1>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <p class="text-blue-800 font-semibold">Nomor Pendaftaran Anda:</p>
                    <p class="text-3xl font-bold text-blue-600 my-2"><?php echo str_pad($registration_id, 5, '0', STR_PAD_LEFT); ?></p>
                    <p class="text-sm text-blue-700">Simpan nomor ini untuk mengecek status pendaftaran</p>
                </div>

                <div class="text-left mb-8">
                    <h2 class="text-lg font-semibold mb-3">Langkah Selanjutnya:</h2>
                    <ol class="list-decimal list-inside space-y-2 text-gray-700">
                        <li>Simpan nomor pendaftaran dan email yang digunakan saat mendaftar</li>
                        <li>Tunggu verifikasi dokumen dari pihak sekolah (1-3 hari kerja)</li>
                        <li>Cek status pendaftaran secara berkala melalui halaman cek status</li>
                        <li>Jika diterima, lakukan daftar ulang sesuai jadwal yang ditentukan</li>
                    </ol>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-8">
                    <h3 class="font-semibold text-yellow-800 mb-2">Penting!</h3>
                    <p class="text-yellow-700 text-sm">
                        Pastikan email yang didaftarkan aktif karena semua informasi terkait pendaftaran akan dikirimkan melalui email tersebut.
                    </p>
                </div>

                <div class="space-y-4">
                    <a href="cek-status.php" class="block w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition duration-300">
                        Cek Status Pendaftaran
                    </a>
                    <a href="index.php" class="block w-full bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300 transition duration-300">
                        Kembali ke Beranda
                    </a>
                </div>

                <div class="mt-8 text-sm text-gray-600">
                    <p>Ada pertanyaan? Hubungi kami di:</p>
                    <p class="font-semibold">info@sekolahunggulan.sch.id</p>
                    <p class="font-semibold">(061) 123-4567</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Prevent form resubmission on page refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>
