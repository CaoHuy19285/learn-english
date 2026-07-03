-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Máy chủ: localhost
-- Thời gian đã tạo: Th7 03, 2026 lúc 08:26 PM
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
-- Cấu trúc bảng cho bảng `nguoidung`
--

DROP TABLE IF EXISTS `nguoidung`;
CREATE TABLE `nguoidung` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(10) DEFAULT 'user',
  `xp` int(11) DEFAULT 0,
  `streak` int(11) DEFAULT 0,
  `last_study_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Đang đổ dữ liệu cho bảng `nguoidung`
--

INSERT INTO `nguoidung` (`id`, `username`, `password`, `role`, `xp`, `streak`, `last_study_date`) VALUES
(1, 'admin', '123456', 'admin', 400, 7, '2026-07-02 14:20:46'),
(2, 'hocvien', '123456', 'user', 120, 1, '2026-07-01 14:20:46');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `typeword`
--

DROP TABLE IF EXISTS `typeword`;
CREATE TABLE `typeword` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `color_theme` varchar(30) DEFAULT 'purple'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Đang đổ dữ liệu cho bảng `typeword`
--

INSERT INTO `typeword` (`id`, `name`, `slug`, `description`, `color_theme`) VALUES
(1, 'IT & Tech', 'it-tech', 'Từ vựng chuyên ngành Công nghệ thông tin', 'purple'),
(2, 'Arts & Culture', 'arts-culture', 'Nghệ thuật, hội họa và các nét văn hóa', 'pink'),
(3, 'Business', 'business', 'Kinh tế thương mại và tài chính doanh nghiệp', 'green'),
(4, 'Science', 'science', 'Các thuật ngữ khoa học đời sống và vũ trụ', 'indigo'),
(5, 'Travel & Culture', 'travel-culture', 'Hành trình khám phá và giao lưu du lịch', 'orange'),
(6, 'Health & Fitness', 'health-fitness', 'Từ vựng về sức khỏe, y tế và thể hình', 'pink'),
(7, 'Environment', 'environment', 'Bảo vệ môi trường và thế giới tự nhiên', 'green'),
(8, 'Education', 'education', 'Chủ đề trường học, giáo dục và học thuật', 'indigo');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'admin', '123456', 'admin', '2026-07-01 22:43:27'),
(2, 'user', '123456', 'user', '2026-07-01 22:43:27');

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
(9, 1, 'API', '/ˌeɪ.piˈaɪ/', 'Giao diện lập trình', 'Connect via public API.', 'easy', 'https://cdn-icons-png.flaticon.com/512/8155/8155554.png'),
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
(89, 8, 'Semester', '/sɪˈmes.tər/', 'Học kỳ', 'We have final exams at the end of the spring semester.', 'Dễ', NULL);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `nguoidung`
--
ALTER TABLE `nguoidung`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Chỉ mục cho bảng `typeword`
--
ALTER TABLE `typeword`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

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
-- AUTO_INCREMENT cho bảng `nguoidung`
--
ALTER TABLE `nguoidung`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `typeword`
--
ALTER TABLE `typeword`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `vocabulary`
--
ALTER TABLE `vocabulary`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `vocabulary`
--
ALTER TABLE `vocabulary`
  ADD CONSTRAINT `vocabulary_ibfk_1` FOREIGN KEY (`typeword_id`) REFERENCES `typeword` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
