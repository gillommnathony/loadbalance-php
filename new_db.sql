-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versi server:                 5.7.24 - MySQL Community Server (GPL)
-- OS Server:                    Win64
-- HeidiSQL Versi:               10.3.0.5771
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- membuang struktur untuk table db_videoplayer.tb_gdrive_auth
DROP TABLE IF EXISTS `tb_gdrive_auth`;
CREATE TABLE IF NOT EXISTS `tb_gdrive_auth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `api_key` varchar(50) NOT NULL,
  `client_id` varchar(100) NOT NULL,
  `client_secret` varchar(50) NOT NULL,
  `refresh_token` varchar(150) NOT NULL,
  `created` int(11) NOT NULL,
  `modified` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL DEFAULT '1',
  `status` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Membuang data untuk tabel db_videoplayer.tb_gdrive_auth: 0 rows
DELETE FROM `tb_gdrive_auth`;
/*!40000 ALTER TABLE `tb_gdrive_auth` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_gdrive_auth` ENABLE KEYS */;

-- membuang struktur untuk table db_videoplayer.tb_gdrive_mirrors
DROP TABLE IF EXISTS `tb_gdrive_mirrors`;
CREATE TABLE IF NOT EXISTS `tb_gdrive_mirrors` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `gdrive_id` varchar(50) NOT NULL,
  `mirror_id` varchar(50) NOT NULL,
  `mirror_email` varchar(255) NOT NULL,
  `added` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Membuang data untuk tabel db_videoplayer.tb_gdrive_mirrors: 0 rows
DELETE FROM `tb_gdrive_mirrors`;
/*!40000 ALTER TABLE `tb_gdrive_mirrors` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_gdrive_mirrors` ENABLE KEYS */;

-- membuang struktur untuk table db_videoplayer.tb_loadbalancers
DROP TABLE IF EXISTS `tb_loadbalancers`;
CREATE TABLE IF NOT EXISTS `tb_loadbalancers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `link` varchar(255) NOT NULL,
  `status` int(1) NOT NULL DEFAULT '1',
  `public` int(1) NOT NULL DEFAULT '0',
  `added` int(11) NOT NULL,
  `updated` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Membuang data untuk tabel db_videoplayer.tb_loadbalancers: 0 rows
DELETE FROM `tb_loadbalancers`;
/*!40000 ALTER TABLE `tb_loadbalancers` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_loadbalancers` ENABLE KEYS */;

-- membuang struktur untuk table db_videoplayer.tb_sessions
DROP TABLE IF EXISTS `tb_sessions`;
CREATE TABLE IF NOT EXISTS `tb_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(25) NOT NULL,
  `useragent` varchar(250) NOT NULL,
  `created` int(15) NOT NULL,
  `username` varchar(50) NOT NULL,
  `expired` int(15) NOT NULL,
  `token` varchar(250) NOT NULL,
  `stat` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Membuang data untuk tabel db_videoplayer.tb_sessions: 0 rows
DELETE FROM `tb_sessions`;
/*!40000 ALTER TABLE `tb_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_sessions` ENABLE KEYS */;

-- membuang struktur untuk table db_videoplayer.tb_settings
DROP TABLE IF EXISTS `tb_settings`;
CREATE TABLE IF NOT EXISTS `tb_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(150) NOT NULL,
  `value` text,
  `updated` int(15) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4;

-- Membuang data untuk tabel db_videoplayer.tb_settings: 67 rows
DELETE FROM `tb_settings`;
/*!40000 ALTER TABLE `tb_settings` DISABLE KEYS */;
INSERT INTO `tb_settings` (`id`, `key`, `value`, `updated`) VALUES
	(1, 'site_name', 'GDPlayer', 1616494496),
	(2, 'site_slogan', 'Google Drive Video Player', 1616494496),
	(3, 'site_description', 'Google Drive Video Player', 1616494496),
	(4, 'main_site', 'http://gdplayer.test/', 1616494496),
	(5, 'production_mode', 'false', 1616494496),
	(6, 'load_balancer_methods', 'direct', 1616494496),
	(7, 'public_video_user', '1', 1616494496),
	(8, 'google_analytics_id', '', 1616494496),
	(9, 'google_tag_manager', '', 1616494496),
	(10, 'histats_id', '', 1616494496),
	(11, 'recaptcha_site_key', '', 1616494496),
	(12, 'recaptcha_secret_key', '', 1616494496),
	(13, 'disqus_shortname', '', 1616494496),
	(14, 'chat_widget', '', 1616494496),
	(15, 'player', 'jwplayer', 1616494496),
	(16, 'player_skin', '', 1616494496),
	(17, 'player_color', '673AB7', 1616494496),
	(18, 'subtitle_color', 'ffff00', 1616494496),
	(19, 'poster', '', 1616494496),
	(20, 'small_logo_file', '', 1616494496),
	(21, 'small_logo_link', '', 1616494496),
	(22, 'logo_file', '', 1616494496),
	(23, 'logo_open_link', '', 1616494496),
	(24, 'logo_position', 'top-right', 1616494496),
	(25, 'logo_margin', '8', 1616494496),
	(26, 'vast_client', 'vast', 1616494496),
	(27, 'vast_offset', '[""]', 1616494496),
	(28, 'vast_xml', '[""]', 1616494496),
	(29, 'vast_skip', '0', 1616494496),
	(30, 'dl_banner_top', '', 1616494496),
	(31, 'dl_banner_bottom', '', 1616494496),
	(32, 'sh_banner_top', '', 1616494496),
	(33, 'sh_banner_bottom', '', 1616494496),
	(34, 'block_adblocker', 'false', 1616494496),
	(35, 'direct_ads_link', '', 1616494496),
	(36, 'popup_ads_link', '', 1616494496),
	(37, 'popup_ads_code', '', 1616494496),
	(38, 'main_url_shortener', '', 1616494496),
	(39, 'additional_url_shortener', 'random', 1616494496),
	(40, 'additional_url_shortener_adf.ly', '', 1616494496),
	(41, 'additional_url_shortener_adtival.network', '', 1616494496),
	(42, 'additional_url_shortener_clk.sh', '', 1616494496),
	(43, 'additional_url_shortener_cutpaid.com', '', 1616494496),
	(44, 'additional_url_shortener_ouo.io', '', 1616494496),
	(45, 'additional_url_shortener_shrinkads.com', '', 1616494496),
	(46, 'additional_url_shortener_safelinkblogger.com', '', 1616494496),
	(47, 'additional_url_shortener_safelinku.com', '', 1616494496),
	(48, 'additional_url_shortener_shorten-link.com', '', 1616494496),
	(49, 'additional_url_shortener_wi.cr', '', 1616494496),
	(50, 'additional_url_shortener_ylinkz.com', '', 1616494496),
	(51, 'disable_confirm', 'true', 1616494496),
	(52, 'smtp_provider', '', 1616494496),
	(53, 'smtp_host', '', 1616494496),
	(54, 'smtp_port', '', 1616494496),
	(55, 'smtp_email', '', 1616494496),
	(56, 'smtp_password', '', 1616494496),
	(57, 'smtp_sender', '', 1616494496),
	(58, 'smtp_reply_email', '', 1616494496),
	(59, 'smtp_reply_name', '', 1616494496),
	(60, 'anti_captcha', '', 1616494496),
	(61, 'uptobox_api', '', 1616494496),
	(62, 'bypass_host', '["anonfile","bayfiles","dood","dropbox","fembed","filerio","filesim","gdrive","gofile","hxfile","mediafire","mixdropto","mp4upload","okru","okstream","streamable","streamtape","supervideo","uploadsmobi","upstream","uptobox","userscloud","vidlox","vidoza","yadisk","yourupload","zippyshare"]', 1616494496),
	(63, 'proxy_list', '', 1616494496),
	(64, 'word_blacklisted', '', 1616494496),
	(65, 'domain_whitelisted', '', 1616494496),
	(66, 'domain_blacklisted', '', 1616494496),
	(67, 'link_blacklisted', '', 1616494496);
/*!40000 ALTER TABLE `tb_settings` ENABLE KEYS */;

-- membuang struktur untuk table db_videoplayer.tb_subtitles
DROP TABLE IF EXISTS `tb_subtitles`;
CREATE TABLE IF NOT EXISTS `tb_subtitles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `language` varchar(25) NOT NULL,
  `link` text NOT NULL,
  `vid` int(11) NOT NULL,
  `added` int(15) NOT NULL,
  `uid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Membuang data untuk tabel db_videoplayer.tb_subtitles: 0 rows
DELETE FROM `tb_subtitles`;
/*!40000 ALTER TABLE `tb_subtitles` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_subtitles` ENABLE KEYS */;

-- membuang struktur untuk table db_videoplayer.tb_subtitle_manager
DROP TABLE IF EXISTS `tb_subtitle_manager`;
CREATE TABLE IF NOT EXISTS `tb_subtitle_manager` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file_name` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL,
  `file_type` varchar(25) NOT NULL,
  `language` varchar(50) DEFAULT NULL,
  `added` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `host` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Membuang data untuk tabel db_videoplayer.tb_subtitle_manager: 0 rows
DELETE FROM `tb_subtitle_manager`;
/*!40000 ALTER TABLE `tb_subtitle_manager` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_subtitle_manager` ENABLE KEYS */;

-- membuang struktur untuk table db_videoplayer.tb_users
DROP TABLE IF EXISTS `tb_users`;
CREATE TABLE IF NOT EXISTS `tb_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(500) NOT NULL,
  `name` varchar(50) NOT NULL,
  `status` int(1) NOT NULL,
  `added` int(15) NOT NULL,
  `updated` int(15) DEFAULT NULL,
  `role` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user` (`user`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

-- Membuang data untuk tabel db_videoplayer.tb_users: 1 rows
DELETE FROM `tb_users`;
/*!40000 ALTER TABLE `tb_users` DISABLE KEYS */;
INSERT INTO `tb_users` (`id`, `user`, `email`, `password`, `name`, `status`, `added`, `updated`, `role`) VALUES
	(1, 'admin', 'admin@gdplayer.top', '$2y$10$C1/JeXkcaXy9c3Nisc4e3eOkpuZj7WuCh0pDQwZA.daUm7Q577V.C', 'Admin', 1, 1590994524, 1616494360, 0);
/*!40000 ALTER TABLE `tb_users` ENABLE KEYS */;

-- membuang struktur untuk table db_videoplayer.tb_videos
DROP TABLE IF EXISTS `tb_videos`;
CREATE TABLE IF NOT EXISTS `tb_videos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `host` varchar(50) NOT NULL,
  `host_id` varchar(500) NOT NULL,
  `ahost` varchar(50) NOT NULL,
  `ahost_id` varchar(500) NOT NULL,
  `uid` int(11) NOT NULL,
  `added` int(15) NOT NULL,
  `updated` int(15) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Membuang data untuk tabel db_videoplayer.tb_videos: 0 rows
DELETE FROM `tb_videos`;
/*!40000 ALTER TABLE `tb_videos` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_videos` ENABLE KEYS */;

-- membuang struktur untuk table db_videoplayer.tb_videos_short
DROP TABLE IF EXISTS `tb_videos_short`;
CREATE TABLE IF NOT EXISTS `tb_videos_short` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(50) NOT NULL,
  `vid` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Membuang data untuk tabel db_videoplayer.tb_videos_short: 0 rows
DELETE FROM `tb_videos_short`;
/*!40000 ALTER TABLE `tb_videos_short` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_videos_short` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
