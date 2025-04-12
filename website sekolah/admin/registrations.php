<?php
session_start();
require_once '../config/database.php';
require_once '../includes/registration.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$registration = new Registration($pdo);
$message = '';
$error = '';

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $id = $_POST['id'] ?? 0;
        $status = $_POST['status'] ?? '';
        
        if ($registration->updateRegistrationStatus($id, $status)) {
            $message = 'Status pendaftaran berhasil diperbarui';
        } else {
            $error = 'Gagal memperbarui status pendaftaran';
        }
    }
}

// Get all registrations
$registrations = $registration->getAllRegistrations();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pendaftaran - Admin Panel</title>
    
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
                        <a href="gallery.php" class="block py-2 px-4 rounded hover:bg-gray-700">
                            <i class="fas fa-images mr-2"></i> Galeri
                        </a>
                    </li>
                    <li>
                        <a href="registrations.php" class="block py-2 px-4 rounded bg-blue-600">
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
            <h1 class="text-2xl font-bold mb-8">Kelola Pendaftaran Siswa</h1>

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

            <!-- Registration List -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telepon</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Daftar</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($registrations as $reg): ?>
                        <tr>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($reg['full_name']); ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($reg['email']); ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($reg['phone']); ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-500">
                                    <?php echo date('d M Y', strtotime($reg['registration_date'])); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    <?php echo $reg['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                        ($reg['status'] === 'accepted' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>">
                                    <?php echo ucfirst($reg['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <button onclick="showDetailsModal(<?php echo htmlspecialchars(json_encode($reg)); ?>)" 
                                        class="text-blue-600 hover:text-blue-900 mr-3">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="showStatusModal(<?php echo $reg['id']; ?>, '<?php echo $reg['status']; ?>')" 
                                        class="text-green-600 hover:text-green-900">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Details Modal -->
    <div id="detailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white w-full max-w-md mx-4 rounded-lg shadow-lg">
                <div class="p-6">
                    <h2 class="text-xl font-bold mb-4">Detail Pendaftaran</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-1">Nama Lengkap</label>
                            <p id="detail_name" class="text-gray-600"></p>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-1">Tanggal Lahir</label>
                            <p id="detail_birth_date" class="text-gray-600"></p>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-1">Jenis Kelamin</label>
                            <p id="detail_gender" class="text-gray-600"></p>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-1">Nama Orang Tua</label>
                            <p id="detail_parent" class="text-gray-600"></p>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-1">Alamat</label>
                            <p id="detail_address" class="text-gray-600"></p>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-1">Asal Sekolah</label>
                            <p id="detail_school" class="text-gray-600"></p>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button onclick="hideDetailsModal()" 
                                class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div id="statusModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white w-full max-w-md mx-4 rounded-lg shadow-lg">
                <div class="p-6">
                    <h2 class="text-xl font-bold mb-4">Update Status Pendaftaran</h2>
                    <form action="" method="POST">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="id" id="status_id">
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="status">
                                Status
                            </label>
                            <select name="status" id="status" required
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="pending">Pending</option>
                                <option value="accepted">Diterima</option>
                                <option value="rejected">Ditolak</option>
                            </select>
                        </div>
                        <div class="flex justify-end">
                            <button type="button" onclick="hideStatusModal()" 
                                    class="bg-gray-500 text-white px-4 py-2 rounded mr-2 hover:bg-gray-600">
                                Batal
                            </button>
                            <button type="submit" 
                                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                                Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showDetailsModal(registration) {
            document.getElementById('detail_name').textContent = registration.full_name;
            document.getElementById('detail_birth_date').textContent = registration.birth_date;
            document.getElementById('detail_gender').textContent = registration.gender === 'L' ? 'Laki-laki' : 'Perempuan';
            document.getElementById('detail_parent').textContent = registration.parent_name;
            document.getElementById('detail_address').textContent = registration.address;
            document.getElementById('detail_school').textContent = registration.previous_school || '-';
            document.getElementById('detailsModal').classList.remove('hidden');
        }

        function hideDetailsModal() {
            document.getElementById('detailsModal').classList.add('hidden');
        }

        function showStatusModal(id, currentStatus) {
            document.getElementById('status_id').value = id;
            document.getElementById('status').value = currentStatus;
            document.getElementById('statusModal').classList.remove('hidden');
        }

        function hideStatusModal() {
            document.getElementById('statusModal').classList.add('hidden');
        }
    </script>
</body>
</html>
