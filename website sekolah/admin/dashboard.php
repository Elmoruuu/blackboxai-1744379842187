<?php
session_start();
require_once '../config/database.php';
require_once '../includes/news.php';
require_once '../includes/registration.php';
require_once '../includes/gallery.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$news = new News($pdo);
$registration = new Registration($pdo);
$gallery = new Gallery($pdo);

// Get statistics
$totalNews = count($news->getAllNews(999));
$totalRegistrations = count($registration->getAllRegistrations());
$totalGalleryImages = count($gallery->getAllImages());
$pendingRegistrations = count($registration->getAllRegistrations('pending'));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Sekolah Unggulan</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <span class="text-xl font-bold text-blue-600">Admin Panel</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <a href="logout.php" class="text-red-600 hover:text-red-700">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-gray-800 text-white">
            <div class="p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="dashboard.php" class="block py-2 px-4 rounded bg-blue-600">
                            <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="news.php" class="block py-2 px-4 rounded hover:bg-gray-700">
                            <i class="fas fa-newspaper mr-2"></i> Berita
                        </a>
                    </li>
                    <li>
                        <a href="gallery.php" class="block py-2 px-4 rounded hover:bg-gray-700">
                            <i class="fas fa-images mr-2"></i> Galeri
                        </a>
                    </li>
                    <li>
                        <a href="registrations.php" class="block py-2 px-4 rounded hover:bg-gray-700">
                            <i class="fas fa-user-graduate mr-2"></i> Pendaftaran
                        </a>
                    </li>
                    <li>
                        <a href="settings.php" class="block py-2 px-4 rounded hover:bg-gray-700">
                            <i class="fas fa-cog mr-2"></i> Pengaturan
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <h1 class="text-2xl font-bold mb-8">Dashboard Overview</h1>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <i class="fas fa-newspaper text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-500">Total Berita</p>
                            <p class="text-2xl font-semibold"><?php echo $totalNews; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <i class="fas fa-user-graduate text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-500">Total Pendaftar</p>
                            <p class="text-2xl font-semibold"><?php echo $totalRegistrations; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                            <i class="fas fa-clock text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-500">Pendaftar Pending</p>
                            <p class="text-2xl font-semibold"><?php echo $pendingRegistrations; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                            <i class="fas fa-images text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-500">Total Foto</p>
                            <p class="text-2xl font-semibold"><?php echo $totalGalleryImages; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">Aktivitas Terbaru</h2>
                <div class="space-y-4">
                    <?php
                    // Get recent registrations
                    $recentRegistrations = $registration->getAllRegistrations(null, 5);
                    foreach ($recentRegistrations as $reg): ?>
                        <div class="flex items-center justify-between border-b pb-2">
                            <div>
                                <p class="font-medium"><?php echo htmlspecialchars($reg['full_name']); ?></p>
                                <p class="text-sm text-gray-500">Mendaftar pada <?php echo date('d M Y', strtotime($reg['registration_date'])); ?></p>
                            </div>
                            <span class="px-2 py-1 text-xs rounded-full <?php echo $reg['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'; ?>">
                                <?php echo ucfirst($reg['status']); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add any JavaScript functionality needed for the dashboard
    </script>
</body>
</html>
