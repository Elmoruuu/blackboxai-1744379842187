<?php
require_once 'config/database.php';
require_once 'includes/registration.php';

$registration = new Registration($pdo);
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $uploadDir = 'uploads/documents/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Handle document uploads
    $documents = [];
    $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    $requiredDocs = ['ijazah', 'kk', 'akta', 'foto'];
    $uploadSuccess = true;

    foreach ($requiredDocs as $doc) {
        if (isset($_FILES[$doc]) && $_FILES[$doc]['error'] === UPLOAD_ERR_OK) {
            if (!in_array($_FILES[$doc]['type'], $allowedTypes)) {
                $error = 'Tipe file tidak didukung. Gunakan PDF, JPG, atau PNG.';
                $uploadSuccess = false;
                break;
            }
            if ($_FILES[$doc]['size'] > $maxSize) {
                $error = 'Ukuran file terlalu besar. Maksimal 5MB.';
                $uploadSuccess = false;
                break;
            }

            $fileName = uniqid() . '_' . basename($_FILES[$doc]['name']);
            $uploadFile = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES[$doc]['tmp_name'], $uploadFile)) {
                $documents[$doc] = $uploadFile;
            } else {
                $error = 'Gagal mengunggah dokumen.';
                $uploadSuccess = false;
                break;
            }
        } else {
            $error = 'Semua dokumen wajib diunggah.';
            $uploadSuccess = false;
            break;
        }
    }

    if ($uploadSuccess) {
        $registrationData = [
            'full_name' => $_POST['full_name'] ?? '',
            'birth_date' => $_POST['birth_date'] ?? '',
            'birth_place' => $_POST['birth_place'] ?? '',
            'gender' => $_POST['gender'] ?? '',
            'religion' => $_POST['religion'] ?? '',
            'parent_name' => $_POST['parent_name'] ?? '',
            'parent_occupation' => $_POST['parent_occupation'] ?? '',
            'parent_income' => $_POST['parent_income'] ?? '',
            'address' => $_POST['address'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'email' => $_POST['email'] ?? '',
            'previous_school' => $_POST['previous_school'] ?? '',
            'documents' => json_encode($documents)
        ];

        $errors = $registration->validateRegistrationData($registrationData);

        if (empty($errors)) {
            if ($registration->registerStudent($registrationData)) {
                header('Location: registration-success.php');
                exit;
            } else {
                $error = 'Terjadi kesalahan saat memproses pendaftaran.';
            }
        } else {
            $error = implode('<br>', $errors);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Siswa Baru - Sekolah Unggulan</title>
    
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
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold text-center mb-8">Pendaftaran Siswa Baru</h1>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-xl font-semibold mb-4">Persyaratan Pendaftaran:</h2>
                <ul class="list-disc list-inside space-y-2 text-gray-700">
                    <li>Scan Ijazah/Surat Keterangan Lulus</li>
                    <li>Scan Kartu Keluarga</li>
                    <li>Scan Akta Kelahiran</li>
                    <li>Pas Foto 3x4 (latar merah)</li>
                    <li>Format file: PDF/JPG/PNG (max 5MB)</li>
                </ul>
            </div>

            <form action="" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-md p-6">
                <!-- Data Pribadi -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold mb-4 pb-2 border-b">Data Pribadi</h3>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="full_name">
                                Nama Lengkap *
                            </label>
                            <input type="text" name="full_name" id="full_name" required
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="birth_place">
                                Tempat Lahir *
                            </label>
                            <input type="text" name="birth_place" id="birth_place" required
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="birth_date">
                                Tanggal Lahir *
                            </label>
                            <input type="date" name="birth_date" id="birth_date" required
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                Jenis Kelamin *
                            </label>
                            <div class="flex space-x-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="gender" value="L" required class="form-radio">
                                    <span class="ml-2">Laki-laki</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="gender" value="P" required class="form-radio">
                                    <span class="ml-2">Perempuan</span>
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="religion">
                                Agama *
                            </label>
                            <select name="religion" id="religion" required
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="">Pilih Agama</option>
                                <option value="Islam">Islam</option>
                                <option value="Kristen">Kristen</option>
                                <option value="Katolik">Katolik</option>
                                <option value="Hindu">Hindu</option>
                                <option value="Buddha">Buddha</option>
                                <option value="Konghucu">Konghucu</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Data Orang Tua -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold mb-4 pb-2 border-b">Data Orang Tua</h3>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="parent_name">
                                Nama Orang Tua/Wali *
                            </label>
                            <input type="text" name="parent_name" id="parent_name" required
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="parent_occupation">
                                Pekerjaan Orang Tua *
                            </label>
                            <input type="text" name="parent_occupation" id="parent_occupation" required
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="parent_income">
                                Penghasilan per Bulan *
                            </label>
                            <select name="parent_income" id="parent_income" required
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="">Pilih Rentang</option>
                                <option value="< 2 juta">< Rp 2.000.000</option>
                                <option value="2-5 juta">Rp 2.000.000 - Rp 5.000.000</option>
                                <option value="5-10 juta">Rp 5.000.000 - Rp 10.000.000</option>
                                <option value="> 10 juta">> Rp 10.000.000</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Kontak dan Alamat -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold mb-4 pb-2 border-b">Kontak dan Alamat</h3>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="address">
                                Alamat Lengkap *
                            </label>
                            <textarea name="address" id="address" rows="3" required
                                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="phone">
                                Nomor Telepon *
                            </label>
                            <input type="tel" name="phone" id="phone" required
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                                Email *
                            </label>
                            <input type="email" name="email" id="email" required
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                    </div>
                </div>

                <!-- Data Akademik -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold mb-4 pb-2 border-b">Data Akademik</h3>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="previous_school">
                                Asal Sekolah *
                            </label>
                            <input type="text" name="previous_school" id="previous_school" required
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                    </div>
                </div>

                <!-- Upload Dokumen -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold mb-4 pb-2 border-b">Upload Dokumen</h3>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="ijazah">
                                Scan Ijazah/SKL *
                            </label>
                            <input type="file" name="ijazah" id="ijazah" required accept=".pdf,.jpg,.jpeg,.png"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="kk">
                                Scan Kartu Keluarga *
                            </label>
                            <input type="file" name="kk" id="kk" required accept=".pdf,.jpg,.jpeg,.png"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="akta">
                                Scan Akta Kelahiran *
                            </label>
                            <input type="file" name="akta" id="akta" required accept=".pdf,.jpg,.jpeg,.png"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="foto">
                                Pas Foto 3x4 *
                            </label>
                            <input type="file" name="foto" id="foto" required accept=".jpg,.jpeg,.png"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-center">
                    <button type="submit" name="register"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Kirim Pendaftaran
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script>
        // Form validation and preview uploaded files
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function() {
                if (this.files[0]) {
                    const fileSize = this.files[0].size / 1024 / 1024; // in MB
                    if (fileSize > 5) {
                        alert('Ukuran file terlalu besar. Maksimal 5MB');
                        this.value = '';
                    }
                }
            });
        });
    </script>
</body>
</html>
