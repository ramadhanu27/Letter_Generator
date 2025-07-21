<?php

/**
 * Home Page - Indonesian PDF Letter Generator
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/models/User.php';

// Check if user is already logged in
$isLoggedIn = User::isLoggedIn();
$userRole = $isLoggedIn ? ($_SESSION['role'] ?? 'user') : null;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Indonesian PDF Letter Generator - Buat Surat Resmi dengan Mudah</title>
    <meta name="description" content="Generator surat resmi Indonesia dalam format PDF. Buat surat keterangan, surat lamaran, surat resmi dengan template profesional dan mudah digunakan.">
    <meta name="keywords" content="generator surat, surat resmi, PDF, Indonesia, template surat, surat keterangan, surat lamaran">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="public/assets/css/security-protection.css" rel="stylesheet">
    <link href="public/assets/css/home.css" rel="stylesheet">

    <style>
        /* Indonesian Professional Color Scheme - Darker Palette */
        :root {
            --primary-blue: #1E40AF;
            --primary-navy: #1E293B;
            --dark-navy: #0F172A;
            --indonesian-red: #B91C1C;
            --golden-yellow: #D97706;
            --emerald-green: #047857;
            --slate-gray: #334155;
            --cool-gray: #475569;
            --light-gray: #F1F5F9;
            --warm-orange: #C2410C;
            --success-green: #15803D;
            --warning-amber: #B45309;
            --info-blue: #0369A1;
            --white: #FFFFFF;
            --black: #0F172A;
            --text-primary: #F8FAFC;
            --text-secondary: #E2E8F0;
        }

        .gradient-bg {
            /* Darker gradient for better contrast */
            background: linear-gradient(135deg, #0F172A 0%, #1E293B 30%, #334155 70%, #475569 100%);
            background: linear-gradient(135deg, #0F172A 0%, var(--primary-navy) 30%, var(--slate-gray) 70%, var(--cool-gray) 100%);
            color: white;
            position: relative;
        }

        .gradient-bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.8) 0%, rgba(30, 41, 59, 0.6) 50%, rgba(51, 65, 85, 0.4) 100%);
            pointer-events: none;
        }

        .hero-text {
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .hero-title {
            text-shadow: 0 3px 6px rgba(0, 0, 0, 0.4);
        }

        .hero-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='30' cy='30' r='3'/%3E%3Ccircle cx='10' cy='10' r='2'/%3E%3Ccircle cx='50' cy='50' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            position: relative;
        }

        .hero-pattern::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.1);
            pointer-events: none;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .feature-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: var(--white);
            border: 1px solid rgba(30, 64, 175, 0.1);
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px rgba(30, 64, 175, 0.15);
            border-color: var(--primary-blue);
        }

        .stats-counter {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(45deg, #FCD34D, #F59E0B, #EAB308);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
        }

        .floating-animation {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px) rotate(0deg);
            }

            33% {
                transform: translateY(-15px) rotate(1deg);
            }

            66% {
                transform: translateY(-25px) rotate(-1deg);
            }
        }

        /* Navigation Enhancements */
        .nav-primary {
            background: rgba(248, 250, 252, 0.98);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(51, 65, 85, 0.2);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .nav-link-primary {
            color: var(--slate-gray);
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .nav-link-primary:hover,
        .nav-link-primary.active {
            color: var(--primary-blue);
            background: rgba(30, 64, 175, 0.1);
            border-radius: 6px;
        }

        /* Button Styles */
        .btn-primary {
            background: linear-gradient(135deg, #1E40AF, #1E293B);
            color: var(--white);
            transition: all 0.3s ease;
            border: 2px solid transparent;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #1D4ED8, #0F172A);
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(30, 64, 175, 0.4);
            border-color: #3B82F6;
        }

        .btn-secondary {
            background: linear-gradient(135deg, #F59E0B, #D97706);
            color: var(--white);
            font-weight: 700;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #EAB308, #CA8A04);
            color: var(--white);
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(245, 158, 11, 0.4);
            border-color: #FCD34D;
        }

        /* Template Card Colors - Darker Combinations */
        .template-card-blue {
            background: linear-gradient(135deg, #1E40AF, #1E293B);
        }

        .template-card-green {
            background: linear-gradient(135deg, #059669, #047857);
        }

        .template-card-purple {
            background: linear-gradient(135deg, #7C3AED, #5B21B6);
        }

        .template-card-red {
            background: linear-gradient(135deg, #DC2626, #B91C1C);
        }

        .template-card-yellow {
            background: linear-gradient(135deg, #D97706, #B45309);
        }

        .template-card-indigo {
            background: linear-gradient(135deg, #4F46E5, #3730A3);
        }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="nav-primary shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <i class="fas fa-file-pdf text-2xl mr-3" style="color: var(--indonesian-red);"></i>
                        <span class="text-xl font-bold" style="color: var(--primary-navy);">PDF Letter Generator</span>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-4">
                        <a href="#home" class="nav-link-primary active px-3 py-2 rounded-md text-sm font-medium">Beranda</a>
                        <a href="#features" class="nav-link-primary px-3 py-2 rounded-md text-sm font-medium">Fitur</a>
                        <a href="#templates" class="nav-link-primary px-3 py-2 rounded-md text-sm font-medium">Template</a>
                        <a href="#about" class="nav-link-primary px-3 py-2 rounded-md text-sm font-medium">Tentang</a>
                    </div>
                </div>

                <!-- Auth Buttons -->
                <div class="flex items-center space-x-4">
                    <?php if ($isLoggedIn): ?>
                        <a href="<?php echo $userRole === 'admin' ? 'admin' : 'dashboard'; ?>"
                            class="btn-primary px-4 py-2 rounded-lg">
                            <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                        </a>
                        <a href="logout" class="nav-link-primary px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-sign-out-alt mr-1"></i>Logout
                        </a>
                    <?php else: ?>
                        <a href="login" class="nav-link-primary px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-sign-in-alt mr-1"></i>Masuk
                        </a>
                        <a href="register" class="btn-primary px-4 py-2 rounded-lg">
                            <i class="fas fa-user-plus mr-2"></i>Daftar
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button type="button" class="text-gray-700 hover:text-blue-600" onclick="toggleMobileMenu()">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div id="mobile-menu" class="md:hidden hidden bg-white border-t">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="#home" class="block text-blue-600 px-3 py-2 rounded-md text-base font-medium">Beranda</a>
                <a href="#features" class="block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-base font-medium">Fitur</a>
                <a href="#templates" class="block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-base font-medium">Template</a>
                <a href="#about" class="block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-base font-medium">Tentang</a>
                <?php if (!$isLoggedIn): ?>
                    <a href="login" class="block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-base font-medium">Masuk</a>
                    <a href="register" class="block bg-blue-600 text-white px-3 py-2 rounded-md text-base font-medium">Daftar</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="gradient-bg hero-pattern min-h-screen flex items-center">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Hero Content -->
                <div class="text-white hero-content relative z-10">
                    <h1 class="text-4xl md:text-6xl font-bold mb-6 leading-tight hero-title" style="color: var(--text-primary);">
                        Buat Surat Resmi
                        <span style="color: #FCD34D; text-shadow: 0 3px 6px rgba(0, 0, 0, 0.7); font-weight: 900;">Profesional</span>
                        dalam Hitungan Menit
                    </h1>
                    <p class="text-xl md:text-2xl mb-8 leading-relaxed hero-text" style="color: var(--text-secondary);">
                        Generator surat resmi Indonesia dengan template profesional.
                        Mudah, cepat, dan sesuai standar formal Indonesia.
                    </p>

                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 mb-8">
                        <?php if ($isLoggedIn): ?>
                            <a href="<?php echo $userRole === 'admin' ? 'admin' : 'dashboard'; ?>"
                                class="btn-secondary px-8 py-4 rounded-lg font-semibold text-lg text-center">
                                <i class="fas fa-rocket mr-2"></i>Mulai Membuat Surat
                            </a>
                        <?php else: ?>
                            <a href="register" class="btn-secondary px-8 py-4 rounded-lg font-semibold text-lg text-center">
                                <i class="fas fa-rocket mr-2"></i>Mulai Gratis Sekarang
                            </a>
                            <a href="login" class="glass-card text-white px-8 py-4 rounded-lg font-semibold text-lg hover:bg-white hover:bg-opacity-20 transition-all duration-200 text-center">
                                <i class="fas fa-sign-in-alt mr-2"></i>Sudah Punya Akun?
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-3 gap-8 text-center relative z-10">
                        <div>
                            <div class="stats-counter">50+</div>
                            <p class="font-semibold text-lg" style="color: var(--text-secondary); text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);">Template Surat</p>
                        </div>
                        <div>
                            <div class="stats-counter">1000+</div>
                            <p class="font-semibold text-lg" style="color: var(--text-secondary); text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);">Pengguna Aktif</p>
                        </div>
                        <div>
                            <div class="stats-counter">99%</div>
                            <p class="font-semibold text-lg" style="color: var(--text-secondary); text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);">Kepuasan User</p>
                        </div>
                    </div>
                </div>

                <!-- Hero Image/Animation -->
                <div class="relative">
                    <div class="floating-animation">
                        <div class="glass-card rounded-2xl p-8 text-center">
                            <i class="fas fa-file-pdf text-6xl text-yellow-300 mb-4"></i>
                            <h3 class="text-2xl font-bold text-white mb-4">PDF Siap Cetak</h3>
                            <p class="text-blue-100 mb-6">Format profesional, siap untuk keperluan resmi</p>
                            <div class="bg-white bg-opacity-20 rounded-lg p-4">
                                <div class="flex items-center justify-center space-x-2 text-white">
                                    <i class="fas fa-check-circle text-green-400"></i>
                                    <span>Format Standar Indonesia</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Mengapa Memilih Kami?</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Solusi lengkap untuk kebutuhan surat resmi Anda dengan teknologi terdepan dan kemudahan penggunaan
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="feature-card rounded-xl p-8 shadow-lg">
                    <div class="w-16 h-16 rounded-lg flex items-center justify-center mb-6 feature-icon" style="background: linear-gradient(135deg, #1E40AF, #0F172A);">
                        <i class="fas fa-magic text-2xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Template Profesional</h3>
                    <p class="text-gray-600 leading-relaxed">
                        50+ template surat resmi yang sesuai dengan standar formal Indonesia.
                        Siap pakai dan mudah dikustomisasi.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="feature-card rounded-xl p-8 shadow-lg">
                    <div class="w-16 h-16 rounded-lg flex items-center justify-center mb-6 feature-icon" style="background: linear-gradient(135deg, #047857, #15803D);">
                        <i class="fas fa-bolt text-2xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Cepat & Mudah</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Buat surat dalam hitungan menit. Interface yang intuitif
                        memungkinkan siapa saja dapat membuat surat profesional.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="feature-card rounded-xl p-8 shadow-lg">
                    <div class="w-16 h-16 rounded-lg flex items-center justify-center mb-6 feature-icon" style="background: linear-gradient(135deg, #B91C1C, #C2410C);">
                        <i class="fas fa-file-pdf text-2xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Export PDF Berkualitas</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Hasil akhir berupa PDF berkualitas tinggi, siap cetak,
                        dan sesuai standar dokumen resmi.
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="feature-card rounded-xl p-8 shadow-lg">
                    <div class="w-16 h-16 rounded-lg flex items-center justify-center mb-6 feature-icon" style="background: linear-gradient(135deg, #B45309, #D97706);">
                        <i class="fas fa-shield-alt text-2xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Aman & Terpercaya</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Data Anda aman dengan enkripsi tingkat tinggi.
                        Privasi dan keamanan adalah prioritas utama kami.
                    </p>
                </div>

                <!-- Feature 5 -->
                <div class="feature-card rounded-xl p-8 shadow-lg">
                    <div class="w-16 h-16 rounded-lg flex items-center justify-center mb-6 feature-icon" style="background: linear-gradient(135deg, #0369A1, #1E40AF);">
                        <i class="fas fa-cloud text-2xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Akses Dimana Saja</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Platform berbasis web yang dapat diakses dari perangkat apapun.
                        Buat surat kapan saja, dimana saja.
                    </p>
                </div>

                <!-- Feature 6 -->
                <div class="feature-card rounded-xl p-8 shadow-lg">
                    <div class="w-16 h-16 rounded-lg flex items-center justify-center mb-6 feature-icon" style="background: linear-gradient(135deg, #334155, #475569);">
                        <i class="fas fa-history text-2xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Riwayat Tersimpan</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Semua surat yang pernah dibuat tersimpan otomatis.
                        Mudah untuk mengedit atau membuat surat serupa.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Templates Section -->
    <section id="templates" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Template Surat Populer</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Pilihan template surat resmi yang paling sering digunakan untuk berbagai keperluan
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Template 1 -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    <div class="h-48 template-card-blue flex items-center justify-center">
                        <i class="fas fa-briefcase text-6xl text-white"></i>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Surat Lamaran Kerja</h3>
                        <p class="text-gray-600 mb-4">Template profesional untuk melamar pekerjaan dengan format yang menarik dan sesuai standar HR.</p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">
                                <i class="fas fa-download mr-1"></i>1,234 downloads
                            </span>
                            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">Populer</span>
                        </div>
                    </div>
                </div>

                <!-- Template 2 -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    <div class="h-48 template-card-green flex items-center justify-center">
                        <i class="fas fa-id-card text-6xl text-white"></i>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Surat Keterangan</h3>
                        <p class="text-gray-600 mb-4">Template untuk berbagai jenis surat keterangan seperti keterangan kerja, domisili, dan lainnya.</p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">
                                <i class="fas fa-download mr-1"></i>987 downloads
                            </span>
                            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">Terbaru</span>
                        </div>
                    </div>
                </div>

                <!-- Template 3 -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    <div class="h-48 template-card-purple flex items-center justify-center">
                        <i class="fas fa-graduation-cap text-6xl text-white"></i>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Surat Akademik</h3>
                        <p class="text-gray-600 mb-4">Template untuk keperluan akademik seperti surat izin, permohonan, dan surat resmi kampus.</p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">
                                <i class="fas fa-download mr-1"></i>756 downloads
                            </span>
                            <span class="bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-sm font-medium">Akademik</span>
                        </div>
                    </div>
                </div>

                <!-- Template 4 -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    <div class="h-48 template-card-red flex items-center justify-center">
                        <i class="fas fa-building text-6xl text-white"></i>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Surat Bisnis</h3>
                        <p class="text-gray-600 mb-4">Template untuk keperluan bisnis seperti surat penawaran, kontrak, dan korespondensi bisnis.</p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">
                                <i class="fas fa-download mr-1"></i>654 downloads
                            </span>
                            <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-medium">Bisnis</span>
                        </div>
                    </div>
                </div>

                <!-- Template 5 -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    <div class="h-48 template-card-yellow flex items-center justify-center">
                        <i class="fas fa-file-contract text-6xl text-white"></i>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Surat Resmi</h3>
                        <p class="text-gray-600 mb-4">Template untuk surat resmi pemerintahan, organisasi, dan instansi dengan format yang tepat.</p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">
                                <i class="fas fa-download mr-1"></i>543 downloads
                            </span>
                            <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-medium">Resmi</span>
                        </div>
                    </div>
                </div>

                <!-- Template 6 -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    <div class="h-48 template-card-indigo flex items-center justify-center">
                        <i class="fas fa-heart text-6xl text-white"></i>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Surat Pribadi</h3>
                        <p class="text-gray-600 mb-4">Template untuk keperluan pribadi seperti surat izin, permohonan, dan surat personal lainnya.</p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">
                                <i class="fas fa-download mr-1"></i>432 downloads
                            </span>
                            <span class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-sm font-medium">Pribadi</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-12">
                <?php if ($isLoggedIn): ?>
                    <a href="<?php echo $userRole === 'admin' ? 'admin' : 'dashboard'; ?>"
                        class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors inline-block">
                        <i class="fas fa-eye mr-2"></i>Lihat Semua Template
                    </a>
                <?php else: ?>
                    <a href="register" class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors inline-block">
                        <i class="fas fa-rocket mr-2"></i>Mulai Membuat Surat
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- About Content -->
                <div>
                    <h2 class="text-4xl font-bold text-gray-900 mb-6">Tentang Indonesian PDF Letter Generator</h2>
                    <p class="text-lg text-gray-600 mb-6 leading-relaxed">
                        Kami adalah platform terdepan untuk pembuatan surat resmi Indonesia dalam format PDF.
                        Dengan pengalaman lebih dari 5 tahun, kami telah membantu ribuan pengguna membuat
                        surat profesional dengan mudah dan cepat.
                    </p>
                    <p class="text-lg text-gray-600 mb-8 leading-relaxed">
                        Misi kami adalah menyederhanakan proses pembuatan surat resmi tanpa mengurangi
                        kualitas dan profesionalitas. Setiap template dirancang sesuai dengan standar
                        formal Indonesia dan dapat disesuaikan dengan kebutuhan spesifik Anda.
                    </p>

                    <!-- Key Points -->
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-check text-blue-600"></i>
                            </div>
                            <span class="text-gray-700">Template sesuai standar formal Indonesia</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-check text-blue-600"></i>
                            </div>
                            <span class="text-gray-700">Interface yang mudah digunakan</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-check text-blue-600"></i>
                            </div>
                            <span class="text-gray-700">Hasil PDF berkualitas tinggi</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-check text-blue-600"></i>
                            </div>
                            <span class="text-gray-700">Keamanan data terjamin</span>
                        </div>
                    </div>
                </div>

                <!-- About Image/Stats -->
                <div class="relative">
                    <div class="bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl p-8 text-white">
                        <h3 class="text-2xl font-bold mb-8 text-center">Statistik Platform</h3>

                        <div class="grid grid-cols-2 gap-6">
                            <div class="text-center">
                                <div class="text-3xl font-bold mb-2">50+</div>
                                <div class="text-blue-100">Template Tersedia</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold mb-2">1000+</div>
                                <div class="text-blue-100">Pengguna Aktif</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold mb-2">5000+</div>
                                <div class="text-blue-100">Surat Dibuat</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold mb-2">99%</div>
                                <div class="text-blue-100">Kepuasan User</div>
                            </div>
                        </div>

                        <div class="mt-8 text-center">
                            <div class="bg-white bg-opacity-20 rounded-lg p-4">
                                <i class="fas fa-award text-3xl mb-2"></i>
                                <div class="font-semibold">Platform Terpercaya</div>
                                <div class="text-sm text-blue-100">Sejak 2019</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 gradient-bg hero-pattern">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl font-bold text-white mb-6">
                Siap Membuat Surat Profesional?
            </h2>
            <p class="text-xl text-blue-100 mb-8 leading-relaxed">
                Bergabunglah dengan ribuan pengguna yang telah mempercayai platform kami
                untuk kebutuhan surat resmi mereka.
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <?php if ($isLoggedIn): ?>
                    <a href="<?php echo $userRole === 'admin' ? 'admin' : 'dashboard'; ?>"
                        class="bg-yellow-400 text-gray-900 px-8 py-4 rounded-lg font-semibold text-lg hover:bg-yellow-300 transition-all duration-200">
                        <i class="fas fa-tachometer-alt mr-2"></i>Buka Dashboard
                    </a>
                <?php else: ?>
                    <a href="register" class="bg-yellow-400 text-gray-900 px-8 py-4 rounded-lg font-semibold text-lg hover:bg-yellow-300 transition-all duration-200">
                        <i class="fas fa-rocket mr-2"></i>Daftar Gratis Sekarang
                    </a>
                    <a href="login" class="glass-card text-white px-8 py-4 rounded-lg font-semibold text-lg hover:bg-white hover:bg-opacity-20 transition-all duration-200">
                        <i class="fas fa-sign-in-alt mr-2"></i>Masuk ke Akun
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer style="background: linear-gradient(135deg, var(--primary-navy) 0%, var(--black) 100%);" class="text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8">
                <!-- Company Info -->
                <div class="md:col-span-2">
                    <div class="flex items-center mb-4">
                        <i class="fas fa-file-pdf text-2xl mr-3" style="color: var(--indonesian-red);"></i>
                        <span class="text-xl font-bold">PDF Letter Generator</span>
                    </div>
                    <p class="text-gray-400 mb-4 leading-relaxed">
                        Platform terdepan untuk pembuatan surat resmi Indonesia dalam format PDF.
                        Mudah, cepat, dan profesional.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-blue-400 transition-colors">
                            <i class="fab fa-facebook-f text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-blue-400 transition-colors">
                            <i class="fab fa-twitter text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-blue-400 transition-colors">
                            <i class="fab fa-instagram text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-blue-400 transition-colors">
                            <i class="fab fa-linkedin-in text-xl"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="#home" class="text-gray-400 hover:text-white transition-colors">Beranda</a></li>
                        <li><a href="#features" class="text-gray-400 hover:text-white transition-colors">Fitur</a></li>
                        <li><a href="#templates" class="text-gray-400 hover:text-white transition-colors">Template</a></li>
                        <li><a href="#about" class="text-gray-400 hover:text-white transition-colors">Tentang</a></li>
                    </ul>
                </div>

                <!-- Support -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Support</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Bantuan</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">FAQ</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Kontak</a></li>
                        <li><a href="app/views/static/privacy.php" class="text-gray-400 hover:text-white transition-colors">Privacy Policy</a></li>
                        <li><a href="app/views/static/terms.php" class="text-gray-400 hover:text-white transition-colors">Terms of Service</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-800 mt-8 pt-8 text-center">
                <p class="text-gray-400">
                    &copy; 2025 Indonesian PDF Letter Generator. All rights reserved.
                </p>
            </div>
        </div>
    </footer>

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        }

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Counter animation
        function animateCounters() {
            const counters = document.querySelectorAll('.stats-counter');
            counters.forEach(counter => {
                const target = parseInt(counter.textContent);
                const increment = target / 100;
                let current = 0;

                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        counter.textContent = target + (counter.textContent.includes('%') ? '%' : '+');
                        clearInterval(timer);
                    } else {
                        counter.textContent = Math.floor(current) + (counter.textContent.includes('%') ? '%' : '+');
                    }
                }, 20);
            });
        }

        // Trigger counter animation when in view
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                    observer.unobserve(entry.target);
                }
            });
        });

        document.addEventListener('DOMContentLoaded', () => {
            const statsSection = document.querySelector('.stats-counter').parentElement.parentElement;
            observer.observe(statsSection);
        });
    </script>

    <!-- Home Page JavaScript -->
    <script src="public/assets/js/home.js"></script>

    <!-- Security Protection Script -->
    <script src="public/assets/js/security-protection.js"></script>
</body>

</html>