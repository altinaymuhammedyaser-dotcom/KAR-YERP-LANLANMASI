-- ============================================================================
-- EK ÖZELLİKLER: SINAV, SERTİFİKA, BİLDİRİM, BECERİ TAKİP
-- Mevcut database.sql'in sonuna eklenmesi için.
-- ============================================================================

-- --------------------------------------------------------
-- 27. SINAVLAR
-- --------------------------------------------------------
DROP TABLE IF EXISTS `exams`;
CREATE TABLE `exams` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(160) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `category` VARCHAR(100) DEFAULT NULL,
  `time_limit_minutes` INT UNSIGNED NOT NULL DEFAULT 30,
  `passing_score` INT UNSIGNED NOT NULL DEFAULT 70,
  `certificate` TINYINT(1) NOT NULL DEFAULT 1,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_exams_active` (`is_active`),
  KEY `idx_exams_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 28. SINAV SORULARI
-- --------------------------------------------------------
DROP TABLE IF EXISTS `exam_questions`;
CREATE TABLE `exam_questions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `exam_id` INT UNSIGNED NOT NULL,
  `question` TEXT NOT NULL,
  `type` ENUM('single','multiple','true_false') NOT NULL DEFAULT 'single',
  `options` JSON DEFAULT NULL,
  `correct_answer` JSON NOT NULL,
  `points` INT UNSIGNED NOT NULL DEFAULT 1,
  `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_eq_exam` (`exam_id`),
  CONSTRAINT `fk_eq_exam` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 29. SINAV SONUÇLARI
-- --------------------------------------------------------
DROP TABLE IF EXISTS `exam_results`;
CREATE TABLE `exam_results` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `exam_id` INT UNSIGNED NOT NULL,
  `score` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `passed` TINYINT(1) NOT NULL DEFAULT 0,
  `answers` JSON DEFAULT NULL,
  `started_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `finished_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_er_user` (`user_id`),
  KEY `idx_er_exam` (`exam_id`),
  CONSTRAINT `fk_er_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_er_exam` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 30. SERTİFİKALAR
-- --------------------------------------------------------
DROP TABLE IF EXISTS `certificates`;
CREATE TABLE `certificates` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `exam_id` INT UNSIGNED NOT NULL,
  `certificate_code` VARCHAR(32) NOT NULL,
  `issued_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `file_path` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cert_code` (`certificate_code`),
  KEY `idx_cert_user` (`user_id`),
  KEY `idx_cert_exam` (`exam_id`),
  CONSTRAINT `fk_cert_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cert_exam` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 31. BİLDİRİMLER
-- --------------------------------------------------------
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(160) NOT NULL,
  `message` TEXT DEFAULT NULL,
  `type` ENUM('info','success','warning','danger') NOT NULL DEFAULT 'info',
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `url` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notif_user_read` (`user_id`,`is_read`),
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 32. KULLANICI BECERİ TAKİBİ
-- --------------------------------------------------------
DROP TABLE IF EXISTS `user_skills`;
CREATE TABLE `user_skills` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `skill_name` VARCHAR(120) NOT NULL,
  `level` ENUM('bașlangıç','orta','ileri','uzman') NOT NULL DEFAULT 'bașlangıç',
  `score` INT UNSIGNED NOT NULL DEFAULT 0,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_skill` (`user_id`,`skill_name`),
  CONSTRAINT `fk_us_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- ÖRNEK SINAV VERİLERİ (isteğe bağlı)
-- --------------------------------------------------------
INSERT INTO `exams` (`title`, `description`, `category`, `time_limit_minutes`, `passing_score`, `certificate`, `is_active`) VALUES
('Temel Beceriler Sınavı', 'Yazılım ve analiz becerilerinizi ölçen başlangıç seviye sınav.', 'Teknoloji', 20, 70, 1, 1),
('İşletme ve Yönetim Sınavı', 'İşletme, pazarlama ve proje yönetimi bilgilerinizi test edin.', 'İşletme', 20, 70, 1, 1),
('Sağlık Meslekleri Sınavı', 'Sağlık sektöründe temel kavramları ölçen sınav.', 'Sağlık', 25, 70, 1, 1);

INSERT INTO `exam_questions` (`exam_id`, `question`, `type`, `options`, `correct_answer`, `points`, `sort_order`) VALUES
(1, 'Hangisi veritabanı yönetim sistemi örneğidir?', 'single', '["MySQL","Photoshop","Excel","Word"]', '["MySQL"]', 10, 1),
(1, 'Aşağıdakilerden hangisi programlama dili değildir?', 'single', '["Python","Java","HTML","C++"]', '["HTML"]', 10, 2),
(1, 'CSS kısaltması ne anlama gelir?', 'single', '["Cascading Style Sheets","Computer Style Sheets","Creative Style Sheets","Colorful Style Sheets"]', '["Cascading Style Sheets"]', 10, 3),
(2, 'SWOT analizinde "S" hangi kelimeyi ifade eder?', 'single', '["Strengths","Sales","Strategy","Support"]', '["Strengths"]', 10, 1),
(2, 'Pazar payı artırmak hangi strateji kapsamında ele alınır?', 'single', '["Pazarlama Stratejisi","Üretim Stratejisi","İnsan Kaynakları Stratejisi","Finans Stratejisi"]', '["Pazarlama Stratejisi"]', 10, 2);

-- Örnek kullanıcı için (demo kullanıcı ID=1 varsayılır) bir sınav sonucu ve sertifika
-- NOT: Bu satırları sadece demo kullanıcı varsa çalıştırın.
-- INSERT INTO `exam_results` (`user_id`, `exam_id`, `score`, `passed`, `answers`, `finished_at`) VALUES (1, 1, 100.00, 1, '["MySQL","HTML","Cascading Style Sheets"]', NOW());
-- INSERT INTO `certificates` (`user_id`, `exam_id`, `certificate_code`) VALUES (1, 1, MD5(CONCAT('cert',1,NOW())));
