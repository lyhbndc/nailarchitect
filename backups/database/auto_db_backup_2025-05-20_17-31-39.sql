-- Database Backup
-- Generated: 2025-05-20 17:31:39
-- Database: nail_architect_db

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


-- Table structure for table `2fa_attempts`
DROP TABLE IF EXISTS `2fa_attempts`;
CREATE TABLE `2fa_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `attempt_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `success` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `admin_id` (`admin_id`),
  KEY `idx_attempt_time` (`attempt_time`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `2fa_attempts`
INSERT INTO `2fa_attempts` VALUES ('1', '7', NULL, '::1', '2025-05-16 01:14:13', '1');
INSERT INTO `2fa_attempts` VALUES ('2', '6', NULL, '::1', '2025-05-16 06:16:51', '1');
INSERT INTO `2fa_attempts` VALUES ('3', '8', NULL, '::1', '2025-05-16 08:37:13', '1');
INSERT INTO `2fa_attempts` VALUES ('4', '8', NULL, '::1', '2025-05-16 08:37:50', '1');
INSERT INTO `2fa_attempts` VALUES ('5', '8', NULL, '::1', '2025-05-16 08:38:33', '1');
INSERT INTO `2fa_attempts` VALUES ('6', '8', NULL, '::1', '2025-05-16 08:40:04', '1');
INSERT INTO `2fa_attempts` VALUES ('7', '8', NULL, '::1', '2025-05-16 08:40:22', '1');
INSERT INTO `2fa_attempts` VALUES ('8', '8', NULL, '::1', '2025-05-16 08:40:40', '1');
INSERT INTO `2fa_attempts` VALUES ('9', '8', NULL, '::1', '2025-05-16 13:52:47', '1');
INSERT INTO `2fa_attempts` VALUES ('10', '8', NULL, '::1', '2025-05-19 22:20:10', '1');
INSERT INTO `2fa_attempts` VALUES ('11', '8', NULL, '::1', '2025-05-20 16:48:27', '1');


-- Table structure for table `admin_users`
DROP TABLE IF EXISTS `admin_users`;
CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('admin','super_admin') DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `mfa_verified_session` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `admin_users`
INSERT INTO `admin_users` VALUES ('1', 'admin', 'admin@nailarchitect.com', '$2y$10$ZF0Zi5NUyMc3uOjaZKr16uPA3JIHsDVK7eBSEJWLVlyN82I1nknnK', 'Admin', 'User', '09123456789', 'super_admin', '2025-05-13 01:35:49', '2025-05-20 17:19:32', '1', NULL);
INSERT INTO `admin_users` VALUES ('2', 'kuznets', 'kuznets.calleja@gmail.com', '$2y$10$631IoFLO9kn/0D1Mz1CLSeSAwy0Ih2TCVKp0h8N8xlTxJulnceOtG', 'Lele', 'Coy', '12312312312', 'admin', '2025-05-13 17:52:40', '2025-05-14 14:44:26', '1', NULL);


-- Table structure for table `booking_images`
DROP TABLE IF EXISTS `booking_images`;
CREATE TABLE `booking_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`),
  CONSTRAINT `booking_images_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `booking_images`
INSERT INTO `booking_images` VALUES ('2', '2', 'uploads/inspirations/NAI-8455408_0_ballot.png');
INSERT INTO `booking_images` VALUES ('3', '3', 'uploads/inspirations/NAI-1939264_0_send.png');
INSERT INTO `booking_images` VALUES ('4', '4', 'uploads/inspirations/NAI-3229037_0_send.png');
INSERT INTO `booking_images` VALUES ('5', '8', 'uploads/inspirations/NAI-9689707_0_mdi_vote.png');
INSERT INTO `booking_images` VALUES ('6', '9', 'uploads/inspirations/NAI-6387058_0_asdasdasd.jpg');
INSERT INTO `booking_images` VALUES ('7', '10', 'uploads/inspirations/NAI-7501589_0_login4.png');
INSERT INTO `booking_images` VALUES ('8', '10', 'uploads/inspirations/NAI-7501589_1_133886785738657170.jpg');
INSERT INTO `booking_images` VALUES ('9', '11', 'uploads/inspirations/NAI-2647751_0_80e99433-7615-4b0f-8e9d-a0ec58dfe0fa-XXX_D07G1PAT13_20P6X18LINES.webp');


-- Table structure for table `bookings`
DROP TABLE IF EXISTS `bookings`;
CREATE TABLE `bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `service` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `time` varchar(10) NOT NULL,
  `notes` text DEFAULT NULL,
  `technician` varchar(50) DEFAULT 'TBD',
  `duration` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `reference_id` varchar(20) NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `bookings`
INSERT INTO `bookings` VALUES ('2', '7', 'Tapuu Lelee', 'angcuteko213@gmail.com', '09491145757', 'press-ons', '2025-05-13', '14:00', 'hehe', 'TBD', '45', '300.00', '0', 'cancelled', '2025-05-11 18:53:10');
INSERT INTO `bookings` VALUES ('3', '7', 'Tapuu Lelee', 'angcuteko213@gmail.com', '09491145757', 'other', '2025-05-14', '14:00', 'asdasd', 'TBD', '60', '500.00', 'NAI-1939264', 'confirmed', '2025-05-11 18:59:02');
INSERT INTO `bookings` VALUES ('4', '7', 'Tapuu Lelee', 'angcuteko213@gmail.com', '09491145757', 'soft-gel', '2025-05-15', '14:00', 'asdasd', 'TBD', '60', '800.00', 'NAI-3229037', 'cancelled', '2025-05-11 19:02:29');
INSERT INTO `bookings` VALUES ('5', '6', 'Berlin Dela Cruz', 'maeracreation@gmail.com', '123187237612', 'other', '2025-05-12', '10:00', '', 'TBD', '60', '500.00', 'NAI-2981935', 'completed', '2025-05-11 21:32:58');
INSERT INTO `bookings` VALUES ('6', '7', 'Tapuu Lelee', 'angcuteko213@gmail.com', '09491145757', 'soft-gel', '2025-05-12', '15:00', '', 'TBD', '60', '800.00', 'NAI-9264289', 'completed', '2025-05-11 21:35:29');
INSERT INTO `bookings` VALUES ('7', '7', 'Tapuu Lelee', 'angcuteko213@gmail.com', '09491145757', 'builder-gel', '2025-05-12', '18:00', '', 'TBD', '60', '750.00', 'NAI-3070252', 'confirmed', '2025-05-11 22:03:34');
INSERT INTO `bookings` VALUES ('8', '7', 'Tapuu Lelee', 'angcuteko213@gmail.com', '09491145757', 'removal-fill', '2025-05-17', '11:00', 'wazzup', 'TBD', '30', '150.00', 'NAI-9689707', 'confirmed', '2025-05-12 00:40:38');
INSERT INTO `bookings` VALUES ('9', '7', 'Tapuu Lelee', 'angcuteko213@gmail.com', '09491145757', 'builder-gel', '2025-05-14', '15:00', '', 'TBD', '60', '750.00', 'NAI-6387058', 'confirmed', '2025-05-12 08:29:43');
INSERT INTO `bookings` VALUES ('10', '7', 'Tapuu Lelee', 'angcuteko213@gmail.com', '09491145757', 'soft-gel', '2025-05-14', '16:00', 'hehe', 'TBD', '60', '800.00', 'NAI-7501589', 'pending', '2025-05-12 19:13:38');
INSERT INTO `bookings` VALUES ('11', '7', 'Tapuu Lelee', 'angcuteko213@gmail.com', '09491145757', 'menicure', '2025-05-29', '14:00', 'hello', 'TBD', '45', '400.00', 'NAI-2647751', 'cancelled', '2025-05-14 13:44:18');


-- Table structure for table `chat_conversations`
DROP TABLE IF EXISTS `chat_conversations`;
CREATE TABLE `chat_conversations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('active','closed') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `chat_conversations_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_conversations_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for table `chat_messages`
DROP TABLE IF EXISTS `chat_messages`;
CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `conversation_id` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `sender_type` enum('admin','user') NOT NULL,
  `message` text NOT NULL,
  `has_attachment` tinyint(1) NOT NULL DEFAULT 0,
  `read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `conversation_id` (`conversation_id`),
  CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `chat_conversations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for table `inquiries`
DROP TABLE IF EXISTS `inquiries`;
CREATE TABLE `inquiries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read','responded') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_email` (`email`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_subject` (`subject`),
  FULLTEXT KEY `ft_message` (`message`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `inquiries`
INSERT INTO `inquiries` VALUES ('3', 'Anna', 'Reyes', 'anna.reyes@email.com', '09369871234', 'General Inquiry', 'I love your work! Do you have any ongoing promotions or packages for regular clients?', 'responded', '2025-05-14 04:07:53', '2025-05-14 04:07:53');
INSERT INTO `inquiries` VALUES ('4', 'Patricia', 'Garcia', 'patricia.g@email.com', NULL, 'Feedback', 'Just wanted to say thank you for the amazing service last week. My nails still look perfect!', 'responded', '2025-05-14 04:07:53', '2025-05-14 04:07:53');
INSERT INTO `inquiries` VALUES ('7', 'aaa', '', 'bondoc.aaliyah.b@gmail.com', '123187237612', 'services', 'asdasdas', 'read', '2025-05-14 04:19:21', '2025-05-14 14:08:49');
INSERT INTO `inquiries` VALUES ('8', 'sdasd', '', 'admin@nailarchitect.com', '0987655323', 'general', 'sdasda', 'read', '2025-05-14 04:19:34', '2025-05-14 04:38:50');
INSERT INTO `inquiries` VALUES ('9', 'aaa', '', 'admin@nailarchitect.com', '123187237612', 'general', 'sadasd', 'read', '2025-05-14 04:19:44', '2025-05-14 04:25:57');


-- Table structure for table `inquiry_statistics`
DROP TABLE IF EXISTS `inquiry_statistics`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `inquiry_statistics` AS select count(0) AS `total_inquiries`,sum(case when `inquiries`.`status` = 'unread' then 1 else 0 end) AS `unread_count`,sum(case when `inquiries`.`status` = 'read' then 1 else 0 end) AS `read_count`,sum(case when `inquiries`.`status` = 'responded' then 1 else 0 end) AS `responded_count`,cast(`inquiries`.`created_at` as date) AS `inquiry_date` from `inquiries` group by cast(`inquiries`.`created_at` as date);

-- Data for table `inquiry_statistics`
INSERT INTO `inquiry_statistics` VALUES ('5', '0', '3', '2', '2025-05-14');


-- Table structure for table `message_attachments`
DROP TABLE IF EXISTS `message_attachments`;
CREATE TABLE `message_attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL,
  `file_type` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `message_id` (`message_id`),
  CONSTRAINT `message_attachments_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `message_attachments`
INSERT INTO `message_attachments` VALUES ('3', '19', 'HELLO.png', 'uploads/messages/682010b957cef_HELLO.png', '1883', 'image/png', '2025-05-11 18:51:37');
INSERT INTO `message_attachments` VALUES ('4', '23', 'asdasdasd.jpg', 'uploads/messages/6821b2e771c2a_asdasdasd.jpg', '52413', 'image/jpeg', '2025-05-13 00:35:51');


-- Table structure for table `messages`
DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'ID of the user this message belongs to',
  `sender_id` int(11) DEFAULT NULL COMMENT 'ID of the sender (user ID or NULL for salon)',
  `subject` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `has_attachment` tinyint(1) NOT NULL DEFAULT 0,
  `read_status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0 = unread, 1 = read',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `messages`
INSERT INTO `messages` VALUES ('3', '6', '6', 'asda', 'asdasd', '0', '1', '2025-05-04 15:27:35');
INSERT INTO `messages` VALUES ('4', '6', '6', 'hello', '123123', '0', '1', '2025-05-04 09:48:27');
INSERT INTO `messages` VALUES ('5', '6', '6', 'Re: Salon Conversation', 'hi', '0', '0', '2025-05-05 03:48:36');
INSERT INTO `messages` VALUES ('6', '6', '6', 'Re: Salon Conversation', 'meow meow meow\\r\\n', '0', '0', '2025-05-05 03:48:46');
INSERT INTO `messages` VALUES ('7', '6', '6', 'Re: Salon Conversation', 'meow meow', '0', '0', '2025-05-05 03:48:53');
INSERT INTO `messages` VALUES ('8', '6', '6', 'Re: Salon Conversation', 'asdafasdf ', '0', '0', '2025-05-05 03:48:56');
INSERT INTO `messages` VALUES ('9', '6', NULL, 'Re: Re: Salon Conversation', 'hi', '0', '1', '2025-05-05 05:12:26');
INSERT INTO `messages` VALUES ('10', '6', NULL, 'test', 'hey', '0', '1', '2025-05-05 05:13:12');
INSERT INTO `messages` VALUES ('11', '6', NULL, 'Nail Architect', 'hi\r\n', '0', '1', '2025-05-05 05:16:19');
INSERT INTO `messages` VALUES ('17', '7', '7', 'Test', 'asdasd', '0', '0', '2025-05-05 10:13:59');
INSERT INTO `messages` VALUES ('18', '7', NULL, 'Nail Architect', 'niggus', '0', '1', '2025-05-05 10:14:09');
INSERT INTO `messages` VALUES ('19', '7', '7', 'Re: Salon Conversation', 'hi', '1', '0', '2025-05-11 04:51:37');
INSERT INTO `messages` VALUES ('20', '7', '7', 'Re: Salon Conversation', 'hi', '0', '0', '2025-05-12 10:28:29');
INSERT INTO `messages` VALUES ('21', '7', '7', 'Re: Salon Conversation', 'hello', '0', '0', '2025-05-12 10:29:15');
INSERT INTO `messages` VALUES ('22', '7', NULL, 'Nail Architect', 'hello', '0', '1', '2025-05-12 10:29:38');
INSERT INTO `messages` VALUES ('23', '7', '7', 'Re: Salon Conversation', 'asdasd', '1', '0', '2025-05-12 10:35:51');
INSERT INTO `messages` VALUES ('24', '7', '7', 'Live Chat Request', 'I\\\'d like to chat with a live agent please.', '0', '0', '2025-05-12 10:50:00');
INSERT INTO `messages` VALUES ('25', '7', '7', 'Chat Widget Message', 'hi', '0', '0', '2025-05-12 10:50:05');
INSERT INTO `messages` VALUES ('26', '7', NULL, 'Nail Architect', 'hello', '0', '1', '2025-05-12 10:50:15');
INSERT INTO `messages` VALUES ('27', '7', NULL, 'Nail Architect', 'wazzup', '0', '1', '2025-05-12 10:50:32');
INSERT INTO `messages` VALUES ('28', '7', '7', 'Chat Widget Message', 'hehe', '0', '0', '2025-05-12 10:56:28');
INSERT INTO `messages` VALUES ('29', '7', '7', 'Chat Widget Message', 'hey', '0', '0', '2025-05-12 10:57:53');
INSERT INTO `messages` VALUES ('30', '7', NULL, 'Nail Architect', '123', '0', '1', '2025-05-12 10:58:02');
INSERT INTO `messages` VALUES ('31', '7', NULL, 'Nail Architect', '123123', '0', '1', '2025-05-12 10:58:26');


-- Table structure for table `password_resets`
DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`),
  KEY `idx_token` (`token`),
  CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `password_resets`
INSERT INTO `password_resets` VALUES ('12', '6', 'c1a2077498cf6d6132537a7e3ad1590dbdaee95ac677e7d1bbc384f60ab7a035', '2025-05-14 00:03:11', '2025-05-14 05:03:11');


-- Table structure for table `payment_proofs`
DROP TABLE IF EXISTS `payment_proofs`;
CREATE TABLE `payment_proofs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`),
  CONSTRAINT `payment_proofs_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `payment_proofs`
INSERT INTO `payment_proofs` VALUES ('2', '2', 'uploads/payments/NAI-8455408_ballot.png', '2025-05-11 18:53:10');
INSERT INTO `payment_proofs` VALUES ('3', '3', 'uploads/payments/NAI-1939264_logout.png', '2025-05-11 18:59:02');
INSERT INTO `payment_proofs` VALUES ('4', '4', 'uploads/payments/NAI-3229037_send.png', '2025-05-11 19:02:29');
INSERT INTO `payment_proofs` VALUES ('5', '5', 'uploads/payments/NAI-2981935_asdasdasd.jpg', '2025-05-11 21:32:58');
INSERT INTO `payment_proofs` VALUES ('6', '6', 'uploads/payments/NAI-9264289_logout.png', '2025-05-11 21:35:29');
INSERT INTO `payment_proofs` VALUES ('7', '7', 'uploads/payments/NAI-3070252_mdi_vote.png', '2025-05-11 22:03:34');
INSERT INTO `payment_proofs` VALUES ('8', '8', 'uploads/payments/NAI-9689707_HELLO.png', '2025-05-12 00:40:38');
INSERT INTO `payment_proofs` VALUES ('9', '9', 'uploads/payments/NAI-6387058_asdasdasd.jpg', '2025-05-12 08:29:43');
INSERT INTO `payment_proofs` VALUES ('10', '10', 'uploads/payments/NAI-7501589_login3.png', '2025-05-12 19:13:38');
INSERT INTO `payment_proofs` VALUES ('11', '11', 'uploads/payments/NAI-2647751_111.png', '2025-05-14 13:44:18');


-- Table structure for table `user_2fa`
DROP TABLE IF EXISTS `user_2fa`;
CREATE TABLE `user_2fa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `secret` varchar(32) NOT NULL,
  `enabled` tinyint(1) DEFAULT 0,
  `backup_codes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user` (`user_id`),
  UNIQUE KEY `unique_admin` (`admin_id`),
  CONSTRAINT `user_2fa_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_2fa_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `user_2fa`
INSERT INTO `user_2fa` VALUES ('1', '7', NULL, 'YZZLJTJU2474GI5O', '1', '[\"A80FCB1E\",\"9169C069\",\"AE70EE89\",\"21A4521F\",\"EE8E8897\",\"BD9A0D5E\",\"470AF857\"]', '2025-05-16 01:08:30', '2025-05-16 08:33:17');
INSERT INTO `user_2fa` VALUES ('2', '6', NULL, '73A4SR7LTEMOQO7Z', '1', '[\"D2453BAE\",\"F7A83E4B\",\"5759C71E\",\"3B3DCA06\",\"B58B804F\",\"9D9AEBC1\",\"73049697\",\"904E7103\"]', '2025-05-16 06:15:00', '2025-05-16 06:15:30');
INSERT INTO `user_2fa` VALUES ('3', '8', NULL, 'PSKOD4SDYKNZF2HC', '1', '[\"80D185A2\",\"16FC4248\",\"AE95DF1B\",\"E19ACA4E\",\"F6F47CA8\",\"F8C5688C\",\"38D7607F\",\"9401B2A3\"]', '2025-05-16 08:36:16', '2025-05-16 08:36:49');


-- Table structure for table `user_profile_history`
DROP TABLE IF EXISTS `user_profile_history`;
CREATE TABLE `user_profile_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `old_first_name` varchar(50) NOT NULL,
  `old_last_name` varchar(50) NOT NULL,
  `old_email` varchar(100) NOT NULL,
  `old_phone` varchar(20) NOT NULL,
  `new_first_name` varchar(50) NOT NULL,
  `new_last_name` varchar(50) NOT NULL,
  `new_email` varchar(100) NOT NULL,
  `new_phone` varchar(20) NOT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `changed_by` int(11) DEFAULT NULL COMMENT 'Who made the change (user_id or admin_id)',
  `update_past_records` tinyint(1) DEFAULT 0 COMMENT '1 if past records were updated',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `changed_at` (`changed_at`),
  KEY `idx_user_changed` (`user_id`,`changed_at`),
  CONSTRAINT `user_profile_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for table `users`
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `verification_token` varchar(64) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `mfa_verified_session` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email_token` (`email`,`verification_token`),
  KEY `idx_is_verified` (`is_verified`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_full_name` (`first_name`,`last_name`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `users`
INSERT INTO `users` VALUES ('2', 'Lele', 'Lele', 'cutie@gmail.com', '12312312', '$2y$10$SDHocuC7BeFd8./xbmfVXeQHmlSOZneuAFVlB/qnX2dKRbZ7d6SMG', '2025-04-26 23:14:59', NULL, NULL, '0', NULL);
INSERT INTO `users` VALUES ('3', 'SF', 'SF', 'asdas@gmail.com', '123544566', '$2y$10$UZs/j0PSxs69DEPcrXQ3XOWVbSPFknSqbbDTKbcM9oCejwTtNfn1.', '2025-04-27 23:21:09', NULL, NULL, '0', NULL);
INSERT INTO `users` VALUES ('4', 'Jez', 'Ariel Pogi', 'jezariel13@gmail.com', '123187237612', '$2y$10$xWQi8Dg5Dm8.vizhjEuMG.lU4ADOm8zyUMEtrVcJbYHIoDsx789j.', '2025-05-04 21:53:17', '041c3ab4200b468a70718edeced32de6', '2025-05-05 07:53:17', '0', NULL);
INSERT INTO `users` VALUES ('6', 'Berlin', 'Dela Cruz', 'maeracreation@gmail.com', '123187237612', '$2y$10$ryGclcJPwGdRc02KbmCfKulXnGcvElQp5Y1b4B4zdJdrFmxjQxrcG', '2025-05-04 22:14:43', NULL, '2025-05-05 08:14:43', '1', NULL);
INSERT INTO `users` VALUES ('7', 'Tapuu', 'Lelee', 'angcuteko213@gmail.com', '09491145757', '$2y$10$S6znDhcFruas5UbaKeJODOqx8kK07r9zgFsyIbmd3FAISMNLUpRC6', '2025-05-05 23:15:17', NULL, '2025-05-13 09:58:46', '1', NULL);
INSERT INTO `users` VALUES ('8', 'John', 'Lexer', 'johnlexercrisostomo@gmail.com', '09491145757', '$2y$10$UU9xGP2jti9WQgDjHOhJx.trAyjUuCp7C2V7pbMskilh9n2UQZWEi', '2025-05-16 08:35:13', NULL, '2025-05-17 02:35:13', '1', NULL);
INSERT INTO `users` VALUES ('9', 'Coy', 'Coy', 'kuznets.calleja@gmail.com', '09491145757', '$2y$10$G0.l9AqO0tXDLYVGIQXLAeeeqvcahmG37l5YqxGNTPcy9.kGRvLHC', '2025-05-18 10:45:54', '62442bfede221f93667cc5b851e1bd10', '2025-05-19 04:45:54', '0', NULL);

