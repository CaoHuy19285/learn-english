-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Máy chủ: localhost
-- Thời gian đã tạo: Th7 06, 2026 lúc 11:33 AM
-- Phiên bản máy phục vụ: 10.4.19-MariaDB
-- Phiên bản PHP: 8.0.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `wordwise_db`
--
CREATE DATABASE IF NOT EXISTS `wordwise_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `wordwise_db`;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `flashcard_cards`
--

DROP TABLE IF EXISTS `flashcard_cards`;
CREATE TABLE `flashcard_cards` (
  `id` int(11) NOT NULL,
  `set_id` int(11) NOT NULL,
  `front` text NOT NULL,
  `back` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `hint` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Đang đổ dữ liệu cho bảng `flashcard_cards`
--

INSERT INTO `flashcard_cards` (`id`, `set_id`, `front`, `back`, `image`, `hint`, `created_at`) VALUES
(1, 1, 'hello', 'xin chào', NULL, NULL, '2026-07-06 02:36:20'),
(5, 3, 'alo', 'hay', NULL, NULL, '2026-07-06 05:18:21'),
(6, 3, 'how', 'www', NULL, NULL, '2026-07-06 05:18:21');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `flashcard_sets`
--

DROP TABLE IF EXISTS `flashcard_sets`;
CREATE TABLE `flashcard_sets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Đang đổ dữ liệu cho bảng `flashcard_sets`
--

INSERT INTO `flashcard_sets` (`id`, `user_id`, `title`, `description`, `is_public`, `created_at`, `updated_at`) VALUES
(1, 3, '10 từ toeic', '10 từ toeic thôi', 1, '2026-07-06 02:36:20', '2026-07-06 02:36:20'),
(3, 1, '500 từ vựng', '500 từ thông dụng', 1, '2026-07-06 05:10:15', '2026-07-06 05:10:15');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `folders`
--

DROP TABLE IF EXISTS `folders`;
CREATE TABLE `folders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Đang đổ dữ liệu cho bảng `folders`
--

INSERT INTO `folders` (`id`, `user_id`, `name`, `created_at`, `updated_at`) VALUES
(1, 3, 'toeic', '2026-07-06 08:48:31', '2026-07-06 08:48:31');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `folder_items`
--

DROP TABLE IF EXISTS `folder_items`;
CREATE TABLE `folder_items` (
  `id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  `item_type` enum('flashcard','vocabulary','quiz') DEFAULT 'flashcard',
  `item_id` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `game_answers`
--

DROP TABLE IF EXISTS `game_answers`;
CREATE TABLE `game_answers` (
  `id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `user_answer` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT 0,
  `answer_time` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `game_sessions`
--

DROP TABLE IF EXISTS `game_sessions`;
CREATE TABLE `game_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `hearts_remaining` int(11) DEFAULT 5,
  `score` int(11) DEFAULT 0,
  `status` enum('active','completed','failed') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Đang đổ dữ liệu cho bảng `game_sessions`
--

INSERT INTO `game_sessions` (`id`, `user_id`, `start_time`, `end_time`, `hearts_remaining`, `score`, `status`, `created_at`) VALUES
(1, 2, '2026-07-04 10:00:00', '2026-07-04 10:10:00', 3, 80, 'completed', '2026-07-04 17:00:00'),
(2, 2, '2026-07-04 14:30:00', NULL, 5, 0, 'active', '2026-07-04 21:30:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'system',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `created_at`) VALUES
(3, 2, 'Chào buổi sáng!', 'Sáng rồi bạn ơi! Khởi động ngày mới tràn đầy năng lượng với vài từ vựng nào!', 'greeting', 1, '2026-07-05 18:39:56');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `questions`
--

DROP TABLE IF EXISTS `questions`;
CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `difficulty` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'medium',
  `question_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `correct_answer` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `options` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hint` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `typeword_id` int(11) DEFAULT NULL,
  `vocabulary_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `questions`
--

INSERT INTO `questions` (`id`, `type_id`, `difficulty`, `question_text`, `correct_answer`, `options`, `hint`, `typeword_id`, `vocabulary_id`) VALUES
(1, 3, 'easy', 'Xin chào', 'Hello', NULL, 'Gợi ý: H_ll_', NULL, NULL),
(2, 4, 'easy', 'Hello', 'Xin chào', NULL, NULL, NULL, NULL),
(3, 1, 'easy', 'Con mèo', 'Cat', '[\"Cat\", \"Dog\", \"Bird\", \"Fish\"]', NULL, NULL, NULL),
(4, 1, 'easy', 'Quả táo', 'Apple', '[\"Apple\", \"Banana\", \"Orange\", \"Mango\"]', NULL, NULL, NULL),
(5, 3, 'medium', 'Tôi thích đi du lịch bằng tàu hỏa.', 'I like to travel by train', NULL, 'Sử dụng từ: travel, train', NULL, NULL),
(6, 6, 'medium', 'I want to buy a train ______.', 'ticket', NULL, 'Gợi ý: Vé (Danh từ)', NULL, NULL),
(7, 7, 'medium', 'Chọn các từ tiếng Anh CÓ THẬT về chủ đề Du lịch:', '[\"Travel\", \"Train\", \"Ticket\", \"Station\"]', '[\"Travel\", \"Traval\", \"Train\", \"Tran\", \"Ticket\", \"Tickit\", \"Station\", \"Stasion\"]', NULL, NULL, NULL),
(8, 6, 'hard', 'The rapid development of technology has dramatically changed human ______.', 'society', NULL, 'Gợi ý: Xã hội (Danh từ)', NULL, NULL),
(9, 2, 'hard', 'Hãy đọc to câu sau: \"Environmental sustainability is crucial for our future.\"', 'Environmental sustainability is crucial for our future.', NULL, NULL, NULL, NULL),
(10, 7, 'hard', 'Chọn các từ tiếng Anh CÓ THẬT (Advanced):', '[\"Environment\", \"Crucial\", \"Development\"]', '[\"Environment\", \"Enviroment\", \"Crucial\", \"Crusial\", \"Development\", \"Developement\"]', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `question_types`
--

DROP TABLE IF EXISTS `question_types`;
CREATE TABLE `question_types` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `icon` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Đang đổ dữ liệu cho bảng `question_types`
--

INSERT INTO `question_types` (`id`, `name`, `description`, `icon`) VALUES
(1, 'Match Audio', 'Nghe và chọn từ đúng', '🎧'),
(2, 'Shadowing', 'Đọc lại câu tiếng Anh', '🎤'),
(3, 'Translate EN→VI', 'Dịch tiếng Anh sang tiếng Việt', '📝'),
(4, 'Translate VI→EN', 'Dịch tiếng Việt sang tiếng Anh', '📝'),
(5, 'Listening', 'Nghe và trả lời câu hỏi', '🎧'),
(6, 'Fill Blank', 'Điền từ còn thiếu', '✏️'),
(7, 'Chọn Từ (DET)', 'Chọn các từ tiếng Anh có thật trong danh sách', '📝');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `study_sessions`
--

DROP TABLE IF EXISTS `study_sessions`;
CREATE TABLE `study_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `duration_minutes` int(11) DEFAULT 0,
  `date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `typeword`
--

DROP TABLE IF EXISTS `typeword`;
CREATE TABLE `typeword` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `color_theme` varchar(30) DEFAULT 'purple',
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Đang đổ dữ liệu cho bảng `typeword`
--

INSERT INTO `typeword` (`id`, `name`, `description`, `color_theme`, `image`) VALUES
(1, 'IT & Tech', 'Từ vựng chuyên ngành Công nghệ thông tin', 'purple', NULL),
(2, 'Arts & Culture', 'Nghệ thuật, hội họa và các nét văn hóa', 'pink', NULL),
(3, 'Business', 'Kinh tế thương mại và tài chính doanh nghiệp', 'green', NULL),
(4, 'Science', 'Các thuật ngữ khoa học đời sống và vũ trụ', 'indigo', NULL),
(5, 'Travel & Culture', 'Hành trình khám phá và giao lưu du lịch', 'orange', NULL),
(6, 'Health & Fitness', 'Từ vựng về sức khỏe, y tế và thể hình', 'pink', NULL),
(7, 'Environment', 'Bảo vệ môi trường và thế giới tự nhiên', 'green', NULL),
(8, 'Education', 'Chủ đề trường học, giáo dục và học thuật', 'indigo', NULL),
(9, 'Common', 'các từ thông dụng hàng ngày', 'green', NULL),
(10, 'Fashion & Style', 'Từ vựng về thời trang và phong cách', 'pink', NULL),
(11, 'Advanced Technology', 'Từ vựng công nghệ cao cấp', 'indigo', NULL),
(12, 'Travel & Adventure', 'Từ vựng về du lịch và phiêu lưu', 'orange', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('user','admin') COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` enum('Nam','Nữ','Khác') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'default_avatar.png',
  `birth_year` int(4) DEFAULT NULL,
  `xp` int(11) DEFAULT 0,
  `streak` int(11) DEFAULT 0,
  `last_study_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_studied_at` datetime DEFAULT current_timestamp(),
  `hearts` int(11) DEFAULT 5
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `full_name`, `gender`, `avatar`, `birth_year`, `xp`, `streak`, `last_study_date`, `created_at`, `last_studied_at`, `hearts`) VALUES
(1, 'admin', '123456', 'admin', 'Quản Trị Viên', 'Nam', '9872313293eb76dbf85281ec9fdd4ace.png', 1995, 400, 7, '2026-07-02 14:20:46', '2026-07-04 23:57:01', '2026-07-05 18:16:42', 5),
(2, 'user', '123456', 'user', 'Học Viên', 'Nữ', 'e47b75a7cd5d12ccc72b36f401fca12f.jpg', 2002, 120, 1, '2026-07-01 14:20:46', '2026-07-04 23:57:01', '2026-07-05 18:16:42', 5),
(3, 'user1@gmail.com', '123456', 'user', 'John', NULL, 'c50d09e6ab51e1fae729a011a0f0092d.png', 0, 0, 1, '2026-07-06 01:55:40', '2026-07-06 02:22:09', '2026-07-05 19:22:09', 5);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `user_activity`
--

DROP TABLE IF EXISTS `user_activity`;
CREATE TABLE `user_activity` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity_type` enum('learn','quiz','flashcard','game') DEFAULT 'learn',
  `vocabulary_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Đang đổ dữ liệu cho bảng `user_activity`
--

INSERT INTO `user_activity` (`id`, `user_id`, `activity_type`, `vocabulary_id`, `created_at`) VALUES
(1, 3, 'flashcard', 267, '2026-07-06 08:47:43'),
(2, 3, 'flashcard', 268, '2026-07-06 08:47:45'),
(3, 3, 'flashcard', 269, '2026-07-06 08:47:45'),
(4, 3, 'flashcard', 270, '2026-07-06 08:47:46'),
(5, 3, 'flashcard', 271, '2026-07-06 08:47:48'),
(6, 3, 'flashcard', 272, '2026-07-06 08:47:49'),
(7, 3, 'flashcard', 273, '2026-07-06 08:47:49'),
(8, 3, 'flashcard', 274, '2026-07-06 08:47:49'),
(9, 3, 'flashcard', 275, '2026-07-06 08:47:50'),
(10, 3, 'flashcard', 276, '2026-07-06 08:47:50'),
(11, 3, 'flashcard', 277, '2026-07-06 08:47:50'),
(12, 3, 'flashcard', 278, '2026-07-06 08:47:51'),
(13, 3, 'flashcard', 279, '2026-07-06 08:47:52'),
(14, 3, 'flashcard', 280, '2026-07-06 08:47:52'),
(15, 3, 'flashcard', 253, '2026-07-06 08:50:54'),
(16, 3, 'flashcard', 254, '2026-07-06 08:50:55'),
(17, 3, 'flashcard', 255, '2026-07-06 08:50:57'),
(18, 3, 'flashcard', 256, '2026-07-06 08:50:58'),
(19, 3, 'flashcard', 257, '2026-07-06 08:50:58'),
(20, 3, 'flashcard', 258, '2026-07-06 08:50:59'),
(21, 3, 'flashcard', 259, '2026-07-06 08:50:59'),
(22, 3, 'flashcard', 260, '2026-07-06 08:50:59'),
(23, 3, 'flashcard', 261, '2026-07-06 08:50:59'),
(24, 3, 'flashcard', 262, '2026-07-06 08:50:59'),
(25, 3, 'flashcard', 263, '2026-07-06 08:50:59'),
(26, 3, 'flashcard', 264, '2026-07-06 08:51:00'),
(27, 3, 'flashcard', 265, '2026-07-06 08:51:00'),
(28, 3, 'flashcard', 266, '2026-07-06 08:51:00'),
(29, 3, 'flashcard', 60, '2026-07-06 08:51:16'),
(30, 3, 'flashcard', 61, '2026-07-06 08:51:16'),
(31, 3, 'flashcard', 62, '2026-07-06 08:51:16'),
(32, 3, 'quiz', 267, '2026-07-06 08:51:58'),
(33, 3, 'quiz', 60, '2026-07-06 08:55:40'),
(34, 3, 'quiz', 61, '2026-07-06 08:55:42'),
(35, 3, 'quiz', 62, '2026-07-06 08:55:43'),
(36, 3, 'quiz', 63, '2026-07-06 08:55:45'),
(37, 3, 'quiz', 64, '2026-07-06 08:55:46'),
(38, 3, 'quiz', 65, '2026-07-06 08:55:48'),
(39, 3, 'quiz', 66, '2026-07-06 08:55:49'),
(40, 3, 'quiz', 67, '2026-07-06 08:55:51'),
(41, 3, 'quiz', 68, '2026-07-06 08:55:52'),
(42, 3, 'quiz', 69, '2026-07-06 08:55:53');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `user_progress`
--

DROP TABLE IF EXISTS `user_progress`;
CREATE TABLE `user_progress` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vocabulary_id` int(11) NOT NULL,
  `status` enum('learned','reviewing','mastered') DEFAULT 'learned',
  `correct_count` int(11) DEFAULT 0,
  `wrong_count` int(11) DEFAULT 0,
  `last_review` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Đang đổ dữ liệu cho bảng `user_progress`
--

INSERT INTO `user_progress` (`id`, `user_id`, `vocabulary_id`, `status`, `correct_count`, `wrong_count`, `last_review`, `created_at`) VALUES
(1, 3, 267, 'learned', 1, 1, '2026-07-06 01:51:58', '2026-07-06 08:47:43'),
(2, 3, 268, 'learned', 1, 0, '2026-07-06 01:47:45', '2026-07-06 08:47:45'),
(3, 3, 269, 'learned', 1, 0, '2026-07-06 01:47:45', '2026-07-06 08:47:45'),
(4, 3, 270, 'learned', 1, 0, '2026-07-06 01:47:46', '2026-07-06 08:47:46'),
(5, 3, 271, 'learned', 1, 0, '2026-07-06 01:47:48', '2026-07-06 08:47:48'),
(6, 3, 272, 'learned', 1, 0, '2026-07-06 01:47:49', '2026-07-06 08:47:49'),
(7, 3, 273, 'learned', 1, 0, '2026-07-06 01:47:49', '2026-07-06 08:47:49'),
(8, 3, 274, 'learned', 2, 1, '2026-07-06 01:52:14', '2026-07-06 08:47:49'),
(9, 3, 275, 'learned', 2, 1, '2026-07-06 01:52:10', '2026-07-06 08:47:50'),
(10, 3, 276, 'learned', 1, 0, '2026-07-06 01:47:50', '2026-07-06 08:47:50'),
(11, 3, 277, 'learned', 2, 0, '2026-07-06 01:52:12', '2026-07-06 08:47:50'),
(12, 3, 278, 'learned', 2, 0, '2026-07-06 01:52:05', '2026-07-06 08:47:51'),
(13, 3, 279, 'learned', 1, 0, '2026-07-06 01:47:52', '2026-07-06 08:47:52'),
(14, 3, 280, 'learned', 2, 2, '2026-07-06 01:52:15', '2026-07-06 08:47:52'),
(15, 3, 253, 'learned', 1, 0, '2026-07-06 01:50:54', '2026-07-06 08:50:54'),
(16, 3, 254, 'learned', 1, 0, '2026-07-06 01:50:55', '2026-07-06 08:50:55'),
(17, 3, 255, 'learned', 1, 0, '2026-07-06 01:50:57', '2026-07-06 08:50:57'),
(18, 3, 256, 'reviewing', 0, 1, '2026-07-06 01:50:58', '2026-07-06 08:50:58'),
(19, 3, 257, 'reviewing', 0, 1, '2026-07-06 01:50:58', '2026-07-06 08:50:58'),
(20, 3, 258, 'reviewing', 0, 1, '2026-07-06 01:50:59', '2026-07-06 08:50:59'),
(21, 3, 259, 'reviewing', 0, 1, '2026-07-06 01:50:59', '2026-07-06 08:50:59'),
(22, 3, 260, 'reviewing', 0, 1, '2026-07-06 01:50:59', '2026-07-06 08:50:59'),
(23, 3, 261, 'reviewing', 0, 1, '2026-07-06 01:50:59', '2026-07-06 08:50:59'),
(24, 3, 262, 'reviewing', 0, 1, '2026-07-06 01:50:59', '2026-07-06 08:50:59'),
(25, 3, 263, 'reviewing', 0, 1, '2026-07-06 01:50:59', '2026-07-06 08:50:59'),
(26, 3, 264, 'reviewing', 0, 1, '2026-07-06 01:51:00', '2026-07-06 08:51:00'),
(27, 3, 265, 'reviewing', 0, 1, '2026-07-06 01:51:00', '2026-07-06 08:51:00'),
(28, 3, 266, 'reviewing', 0, 1, '2026-07-06 01:51:00', '2026-07-06 08:51:00'),
(29, 3, 60, 'learned', 1, 0, '2026-07-06 01:51:16', '2026-07-06 08:51:16'),
(30, 3, 61, 'learned', 1, 0, '2026-07-06 01:51:16', '2026-07-06 08:51:16'),
(31, 3, 62, 'learned', 1, 0, '2026-07-06 01:51:16', '2026-07-06 08:51:16'),
(32, 3, 49, 'learned', 1, 2, '2026-07-06 01:52:33', '2026-07-06 08:52:28'),
(33, 3, 47, 'learned', 1, 3, '2026-07-06 01:52:46', '2026-07-06 08:52:30'),
(34, 3, 6, 'learned', 1, 0, '2026-07-06 01:52:41', '2026-07-06 08:52:41'),
(35, 3, 41, 'learned', 1, 0, '2026-07-06 01:52:44', '2026-07-06 08:52:44'),
(36, 3, 43, 'learned', 1, 0, '2026-07-06 01:52:46', '2026-07-06 08:52:46'),
(37, 3, 63, 'learned', 1, 0, '2026-07-06 01:55:45', '2026-07-06 08:55:45'),
(38, 3, 64, 'reviewing', 0, 1, '2026-07-06 01:55:46', '2026-07-06 08:55:46'),
(39, 3, 65, 'reviewing', 0, 1, '2026-07-06 01:55:48', '2026-07-06 08:55:48'),
(40, 3, 66, 'learned', 1, 0, '2026-07-06 01:55:49', '2026-07-06 08:55:49'),
(41, 3, 67, 'reviewing', 0, 1, '2026-07-06 01:55:51', '2026-07-06 08:55:51'),
(42, 3, 68, 'reviewing', 0, 1, '2026-07-06 01:55:52', '2026-07-06 08:55:52'),
(43, 3, 69, 'reviewing', 0, 1, '2026-07-06 01:55:53', '2026-07-06 08:55:53');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `vocabulary`
--

DROP TABLE IF EXISTS `vocabulary`;
CREATE TABLE `vocabulary` (
  `id` int(11) NOT NULL,
  `typeword_id` int(11) NOT NULL,
  `word` varchar(100) NOT NULL,
  `ipa` varchar(100) NOT NULL,
  `definition` text NOT NULL,
  `example` text DEFAULT NULL,
  `difficulty` varchar(20) DEFAULT 'Trung bình',
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Đang đổ dữ liệu cho bảng `vocabulary`
--

INSERT INTO `vocabulary` (`id`, `typeword_id`, `word`, `ipa`, `definition`, `example`, `difficulty`, `image`) VALUES
(1, 1, 'Algorithm', '/ˈæl.ɡə.rɪ.ðəm/', 'Thuật toán, quy trình xử lý dữ liệu theo bước', 'The search engine uses a complex algorithm.', 'Trung bình', NULL),
(2, 1, 'API', '/ˌeɪ.piˈaɪ/', 'Giao diện lập trình ứng dụng kết nối hệ thống', 'The weather app fetches live data through a public API.', 'Dễ', NULL),
(3, 1, 'Repository', '/rɪˈpɒz.ɪ.tər.i/', 'Kho lưu trữ mã nguồn trực tuyến', 'Push your code changes to the remote repository.', 'Khó', NULL),
(4, 2, 'Masterpiece', '/ˈmɑː.stə.piːs/', 'Kiệt tác nghệ thuật để đời', 'Leonardo da Vinci painted several masterpieces.', 'Trung bình', NULL),
(5, 3, 'Revenue', '/ˈrev.ən.juː/', 'Doanh thu, tiền thu về trước khi trừ chi phí', 'The company reported a 20% increase in annual revenue.', 'Khó', NULL),
(6, 4, 'Hypothesis', '/haɪˈpɒθ.ə.sɪs/', 'Giả thuyết khoa học cần chứng minh', 'They formulated a new hypothesis to explain the phenomenon.', 'Khó', NULL),
(7, 5, 'Itinerary', '/aɪˈtɪn.ər.ər.i/', 'Lịch trình chi tiết của một chuyến đi', 'We must follow the strict travel itinerary.', 'Trung bình', NULL),
(8, 1, 'Algorithm', '/ˈæl.ɡə.rɪ.ðəm/', 'Thuật toán', 'The app uses a complex algorithm.', 'medium', 'https://cdn-icons-png.flaticon.com/512/1792/1792183.png'),
(10, 1, 'Database', '/ˈdeɪ.tə.beɪs/', 'Cơ sở dữ liệu', 'The company stores customer details in a secure database.', 'Dễ', NULL),
(11, 1, 'Framework', '/ˈfreɪm.wɜːk/', 'Khung phần mềm', 'Laravel is a popular PHP framework.', 'Trung bình', NULL),
(12, 1, 'Frontend', '/ˈfrʌnt.end/', 'Phần giao diện người dùng', 'He is learning HTML and CSS to become a frontend developer.', 'Dễ', NULL),
(13, 1, 'Backend', '/ˈbæk.end/', 'Phần xử lý máy chủ', 'The backend connects the database to the website.', 'Dễ', NULL),
(14, 1, 'Encryption', '/ɪnˈkrɪp.ʃən/', 'Sự mã hóa dữ liệu', 'End-to-end encryption keeps your messages safe.', 'Khó', NULL),
(15, 1, 'Malware', '/ˈmæl.weər/', 'Phần mềm độc hại', 'Do not click suspicious links to avoid malware.', 'Trung bình', NULL),
(16, 1, 'Firewall', '/ˈfaɪə.wɔːl/', 'Tường lửa', 'The network is protected by a strong firewall.', 'Trung bình', NULL),
(17, 1, 'Debugging', '/diːˈbʌɡ.ɪŋ/', 'Gỡ lỗi lập trình', 'Debugging can take more time than writing the code.', 'Trung bình', NULL),
(18, 1, 'Server', '/ˈsɜː.vər/', 'Máy chủ', 'The website is down because the server crashed.', 'Dễ', NULL),
(19, 1, 'Bandwidth', '/ˈbænd.wɪdθ/', 'Băng thông', 'High bandwidth is required for streaming 4K videos.', 'Khó', NULL),
(20, 2, 'Exhibition', '/ˌek.sɪˈbɪʃ.ən/', 'Cuộc triển lãm', 'The art gallery is hosting a new exhibition.', 'Trung bình', NULL),
(21, 2, 'Heritage', '/ˈher.ɪ.tɪdʒ/', 'Di sản văn hóa', 'Hoi An is recognized as a world cultural heritage.', 'Trung bình', NULL),
(22, 2, 'Sculpture', '/ˈskʌlp.tʃər/', 'Tác phẩm điêu khắc', 'The museum has a collection of ancient Roman sculptures.', 'Khó', NULL),
(23, 2, 'Portrait', '/ˈpɔː.trət/', 'Bức chân dung', 'She painted a beautiful portrait of her mother.', 'Dễ', NULL),
(24, 2, 'Landscape', '/ˈlænd.skeɪp/', 'Phong cảnh', 'The artist is famous for his landscape paintings.', 'Dễ', NULL),
(25, 2, 'Symphony', '/ˈsɪm.fə.ni/', 'Bản giao hưởng', 'Beethoven’s ninth symphony is a masterpiece.', 'Trung bình', NULL),
(26, 2, 'Calligraphy', '/kəˈlɪɡ.rə.fi/', 'Thư pháp', 'He practices Japanese calligraphy in his free time.', 'Khó', NULL),
(27, 2, 'Architecture', '/ˈɑː.kɪ.tek.tʃər/', 'Kiến trúc', 'The architecture of this building is very modern.', 'Trung bình', NULL),
(28, 2, 'Artifact', '/ˈɑː.tɪ.fækt/', 'Đồ tạo tác, cổ vật', 'Archaeologists found many valuable artifacts in the tomb.', 'Khó', NULL),
(29, 2, 'Inspire', '/ɪnˈspaɪər/', 'Truyền cảm hứng', 'Nature inspires many artists to create great works.', 'Dễ', NULL),
(30, 3, 'Investment', '/ɪnˈvest.mənt/', 'Sự đầu tư', 'Buying real estate is a good long-term investment.', 'Trung bình', NULL),
(31, 3, 'Profit', '/ˈprɒf.ɪt/', 'Lợi nhuận', 'The company made a huge profit this year.', 'Dễ', NULL),
(32, 3, 'Strategy', '/ˈstræt.ə.dʒi/', 'Chiến lược', 'We need a new marketing strategy to attract customers.', 'Trung bình', NULL),
(33, 3, 'Bankruptcy', '/ˈbæŋ.krəpt.si/', 'Sự phá sản', 'The firm went into bankruptcy due to poor management.', 'Khó', NULL),
(34, 3, 'Negotiation', '/nəˌɡəʊ.ʃiˈeɪ.ʃən/', 'Sự đàm phán', 'The negotiation between the two companies took days.', 'Khó', NULL),
(35, 3, 'Stock', '/stɒk/', 'Cổ phiếu', 'He lost a lot of money in the stock market.', 'Trung bình', NULL),
(36, 3, 'Entrepreneur', '/ˌɒn.trə.prəˈnɜːr/', 'Doanh nhân', 'Steve Jobs was a famous and visionary entrepreneur.', 'Khó', NULL),
(37, 3, 'Contract', '/ˈkɒn.trækt/', 'Hợp đồng', 'Please read the contract carefully before signing.', 'Dễ', NULL),
(38, 3, 'Interest', '/ˈɪn.trəst/', 'Lãi suất', 'The bank has increased its interest rates.', 'Trung bình', NULL),
(39, 3, 'Marketing', '/ˈmɑː.kɪ.tɪŋ/', 'Tiếp thị', 'Good marketing can boost sales significantly.', 'Dễ', NULL),
(40, 4, 'Evolution', '/ˌiː.vəˈluː.ʃən/', 'Sự tiến hóa', 'Darwin proposed the theory of evolution.', 'Trung bình', NULL),
(41, 4, 'Cell', '/sel/', 'Tế bào', 'Cells are the basic building blocks of all living things.', 'Dễ', NULL),
(42, 4, 'Molecule', '/ˈmɒl.ɪ.kjuːl/', 'Phân tử', 'A water molecule consists of two hydrogen atoms and one oxygen atom.', 'Khó', NULL),
(43, 4, 'Gravity', '/ˈɡræv.ə.ti/', 'Trọng lực', 'Gravity pulls objects toward the center of the Earth.', 'Trung bình', NULL),
(44, 4, 'Ecosystem', '/ˈiː.kəʊˌsɪs.təm/', 'Hệ sinh thái', 'Pollution is destroying the marine ecosystem.', 'Khó', NULL),
(45, 4, 'Experiment', '/ɪkˈsper.ɪ.mənt/', 'Cuộc thí nghiệm', 'The scientists are conducting a new chemistry experiment.', 'Trung bình', NULL),
(46, 4, 'Genetic', '/dʒəˈnet.ɪk/', 'Thuộc về di truyền', 'Eye color is a genetic trait.', 'Trung bình', NULL),
(47, 4, 'Radiation', '/ˌreɪ.diˈeɪ.ʃən/', 'Bức xạ', 'Exposure to high levels of radiation is dangerous.', 'Khó', NULL),
(48, 4, 'Microscope', '/ˈmaɪ.krə.skəʊp/', 'Kính hiển vi', 'We observe bacteria through a powerful microscope.', 'Trung bình', NULL),
(49, 4, 'Astronomy', '/əˈstrɒn.ə.mi/', 'Thiên văn học', 'She studies astronomy because she loves the stars.', 'Khó', NULL),
(50, 5, 'Destination', '/ˌdes.tɪˈneɪ.ʃən/', 'Điểm đến', 'Paris is a popular tourist destination.', 'Dễ', NULL),
(51, 5, 'Souvenir', '/ˌsuː.vənˈɪər/', 'Quà lưu niệm', 'I bought a keychain as a souvenir from my trip.', 'Trung bình', NULL),
(52, 5, 'Passport', '/ˈpɑːs.pɔːt/', 'Hộ chiếu', 'You need a valid passport to travel abroad.', 'Dễ', NULL),
(53, 5, 'Sightseeing', '/ˈsaɪtˌsiː.ɪŋ/', 'Ngắm cảnh', 'We spent the whole day sightseeing in London.', 'Trung bình', NULL),
(54, 5, 'Accommodation', '/əˌkɒm.əˈdeɪ.ʃən/', 'Chỗ ở', 'The price of the tour includes flights and accommodation.', 'Khó', NULL),
(55, 5, 'Journey', '/ˈdʒɜː.ni/', 'Cuộc hành trình', 'It was a long and exhausting journey across the desert.', 'Trung bình', NULL),
(56, 5, 'Expedition', '/ˌek.spəˈdɪʃ.ən/', 'Chuyến thám hiểm', 'They organized an expedition to the Amazon rainforest.', 'Khó', NULL),
(57, 5, 'Luggage', '/ˈlʌɡ.ɪdʒ/', 'Hành lý', 'Please keep your luggage with you at all times.', 'Dễ', NULL),
(58, 5, 'Guidebook', '/ˈɡaɪd.bʊk/', 'Sách hướng dẫn du lịch', 'I read the guidebook to find the best local restaurants.', 'Dễ', NULL),
(59, 5, 'Tourism', '/ˈtʊə.rɪ.zəm/', 'Ngành du lịch', 'Tourism is the main source of income for this island.', 'Trung bình', NULL),
(60, 6, 'Nutrition', '/njuːˈtrɪʃ.ən/', 'Dinh dưỡng', 'Good nutrition is essential for a healthy lifestyle.', 'Trung bình', NULL),
(61, 6, 'Disease', '/dɪˈziːz/', 'Bệnh tật', 'Smoking increases the risk of heart disease.', 'Trung bình', NULL),
(62, 6, 'Workout', '/ˈwɜːk.aʊt/', 'Tập luyện thể thao', 'He does a 30-minute workout every morning.', 'Dễ', NULL),
(63, 6, 'Surgery', '/ˈsɜː.dʒər.i/', 'Phẫu thuật', 'The patient is recovering quickly after the surgery.', 'Khó', NULL),
(64, 6, 'Therapy', '/ˈθer.ə.pi/', 'Liệu pháp điều trị', 'Physical therapy helped him walk again after the accident.', 'Khó', NULL),
(65, 6, 'Symptom', '/ˈsɪmp.təm/', 'Triệu chứng', 'Fever and a cough are common symptoms of the flu.', 'Trung bình', NULL),
(66, 6, 'Immune', '/ɪˈmjuːn/', 'Miễn dịch', 'Vitamin C helps boost your immune system.', 'Khó', NULL),
(67, 6, 'Medicine', '/ˈmed.ɪ.sən/', 'Thuốc men', 'Did you take your medicine this morning?', 'Dễ', NULL),
(68, 6, 'Vaccine', '/ˈvæk.siːn/', 'Vắc-xin', 'The new vaccine is highly effective against the virus.', 'Trung bình', NULL),
(69, 6, 'Calorie', '/ˈkæl.ər.i/', 'Calo (năng lượng)', 'Running burns a lot of calories.', 'Dễ', NULL),
(70, 7, 'Pollution', '/pəˈluː.ʃən/', 'Sự ô nhiễm', 'Air pollution is a major problem in big cities.', 'Dễ', NULL),
(71, 7, 'Conservation', '/ˌkɒn.səˈveɪ.ʃən/', 'Sự bảo tồn', 'Wildlife conservation is important to protect endangered species.', 'Khó', NULL),
(72, 7, 'Recycling', '/ˌriːˈsaɪ.klɪŋ/', 'Tái chế', 'Recycling plastic helps reduce waste.', 'Trung bình', NULL),
(73, 7, 'Climate', '/ˈklaɪ.mət/', 'Khí hậu', 'Climate change is causing sea levels to rise.', 'Dễ', NULL),
(74, 7, 'Wildlife', '/ˈwaɪld.laɪf/', 'Động vật hoang dã', 'The documentary is about the wildlife in Africa.', 'Trung bình', NULL),
(75, 7, 'Deforestation', '/diːˌfɒr.ɪˈsteɪ.ʃən/', 'Nạn phá rừng', 'Deforestation destroys the natural habitats of many animals.', 'Khó', NULL),
(76, 7, 'Renewable', '/rɪˈnjuː.ə.bəl/', 'Có thể tái tạo', 'Solar and wind are renewable energy sources.', 'Khó', NULL),
(77, 7, 'Atmosphere', '/ˈæt.mə.sfɪər/', 'Bầu khí quyển', 'The Earth’s atmosphere protects us from the sun’s rays.', 'Trung bình', NULL),
(78, 7, 'Habitat', '/ˈhæb.ɪ.tæt/', 'Môi trường sống', 'The panda’s natural habitat is the bamboo forest.', 'Trung bình', NULL),
(79, 7, 'Species', '/ˈspiː.ʃiːz/', 'Loài sinh vật', 'Many plant and animal species are in danger of extinction.', 'Khó', NULL),
(80, 8, 'Scholarship', '/ˈskɒl.ə.ʃɪp/', 'Học bổng', 'She won a full scholarship to study in the US.', 'Trung bình', NULL),
(81, 8, 'Assignment', '/əˈsaɪn.mənt/', 'Bài tập lớn / Nhiệm vụ', 'The students have to submit their math assignment by Friday.', 'Trung bình', NULL),
(82, 8, 'Curriculum', '/kəˈrɪk.jə.ləm/', 'Chương trình giảng dạy', 'Coding has been added to the primary school curriculum.', 'Khó', NULL),
(83, 8, 'Lecture', '/ˈlek.tʃər/', 'Bài giảng', 'The professor gave a fascinating lecture on history.', 'Trung bình', NULL),
(84, 8, 'Diploma', '/dɪˈpləʊ.mə/', 'Bằng cấp, chứng chỉ', 'He received his high school diploma last week.', 'Trung bình', NULL),
(85, 8, 'Campus', '/ˈkæm.pəs/', 'Khuôn viên trường đại học', 'The university campus is huge and very beautiful.', 'Dễ', NULL),
(86, 8, 'Graduate', '/ˈɡrædʒ.u.ət/', 'Tốt nghiệp / Sinh viên tốt nghiệp', 'After she graduates, she wants to become a teacher.', 'Dễ', NULL),
(87, 8, 'Plagiarism', '/ˈpleɪ.dʒər.ɪ.zəm/', 'Đạo văn', 'Plagiarism is strictly forbidden in academic writing.', 'Khó', NULL),
(88, 8, 'Tutor', '/ˈtjuː.tər/', 'Gia sư', 'His parents hired a tutor to help him with chemistry.', 'Trung bình', NULL),
(89, 8, 'Semester', '/sɪˈmes.tər/', 'Học kỳ', 'We have final exams at the end of the spring semester.', 'Dễ', NULL),
(90, 8, 'Hello', '/helo/', 'Xin chào', 'hello everyone.', 'Dễ', ''),
(253, 10, 'Fashion', '/ˈfæʃ.ən/', 'Thời trang, mốt', 'She follows the latest fashion trends.', 'Trung bình', NULL),
(254, 10, 'Style', '/staɪl/', 'Phong cách, kiểu dáng', 'He has a unique style of dressing.', 'Trung bình', NULL),
(255, 10, 'Designer', '/dɪˈzaɪ.nər/', 'Nhà thiết kế', 'She is a famous fashion designer.', 'Trung bình', NULL),
(256, 10, 'Elegant', '/ˈel.ɪ.ɡənt/', 'Thanh lịch, tao nhã', 'She wore an elegant dress to the party.', 'Khó', NULL),
(257, 10, 'Casual', '/ˈkæʒ.u.əl/', 'Thường ngày, không trang trọng', 'I prefer casual clothes on weekends.', 'Trung bình', NULL),
(258, 10, 'Accessory', '/əkˈses.ər.i/', 'Phụ kiện', 'Sunglasses are a great accessory.', 'Khó', NULL),
(259, 10, 'Trend', '/trend/', 'Xu hướng', 'This trend is popular among teenagers.', 'Trung bình', NULL),
(260, 10, 'Outfit', '/ˈaʊt.fɪt/', 'Trang phục, bộ đồ', 'She bought a new outfit for the party.', 'Trung bình', NULL),
(261, 10, 'Wardrobe', '/ˈwɔː.drəʊb/', 'Tủ quần áo', 'He has a huge wardrobe.', 'Trung bình', NULL),
(262, 10, 'Comfortable', '/ˈkʌm.fət.ə.bəl/', 'Thoải mái', 'These shoes are very comfortable.', 'Dễ', NULL),
(263, 10, 'Chic', '/ʃiːk/', 'Sành điệu, lịch sự', 'She has a chic and modern style.', 'Khó', NULL),
(264, 10, 'Vintage', '/ˈvɪn.tɪdʒ/', 'Cổ điển, vintage', 'I love vintage clothing.', 'Trung bình', NULL),
(265, 10, 'Tailor', '/ˈteɪ.lər/', 'Thợ may', 'He goes to a tailor for his suits.', 'Khó', NULL),
(266, 10, 'Dress', '/dres/', 'Váy, mặc quần áo', 'She dressed quickly and left.', 'Dễ', NULL),
(267, 11, 'Artificial Intelligence', '/ˌɑː.tɪ.fɪʃ.əl ɪnˈtel.ɪ.dʒəns/', 'Trí tuệ nhân tạo', 'AI is transforming many industries.', 'Khó', NULL),
(268, 11, 'Machine Learning', '/məˈʃiːn ˈlɜː.nɪŋ/', 'Học máy', 'Machine learning improves over time.', 'Khó', NULL),
(269, 11, 'Blockchain', '/ˈblɒk.tʃeɪn/', 'Chuỗi khối', 'Blockchain ensures secure transactions.', 'Khó', NULL),
(270, 11, 'Cybersecurity', '/ˌsaɪ.bə.sɪˈkʊə.rɪ.ti/', 'An ninh mạng', 'Cybersecurity is critical for businesses.', 'Khó', NULL),
(271, 11, 'Robotics', '/rəʊˈbɒt.ɪks/', 'Ngành robot', 'Robotics is advancing rapidly.', 'Khó', NULL),
(272, 11, 'Quantum', '/ˈkwɒn.təm/', 'Lượng tử', 'Quantum computers are the future.', 'Khó', NULL),
(273, 11, 'Virtual Reality', '/ˈvɜː.tʃu.əl riˈæl.ə.ti/', 'Thực tế ảo', 'Virtual reality creates immersive experiences.', 'Khó', NULL),
(274, 11, 'Augmented Reality', '/ɔːɡˈmen.tɪd riˈæl.ə.ti/', 'Thực tế tăng cường', 'AR adds digital elements to the real world.', 'Khó', NULL),
(275, 11, 'Internet of Things', '/ˈɪn.tə.net əv θɪŋz/', 'Vạn vật kết nối', 'IoT connects smart devices.', 'Khó', NULL),
(276, 11, 'Algorithm', '/ˈæl.ɡə.rɪ.ðəm/', 'Thuật toán', 'The algorithm sorts data efficiently.', 'Trung bình', NULL),
(277, 11, 'Data Science', '/ˈdeɪ.tə ˈsaɪ.əns/', 'Khoa học dữ liệu', 'Data science extracts insights from data.', 'Khó', NULL),
(278, 11, 'Cloud Computing', '/klaʊd kəmˈpjuː.tɪŋ/', 'Điện toán đám mây', 'Cloud computing offers scalable resources.', 'Khó', NULL),
(279, 11, 'Biometrics', '/ˌbaɪ.əʊˈmet.rɪks/', 'Sinh trắc học', 'Biometrics are used for security.', 'Khó', NULL),
(280, 11, 'Automation', '/ˌɔː.təˈmeɪ.ʃən/', 'Tự động hóa', 'Automation increases productivity.', 'Khó', NULL),
(281, 12, 'Journey', '/ˈdʒɜː.ni/', 'Hành trình', 'Their journey across the desert was tough.', 'Trung bình', NULL),
(282, 12, 'Adventure', '/ədˈven.tʃər/', 'Cuộc phiêu lưu', 'He loves adventure and outdoor activities.', 'Trung bình', NULL),
(283, 12, 'Explore', '/ɪkˈsplɔːr/', 'Khám phá', 'They want to explore the unknown lands.', 'Trung bình', NULL),
(284, 12, 'Destination', '/ˌdes.tɪˈneɪ.ʃən/', 'Điểm đến', 'Paris is a top destination for tourists.', 'Trung bình', NULL),
(285, 12, 'Wanderlust', '/ˈwɒn.də.lʌst/', 'Đam mê du lịch', 'She has a strong wanderlust.', 'Khó', NULL),
(286, 12, 'Safari', '/səˈfɑː.ri/', 'Chuyến săn bắn (châu Phi)', 'They went on a safari in Kenya.', 'Khó', NULL),
(287, 12, 'Expedition', '/ˌek.spəˈdɪʃ.ən/', 'Chuyến thám hiểm', 'The expedition to the North Pole was successful.', 'Khó', NULL),
(288, 12, 'Itinerary', '/aɪˈtɪn.ər.ər.i/', 'Lịch trình', 'Please check the itinerary for the trip.', 'Khó', NULL),
(289, 12, 'Accommodation', '/əˌkɒm.əˈdeɪ.ʃən/', 'Chỗ ở', 'We booked accommodation near the beach.', 'Khó', NULL),
(290, 12, 'Pack', '/pæk/', 'Đóng gói hành lý', 'I need to pack for the trip.', 'Dễ', NULL),
(291, 12, 'Backpacking', '/ˈbæk.pæk.ɪŋ/', 'Du lịch ba lô', 'Backpacking is popular among young travelers.', 'Trung bình', NULL),
(292, 12, 'Souvenir', '/ˌsuː.vənˈɪər/', 'Quà lưu niệm', 'I bought a souvenir from the museum.', 'Trung bình', NULL),
(293, 12, 'Guidebook', '/ˈɡaɪd.bʊk/', 'Sách hướng dẫn', 'The guidebook helped us find the best restaurants.', 'Trung bình', NULL),
(294, 12, 'Scenic', '/ˈsiː.nɪk/', 'Đẹp (cảnh quan)', 'The scenic route along the coast is amazing.', 'Trung bình', NULL);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `flashcard_cards`
--
ALTER TABLE `flashcard_cards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `set_id` (`set_id`);

--
-- Chỉ mục cho bảng `flashcard_sets`
--
ALTER TABLE `flashcard_sets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `folders`
--
ALTER TABLE `folders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `folder_items`
--
ALTER TABLE `folder_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `folder_id` (`folder_id`);

--
-- Chỉ mục cho bảng `game_answers`
--
ALTER TABLE `game_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Chỉ mục cho bảng `game_sessions`
--
ALTER TABLE `game_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type_id` (`type_id`),
  ADD KEY `typeword_id` (`typeword_id`),
  ADD KEY `vocabulary_id` (`vocabulary_id`);

--
-- Chỉ mục cho bảng `question_types`
--
ALTER TABLE `question_types`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `study_sessions`
--
ALTER TABLE `study_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `typeword`
--
ALTER TABLE `typeword`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Chỉ mục cho bảng `user_activity`
--
ALTER TABLE `user_activity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `vocabulary_id` (`vocabulary_id`);

--
-- Chỉ mục cho bảng `user_progress`
--
ALTER TABLE `user_progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `vocabulary_id` (`vocabulary_id`);

--
-- Chỉ mục cho bảng `vocabulary`
--
ALTER TABLE `vocabulary`
  ADD PRIMARY KEY (`id`),
  ADD KEY `typeword_id` (`typeword_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `flashcard_cards`
--
ALTER TABLE `flashcard_cards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `flashcard_sets`
--
ALTER TABLE `flashcard_sets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `folders`
--
ALTER TABLE `folders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `folder_items`
--
ALTER TABLE `folder_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `game_answers`
--
ALTER TABLE `game_answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `game_sessions`
--
ALTER TABLE `game_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `question_types`
--
ALTER TABLE `question_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `study_sessions`
--
ALTER TABLE `study_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `typeword`
--
ALTER TABLE `typeword`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `user_activity`
--
ALTER TABLE `user_activity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT cho bảng `user_progress`
--
ALTER TABLE `user_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT cho bảng `vocabulary`
--
ALTER TABLE `vocabulary`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=295;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `flashcard_cards`
--
ALTER TABLE `flashcard_cards`
  ADD CONSTRAINT `flashcard_cards_ibfk_1` FOREIGN KEY (`set_id`) REFERENCES `flashcard_sets` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `flashcard_sets`
--
ALTER TABLE `flashcard_sets`
  ADD CONSTRAINT `flashcard_sets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `folders`
--
ALTER TABLE `folders`
  ADD CONSTRAINT `folders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `folder_items`
--
ALTER TABLE `folder_items`
  ADD CONSTRAINT `folder_items_ibfk_1` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `game_answers`
--
ALTER TABLE `game_answers`
  ADD CONSTRAINT `game_answers_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `game_sessions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `game_answers_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `game_sessions`
--
ALTER TABLE `game_sessions`
  ADD CONSTRAINT `game_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `question_types` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `questions_ibfk_2` FOREIGN KEY (`typeword_id`) REFERENCES `typeword` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `questions_ibfk_3` FOREIGN KEY (`vocabulary_id`) REFERENCES `vocabulary` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `study_sessions`
--
ALTER TABLE `study_sessions`
  ADD CONSTRAINT `study_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `user_activity`
--
ALTER TABLE `user_activity`
  ADD CONSTRAINT `user_activity_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_activity_ibfk_2` FOREIGN KEY (`vocabulary_id`) REFERENCES `vocabulary` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `user_progress`
--
ALTER TABLE `user_progress`
  ADD CONSTRAINT `user_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_progress_ibfk_2` FOREIGN KEY (`vocabulary_id`) REFERENCES `vocabulary` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `vocabulary`
--
ALTER TABLE `vocabulary`
  ADD CONSTRAINT `vocabulary_ibfk_1` FOREIGN KEY (`typeword_id`) REFERENCES `typeword` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
