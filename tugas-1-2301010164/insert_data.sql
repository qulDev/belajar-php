-- ================================================================================
-- INSERT DATA SAMPLE - KOTA MATARAM DAN BIMA
-- Database: tugas_1_pws
-- ================================================================================

USE tugas_1_pws;

-- ================================================================================
-- INSERT DATA CITY (KOTA)
-- ================================================================================

-- Kota Mataram (Ibukota Provinsi Nusa Tenggara Barat)
INSERT INTO city (name, province, population, area) VALUES
('Mataram', 'Nusa Tenggara Barat', 441000, 61.30);

-- Kota Bima (Kota di Pulau Sumbawa)
INSERT INTO city (name, province, population, area) VALUES
('Bima', 'Nusa Tenggara Barat', 155140, 222.25);

-- Kota tambahan (opsional)
INSERT INTO city (name, province, population, area) VALUES
('Denpasar', 'Bali', 897300, 127.78);

-- ================================================================================
-- INSERT DATA DISTRICT (KECAMATAN) - KOTA MATARAM
-- ================================================================================

-- Mataram memiliki 6 kecamatan
INSERT INTO district (name, city_id, postal_code, population) VALUES
('Ampenan', 1, '83511', 75000),
('Mataram', 1, '83121', 85000),
('Cakranegara', 1, '83231', 95000),
('Sekarbela', 1, '83114', 70000),
('Selaparang', 1, '83125', 80000),
('Sandubaya', 1, '83238', 65000);

-- ================================================================================
-- INSERT DATA DISTRICT (KECAMATAN) - KOTA BIMA
-- ================================================================================

-- Bima memiliki 5 kecamatan
INSERT INTO district (name, city_id, postal_code, population) VALUES
('Raba', 2, '84117', 35000),
('Asakota', 2, '84133', 32000),
('Rasanae Barat', 2, '84115', 28000),
('Rasanae Timur', 2, '84116', 30000),
('Mpunda', 2, '84134', 30000);

-- ================================================================================
-- INSERT DATA DISTRICT (KECAMATAN) - KOTA DENPASAR (OPSIONAL)
-- ================================================================================

-- Denpasar memiliki 4 kecamatan
INSERT INTO district (name, city_id, postal_code, population) VALUES
('Denpasar Selatan', 3, '80221', 250000),
('Denpasar Timur', 3, '80234', 270000),
('Denpasar Barat', 3, '80119', 210000),
('Denpasar Utara', 3, '80116', 167000);

-- ================================================================================
-- VERIFIKASI DATA
-- ================================================================================

-- Tampilkan semua city
SELECT * FROM city;

-- Tampilkan semua district dengan nama city
SELECT d.*, c.name as city_name, c.province 
FROM district d 
LEFT JOIN city c ON d.city_id = c.id
ORDER BY c.id, d.id;

-- Hitung jumlah district per city
SELECT c.name as city_name, COUNT(d.id) as total_districts
FROM city c
LEFT JOIN district d ON c.id = d.city_id
GROUP BY c.id, c.name;
