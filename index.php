<?php
// Konfigurasi Database
$host = 'localhost';
$dbname = 'db_sistamik';
$username = 'root';
$password = '';

// Koneksi Database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Inisialisasi variabel untuk pesan
$success_message = '';
$error_message = '';

// CREATE - Tambah Data Mahasiswa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    try {
        $stmt = $pdo->prepare("INSERT INTO mahasiswa (nim, nama_lengkap, jenis_kelamin, tempat_lahir, tanggal_lahir, alamat, no_telepon, email, id_jurusan, angkatan, status, tanggal_masuk) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $tanggal_masuk = !empty($_POST['tanggal_masuk']) ? $_POST['tanggal_masuk'] : date('Y-m-d');
        
        $stmt->execute([
            $_POST['nim'],
            $_POST['nama_lengkap'],
            $_POST['jenis_kelamin'],
            $_POST['tempat_lahir'],
            $_POST['tanggal_lahir'],
            $_POST['alamat'],
            $_POST['no_telepon'],
            $_POST['email'],
            $_POST['id_jurusan'],
            $_POST['angkatan'],
            $_POST['status'],
            $tanggal_masuk
        ]);
        
        $success_message = '✅ Data mahasiswa berhasil ditambahkan!';
    } catch(PDOException $e) {
        $error_message = '❌ Gagal menambahkan data: ' . $e->getMessage();
    }
}

// UPDATE - Edit Data Mahasiswa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    try {
        $stmt = $pdo->prepare("UPDATE mahasiswa SET nim=?, nama_lengkap=?, jenis_kelamin=?, tempat_lahir=?, tanggal_lahir=?, alamat=?, no_telepon=?, email=?, id_jurusan=?, angkatan=?, status=?, tanggal_masuk=? WHERE id_mahasiswa=?");
        
        $stmt->execute([
            $_POST['nim'],
            $_POST['nama_lengkap'],
            $_POST['jenis_kelamin'],
            $_POST['tempat_lahir'],
            $_POST['tanggal_lahir'],
            $_POST['alamat'],
            $_POST['no_telepon'],
            $_POST['email'],
            $_POST['id_jurusan'],
            $_POST['angkatan'],
            $_POST['status'],
            $_POST['tanggal_masuk'],
            $_POST['id_mahasiswa']
        ]);
        
        $success_message = '✅ Data mahasiswa berhasil diperbarui!';
    } catch(PDOException $e) {
        $error_message = '❌ Gagal memperbarui data: ' . $e->getMessage();
    }
}

// DELETE - Hapus Data Mahasiswa
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM mahasiswa WHERE id_mahasiswa = ?");
        $stmt->execute([$_GET['id']]);
        $success_message = '✅ Data mahasiswa berhasil dihapus!';
    } catch(PDOException $e) {
        $error_message = '❌ Gagal menghapus data: ' . $e->getMessage();
    }
}

// Ambil data untuk edit
$edit_data = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE id_mahasiswa = ?");
    $stmt->execute([$_GET['id']]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Load Jurusan
$stmt_jurusan = $pdo->query("SELECT * FROM jurusan ORDER BY nama_jurusan");
$jurusan_list = $stmt_jurusan->fetchAll(PDO::FETCH_ASSOC);

// Load Mahasiswa dengan JOIN ke tabel jurusan
$search = isset($_GET['search']) ? $_GET['search'] : '';
if ($search) {
    $stmt = $pdo->prepare("SELECT m.*, j.nama_jurusan, j.kode_jurusan 
                          FROM mahasiswa m 
                          LEFT JOIN jurusan j ON m.id_jurusan = j.id_jurusan 
                          WHERE m.nim LIKE ? OR m.nama_lengkap LIKE ? OR j.nama_jurusan LIKE ? OR j.kode_jurusan LIKE ? OR m.angkatan LIKE ?
                          ORDER BY m.angkatan DESC, m.nim ASC");
    $search_param = "%$search%";
    $stmt->execute([$search_param, $search_param, $search_param, $search_param, $search_param]);
} else {
    $stmt = $pdo->query("SELECT m.*, j.nama_jurusan, j.kode_jurusan 
                        FROM mahasiswa m 
                        LEFT JOIN jurusan j ON m.id_jurusan = j.id_jurusan 
                        ORDER BY m.angkatan DESC, m.nim ASC");
}
$mahasiswa_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung Statistik
$total_mahasiswa = count($mahasiswa_list);
$total_aktif = count(array_filter($mahasiswa_list, function($m) { return $m['status'] === 'Aktif'; }));
$total_ppl = count(array_filter($mahasiswa_list, function($m) { return $m['kode_jurusan'] === 'PPL'; }));
$total_dm = count(array_filter($mahasiswa_list, function($m) { return $m['kode_jurusan'] === 'DM'; }));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISTAMIK - Sistem Akademik PETIK</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366F1;
            --primary-dark: #4F46E5;
            --primary-light: #818CF8;
            --secondary: #EC4899;
            --success: #10B981;
            --warning: #F59E0B;
            --danger: #EF4444;
            --dark: #0F172A;
            --dark-secondary: #1E293B;
            --dark-tertiary: #334155;
            --light: #F8FAFC;
            --glass: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.18);
            --shadow-3d: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            min-height: 100vh;
            padding: 20px;
            overflow-x: hidden;
            position: relative;
        }

        /* Animated Background Elements */
        body::before {
            content: '';
            position: fixed;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 20% 50%, rgba(99, 102, 241, 0.3) 0%, transparent 50%),
                        radial-gradient(circle at 80% 80%, rgba(236, 72, 153, 0.3) 0%, transparent 50%),
                        radial-gradient(circle at 40% 20%, rgba(16, 185, 129, 0.2) 0%, transparent 50%);
            animation: backgroundFloat 20s ease-in-out infinite;
            z-index: -1;
        }

        @keyframes backgroundFloat {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(30px, -30px) rotate(5deg); }
            66% { transform: translate(-20px, 20px) rotate(-5deg); }
        }

        /* Floating 3D Particles */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            animation: float 15s infinite ease-in-out;
        }

        @keyframes float {
            0%, 100% {
                transform: translate3d(0, 0, 0) scale(1);
                opacity: 0;
            }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% {
                transform: translate3d(100px, -1000px, 100px) scale(0);
                opacity: 0;
            }
        }

        /* Main Container with 3D Transform */
        .container {
            max-width: 1600px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
            perspective: 2000px;
        }

        /* Glassmorphism Header with 3D Effect */
        .header {
            background: var(--glass);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid var(--glass-border);
            border-radius: 30px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: var(--shadow-3d),
                        inset 0 1px 0 rgba(255, 255, 255, 0.2);
            animation: headerSlideDown 0.8s cubic-bezier(0.16, 1, 0.3, 1);
            transform-style: preserve-3d;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        @keyframes headerSlideDown {
            from {
                opacity: 0;
                transform: translateY(-50px) rotateX(20deg);
            }
            to {
                opacity: 1;
                transform: translateY(0) rotateX(0deg);
            }
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .logo-3d {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5em;
            animation: logo3dRotate 10s infinite ease-in-out;
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.4),
                        inset 0 1px 0 rgba(255, 255, 255, 0.3);
            transform-style: preserve-3d;
        }

        @keyframes logo3dRotate {
            0%, 100% { transform: rotateY(0deg) rotateX(0deg); }
            25% { transform: rotateY(180deg) rotateX(10deg); }
            50% { transform: rotateY(360deg) rotateX(0deg); }
            75% { transform: rotateY(540deg) rotateX(-10deg); }
        }

        .header-text h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 2.5em;
            font-weight: 800;
            background: linear-gradient(135deg, #fff, #E0E7FF);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 5px;
            text-shadow: 0 0 30px rgba(255, 255, 255, 0.5);
        }

        .header-text p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1em;
            font-weight: 500;
        }

        .header-actions {
            display: flex;
            gap: 15px;
        }

        .header-btn {
            padding: 12px 24px;
            border: none;
            border-radius: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            background: rgba(255, 255, 255, 0.2);
            color: white;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transform-style: preserve-3d;
            text-decoration: none;
            display: inline-block;
        }

        .header-btn:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            background: rgba(255, 255, 255, 0.3);
        }

        /* Premium Stats Cards with 3D Hover */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--glass);
            backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid var(--glass-border);
            border-radius: 25px;
            padding: 30px;
            position: relative;
            overflow: hidden;
            animation: cardFadeIn 0.6s ease-out backwards;
            transform-style: preserve-3d;
            transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
            cursor: pointer;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            transform: scale(0);
            transition: transform 0.6s ease;
        }

        .stat-card:hover::before {
            transform: scale(1);
        }

        .stat-card:hover {
            transform: translateY(-15px) rotateX(5deg);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4),
                        inset 0 1px 0 rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.4);
        }

        @keyframes cardFadeIn {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8em;
            margin-bottom: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            animation: iconFloat 3s ease-in-out infinite;
        }

        @keyframes iconFloat {
            0%, 100% { transform: translateY(0px) rotateZ(0deg); }
            50% { transform: translateY(-10px) rotateZ(5deg); }
        }

        .stat-card:nth-child(1) .stat-icon {
            background: linear-gradient(135deg, #6366F1, #8B5CF6);
        }

        .stat-card:nth-child(2) .stat-icon {
            background: linear-gradient(135deg, #10B981, #059669);
        }

        .stat-card:nth-child(3) .stat-icon {
            background: linear-gradient(135deg, #F59E0B, #D97706);
        }

        .stat-card:nth-child(4) .stat-icon {
            background: linear-gradient(135deg, #EC4899, #DB2777);
        }

        .stat-content h3 {
            font-size: 2.5em;
            font-weight: 800;
            color: white;
            margin-bottom: 5px;
            font-family: 'Outfit', sans-serif;
        }

        .stat-content p {
            color: rgba(255, 255, 255, 0.8);
            font-weight: 600;
            font-size: 0.95em;
            letter-spacing: 0.5px;
        }

        /* Premium Form Section */
        .form-section {
            background: var(--glass);
            backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid var(--glass-border);
            border-radius: 30px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: var(--shadow-3d);
            animation: formSlideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
        }

        @keyframes formSlideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-section::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary), var(--primary));
            background-size: 200% 100%;
            animation: gradientShift 3s ease infinite;
        }

        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .form-section h2 {
            color: white;
            margin-bottom: 30px;
            font-size: 1.8em;
            font-weight: 700;
            font-family: 'Outfit', sans-serif;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .form-section h2::before {
            content: '';
            width: 6px;
            height: 40px;
            background: linear-gradient(180deg, var(--primary), var(--secondary));
            border-radius: 10px;
            animation: pulseBar 2s ease-in-out infinite;
        }

        @keyframes pulseBar {
            0%, 100% { transform: scaleY(1); opacity: 1; }
            50% { transform: scaleY(0.7); opacity: 0.7; }
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
        }

        .form-group {
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: rgba(255, 255, 255, 0.95);
            font-weight: 600;
            font-size: 0.9em;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 15px 20px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            color: white;
            font-size: 0.95em;
            font-weight: 500;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            backdrop-filter: blur(10px);
        }

        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3),
                        inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }

        .form-group input:focus + label,
        .form-group select:focus + label,
        .form-group textarea:focus + label {
            color: var(--primary-light);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-group select option {
            background: #1E293B;
            color: white;
        }

        /* Premium Buttons with 3D Effect */
        .form-actions {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 16px 35px;
            border: none;
            border-radius: 15px;
            font-size: 1em;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
            transform-style: preserve-3d;
            letter-spacing: 0.5px;
            text-decoration: none;
            display: inline-block;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 20px 50px rgba(99, 102, 241, 0.5);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success), #059669);
            color: white;
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4);
        }

        .btn-success:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 20px 50px rgba(16, 185, 129, 0.5);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger), #DC2626);
            color: white;
            padding: 10px 20px;
            font-size: 0.9em;
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4);
        }

        .btn-danger:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 15px 35px rgba(239, 68, 68, 0.5);
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--warning), #D97706);
            color: white;
            padding: 10px 20px;
            font-size: 0.9em;
            box-shadow: 0 8px 20px rgba(245, 158, 11, 0.4);
        }

        .btn-warning:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 15px 35px rgba(245, 158, 11, 0.5);
        }

        .btn-cancel {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .btn-cancel:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-3px);
        }

        /* Search Box with Animation */
        .search-container {
            margin-bottom: 30px;
            position: relative;
            animation: searchSlideIn 0.6s ease-out;
        }

        @keyframes searchSlideIn {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 18px 60px 18px 25px;
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            color: white;
            font-size: 1em;
            font-weight: 500;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .search-box input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .search-box input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--primary-light);
            box-shadow: 0 15px 40px rgba(99, 102, 241, 0.3);
            transform: translateY(-2px);
        }

        .search-icon {
            position: absolute;
            right: 25px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.3em;
            color: rgba(255, 255, 255, 0.6);
            pointer-events: none;
        }

        /* Premium Table Container */
        .table-container {
            background: var(--glass);
            backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid var(--glass-border);
            border-radius: 30px;
            overflow: hidden;
            box-shadow: var(--shadow-3d);
            animation: tableSlideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes tableSlideUp {
            from {
                opacity: 0;
                transform: translateY(50px) rotateX(10deg);
            }
            to {
                opacity: 1;
                transform: translateY(0) rotateX(0deg);
            }
        }

        .table-wrapper {
            overflow-x: auto;
            max-height: 600px;
        }

        /* Custom Scrollbar */
        .table-wrapper::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        .table-wrapper::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }

        .table-wrapper::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, var(--primary), var(--secondary));
            border-radius: 10px;
        }

        .table-wrapper::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, var(--primary-light), var(--secondary));
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 1000px;
        }

        table thead {
            position: sticky;
            top: 0;
            z-index: 10;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.9), rgba(139, 92, 246, 0.9));
            backdrop-filter: blur(20px);
        }

        table th {
            padding: 20px;
            text-align: left;
            color: white;
            font-weight: 700;
            font-size: 0.9em;
            letter-spacing: 1px;
            text-transform: uppercase;
            border-bottom: 2px solid rgba(255, 255, 255, 0.3);
        }

        table tbody tr {
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.05);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            animation: rowFadeIn 0.5s ease-out backwards;
        }

        @keyframes rowFadeIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        table tbody tr:hover {
            background: rgba(255, 255, 255, 0.12);
            transform: scale(1.01);
            box-shadow: inset 0 0 20px rgba(99, 102, 241, 0.2);
        }

        table td {
            padding: 18px 20px;
            color: rgba(255, 255, 255, 0.95);
            font-weight: 500;
            font-size: 0.9em;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        /* Status Badges with Glow */
        .status-badge {
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            display: inline-block;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            animation: badgePulse 2s ease-in-out infinite;
        }

        @keyframes badgePulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .status-aktif {
            background: linear-gradient(135deg, #10B981, #059669);
            color: white;
            box-shadow: 0 5px 20px rgba(16, 185, 129, 0.4);
        }

        .status-cuti {
            background: linear-gradient(135deg, #F59E0B, #D97706);
            color: white;
            box-shadow: 0 5px 20px rgba(245, 158, 11, 0.4);
        }

        .status-lulus {
            background: linear-gradient(135deg, #3B82F6, #2563EB);
            color: white;
            box-shadow: 0 5px 20px rgba(59, 130, 246, 0.4);
        }

        .status-do {
            background: linear-gradient(135deg, #EF4444, #DC2626);
            color: white;
            box-shadow: 0 5px 20px rgba(239, 68, 68, 0.4);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: rgba(255, 255, 255, 0.8);
        }

        .empty-state-icon {
            font-size: 5em;
            margin-bottom: 20px;
            animation: emptyFloat 3s ease-in-out infinite;
        }

        @keyframes emptyFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        /* Alert Messages */
        .alert {
            padding: 20px 25px;
            border-radius: 20px;
            margin-bottom: 25px;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: alertSlideIn 0.5s cubic-bezier(0.16, 1, 0.3, 1);
            font-weight: 600;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        @keyframes alertSlideIn {
            from {
                opacity: 0;
                transform: translateY(-30px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.9), rgba(5, 150, 105, 0.9));
            color: white;
        }

        .alert-error {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.9), rgba(220, 38, 38, 0.9));
            color: white;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .form-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .header {
                padding: 25px;
                border-radius: 20px;
            }

            .header-content {
                flex-direction: column;
                text-align: center;
            }

            .logo-section {
                flex-direction: column;
            }

            .logo-3d {
                width: 70px;
                height: 70px;
            }

            .header-text h1 {
                font-size: 2em;
            }

            .stats {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .form-section {
                padding: 25px;
                border-radius: 20px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }

            .form-actions .btn {
                width: 100%;
            }

            .action-buttons {
                flex-direction: column;
            }

            .action-buttons .btn {
                width: 100%;
            }

            table th,
            table td {
                padding: 12px 15px;
                font-size: 0.85em;
            }
        }

        @media (max-width: 480px) {
            .header-text h1 {
                font-size: 1.6em;
            }

            .stat-content h3 {
                font-size: 2em;
            }

            .form-section h2 {
                font-size: 1.4em;
            }
        }

        /* 3D Tilt Effect on Cards */
        @media (hover: hover) {
            .stat-card {
                transform-style: preserve-3d;
            }

            .stat-card:hover {
                animation: tilt3D 0.5s ease-in-out;
            }

            @keyframes tilt3D {
                0%, 100% { transform: perspective(1000px) rotateY(0deg) rotateX(0deg); }
                25% { transform: perspective(1000px) rotateY(-5deg) rotateX(2deg) translateY(-15px); }
                75% { transform: perspective(1000px) rotateY(5deg) rotateX(-2deg) translateY(-15px); }
            }
        }
    </style>
</head>
<body>
    <!-- Floating Particles -->
    <div class="particles" id="particles"></div>

    <div class="container">
        <!-- Premium Header -->
        <div class="header">
            <div class="header-content">
                <div class="logo-section">
                    <div class="logo-3d">🎓</div>
                    <div class="header-text">
                        <h1>SISTAMIK</h1>
                        <p>Sistem Akademik Politeknik PETIK Jombang</p>
                    </div>
                </div>
                <div class="header-actions">
                    <a href="index.php" class="header-btn">🔄 Refresh</a>
                    <button class="header-btn">⚙️ Settings</button>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if ($success_message): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
        <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Premium Statistics Cards -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-content">
                    <h3><?php echo $total_mahasiswa; ?></h3>
                    <p>Total Mahasiswa</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-content">
                    <h3><?php echo $total_aktif; ?></h3>
                    <p>Mahasiswa Aktif</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">💻</div>
                <div class="stat-content">
                    <h3><?php echo $total_ppl; ?></h3>
                    <p>Jurusan PPL</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📱</div>
                <div class="stat-content">
                    <h3><?php echo $total_dm; ?></h3>
                    <p>Digital Marketing</p>
                </div>
            </div>
        </div>

        <!-- Form Section -->
        <div class="form-section">
            <h2><?php echo $edit_data ? 'Edit Data Mahasiswa' : 'Tambah Data Mahasiswa Baru'; ?></h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="<?php echo $edit_data ? 'update' : 'create'; ?>">
                <?php if ($edit_data): ?>
                <input type="hidden" name="id_mahasiswa" value="<?php echo $edit_data['id_mahasiswa']; ?>">
                <?php endif; ?>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nim">NIM <span style="color:#EC4899">*</span></label>
                        <input type="text" id="nim" name="nim" placeholder="Contoh: 2301001" value="<?php echo $edit_data ? htmlspecialchars($edit_data['nim']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="nama_lengkap">Nama Lengkap <span style="color:#EC4899">*</span></label>
                        <input type="text" id="nama_lengkap" name="nama_lengkap" placeholder="Nama lengkap mahasiswa" value="<?php echo $edit_data ? htmlspecialchars($edit_data['nama_lengkap']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="jenis_kelamin">Jenis Kelamin <span style="color:#EC4899">*</span></label>
                        <select id="jenis_kelamin" name="jenis_kelamin" required>
                            <option value="">Pilih jenis kelamin</option>
                            <option value="Laki-laki" <?php echo ($edit_data && $edit_data['jenis_kelamin'] == 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki</option>
                            <option value="Perempuan" <?php echo ($edit_data && $edit_data['jenis_kelamin'] == 'Perempuan') ? 'selected' : ''; ?>>Perempuan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="tempat_lahir">Tempat Lahir</label>
                        <input type="text" id="tempat_lahir" name="tempat_lahir" placeholder="Contoh: Jombang" value="<?php echo $edit_data ? htmlspecialchars($edit_data['tempat_lahir']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="tanggal_lahir">Tanggal Lahir</label>
                        <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="<?php echo $edit_data ? $edit_data['tanggal_lahir'] : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="no_telepon">No. Telepon</label>
                        <input type="text" id="no_telepon" name="no_telepon" placeholder="085xxxxxxxxx" value="<?php echo $edit_data ? htmlspecialchars($edit_data['no_telepon']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="mahasiswa@student.petik.ac.id" value="<?php echo $edit_data ? htmlspecialchars($edit_data['email']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="id_jurusan">Jurusan <span style="color:#EC4899">*</span></label>
                        <select id="id_jurusan" name="id_jurusan" required>
                            <option value="">Pilih jurusan</option>
                            <?php foreach ($jurusan_list as $jurusan): ?>
                            <option value="<?php echo $jurusan['id_jurusan']; ?>" <?php echo ($edit_data && $edit_data['id_jurusan'] == $jurusan['id_jurusan']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($jurusan['nama_jurusan']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="angkatan">Angkatan <span style="color:#EC4899">*</span></label>
                        <input type="number" id="angkatan" name="angkatan" placeholder="Contoh: 2024" min="2000" max="2100" value="<?php echo $edit_data ? $edit_data['angkatan'] : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="Aktif" <?php echo ($edit_data && $edit_data['status'] == 'Aktif') ? 'selected' : ''; ?>>Aktif</option>
                            <option value="Cuti" <?php echo ($edit_data && $edit_data['status'] == 'Cuti') ? 'selected' : ''; ?>>Cuti</option>
                            <option value="Lulus" <?php echo ($edit_data && $edit_data['status'] == 'Lulus') ? 'selected' : ''; ?>>Lulus</option>
                            <option value="DO" <?php echo ($edit_data && $edit_data['status'] == 'DO') ? 'selected' : ''; ?>>DO</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="tanggal_masuk">Tanggal Masuk</label>
                        <input type="date" id="tanggal_masuk" name="tanggal_masuk" value="<?php echo $edit_data ? $edit_data['tanggal_masuk'] : ''; ?>">
                    </div>
                </div>
                <div class="form-group" style="margin-top: 25px;">
                    <label for="alamat">Alamat Lengkap</label>
                    <textarea id="alamat" name="alamat" placeholder="Alamat lengkap mahasiswa"><?php echo $edit_data ? htmlspecialchars($edit_data['alamat']) : ''; ?></textarea>
                </div>
                <div class="form-actions">
                    <?php if ($edit_data): ?>
                    <button type="submit" class="btn btn-success">✨ Update Data</button>
                    <a href="index.php" class="btn btn-cancel">❌ Batal</a>
                    <?php else: ?>
                    <button type="submit" class="btn btn-primary">💾 Simpan Data</button>
                    <button type="reset" class="btn btn-cancel">🔄 Reset Form</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Search Container -->
        <div class="search-container">
            <form method="GET" action="">
                <div class="search-box">
                    <input type="text" name="search" placeholder="Cari mahasiswa (NIM, Nama, Jurusan, Angkatan)..." value="<?php echo htmlspecialchars($search); ?>">
                    <span class="search-icon">🔍</span>
                </div>
            </form>
        </div>

        <!-- Premium Table Container -->
        <div class="table-container">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NIM</th>
                            <th>Nama Lengkap</th>
                            <th>Jenis Kelamin</th>
                            <th>Jurusan</th>
                            <th>Angkatan</th>
                            <th>Email</th>
                            <th>No. Telepon</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($mahasiswa_list) > 0): ?>
                            <?php foreach ($mahasiswa_list as $index => $mahasiswa): ?>
                            <tr style="animation-delay: <?php echo $index * 0.05; ?>s">
                                <td><?php echo $index + 1; ?></td>
                                <td><strong><?php echo htmlspecialchars($mahasiswa['nim']); ?></strong></td>
                                <td><?php echo htmlspecialchars($mahasiswa['nama_lengkap']); ?></td>
                                <td><?php echo htmlspecialchars($mahasiswa['jenis_kelamin']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($mahasiswa['nama_jurusan']); ?><br>
                                    <small style="opacity: 0.7;">(<?php echo htmlspecialchars($mahasiswa['kode_jurusan']); ?>)</small>
                                </td>
                                <td><?php echo htmlspecialchars($mahasiswa['angkatan']); ?></td>
                                <td><?php echo $mahasiswa['email'] ? htmlspecialchars($mahasiswa['email']) : '-'; ?></td>
                                <td><?php echo $mahasiswa['no_telepon'] ? htmlspecialchars($mahasiswa['no_telepon']) : '-'; ?></td>
                                <td><span class="status-badge status-<?php echo strtolower($mahasiswa['status']); ?>"><?php echo htmlspecialchars($mahasiswa['status']); ?></span></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="?action=edit&id=<?php echo $mahasiswa['id_mahasiswa']; ?>" class="btn btn-warning">✏️ Edit</a>
                                        <a href="?action=delete&id=<?php echo $mahasiswa['id_mahasiswa']; ?>" class="btn btn-danger" onclick="return confirm('❗ Apakah Anda yakin ingin menghapus data <?php echo htmlspecialchars($mahasiswa['nama_lengkap']); ?>?\n\nTindakan ini tidak dapat dibatalkan!')">🗑️ Hapus</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="empty-state">
                                    <div class="empty-state-icon">📋</div>
                                    <p style="font-size: 1.1em; font-weight: 600;"><?php echo $search ? 'Tidak ada data yang ditemukan' : 'Belum ada data mahasiswa'; ?></p>
                                    <p style="opacity: 0.7; margin-top: 10px;">Silakan tambahkan data baru menggunakan form di atas</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Create Floating Particles
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 50;

            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 15 + 's';
                particle.style.animationDuration = (Math.random() * 10 + 10) + 's';
                particlesContainer.appendChild(particle);
            }
        }

        createParticles();

        // Auto hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-30px)';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 500);
            });
        }, 5000);
    </script>
</body>
</html>