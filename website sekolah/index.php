<?php
require_once 'config/database.php';
require_once 'includes/news.php';
require_once 'includes/gallery.php';

$news = new News($pdo);
$gallery = new Gallery($pdo);

$latestNews = $news->getAllNews(3);
$galleryImages = $gallery->getAllImages(null, 4);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sekolah Unggulan - Pendidikan Berkualitas untuk Masa Depan Cemerlang</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="bg-white shadow-lg fixed w-full z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="#" class="text-xl font-bold text-blue-600">SEKOLAH UNGGULAN</a>
                </div>
                <div class="hidden md:flex space-x-8">
                    <a href="#beranda" class="nav-link text-gray-700 hover:text-blue-600">Beranda</a>
                    <a href="#profil" class="nav-link text-gray-700 hover:text-blue-600">Profil</a>
                    <a href="#berita" class="nav-link text-gray-700 hover:text-blue-600">Berita</a>
                    <a href="#galeri" class="nav-link text-gray-700 hover:text-blue-600">Galeri</a>
                    <a href="#pendaftaran" class="nav-link text-gray-700 hover:text-blue-600">Pendaftaran</a>
                    <a href="#kontak" class="nav-link text-gray-700 hover:text-blue-600">Kontak</a>
                </div>
                <div class="md:hidden">
                    <button class="text-gray-700" onclick="toggleMobileMenu()">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>
        </div>
        <!-- Mobile Menu -->
        <div id="mobileMenu" class="hidden md:hidden bg-white pb-4">
            <a href="#beranda" class="block px-4 py-2 text-gray-700 hover:bg-blue-50">Beranda</a>
            <a href="#profil" class="block px-4 py-2 text-gray-700 hover:bg-blue-50">Profil</a>
            <a href="#berita" class="block px-4 py-2 text-gray-700 hover:bg-blue-50">Berita</a>
            <a href="#galeri" class="block px-4 py-2 text-gray-700 hover:bg-blue-50">Galeri</a>
            <a href="#pendaftaran" class="block px-4 py-2 text-gray-700 hover:bg-blue-50">Pendaftaran</a>
            <a href="#kontak" class="block px-4 py-2 text-gray-700 hover:bg-blue-50">Kontak</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="beranda" class="pt-20 hero-section text-white">
        <div class="max-w-7xl mx-auto px-4 py-20">
            <div class="grid md:grid-cols-2 gap-8 items-center">
                <div class="animate-fade-in">
                    <h1 class="text-4xl md:text-5xl font-bold mb-4">Selamat Datang di Sekolah Unggulan</h1>
                    <p class="text-xl mb-8">Membentuk Generasi Unggul, Berakhlak Mulia, dan Berwawasan Global</p>
                    <a href="#pendaftaran" class="bg-white text-blue-600 px-8 py-3 rounded-full font-semibold hover:bg-blue-50 transition duration-300">Daftar Sekarang</a>
                </div>
                <div class="hidden md:block">
                    <img src="https://images.pexels.com/photos/8613089/pexels-photo-8613089.jpeg" alt="Siswa Belajar" class="rounded-lg shadow-xl hero-image">
                </div>
            </div>
        </div>
    </section>

    <!-- Profile Section -->
    <section id="profil" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12">Profil Sekolah</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-lg shadow-md card">
                    <i class="fas fa-book text-4xl text-blue-600 mb-4"></i>
                    <h3 class="text-xl font-semibold mb-2">Visi</h3>
                    <p>Menjadi lembaga pendidikan terkemuka yang menghasilkan lulusan berkualitas dan berakhlak mulia.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md card">
                    <i class="fas fa-graduation-cap text-4xl text-blue-600 mb-4"></i>
                    <h3 class="text-xl font-semibold mb-2">Misi</h3>
                    <p>Menyelenggarakan pendidikan berkualitas dengan mengintegrasikan nilai-nilai moral dan teknologi modern.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md card">
                    <i class="fas fa-award text-4xl text-blue-600 mb-4"></i>
                    <h3 class="text-xl font-semibold mb-2">Prestasi</h3>
                    <p>Berbagai prestasi akademik dan non-akademik telah diraih di tingkat nasional dan internasional.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- News Section -->
    <section id="berita" class="py-20">
        <div class="max-w-7xl mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12">Berita & Pengumuman</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <?php foreach ($latestNews as $news): ?>
                <div class="bg-white rounded-lg overflow-hidden shadow-md card">
                    <img src="<?php echo htmlspecialchars($news['image_url']); ?>" alt="<?php echo htmlspecialchars($news['title']); ?>" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($news['title']); ?></h3>
                        <p class="text-gray-600 mb-4"><?php echo substr(htmlspecialchars($news['content']), 0, 150) . '...'; ?></p>
                        <a href="berita.php?id=<?php echo $news['id']; ?>" class="text-blue-600 hover:text-blue-700">Baca selengkapnya →</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <section id="galeri" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12">Galeri Kegiatan</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <?php foreach ($galleryImages as $image): ?>
                <img src="<?php echo htmlspecialchars($image['image_url']); ?>" 
                     alt="<?php echo htmlspecialchars($image['title']); ?>" 
                     class="w-full h-48 object-cover rounded-lg shadow-md gallery-image">
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Registration Section -->
    <section id="pendaftaran" class="py-20">
        <div class="max-w-3xl mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12">Pendaftaran Siswa Baru</h2>
            <form action="includes/registration.php" method="POST" class="bg-white p-8 rounded-lg shadow-md">
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="full_name">Nama Lengkap</label>
                    <input class="form-input shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                           id="full_name" name="full_name" type="text" required>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="birth_date">Tanggal Lahir</label>
                    <input class="form-input shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                           id="birth_date" name="birth_date" type="date" required>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Jenis Kelamin</label>
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
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="parent_name">Nama Orang Tua</label>
                    <input class="form-input shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                           id="parent_name" name="parent_name" type="text" required>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="address">Alamat</label>
                    <textarea class="form-input shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                              id="address" name="address" rows="3" required></textarea>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="phone">Nomor Telepon</label>
                    <input class="form-input shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                           id="phone" name="phone" type="tel" required>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email</label>
                    <input class="form-input shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                           id="email" name="email" type="email" required>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="previous_school">Asal Sekolah</label>
                    <input class="form-input shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                           id="previous_school" name="previous_school" type="text">
                </div>
                <div class="flex items-center justify-center">
                    <button class="submit-button bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" 
                            type="submit" name="register">
                        Kirim Pendaftaran
                    </button>
                </div>
            </form>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="kontak" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12">Hubungi Kami</h2>
            <div class="grid md:grid-cols-2 gap-8">
                <div>
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-xl font-semibold mb-4">Informasi Kontak</h3>
                        <div class="space-y-4">
                            <p class="flex items-center">
                                <i class="fas fa-map-marker-alt w-6 text-blue-600"></i>
                                Jl. Pendidikan No. 123, Kota Medan
                            </p>
                            <p class="flex items-center">
                                <i class="fas fa-phone w-6 text-blue-600"></i>
                                (061) 123-4567
                            </p>
                            <p class="flex items-center">
                                <i class="fas fa-envelope w-6 text-blue-600"></i>
                                info@sekolahunggulan.sch.id
                            </p>
                        </div>
                        <div class="mt-6">
                            <h4 class="font-semibold mb-2">Ikuti Kami</h4>
                            <div class="flex space-x-4">
                                <a href="#" class="social-icon text-blue-600 hover:text-blue-700">
                                    <i class="fab fa-facebook text-2xl"></i>
                                </a>
                                <a href="#" class="social-icon text-blue-600 hover:text-blue-700">
                                    <i class="fab fa-twitter text-2xl"></i>
                                </a>
                                <a href="#" class="social-icon text-blue-600 hover:text-blue-700">
                                    <i class="fab fa-instagram text-2xl"></i>
                                </a>
                                <a href="#" class="social-icon text-blue-600 hover:text-blue-700">
                                    <i class="fab fa-youtube text-2xl"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="h-96 bg-gray-300 rounded-lg overflow-hidden shadow-md">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3982.0238626165396!2d98.6722955!3d3.5952488!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zM8KwMzUnNDIuOSJOIDk4wrA0MCcyMC4yIkU!5e0!3m2!1sen!2sid!4v1635134567890!5m2!1sen!2sid" 
                            width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-xl font-semibold mb-4">Sekolah Unggulan</h3>
                    <p class="text-gray-400">Membentuk generasi unggul yang siap menghadapi tantangan global.</p>
                </div>
                <div>
                    <h3 class="text-xl font-semibold mb-4">Link Cepat</h3>
                    <ul class="space-y-2">
                        <li><a href="#beranda" class="text-gray-400 hover:text-white">Beranda</a></li>
                        <li><a href="#profil" class="text-gray-400 hover:text-white">Profil</a></li>
                        <li><a href="#berita" class="text-gray-400 hover:text-white">Berita</a></li>
                        <li><a href="#galeri" class="text-gray-400 hover:text-white">Galeri</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xl font-semibold mb-4">Jam Operasional</h3>
                    <ul class="text-gray-400">
                        <li>Senin - Jumat: 07:00 - 15:00</li>
                        <li>Sabtu: 07:00 - 12:00</li>
                        <li>Minggu: Tutup</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2024 Sekolah Unggulan. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        }

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>
