<?php
session_start();
require_once '../config/database.php';
require_once '../includes/gallery.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$gallery = new Gallery($pdo);
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    if ($gallery->addImage(
                        $_POST['title'],
                        $_FILES['image'],
                        $_POST['description'],
                        $_POST['category']
                    )) {
                        $message = 'Foto berhasil ditambahkan';
                    } else {
                        $error = 'Gagal menambahkan foto';
                    }
                } else {
                    $error = 'Pilih foto untuk diunggah';
                }
                break;

            case 'delete':
                $id = $_POST['id'] ?? 0;
                if ($gallery->deleteImage($id)) {
                    $message = 'Foto berhasil dihapus';
                } else {
                    $error = 'Gagal menghapus foto';
                }
                break;
        }
    }
}

// Get all images
$images = $gallery->getAllImages();
$categories = $gallery->getCategories();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Galeri - Admin Panel</title>
    
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
                        <a href="dashboard.php" class="block py-2 px-4 rounded hover:bg-gray-700">
                            <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="news.php" class="block py-2 px-4 rounded hover:bg-gray-700">
                            <i class="fas fa-newspaper mr-2"></i> Berita
                        </a>
                    </li>
                    <li>
                        <a href="gallery.php" class="block py-2 px-4 rounded bg-blue-600">
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
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold">Kelola Galeri</h1>
                <button onclick="showAddModal()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    <i class="fas fa-plus mr-2"></i> Tambah Foto
                </button>
            </div>

            <?php if ($message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($message); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <!-- Gallery Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php foreach ($images as $image): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <img src="../<?php echo htmlspecialchars($image['image_url']); ?>" 
                         alt="<?php echo htmlspecialchars($image['title']); ?>"
                         class="w-full h-48 object-cover">
                    <div class="p-4">
                        <h3 class="font-semibold text-lg mb-2"><?php echo htmlspecialchars($image['title']); ?></h3>
                        <p class="text-gray-600 text-sm mb-2"><?php echo htmlspecialchars($image['description']); ?></p>
                        <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                            <?php echo htmlspecialchars($image['category']); ?>
                        </span>
                        <div class="mt-4 flex justify-end">
                            <button onclick="confirmDelete(<?php echo $image['id']; ?>)" 
                                    class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Add Image Modal -->
    <div id="addModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white w-full max-w-md mx-4 rounded-lg shadow-lg">
                <div class="p-6">
                    <h2 class="text-xl font-bold mb-4">Tambah Foto Baru</h2>
                    <form action="" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="title">
                                Judul
                            </label>
                            <input type="text" name="title" id="title" required
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="description">
                                Deskripsi
                            </label>
                            <textarea name="description" id="description" rows="3"
                                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="category">
                                Kategori
                            </label>
                            <select name="category" id="category" required
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="">Pilih Kategori</option>
                                <option value="Academic">Akademik</option>
                                <option value="Activities">Kegiatan</option>
                                <option value="Sports">Olahraga</option>
                                <option value="Culture">Budaya</option>
                                <option value="Facilities">Fasilitas</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="image">
                                Foto
                            </label>
                            <input type="file" name="image" id="image" accept="image/*" required
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                        <div class="flex justify-end">
                            <button type="button" onclick="hideAddModal()" 
                                    class="bg-gray-500 text-white px-4 py-2 rounded mr-2 hover:bg-gray-600">
                                Batal
                            </button>
                            <button type="submit" 
                                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showAddModal() {
            document.getElementById('addModal').classList.remove('hidden');
        }

        function hideAddModal() {
            document.getElementById('addModal').classList.add('hidden');
        }

        function confirmDelete(id) {
            if (confirm('Apakah Anda yakin ingin menghapus foto ini?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
