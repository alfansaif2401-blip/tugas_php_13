-- Database: db_sistamik

CREATE DATABASE IF NOT EXISTS db_sistamik;
USE db_sistamik;

-- Tabel Jurusan
CREATE TABLE IF NOT EXISTS jurusan (
    id_jurusan INT AUTO_INCREMENT PRIMARY KEY,
    nama_jurusan VARCHAR(100) NOT NULL,
    kode_jurusan VARCHAR(10) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Mahasiswa
CREATE TABLE IF NOT EXISTS mahasiswa (
    id_mahasiswa INT AUTO_INCREMENT PRIMARY KEY,
    nim VARCHAR(20) NOT NULL UNIQUE,
    nama_lengkap VARCHAR(100) NOT NULL,
    jenis_kelamin ENUM('Laki-laki', 'Perempuan') NOT NULL,
    tempat_lahir VARCHAR(50),
    tanggal_lahir DATE,
    alamat TEXT,
    no_telepon VARCHAR(20),
    email VARCHAR(100),
    id_jurusan INT,
    angkatan YEAR NOT NULL,
    status ENUM('Aktif', 'Cuti', 'Lulus', 'DO') DEFAULT 'Aktif',
    tanggal_masuk DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_jurusan) REFERENCES jurusan(id_jurusan) ON DELETE SET NULL
);

-- Insert sample jurusan
INSERT INTO jurusan (nama_jurusan, kode_jurusan) VALUES
('Pengembangan Perangkat Lunak dan Gim', 'PPL'),
('Digital Marketing', 'DM'),
('Teknik Komputer dan Jaringan', 'TKJ'),
('Multimedia', 'MM')
ON DUPLICATE KEY UPDATE nama_jurusan = nama_jurusan;

-- Insert sample mahasiswa
INSERT INTO mahasiswa (nim, nama_lengkap, jenis_kelamin, tempat_lahir, tanggal_lahir, alamat, no_telepon, email, id_jurusan, angkatan, status, tanggal_masuk) VALUES
('2301001', 'Ahmad Rahman', 'Laki-laki', 'Jombang', '2005-01-15', 'Jl. Sudirman No. 123, Jombang', '081234567890', 'ahmad@student.petik.ac.id', 1, 2023, 'Aktif', '2023-08-01'),
('2301002', 'Siti Nurhaliza', 'Perempuan', 'Kediri', '2004-03-20', 'Jl. Diponegoro No. 45, Kediri', '081345678901', 'siti@student.petik.ac.id', 2, 2023, 'Aktif', '2023-08-01'),
('2201003', 'Budi Santoso', 'Laki-laki', 'Mojokerto', '2003-07-10', 'Jl. Gajah Mada No. 67, Mojokerto', '081456789012', 'budi@student.petik.ac.id', 1, 2022, 'Aktif', '2022-08-01'),
('2201004', 'Maya Sari', 'Perempuan', 'Surabaya', '2003-11-25', 'Jl. Tunjungan No. 89, Surabaya', '081567890123', 'maya@student.petik.ac.id', 3, 2022, 'Cuti', '2022-08-01'),
('2401005', 'Rizki Pratama', 'Laki-laki', 'Malang', '2006-05-30', 'Jl. Basuki Rahmat No. 12, Malang', '081678901234', 'rizki@student.petik.ac.id', 4, 2024, 'Aktif', '2024-08-01'),
('2301006', 'Dewi Lestari', 'Perempuan', 'Blitar', '2004-09-14', 'Jl. Merdeka No. 34, Blitar', '081789012345', 'dewi@student.petik.ac.id', 2, 2023, 'Aktif', '2023-08-01'),
('2201007', 'Eko Wijaya', 'Laki-laki', 'Tulungagung', '2003-12-08', 'Jl. Kartini No. 56, Tulungagung', '081890123456', 'eko@student.petik.ac.id', 1, 2022, 'Lulus', '2022-08-01'),
('2401008', 'Nina Amelia', 'Perempuan', 'Trenggalek', '2006-02-18', 'Jl. Sudirman No. 78, Trenggalek', '081901234567', 'nina@student.petik.ac.id', 3, 2024, 'Aktif', '2024-08-01')
ON DUPLICATE KEY UPDATE nim = nim;
