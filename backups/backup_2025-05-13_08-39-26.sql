-- Database Backup
-- Generated: 2025-05-13 08:39:26
-- Database: nail_architect_db

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


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
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `admin_users`
INSERT INTO `admin_users` VALUES ('1', 'admin', 'admin@nailarchitect.com', '$2y$10$YKjlOJ0TmE6nShqT7p3Nw.A7iAKtGjrNhWUFhcZrNR1kS/aGXCpPu', 'Admin', 'User', '09123456789', 'super_admin', '2025-05-12 17:35:49', NULL, '1');


-- Table structure for table `booking_images`
DROP TABLE IF EXISTS `booking_images`;
CREATE TABLE `booking_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`),
  CONSTRAINT `booking_images_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `booking_images`
INSERT INTO `booking_images` VALUES ('2', '2', 'uploads/inspirations/NAI-8455408_0_ballot.png');
INSERT INTO `booking_images` VALUES ('3', '3', 'uploads/inspirations/NAI-1939264_0_send.png');
INSERT INTO `booking_images` VALUES ('4', '4', 'uploads/inspirations/NAI-3229037_0_send.png');
INSERT INTO `booking_images` VALUES ('5', '8', 'uploads/inspirations/NAI-9689707_0_mdi_vote.png');
INSERT INTO `booking_images` VALUES ('6', '9', 'uploads/inspirations/NAI-6387058_0_asdasdasd.jpg');
INSERT INTO `booking_images` VALUES ('7', '10', 'uploads/inspirations/NAI-7501589_0_login4.png');
INSERT INTO `booking_images` VALUES ('8', '10', 'uploads/inspirations/NAI-7501589_1_133886785738657170.jpg');


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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `bookings`
INSERT INTO `bookings` VALUES ('2', '7', 'Lele Cutie', 'johnlexercrisostomo@gmail.com', '09491145757', 'press-ons', '2025-05-13', '14:00', 'hehe', 'TBD', '45', '300.00', '0', 'cancelled', '2025-05-11 10:53:10');
INSERT INTO `bookings` VALUES ('3', '7', 'Lele Cutie', 'johnlexercrisostomo@gmail.com', '09491145757', 'other', '2025-05-14', '14:00', 'asdasd', 'TBD', '60', '500.00', 'NAI-1939264', 'confirmed', '2025-05-11 10:59:02');
INSERT INTO `bookings` VALUES ('4', '7', 'Lele Cutie', 'johnlexercrisostomo@gmail.com', '09491145757', 'soft-gel', '2025-05-15', '14:00', 'asdasd', 'TBD', '60', '800.00', 'NAI-3229037', 'cancelled', '2025-05-11 11:02:29');
INSERT INTO `bookings` VALUES ('5', '6', 'Berlin Dela Cruz', 'maeracreation@gmail.com', '123187237612', 'other', '2025-05-12', '10:00', '', 'TBD', '60', '500.00', 'NAI-2981935', 'completed', '2025-05-11 13:32:58');
INSERT INTO `bookings` VALUES ('6', '7', 'Lele Cutie', 'johnlexercrisostomo@gmail.com', '09491145757', 'soft-gel', '2025-05-12', '15:00', '', 'TBD', '60', '800.00', 'NAI-9264289', 'completed', '2025-05-11 13:35:29');
INSERT INTO `bookings` VALUES ('7', '7', 'Lele Cutie', 'johnlexercrisostomo@gmail.com', '09491145757', 'builder-gel', '2025-05-12', '18:00', '', 'TBD', '60', '750.00', 'NAI-3070252', 'confirmed', '2025-05-11 14:03:34');
INSERT INTO `bookings` VALUES ('8', '7', 'Lele Cutie', 'johnlexercrisostomo@gmail.com', '09491145757', 'removal-fill', '2025-05-17', '11:00', 'wazzup', 'TBD', '30', '150.00', 'NAI-9689707', 'confirmed', '2025-05-11 16:40:38');
INSERT INTO `bookings` VALUES ('9', '7', 'Lele Cutie', 'johnlexercrisostomo@gmail.com', '09491145757', 'builder-gel', '2025-05-14', '15:00', '', 'TBD', '60', '750.00', 'NAI-6387058', 'confirmed', '2025-05-12 00:29:43');
INSERT INTO `bookings` VALUES ('10', '7', 'Lele Cutie', 'johnlexercrisostomo@gmail.com', '09491145757', 'soft-gel', '2025-05-14', '16:00', 'hehe', 'TBD', '60', '800.00', 'NAI-7501589', 'pending', '2025-05-12 11:13:38');


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
INSERT INTO `message_attachments` VALUES ('3', '19', 'HELLO.png', 'uploads/messages/682010b957cef_HELLO.png', '1883', 'image/png', '2025-05-11 10:51:37');
INSERT INTO `message_attachments` VALUES ('4', '23', 'asdasdasd.jpg', 'uploads/messages/6821b2e771c2a_asdasdasd.jpg', '52413', 'image/jpeg', '2025-05-12 16:35:51');


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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `payment_proofs`
INSERT INTO `payment_proofs` VALUES ('2', '2', 'uploads/payments/NAI-8455408_ballot.png', '2025-05-11 10:53:10');
INSERT INTO `payment_proofs` VALUES ('3', '3', 'uploads/payments/NAI-1939264_logout.png', '2025-05-11 10:59:02');
INSERT INTO `payment_proofs` VALUES ('4', '4', 'uploads/payments/NAI-3229037_send.png', '2025-05-11 11:02:29');
INSERT INTO `payment_proofs` VALUES ('5', '5', 'uploads/payments/NAI-2981935_asdasdasd.jpg', '2025-05-11 13:32:58');
INSERT INTO `payment_proofs` VALUES ('6', '6', 'uploads/payments/NAI-9264289_logout.png', '2025-05-11 13:35:29');
INSERT INTO `payment_proofs` VALUES ('7', '7', 'uploads/payments/NAI-3070252_mdi_vote.png', '2025-05-11 14:03:34');
INSERT INTO `payment_proofs` VALUES ('8', '8', 'uploads/payments/NAI-9689707_HELLO.png', '2025-05-11 16:40:38');
INSERT INTO `payment_proofs` VALUES ('9', '9', 'uploads/payments/NAI-6387058_asdasdasd.jpg', '2025-05-12 00:29:43');
INSERT INTO `payment_proofs` VALUES ('10', '10', 'uploads/payments/NAI-7501589_login3.png', '2025-05-12 11:13:38');


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
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email_token` (`email`,`verification_token`),
  KEY `idx_is_verified` (`is_verified`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_full_name` (`first_name`,`last_name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `users`
INSERT INTO `users` VALUES ('2', 'Lele', 'Lele', 'cutie@gmail.com', '12312312', '$2y$10$SDHocuC7BeFd8./xbmfVXeQHmlSOZneuAFVlB/qnX2dKRbZ7d6SMG', '2025-04-26 15:14:59', NULL, NULL, '0');
INSERT INTO `users` VALUES ('3', 'SF', 'SF', 'asdas@gmail.com', '123544566', '$2y$10$UZs/j0PSxs69DEPcrXQ3XOWVbSPFknSqbbDTKbcM9oCejwTtNfn1.', '2025-04-27 15:21:09', NULL, NULL, '0');
INSERT INTO `users` VALUES ('4', 'Jez', 'Ariel Pogi', 'jezariel13@gmail.com', '123187237612', '$2y$10$xWQi8Dg5Dm8.vizhjEuMG.lU4ADOm8zyUMEtrVcJbYHIoDsx789j.', '2025-05-04 13:53:17', '041c3ab4200b468a70718edeced32de6', '2025-05-05 07:53:17', '0');
INSERT INTO `users` VALUES ('6', 'Berlin', 'Dela Cruz', 'maeracreation@gmail.com', '123187237612', '$2y$10$ryGclcJPwGdRc02KbmCfKulXnGcvElQp5Y1b4B4zdJdrFmxjQxrcG', '2025-05-04 14:14:43', NULL, '2025-05-05 08:14:43', '1');
INSERT INTO `users` VALUES ('7', 'Tapu', 'Lele', 'angcuteko213@gmail.com', '09491145757', '$2y$10$x5D4c/FK8ZozJrJ77TuGi.hVCc5UFMVSM5byXUK/aMFJB6LRfwJkO', '2025-05-05 15:15:17', NULL, '2025-05-13 09:58:46', '1');

