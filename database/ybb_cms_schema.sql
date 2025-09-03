-- YBB Multi-Tenant CMS Database Schema
-- Generated on 2025-09-03
-- This file contains the complete database schema for easy import

SET FOREIGN_KEY_CHECKS = 0;

-- ==========================================
-- CENTRAL DATABASE TABLES (Landlord)
-- ==========================================

-- Tenants table (Central database)
CREATE TABLE `tenants` (
  `id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `domain` varchar(255) NOT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `primary_color` varchar(7) DEFAULT '#3b82f6',
  `secondary_color` varchar(7) DEFAULT '#64748b',
  `accent_color` varchar(7) DEFAULT '#10b981',
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `og_image_url` varchar(255) DEFAULT NULL,
  `favicon_url` varchar(255) DEFAULT NULL,
  `google_analytics_id` varchar(255) DEFAULT NULL,
  `google_adsense_id` varchar(255) DEFAULT NULL,
  `google_tag_manager_id` varchar(255) DEFAULT NULL,
  `email_from_name` varchar(255) DEFAULT NULL,
  `email_from_address` varchar(255) DEFAULT NULL,
  `gdpr_enabled` tinyint(1) DEFAULT 0,
  `ccpa_enabled` tinyint(1) DEFAULT 0,
  `privacy_policy_url` text DEFAULT NULL,
  `terms_of_service_url` text DEFAULT NULL,
  `enabled_features` json DEFAULT NULL,
  `settings` json DEFAULT NULL,
  `status` enum('active','suspended','pending') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tenants_domain_unique` (`domain`),
  KEY `tenants_domain_index` (`domain`),
  KEY `tenants_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Network Admins table (Central database)
CREATE TABLE `admins` (
  `id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('superadmin','admin','support') DEFAULT 'admin',
  `is_active` tinyint(1) DEFAULT 1,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `settings` json DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admins_email_unique` (`email`),
  KEY `admins_role_index` (`role`),
  KEY `admins_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Domains table (for tenant resolution)
CREATE TABLE `domains` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `domain` varchar(255) NOT NULL,
  `tenant_id` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `domains_domain_unique` (`domain`),
  KEY `domains_tenant_id_index` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- TENANT DATABASE TABLES
-- ==========================================

-- Users table (Tenant database)
CREATE TABLE `users` (
  `id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','editor','author','seo','moderator','analyst','user') DEFAULT 'user',
  `avatar_url` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `settings` json DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_index` (`role`),
  KEY `users_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Posts table (Universal content)
CREATE TABLE `posts` (
  `id` char(36) NOT NULL,
  `tenant_id` char(36) NOT NULL,
  `kind` enum('page','news','guide','program','job') NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `status` enum('draft','review','scheduled','published','archived') DEFAULT 'draft',
  `cover_image_url` varchar(255) DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `og_image_url` varchar(255) DEFAULT NULL,
  `canonical_url` varchar(255) DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `updated_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `posts_tenant_id_slug_unique` (`tenant_id`,`slug`),
  KEY `posts_kind_index` (`kind`),
  KEY `posts_tenant_id_kind_status_published_at_index` (`tenant_id`,`kind`,`status`,`published_at`),
  KEY `posts_tenant_id_slug_index` (`tenant_id`,`slug`),
  KEY `posts_tenant_id_status_index` (`tenant_id`,`status`),
  KEY `posts_tenant_id_created_by_index` (`tenant_id`,`created_by`),
  FULLTEXT KEY `posts_title_excerpt_content_fulltext` (`title`,`excerpt`,`content`),
  CONSTRAINT `posts_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `posts_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `posts_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Programs table (Scholarships, Opportunities, Internships)
CREATE TABLE `pt_program` (
  `id` char(36) NOT NULL,
  `tenant_id` char(36) NOT NULL,
  `post_id` char(36) NOT NULL,
  `program_type` enum('scholarship','opportunity','internship') NOT NULL,
  `organizer_name` varchar(255) NOT NULL,
  `location_text` varchar(255) DEFAULT NULL,
  `country_code` varchar(2) DEFAULT NULL,
  `deadline_at` datetime DEFAULT NULL,
  `is_rolling` tinyint(1) DEFAULT 0,
  `funding_type` enum('fully_funded','partially_funded','unfunded') DEFAULT NULL,
  `stipend_amount` decimal(18,2) DEFAULT NULL,
  `fee_amount` decimal(18,2) DEFAULT NULL,
  `program_length_text` varchar(255) DEFAULT NULL,
  `eligibility_text` text DEFAULT NULL,
  `apply_url` varchar(255) NOT NULL,
  `extra` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pt_program_tenant_id_post_id_unique` (`tenant_id`,`post_id`),
  KEY `pt_program_program_type_index` (`program_type`),
  KEY `pt_program_tenant_id_program_type_index` (`tenant_id`,`program_type`),
  KEY `pt_program_tenant_id_country_code_index` (`tenant_id`,`country_code`),
  KEY `pt_program_tenant_id_deadline_at_index` (`tenant_id`,`deadline_at`),
  KEY `pt_program_tenant_id_funding_type_index` (`tenant_id`,`funding_type`),
  KEY `pt_program_tenant_id_is_rolling_index` (`tenant_id`,`is_rolling`),
  KEY `pt_program_tenant_id_program_type_country_code_deadline_at_index` (`tenant_id`,`program_type`,`country_code`,`deadline_at`),
  CONSTRAINT `pt_program_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pt_program_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Jobs table (Job listings)
CREATE TABLE `pt_job` (
  `id` char(36) NOT NULL,
  `tenant_id` char(36) NOT NULL,
  `post_id` char(36) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `employment_type` enum('full_time','part_time','contract','internship') NOT NULL,
  `workplace_type` enum('onsite','hybrid','remote') NOT NULL,
  `title_override` varchar(255) DEFAULT NULL,
  `location_city` varchar(255) DEFAULT NULL,
  `country_code` varchar(2) DEFAULT NULL,
  `min_salary` decimal(18,2) DEFAULT NULL,
  `max_salary` decimal(18,2) DEFAULT NULL,
  `salary_currency` varchar(3) DEFAULT NULL,
  `salary_period` enum('year','month','day','hour') DEFAULT NULL,
  `experience_level` enum('junior','mid','senior','lead') DEFAULT NULL,
  `responsibilities` longtext DEFAULT NULL,
  `requirements` longtext DEFAULT NULL,
  `benefits` json DEFAULT NULL,
  `deadline_at` datetime DEFAULT NULL,
  `apply_url` varchar(255) NOT NULL,
  `extra` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pt_job_tenant_id_post_id_unique` (`tenant_id`,`post_id`),
  KEY `pt_job_employment_type_index` (`employment_type`),
  KEY `pt_job_workplace_type_index` (`workplace_type`),
  KEY `pt_job_experience_level_index` (`experience_level`),
  KEY `pt_job_tenant_id_workplace_type_index` (`tenant_id`,`workplace_type`),
  KEY `pt_job_tenant_id_employment_type_index` (`tenant_id`,`employment_type`),
  KEY `pt_job_tenant_id_country_code_index` (`tenant_id`,`country_code`),
  KEY `pt_job_tenant_id_experience_level_index` (`tenant_id`,`experience_level`),
  KEY `pt_job_tenant_id_deadline_at_index` (`tenant_id`,`deadline_at`),
  KEY `pt_job_tenant_id_company_name_index` (`tenant_id`,`company_name`),
  KEY `pt_job_tenant_id_workplace_type_employment_type_index` (`tenant_id`,`workplace_type`,`employment_type`),
  KEY `pt_job_tenant_id_min_salary_max_salary_index` (`tenant_id`,`min_salary`,`max_salary`),
  CONSTRAINT `pt_job_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pt_job_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Terms table (Categories, Tags, Locations, Skills, Industries)
CREATE TABLE `terms` (
  `id` char(36) NOT NULL,
  `tenant_id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('category','tag','location','skill','industry') NOT NULL,
  `parent_id` char(36) DEFAULT NULL,
  `color` varchar(7) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `meta` json DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `post_count` int(10) unsigned DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `terms_tenant_id_slug_type_unique` (`tenant_id`,`slug`,`type`),
  KEY `terms_type_index` (`type`),
  KEY `terms_tenant_id_type_index` (`tenant_id`,`type`),
  KEY `terms_tenant_id_slug_index` (`tenant_id`,`slug`),
  KEY `terms_tenant_id_parent_id_index` (`tenant_id`,`parent_id`),
  KEY `terms_tenant_id_is_featured_index` (`tenant_id`,`is_featured`),
  CONSTRAINT `terms_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `terms_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `terms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Term-Post relationship table
CREATE TABLE `term_post` (
  `id` char(36) NOT NULL,
  `tenant_id` char(36) NOT NULL,
  `term_id` char(36) NOT NULL,
  `post_id` char(36) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `term_post_tenant_id_term_id_post_id_unique` (`tenant_id`,`term_id`,`post_id`),
  KEY `term_post_tenant_id_term_id_index` (`tenant_id`,`term_id`),
  KEY `term_post_tenant_id_post_id_index` (`tenant_id`,`post_id`),
  CONSTRAINT `term_post_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `term_post_term_id_foreign` FOREIGN KEY (`term_id`) REFERENCES `terms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `term_post_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Media table (File uploads)
CREATE TABLE `media` (
  `id` char(36) NOT NULL,
  `tenant_id` char(36) NOT NULL,
  `post_id` char(36) DEFAULT NULL,
  `uploaded_by` char(36) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `mime_type` varchar(255) NOT NULL,
  `disk` varchar(255) DEFAULT 'public',
  `conversions_disk` varchar(255) DEFAULT NULL,
  `size` bigint(20) unsigned NOT NULL,
  `manipulations` json DEFAULT NULL,
  `custom_properties` json DEFAULT NULL,
  `generated_conversions` json DEFAULT NULL,
  `responsive_images` json DEFAULT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `caption` text DEFAULT NULL,
  `collection_name` varchar(255) DEFAULT NULL,
  `order_column` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `media_tenant_id_post_id_index` (`tenant_id`,`post_id`),
  KEY `media_tenant_id_collection_name_index` (`tenant_id`,`collection_name`),
  KEY `media_tenant_id_mime_type_index` (`tenant_id`,`mime_type`),
  KEY `media_tenant_id_uploaded_by_index` (`tenant_id`,`uploaded_by`),
  CONSTRAINT `media_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `media_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `media_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ads table (Advertisement management)
CREATE TABLE `ads` (
  `id` char(36) NOT NULL,
  `tenant_id` char(36) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` varchar(255) DEFAULT 'banner',
  `placement` varchar(255) NOT NULL,
  `content` json NOT NULL,
  `targeting` json DEFAULT NULL,
  `settings` json DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `priority` int(11) DEFAULT 0,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `max_impressions` int(11) DEFAULT NULL,
  `max_clicks` int(11) DEFAULT NULL,
  `current_impressions` int(11) DEFAULT 0,
  `current_clicks` int(11) DEFAULT 0,
  `click_rate` decimal(5,2) DEFAULT 0.00,
  `status` varchar(255) DEFAULT 'active',
  `created_by` char(36) NOT NULL,
  `updated_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ads_tenant_id_is_active_placement_index` (`tenant_id`,`is_active`,`placement`),
  KEY `ads_tenant_id_status_priority_index` (`tenant_id`,`status`,`priority`),
  KEY `ads_start_date_end_date_index` (`start_date`,`end_date`),
  CONSTRAINT `ads_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ads_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ads_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ad Impressions table (Analytics)
CREATE TABLE `ad_impressions` (
  `id` char(36) NOT NULL,
  `tenant_id` char(36) NOT NULL,
  `ad_id` char(36) NOT NULL,
  `user_id` char(36) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `page_url` varchar(500) DEFAULT NULL,
  `referrer` varchar(500) DEFAULT NULL,
  `device_type` varchar(50) DEFAULT NULL,
  `browser` varchar(100) DEFAULT NULL,
  `os` varchar(100) DEFAULT NULL,
  `country` varchar(2) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ad_impressions_tenant_id_ad_id_index` (`tenant_id`,`ad_id`),
  KEY `ad_impressions_tenant_id_created_at_index` (`tenant_id`,`created_at`),
  KEY `ad_impressions_user_id_index` (`user_id`),
  KEY `ad_impressions_session_id_index` (`session_id`),
  CONSTRAINT `ad_impressions_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ad_impressions_ad_id_foreign` FOREIGN KEY (`ad_id`) REFERENCES `ads` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ad_impressions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ad Clicks table (Analytics)
CREATE TABLE `ad_clicks` (
  `id` char(36) NOT NULL,
  `tenant_id` char(36) NOT NULL,
  `ad_id` char(36) NOT NULL,
  `impression_id` char(36) DEFAULT NULL,
  `user_id` char(36) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `click_url` varchar(500) DEFAULT NULL,
  `page_url` varchar(500) DEFAULT NULL,
  `referrer` varchar(500) DEFAULT NULL,
  `device_type` varchar(50) DEFAULT NULL,
  `browser` varchar(100) DEFAULT NULL,
  `os` varchar(100) DEFAULT NULL,
  `country` varchar(2) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ad_clicks_tenant_id_ad_id_index` (`tenant_id`,`ad_id`),
  KEY `ad_clicks_tenant_id_created_at_index` (`tenant_id`,`created_at`),
  KEY `ad_clicks_impression_id_index` (`impression_id`),
  KEY `ad_clicks_user_id_index` (`user_id`),
  KEY `ad_clicks_session_id_index` (`session_id`),
  CONSTRAINT `ad_clicks_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ad_clicks_ad_id_foreign` FOREIGN KEY (`ad_id`) REFERENCES `ads` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ad_clicks_impression_id_foreign` FOREIGN KEY (`impression_id`) REFERENCES `ad_impressions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ad_clicks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEO Landing Pages table
CREATE TABLE `seo_landings` (
  `id` char(36) NOT NULL,
  `tenant_id` char(36) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `canonical_url` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `schema_markup` json DEFAULT NULL,
  `target_keyword` varchar(255) DEFAULT NULL,
  `target_filters` json DEFAULT NULL,
  `content_type` enum('programs','jobs','mixed') DEFAULT 'mixed',
  `items_per_page` int(11) DEFAULT 20,
  `views` int(10) unsigned DEFAULT 0,
  `conversion_rate` decimal(5,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `index_status` enum('index','noindex') DEFAULT 'index',
  `follow_status` enum('follow','nofollow') DEFAULT 'follow',
  `created_by` char(36) NOT NULL,
  `updated_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `seo_landings_tenant_id_slug_unique` (`tenant_id`,`slug`),
  KEY `seo_landings_tenant_id_slug_index` (`tenant_id`,`slug`),
  KEY `seo_landings_tenant_id_is_active_index` (`tenant_id`,`is_active`),
  KEY `seo_landings_tenant_id_content_type_index` (`tenant_id`,`content_type`),
  CONSTRAINT `seo_landings_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `seo_landings_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `seo_landings_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Redirects table (URL management)
CREATE TABLE `redirects` (
  `id` char(36) NOT NULL,
  `tenant_id` char(36) NOT NULL,
  `from_url` varchar(255) NOT NULL,
  `to_url` varchar(255) NOT NULL,
  `status_code` enum('301','302','307','308') DEFAULT '301',
  `description` text DEFAULT NULL,
  `hits` int(10) unsigned DEFAULT 0,
  `last_used_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_automatic` tinyint(1) DEFAULT 0,
  `created_reason` varchar(255) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `redirects_tenant_id_from_url_unique` (`tenant_id`,`from_url`),
  KEY `redirects_tenant_id_from_url_index` (`tenant_id`,`from_url`),
  KEY `redirects_tenant_id_is_active_index` (`tenant_id`,`is_active`),
  KEY `redirects_tenant_id_status_code_index` (`tenant_id`,`status_code`),
  CONSTRAINT `redirects_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `redirects_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Newsletter Subscriptions table
CREATE TABLE `newsletter_subscriptions` (
  `id` char(36) NOT NULL,
  `tenant_id` char(36) NOT NULL,
  `email` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `preferences` json DEFAULT NULL,
  `status` enum('active','unsubscribed','bounced','pending') DEFAULT 'pending',
  `frequency` varchar(255) DEFAULT 'weekly',
  `verification_token` varchar(255) DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `unsubscribe_token` varchar(255) NOT NULL,
  `tags` json DEFAULT NULL,
  `source` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `last_sent_at` datetime DEFAULT NULL,
  `emails_sent` int(10) unsigned DEFAULT 0,
  `emails_opened` int(10) unsigned DEFAULT 0,
  `links_clicked` int(10) unsigned DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `newsletter_subscriptions_tenant_id_email_unique` (`tenant_id`,`email`),
  KEY `newsletter_subscriptions_tenant_id_email_index` (`tenant_id`,`email`),
  KEY `newsletter_subscriptions_tenant_id_status_index` (`tenant_id`,`status`),
  KEY `newsletter_subscriptions_tenant_id_frequency_index` (`tenant_id`,`frequency`),
  KEY `newsletter_subscriptions_verification_token_index` (`verification_token`),
  KEY `newsletter_subscriptions_unsubscribe_token_index` (`unsubscribe_token`),
  CONSTRAINT `newsletter_subscriptions_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Email Campaigns table
CREATE TABLE `email_campaigns` (
  `id` char(36) NOT NULL,
  `tenant_id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `preview_text` text DEFAULT NULL,
  `content` longtext NOT NULL,
  `type` enum('newsletter','digest','announcement','promotional') DEFAULT 'newsletter',
  `status` enum('draft','scheduled','sending','sent','paused','cancelled') DEFAULT 'draft',
  `recipient_criteria` json DEFAULT NULL,
  `estimated_recipients` int(10) unsigned DEFAULT 0,
  `actual_recipients` int(10) unsigned DEFAULT 0,
  `scheduled_at` datetime DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `emails_sent` int(10) unsigned DEFAULT 0,
  `emails_delivered` int(10) unsigned DEFAULT 0,
  `emails_opened` int(10) unsigned DEFAULT 0,
  `emails_clicked` int(10) unsigned DEFAULT 0,
  `emails_bounced` int(10) unsigned DEFAULT 0,
  `emails_unsubscribed` int(10) unsigned DEFAULT 0,
  `open_rate` decimal(5,2) DEFAULT 0.00,
  `click_rate` decimal(5,2) DEFAULT 0.00,
  `bounce_rate` decimal(5,2) DEFAULT 0.00,
  `template` varchar(255) DEFAULT NULL,
  `settings` json DEFAULT NULL,
  `created_by` char(36) NOT NULL,
  `updated_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `email_campaigns_tenant_id_status_index` (`tenant_id`,`status`),
  KEY `email_campaigns_tenant_id_type_index` (`tenant_id`,`type`),
  KEY `email_campaigns_tenant_id_scheduled_at_index` (`tenant_id`,`scheduled_at`),
  KEY `email_campaigns_tenant_id_sent_at_index` (`tenant_id`,`sent_at`),
  CONSTRAINT `email_campaigns_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `email_campaigns_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `email_campaigns_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Analytics Events table (Tracking)
CREATE TABLE `analytics_events` (
  `id` char(36) NOT NULL,
  `tenant_id` char(36) NOT NULL,
  `event_type` varchar(255) NOT NULL,
  `event_category` varchar(255) DEFAULT NULL,
  `event_action` varchar(255) DEFAULT NULL,
  `event_label` varchar(255) DEFAULT NULL,
  `event_value` decimal(10,2) DEFAULT NULL,
  `page_url` varchar(255) DEFAULT NULL,
  `page_title` varchar(255) DEFAULT NULL,
  `content_id` char(36) DEFAULT NULL,
  `content_type` varchar(255) DEFAULT NULL,
  `user_id` char(36) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `referrer` varchar(255) DEFAULT NULL,
  `utm_params` json DEFAULT NULL,
  `device_type` varchar(255) DEFAULT NULL,
  `browser` varchar(255) DEFAULT NULL,
  `os` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `custom_data` json DEFAULT NULL,
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `analytics_events_created_at_index` (`created_at`),
  KEY `analytics_events_tenant_id_event_type_created_at_index` (`tenant_id`,`event_type`,`created_at`),
  KEY `analytics_events_tenant_id_content_id_content_type_index` (`tenant_id`,`content_id`,`content_type`),
  KEY `analytics_events_tenant_id_user_id_index` (`tenant_id`,`user_id`),
  KEY `analytics_events_tenant_id_session_id_index` (`tenant_id`,`session_id`),
  KEY `analytics_events_tenant_id_event_category_created_at_index` (`tenant_id`,`event_category`,`created_at`),
  KEY `analytics_events_tenant_id_created_at_index` (`tenant_id`,`created_at`),
  CONSTRAINT `analytics_events_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `analytics_events_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Failed Jobs table (Queue management)
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- SPATIE PERMISSION TABLES (for roles/permissions)
-- ==========================================

CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- SAMPLE DATA (Optional)
-- ==========================================

-- Insert sample permissions
INSERT INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`) VALUES
('view_posts', 'web', NOW(), NOW()),
('create_posts', 'web', NOW(), NOW()),
('edit_posts', 'web', NOW(), NOW()),
('delete_posts', 'web', NOW(), NOW()),
('publish_posts', 'web', NOW(), NOW()),
('view_programs', 'web', NOW(), NOW()),
('create_programs', 'web', NOW(), NOW()),
('edit_programs', 'web', NOW(), NOW()),
('delete_programs', 'web', NOW(), NOW()),
('publish_programs', 'web', NOW(), NOW()),
('view_jobs', 'web', NOW(), NOW()),
('create_jobs', 'web', NOW(), NOW()),
('edit_jobs', 'web', NOW(), NOW()),
('delete_jobs', 'web', NOW(), NOW()),
('publish_jobs', 'web', NOW(), NOW()),
('view_media', 'web', NOW(), NOW()),
('upload_media', 'web', NOW(), NOW()),
('delete_media', 'web', NOW(), NOW()),
('view_users', 'web', NOW(), NOW()),
('create_users', 'web', NOW(), NOW()),
('edit_users', 'web', NOW(), NOW()),
('delete_users', 'web', NOW(), NOW()),
('view_ads', 'web', NOW(), NOW()),
('create_ads', 'web', NOW(), NOW()),
('edit_ads', 'web', NOW(), NOW()),
('delete_ads', 'web', NOW(), NOW()),
('manage_seo', 'web', NOW(), NOW()),
('view_analytics', 'web', NOW(), NOW()),
('manage_settings', 'web', NOW(), NOW()),
('manage_tenant', 'web', NOW(), NOW());

-- Insert sample roles
INSERT INTO `roles` (`name`, `guard_name`, `created_at`, `updated_at`) VALUES
('TenantOwner', 'web', NOW(), NOW()),
('Admin', 'web', NOW(), NOW()),
('Editor', 'web', NOW(), NOW()),
('Author', 'web', NOW(), NOW()),
('SEO', 'web', NOW(), NOW()),
('Moderator', 'web', NOW(), NOW()),
('Analyst', 'web', NOW(), NOW());

-- Insert sample tenant
INSERT INTO `tenants` (
  `id`, `name`, `domain`, `description`, `meta_title`, `meta_description`, 
  `enabled_features`, `status`, `created_at`, `updated_at`
) VALUES (
  UUID(), 
  'Youth Beyond Borders Demo', 
  'demo.ybb-cms.local', 
  'Demo tenant for YBB Multi-Tenant CMS showcasing opportunities and job management.',
  'Youth Beyond Borders - Opportunities & Career Development',
  'Discover scholarships, internships, fellowships, and job opportunities for youth worldwide.',
  '["programs", "jobs", "news", "seo", "ads", "newsletter"]',
  'active',
  NOW(), 
  NOW()
);

SET FOREIGN_KEY_CHECKS = 1;

-- ==========================================
-- INDEXES OPTIMIZATION (Additional)
-- ==========================================

-- Optimize for common search patterns
ALTER TABLE `posts` ADD INDEX `posts_status_published_at_index` (`status`, `published_at`);
ALTER TABLE `posts` ADD INDEX `posts_kind_status_index` (`kind`, `status`);

-- Optimize program searches
ALTER TABLE `pt_program` ADD INDEX `pt_program_deadline_funding_index` (`deadline_at`, `funding_type`);
ALTER TABLE `pt_program` ADD INDEX `pt_program_country_program_type_index` (`country_code`, `program_type`);

-- Optimize job searches  
ALTER TABLE `pt_job` ADD INDEX `pt_job_salary_range_index` (`min_salary`, `max_salary`);
ALTER TABLE `pt_job` ADD INDEX `pt_job_location_remote_index` (`country_code`, `workplace_type`);

-- Optimize analytics queries
ALTER TABLE `analytics_events`
  ADD COLUMN `created_date` DATE GENERATED ALWAYS AS (DATE(`created_at`)) STORED,
  ADD INDEX `analytics_events_date_type_index` (`created_date`, `event_type`);

ALTER TABLE `ad_impressions`
  ADD COLUMN `created_date` DATE GENERATED ALWAYS AS (DATE(`created_at`)) STORED,
  ADD INDEX `ad_impressions_date_index` (`created_date`);

ALTER TABLE `ad_clicks`
  ADD COLUMN `created_date` DATE GENERATED ALWAYS AS (DATE(`created_at`)) STORED,
  ADD INDEX `ad_clicks_date_index` (`created_date`);
  
-- ==========================================
-- NOTES
-- ==========================================
-- 
-- This schema provides:
-- 1. Complete multi-tenant isolation
-- 2. Flexible content management (posts + specialized types)
-- 3. Comprehensive SEO capabilities
-- 4. Advanced advertising system
-- 5. Newsletter and email marketing
-- 6. Detailed analytics tracking
-- 7. Proper indexing for performance
-- 8. Role-based permission system
-- 9. Media management
-- 10. Redirect handling
-- 
-- To use this schema:
-- 1. Create central database and import this file
-- 2. For each tenant, create a new database and import tenant-specific tables
-- 3. Configure your application's tenant resolution
-- 4. Set up proper Laravel migrations for version control
-- 
-- Performance considerations:
-- - All tenant_id columns are properly indexed
-- - FULLTEXT search is enabled on posts
-- - Analytics tables use timestamp-based partitioning recommended
-- - Consider adding database replicas for read-heavy workloads
--