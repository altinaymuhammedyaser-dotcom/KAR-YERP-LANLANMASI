<?php
/**
 * Akilli Kariyer - Veritabani semasi (DDL)
 * Bu dosya tools/build_database.php tarafindan database/database.sql uretilirken kullanilir.
 */

return <<<'SQL'
-- =====================================================================
-- AKILLI KARİYER REHBERİ - VERİTABANI ŞEMASI
-- Hedef: MySQL 8.0+ / MariaDB 10.4+
-- Karakter seti: utf8mb4 (Türkçe karakter desteği)
-- Motor: InnoDB
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+03:00";

CREATE DATABASE IF NOT EXISTS `akilli_kariyer`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `akilli_kariyer`;

-- ---------------------------------------------------------------------
-- 1) KULLANICI TABLOLARI
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `password_resets`;
DROP TABLE IF EXISTS `login_attempts`;
DROP TABLE IF EXISTS `user_activities`;
DROP TABLE IF EXISTS `viewed_careers`;
DROP TABLE IF EXISTS `favorite_careers`;
DROP TABLE IF EXISTS `user_roles`;
DROP TABLE IF EXISTS `user_profiles`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `first_name` VARCHAR(60) NOT NULL,
  `last_name` VARCHAR(60) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `education_level` ENUM('ortaokul','lise','universite','diger') NOT NULL DEFAULT 'universite',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `must_change_password` TINYINT(1) NOT NULL DEFAULT 0,
  `remember_token` VARCHAR(255) DEFAULT NULL,
  `email_verified_at` DATETIME DEFAULT NULL,
  `last_login_at` DATETIME DEFAULT NULL,
  `last_login_ip` VARCHAR(45) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`),
  KEY `idx_users_active` (`is_active`),
  KEY `idx_users_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_profiles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `birth_date` DATE DEFAULT NULL,
  `city` VARCHAR(80) DEFAULT NULL,
  `school_name` VARCHAR(150) DEFAULT NULL,
  `study_field` VARCHAR(150) DEFAULT NULL,
  `grade_level` VARCHAR(60) DEFAULT NULL,
  `bio` TEXT DEFAULT NULL,
  `avatar` VARCHAR(255) DEFAULT NULL,
  `preferred_work_style` ENUM('bireysel','takim','saha','dijital','karma') NOT NULL DEFAULT 'karma',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_profiles_user` (`user_id`),
  CONSTRAINT `fk_profiles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_roles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `role` ENUM('user','editor','admin') NOT NULL DEFAULT 'user',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_role` (`user_id`,`role`),
  KEY `idx_roles_role` (`role`),
  CONSTRAINT `fk_roles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 2) MESLEK TABLOLARI
-- ---------------------------------------------------------------------

CREATE TABLE `career_categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(120) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `icon` VARCHAR(60) NOT NULL DEFAULT 'briefcase',
  `color` VARCHAR(20) NOT NULL DEFAULT '#1C466D',
  `image` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cat_slug` (`slug`),
  KEY `idx_cat_active_sort` (`is_active`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `careers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(160) NOT NULL,
  `slug` VARCHAR(180) NOT NULL,
  `short_description` VARCHAR(320) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `icon` VARCHAR(60) NOT NULL DEFAULT 'briefcase',
  `education_level` ENUM('ortaokul','lise','universite','diger') NOT NULL DEFAULT 'universite',
  `education_detail` VARCHAR(255) DEFAULT NULL,
  `working_areas` VARCHAR(255) DEFAULT NULL,
  `career_path` TEXT DEFAULT NULL,
  `advantages` TEXT DEFAULT NULL,
  `salary_range` VARCHAR(120) DEFAULT NULL,
  `view_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_career_slug` (`slug`),
  KEY `idx_careers_category` (`category_id`),
  KEY `idx_careers_active` (`is_active`),
  KEY `idx_careers_featured` (`is_featured`),
  KEY `idx_careers_views` (`view_count`),
  KEY `idx_careers_name` (`name`),
  FULLTEXT KEY `ft_careers_search` (`name`,`short_description`,`description`),
  CONSTRAINT `fk_careers_category` FOREIGN KEY (`category_id`) REFERENCES `career_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ilgi alani kriterleri (mesleğin hangi ilgi alanlarını ne kadar önemsediği)
CREATE TABLE `career_interests` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `career_id` INT UNSIGNED NOT NULL,
  `interest_key` VARCHAR(80) NOT NULL,
  `weight` TINYINT UNSIGNED NOT NULL DEFAULT 3,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_career_interest` (`career_id`,`interest_key`),
  KEY `idx_ci_interest_key` (`interest_key`),
  CONSTRAINT `fk_ci_career` FOREIGN KEY (`career_id`) REFERENCES `careers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Yetenek kriterleri
CREATE TABLE `career_skills` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `career_id` INT UNSIGNED NOT NULL,
  `ability_key` VARCHAR(80) NOT NULL,
  `weight` TINYINT UNSIGNED NOT NULL DEFAULT 3,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_career_ability` (`career_id`,`ability_key`),
  KEY `idx_cs_ability_key` (`ability_key`),
  CONSTRAINT `fk_cs_career` FOREIGN KEY (`career_id`) REFERENCES `careers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Calisma tercihi kriterleri
CREATE TABLE `career_preferences` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `career_id` INT UNSIGNED NOT NULL,
  `preference_key` VARCHAR(80) NOT NULL,
  `weight` TINYINT UNSIGNED NOT NULL DEFAULT 3,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_career_preference` (`career_id`,`preference_key`),
  KEY `idx_cp_pref_key` (`preference_key`),
  CONSTRAINT `fk_cp_career` FOREIGN KEY (`career_id`) REFERENCES `careers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Calisma ortami kriterleri
CREATE TABLE `career_environments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `career_id` INT UNSIGNED NOT NULL,
  `environment_key` VARCHAR(80) NOT NULL,
  `weight` TINYINT UNSIGNED NOT NULL DEFAULT 3,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_career_environment` (`career_id`,`environment_key`),
  KEY `idx_ce_env_key` (`environment_key`),
  CONSTRAINT `fk_ce_career` FOREIGN KEY (`career_id`) REFERENCES `careers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mesleğin ihtiyac duydugu somut beceriler (beceri uyumu hesabinda kullanilir)
CREATE TABLE `career_competencies` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `career_id` INT UNSIGNED NOT NULL,
  `competency_name` VARCHAR(160) NOT NULL,
  `ability_key` VARCHAR(80) DEFAULT NULL,
  `weight` TINYINT UNSIGNED NOT NULL DEFAULT 3,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cc_career` (`career_id`),
  KEY `idx_cc_ability` (`ability_key`),
  CONSTRAINT `fk_cc_career` FOREIGN KEY (`career_id`) REFERENCES `careers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Metinsel gereksinimler (gorevler, egitim, bolumler, avantajlar...)
CREATE TABLE `career_requirements` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `career_id` INT UNSIGNED NOT NULL,
  `requirement_type` ENUM('task','skill','education','department','certificate','work_area','advantage','disadvantage','path') NOT NULL DEFAULT 'task',
  `title` VARCHAR(200) DEFAULT NULL,
  `content` TEXT DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cr_career_type` (`career_id`,`requirement_type`),
  CONSTRAINT `fk_cr_career` FOREIGN KEY (`career_id`) REFERENCES `careers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 3) KARİYER TESTİ TABLOLARI
-- ---------------------------------------------------------------------

CREATE TABLE `test_categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(120) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `icon` VARCHAR(60) NOT NULL DEFAULT 'sparkles',
  `dimension_type` ENUM('interest','ability','preference','environment') NOT NULL,
  `weight` DECIMAL(5,2) NOT NULL DEFAULT 1.00,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_testcat_slug` (`slug`),
  KEY `idx_testcat_active` (`is_active`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `test_questions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT UNSIGNED NOT NULL,
  `question_text` TEXT NOT NULL,
  `helper_text` VARCHAR(255) DEFAULT NULL,
  `source` VARCHAR(150) DEFAULT NULL,
  `question_type` ENUM('single','multiple','scale','card','scenario') NOT NULL DEFAULT 'single',
  `min_select` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `max_select` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tq_category` (`category_id`),
  KEY `idx_tq_active_sort` (`is_active`,`sort_order`),
  CONSTRAINT `fk_tq_category` FOREIGN KEY (`category_id`) REFERENCES `test_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `test_options` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `question_id` INT UNSIGNED NOT NULL,
  `option_text` VARCHAR(255) NOT NULL,
  `option_key` VARCHAR(80) DEFAULT NULL,
  `score_value` TINYINT NOT NULL DEFAULT 3,
  `mapping_type` ENUM('interest','ability','preference','environment','none') NOT NULL DEFAULT 'none',
  `mapping_key` VARCHAR(80) DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_to_question` (`question_id`),
  KEY `idx_to_mapping` (`mapping_type`,`mapping_key`),
  CONSTRAINT `fk_to_question` FOREIGN KEY (`question_id`) REFERENCES `test_questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `test_sessions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `session_token` VARCHAR(64) NOT NULL,
  `status` ENUM('in_progress','completed','abandoned') NOT NULL DEFAULT 'in_progress',
  `current_index` INT NOT NULL DEFAULT 0,
  `total_questions` INT NOT NULL DEFAULT 0,
  `answered_count` INT NOT NULL DEFAULT 0,
  `started_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `completed_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ts_token` (`session_token`),
  KEY `idx_ts_user` (`user_id`),
  KEY `idx_ts_status` (`status`),
  CONSTRAINT `fk_ts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bir oturumda bir soruya sadece bir cevap kaydedilebilir
CREATE TABLE `test_answers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `session_id` INT UNSIGNED NOT NULL,
  `question_id` INT UNSIGNED NOT NULL,
  `option_ids` VARCHAR(255) DEFAULT NULL,
  `option_keys` VARCHAR(255) DEFAULT NULL,
  `score_value` TINYINT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_answer_session_question` (`session_id`,`question_id`),
  KEY `idx_ta_question` (`question_id`),
  CONSTRAINT `fk_ta_session` FOREIGN KEY (`session_id`) REFERENCES `test_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ta_question` FOREIGN KEY (`question_id`) REFERENCES `test_questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `test_results` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `session_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `interest_scores` LONGTEXT DEFAULT NULL,
  `ability_scores` LONGTEXT DEFAULT NULL,
  `preference_scores` LONGTEXT DEFAULT NULL,
  `environment_scores` LONGTEXT DEFAULT NULL,
  `top_interests` VARCHAR(255) DEFAULT NULL,
  `top_abilities` VARCHAR(255) DEFAULT NULL,
  `top_preferences` VARCHAR(255) DEFAULT NULL,
  `dominant_type` VARCHAR(80) DEFAULT NULL,
  `overall_score` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `summary` TEXT DEFAULT NULL,
  `completed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tr_session` (`session_id`),
  KEY `idx_tr_user` (`user_id`),
  KEY `idx_tr_completed` (`completed_at`),
  CONSTRAINT `fk_tr_session` FOREIGN KEY (`session_id`) REFERENCES `test_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tr_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `career_recommendations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `result_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `career_id` INT UNSIGNED NOT NULL,
  `match_score` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `rank` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `reason` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_rec_result_career` (`result_id`,`career_id`),
  KEY `idx_rec_user` (`user_id`),
  KEY `idx_rec_score` (`match_score`),
  KEY `idx_rec_career` (`career_id`),
  CONSTRAINT `fk_rec_result` FOREIGN KEY (`result_id`) REFERENCES `test_results` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rec_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rec_career` FOREIGN KEY (`career_id`) REFERENCES `careers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Puanlama kirilimlari (hangi bilesen ne kadar katkida bulundu)
CREATE TABLE `career_match_scores` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `recommendation_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `career_id` INT UNSIGNED NOT NULL,
  `interest_score` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `ability_score` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `preference_score` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `environment_score` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `skill_score` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cms_rec` (`recommendation_id`),
  KEY `idx_cms_user` (`user_id`),
  CONSTRAINT `fk_cms_rec` FOREIGN KEY (`recommendation_id`) REFERENCES `career_recommendations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cms_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cms_career` FOREIGN KEY (`career_id`) REFERENCES `careers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 4) KULLANICI ETKİLEŞİMLERİ
-- ---------------------------------------------------------------------

CREATE TABLE `favorite_careers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `career_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_fav_user_career` (`user_id`,`career_id`),
  KEY `idx_fav_career` (`career_id`),
  CONSTRAINT `fk_fav_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_fav_career` FOREIGN KEY (`career_id`) REFERENCES `careers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `viewed_careers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `career_id` INT UNSIGNED NOT NULL,
  `viewed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_vc_user_career` (`user_id`,`career_id`),
  KEY `idx_vc_viewed` (`viewed_at`),
  CONSTRAINT `fk_vc_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_vc_career` FOREIGN KEY (`career_id`) REFERENCES `careers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_activities` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `activity_type` VARCHAR(60) NOT NULL,
  `description` VARCHAR(255) NOT NULL,
  `reference_type` VARCHAR(50) DEFAULT NULL,
  `reference_id` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ua_user` (`user_id`),
  KEY `idx_ua_type` (`activity_type`),
  KEY `idx_ua_created` (`created_at`),
  CONSTRAINT `fk_ua_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 5) İÇERİK TABLOLARI
-- ---------------------------------------------------------------------

CREATE TABLE `blog_categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(120) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `icon` VARCHAR(60) NOT NULL DEFAULT 'newspaper',
  `color` VARCHAR(20) NOT NULL DEFAULT '#1C466D',
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_blogcat_slug` (`slug`),
  KEY `idx_blogcat_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `blog_posts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT UNSIGNED NOT NULL,
  `author_id` INT UNSIGNED DEFAULT NULL,
  `title` VARCHAR(220) NOT NULL,
  `slug` VARCHAR(240) NOT NULL,
  `excerpt` VARCHAR(400) DEFAULT NULL,
  `content` LONGTEXT DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('draft','published') NOT NULL DEFAULT 'draft',
  `reading_time` TINYINT UNSIGNED NOT NULL DEFAULT 4,
  `view_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `published_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_post_slug` (`slug`),
  KEY `idx_post_status_pub` (`status`,`published_at`),
  KEY `idx_post_category` (`category_id`),
  CONSTRAINT `fk_post_category` FOREIGN KEY (`category_id`) REFERENCES `blog_categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_post_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 6) SİSTEM TABLOLARI
-- ---------------------------------------------------------------------

CREATE TABLE `site_settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(80) NOT NULL,
  `setting_value` TEXT DEFAULT NULL,
  `setting_group` VARCHAR(50) NOT NULL DEFAULT 'genel',
  `setting_type` ENUM('text','number','textarea','color','boolean','select') NOT NULL DEFAULT 'text',
  `label` VARCHAR(150) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_setting_key` (`setting_key`),
  KEY `idx_setting_group` (`setting_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `contact_messages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `subject` VARCHAR(200) DEFAULT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('new','read','answered') NOT NULL DEFAULT 'new',
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cm_status` (`status`),
  KEY `idx_cm_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_resets` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `used_at` DATETIME DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pr_token` (`token`),
  KEY `idx_pr_email` (`email`),
  CONSTRAINT `fk_pr_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `login_attempts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(190) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `was_successful` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_la_email_ip` (`email`,`ip_address`),
  KEY `idx_la_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `audit_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `action` VARCHAR(80) NOT NULL,
  `entity_type` VARCHAR(60) DEFAULT NULL,
  `entity_id` INT UNSIGNED DEFAULT NULL,
  `details` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_al_user` (`user_id`),
  KEY `idx_al_action` (`action`),
  KEY `idx_al_created` (`created_at`),
  CONSTRAINT `fk_al_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
SQL;
