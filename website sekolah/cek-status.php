<?php
require_once 'config/database.php';
require_once 'includes/registration.php';

$registration = new Registration($pdo);
$result = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $registration_number = $_POST['registration_number'] ?? '';

    try {
        $stmt = $pdo->prepare("
            SELECT r.*, rt.status, rt.notes, rt.created_at as status_date
            FROM student_registrations r
            LEFT JOIN registration_tracking rt ON rt.registration_id = r.id
            WHERE r.email = :email AND r.id = :registration_number
            ORDER BY rt.created_at DESC
            LIMIT 1
        ");
        
        $stmt->execute([
            ':email' => $email,
            ':registration_number' => $registration_number
        ]);
        
        $result = $stmt->fetch();
        
        if (!$result) {
            $error = 'Data pendaftaran tidak ditemukan. Pastikan email dan nomor pendaftaran benar.';
        }
    } catch(PDOException $e) {
        $error = 'Terjadi kesalahan saat mengecek status pendaftaran.';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Status Pendaftaran - Sekolah Unggulan</title>
    
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
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <h1 class="text-3xl font-bold text-center mb-8">Cek Status Pendaftaran</h1>

            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <?php if (!$result): ?>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <form action="" method="POST">
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                                Email
                            </label>
                            <input type="email" name="email" id="email" required
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="registration_number">
                                Nomor Pendaftaran
                            </label>
                            <input type="text" name="registration_number" id="registration_number" required
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                        <div class="flex items-center justify-center">
                            <button type="submit"
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                Cek Status
                            </button>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold mb-4">Informasi Pendaftaran</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-gray-600">Nomor Pendaftaran:</p>
                                <p class="font-semibold"><?php echo htmlspecialchars($result['id']); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-600">Tanggal Daftar:</p>
                                <p class="font-semibold"><?php echo date('d M Y', strtotime($result['registration_date'])); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-600">Nama:</p>
                                <p class="font-semibold"><?php echo htmlspecialchars($result['full_name']); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-600">Email:</p>
                                <p class="font-semibold"><?php echo htmlspecialchars($result['email']); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-3">Status Pendaftaran</h3>
                        <div class="flex items-center space-x-2">
                            <span class="px-3 py-1 rounded-full text-sm font-semibold
                                <?php echo $result['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                    ($result['status'] === 'accepted' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>">
                                <?php echo ucfirst($result['status']); ?>
                            </span>
                            <span class="text-gray-500 text-sm">
                                Update terakhir: <?php echo date('d M Y H:i', strtotime($result['status_date'])); ?>
                            </span>
                        </div>
                        <?php if ($result['notes']): ?>
                            <div class="mt-3 p-3 bg-gray-50 rounded">
                                <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($result['notes'])); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($result['status'] === 'accepted'): ?>
                        <div class="bg-green-50 border border-green-200 rounded p-4">
                            <h3 class="font-semibold text-green-800 mb-2">Selamat! Anda diterima sebagai siswa baru.</h3>
                            <p class="text-green-700">Silakan melakukan daftar ulang dengan membawa:</p>
                            <ul class="list-disc list-inside text-green-700 mt-2">
                                <li>Bukti pendaftaran</li>
                                <li>Dokumen asli (Ijazah, Akta Kelahiran, Kartu Keluarga)</li>
                                <li>Pas foto terbaru 3x4 (4 lembar)</li>
                                <li>Materai 10000 (2 lembar)</li>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="mt-6 flex justify-center">
                        <a href="cek-status.php" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-search mr-1"></i> Cek Pendaftaran Lain
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
</body>
</html>
