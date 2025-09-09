-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 08, 2025 at 07:37 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `nutrimind`
--

-- --------------------------------------------------------

--
-- Table structure for table `added_to`
--

CREATE TABLE `added_to` (
  `Member_id` int(50) NOT NULL,
  `music_id` int(50) NOT NULL,
  `playlist_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `added_to`
--

INSERT INTO `added_to` (`Member_id`, `music_id`, `playlist_id`) VALUES
(1, 123, 5),
(1, 129, 12),
(1, 125, 12),
(1, 113, 16),
(1, 123, 12),
(1, 196, 16),
(6, 113, 18),
(6, 196, 18),
(1, 131, 12),
(1, 131, 20),
(1, 135, 20),
(1, 138, 20),
(1, 151, 20),
(1, 187, 20),
(9, 121, 23),
(9, 123, 23),
(9, 125, 23),
(9, 129, 23);

-- --------------------------------------------------------

--
-- Table structure for table `Admin`
--

CREATE TABLE `Admin` (
  `Admin_id` int(50) NOT NULL,
  `Phone` int(15) NOT NULL,
  `email` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `Password_hash` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Admin`
--

INSERT INTO `Admin` (`Admin_id`, `Phone`, `email`, `name`, `Password_hash`) VALUES
(1, 1234567, 'abcd@gmail.com', 'Faiza', '123456');

-- --------------------------------------------------------

--
-- Table structure for table `blocked_users`
--

CREATE TABLE `blocked_users` (
  `member_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `checks`
--

CREATE TABLE `checks` (
  `Sleep_id` int(50) NOT NULL,
  `member_id` int(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `checks`
--

INSERT INTO `checks` (`Sleep_id`, `member_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 6),
(5, 1),
(6, 1),
(1, 1),
(2, 1),
(1, 1),
(2, 1),
(1, 1),
(2, 1),
(1, 1),
(2, 1),
(1, 1),
(2, 1),
(1, 1),
(2, 1),
(7, 1),
(1, 1),
(2, 1),
(1, 1),
(2, 1),
(1, 1),
(2, 1),
(1, 1),
(2, 1),
(3, 1),
(4, 6),
(5, 1),
(6, 1),
(1, 1),
(2, 1),
(1, 1),
(2, 1),
(1, 1),
(2, 1),
(1, 1),
(2, 1),
(1, 1),
(2, 1),
(1, 1),
(2, 1),
(8, 9);

-- --------------------------------------------------------

--
-- Table structure for table `consums`
--

CREATE TABLE `consums` (
  `Intake_id` int(50) NOT NULL,
  `member_id` int(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exercise`
--

CREATE TABLE `exercise` (
  `exercise_id` int(50) NOT NULL,
  `duration` int(30) NOT NULL,
  `types_of_exercise` varchar(255) NOT NULL,
  `calories_burned` int(50) NOT NULL,
  `reminder` varchar(255) NOT NULL,
  `time_data` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exercise`
--

INSERT INTO `exercise` (`exercise_id`, `duration`, `types_of_exercise`, `calories_burned`, `reminder`, `time_data`) VALUES
(1, 20, 'Treadmill (walk 5 km/h)', 95, 'A bit tired 😴', '2025-09-06'),
(1, 20, 'Walking (5.5 km/h brisk)', 95, 'Feeling great 😄', '2025-09-08'),
(6, 330, 'Walking (4 km/h)', 1419, '🔥 Amazing! You’ve hit today’s burn target. Recovery and hydration time. 💧', '0000-00-00'),
(7, 125, 'Walking (4 km/h)', 591, '💪 Good groove! ~300 kcal remaining. Plan one more session — you’ve got this!', '0000-00-00'),
(8, 25, 'Walking (4 km/h)', 105, 'Okay 🙂', '2025-09-02'),
(8, 10, 'Walking (4 km/h)', 42, '💪 Good groove! ~458 kcal remaining. Plan one more session — you’ve got this!', '2025-09-05'),
(9, 20, 'Running (8 km/h)', 180, 'Feeling great 😄', '2025-09-08');

-- --------------------------------------------------------

--
-- Table structure for table `food_allergen`
--

CREATE TABLE `food_allergen` (
  `allergen_id` int(50) NOT NULL,
  `member_id` int(11) NOT NULL,
  `food_name` varchar(120) NOT NULL,
  `reminder` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `food_allergen`
--

INSERT INTO `food_allergen` (`allergen_id`, `member_id`, `food_name`, `reminder`, `created_at`) VALUES
(8, 7, 'Duck Egg', '⚠️ Avoid Duck Egg, you are allergic to this food.', '2025-09-02 08:20:54'),
(9, 8, 'Prawn', '⚠️ Avoid Prawn, you are allergic to this food.', '2025-09-02 08:44:14'),
(10, 8, 'chicken', '⚠️ Avoid chicken, you are allergic to this food.', '2025-09-02 15:28:35'),
(11, 1, 'Lemon', '⚠️ Avoid Lemon, you are allergic to this food.', '2025-09-06 09:42:07'),
(12, 1, 'fish', '⚠️ Avoid fish, you are allergic to this food.', '2025-09-08 15:58:48');

-- --------------------------------------------------------

--
-- Table structure for table `manages`
--

CREATE TABLE `manages` (
  `Admin_id` int(50) NOT NULL,
  `member_id` int(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `meal`
--

CREATE TABLE `meal` (
  `Intake_id` int(50) NOT NULL,
  `member_id` int(11) UNSIGNED DEFAULT NULL,
  `log_date` date NOT NULL,
  `calories` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `protein` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `carbs` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `fats` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meal`
--

INSERT INTO `meal` (`Intake_id`, `member_id`, `log_date`, `calories`, `protein`, `carbs`, `fats`) VALUES
(3, 8, '2025-09-07', 1200, 600, 300, 300),
(7, 1, '2025-09-08', 5820, 693, 883, 429),
(14, 9, '2025-09-08', 1720, 68, 223, 57);

-- --------------------------------------------------------

--
-- Table structure for table `member`
--

CREATE TABLE `member` (
  `Member_id` int(50) NOT NULL,
  `Gender` varchar(10) NOT NULL,
  `Height_cm` int(10) NOT NULL,
  `First_name` varchar(100) NOT NULL,
  `Last_name` varchar(100) NOT NULL,
  `Normal` varchar(50) NOT NULL,
  `Overweight` varchar(50) NOT NULL,
  `Obese` varchar(50) NOT NULL,
  `Weight_kg` int(50) NOT NULL,
  `Goal_weight` int(50) DEFAULT NULL,
  `Birth_data` int(50) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Password_hash` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `member`
--

INSERT INTO `member` (`Member_id`, `Gender`, `Height_cm`, `First_name`, `Last_name`, `Normal`, `Overweight`, `Obese`, `Weight_kg`, `Goal_weight`, `Birth_data`, `Email`, `Password_hash`) VALUES
(1, 'Female', 154, 'Faiza', 'yakub', '', 'Yes', '', 63, 55, 2003, 'abc@gmail.com', '$2y$10$ICLI.BNKTAmKYeLiL4DKTO69RobAr6KRyN5uBqqjxB1hGe6ESxKXW'),
(2, 'Male', 160, 'Rubai', 'Mahmud', '', 'Yes', '', 65, NULL, 2000, 'bcd@gmail.com', '$2y$10$cF/HfTfPiYzUjznRAS5W1ukpkOpBHJqQmbmtbmJLi6gZKs3yHqUD2'),
(4, 'Female', 170, 'Sneha', 'Paul', '', 'Yes', '', 82, NULL, 2003, 'sneha04@gmail.com', '$2y$10$T0szhMbOIfIALMvfWzzG3elCdyYbyAPFA.IdUMVjW9JVr.druQJsi'),
(5, 'Male', 174, 'Sheikh', 'Tayim', '', 'Yes', '', 85, NULL, 2008, 'dac@gmail.com', '$2y$10$.h2cdC5ciEY18AwiOcSlwucydLOKCtSlbBmVOVCAg/3qcUUJNSzIq'),
(6, 'Female', 170, 'Shamim', 'Ara', '', 'Yes', '', 86, NULL, 1973, 'abcd@gmail.com', '$2y$10$3orgOP0R3QSLRUtCeKJDmOm7kmfHwLWqTIc0zbtLWAgBXWn95Qmsy'),
(9, 'Female', 160, 'shreya', 'michan', 'Yes', '', '', 62, 55, 2003, 'dbc@gmail.com', '$2y$10$mKPfzQIyzmMFAxsNyZRnXeHYraJXPtesTlvEcyR3o7OxUM/znh8Qy');

-- --------------------------------------------------------

--
-- Table structure for table `Music`
--

CREATE TABLE `Music` (
  `music_id` int(50) NOT NULL,
  `Music name` varchar(255) NOT NULL,
  `mood` varchar(255) NOT NULL,
  `artist_name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `Music_type` varchar(255) NOT NULL,
  `file_path` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Music`
--

INSERT INTO `Music` (`music_id`, `Music name`, `mood`, `artist_name`, `type`, `Music_type`, `file_path`) VALUES
(111, 'New Rules', 'chill', 'Dua Lia', 'audio/mpeg', 'mp3', '/uploads/music/y2mate--Dua-Lipa-New-Rules-Official-Music-Video.mp3'),
(113, 'Rain Sounds ASMR ', 'Sleep', 'N/A', 'audio/mpeg', 'mp3', '/uploads/music/y2mate--Rain-Sounds-ASMR-Heavy-Rain-on-Window-30.mp3'),
(115, 'It\'s My Life ', 'Sad', 'Bon Jovi', 'audio/mpeg', 'mp3', '/uploads/music/y2mate--Bon-Jovi-It-s-My-Life-Official-Music-Video.mp3'),
(117, 'I Think They Call This Love', 'Romantic', 'Elliot James Reay', 'audio/mpeg', 'mp3', '/uploads/music/y2mate--Elliot-James-Reay-I-Think-They-Call-This-Love.mp3'),
(120, 'Photograph', 'Sad', 'Ed Sheeran', 'audio/mpeg', 'mp3', '/uploads/music/y2mate--Ed-Sheeran-Photograph-Official-Music-Video.mp3'),
(121, 'Bella Ciao', 'Happy/Chill', 'Becky G', 'audio/mpeg', 'mp3', '/uploads/music/y2mate--Becky-G-Bella-Ciao-Official-Video.mp3'),
(123, 'Die With A Smile', 'Happy/Romantic', 'Lady Gaga & Bruno Mars', 'audio/mpeg', 'mp3', '/uploads/music/y2mate--Lady-Gaga-Bruno-Mars-Die-With-A-Smile-Official.mp3'),
(125, 'Line Without a Hook ', 'Romantic/Happy', 'Ricky Montgomery ', 'audio/mpeg', 'mp3', '/uploads/music/y2mate--Ricky-Montgomery-Line-Without-a-Hook-Official-Music-Video.mp3'),
(127, 'Katy Perry Part Of Me', 'Sad', 'Katy Perry ', 'audio/mpeg', 'mp3', '/uploads/music/y2mate--Katy-Perry-Part-Of-Me-Official.mp3'),
(129, 'Lover', 'Happy', 'Taylor Swift', 'audio/mpeg', 'mp3', '/uploads/music/y2mate--Taylor-Swift-Lover-Official-Music-Video.mp3'),
(131, 'Pretty Girl ', 'Energetic/Workout', 'Maggie Lindemann', 'audio/mpeg', 'mp3', '/uploads/music/y2mate--Maggie-Lindemann-Pretty-Girl-Official-Music-Video.mp3'),
(133, 'They Don t Care About Us ', 'Workout', 'Michael Jackson ', 'audio/mpeg', 'mp3', '/uploads/music/y2mate--Michael-Jackson-They-Don-t-Care-About-Us-Brazil-Version.mp3'),
(135, 'APT', 'Chill/Workout/Energetic', 'ROSE & Bruno Mars', 'audio/mpeg', 'mp3', '/uploads/music/y2mate--ROSE-Bruno-Mars-APT-Official-Music-Video.mp3'),
(138, 'Left And Right feat Jung Kook', 'Chill/Energetic/Workout', 'Charlie Puth & feat Jung Kook', 'audio/mpeg', 'mp3', '/uploads/music/y2mate--Charlie-Puth-Left-And-Right-feat-Jung-Kook-of.mp3'),
(139, 'Co2', 'Romantic', ' Prateek Kuhad', 'audio/mpeg', 'mp3', '/uploads/music/y2mate--Prateek-Kuhad-Co2-Official-Audio.mp3'),
(143, 'Zara Zara Bahekta Hai', 'Romantic', 'JalRaj RHTDM ', 'audio/mpeg', 'mp3', '/uploads/music/y2mate--Zara-Zara-Bahekta-Hai-JalRaj-RHTDM-Male.mp3'),
(145, 'Sapphire ', 'Workout', ' Ed Sheeran ', 'audio/mpeg', 'mp3', '/uploads/music/y2mate--Ed-Sheeran-Sapphire-Official-Music-Video.mp3'),
(147, 'LEVEL FIVE TUMI ', 'Chill', 'LEVEL FIVE', 'audio/mpeg', 'mp3', '/uploads/music/y2mate--LEVEL-FIVE-TUMI-Official-Lyric-Video.mp3'),
(149, 'Steal My Girl', 'Chill', 'One Direction', 'audio/mpeg', 'mp3', '/uploads/music/y2mate--One-Direction-Steal-My-Girl.mp3'),
(151, 'Uptown Funk ', 'Workout/Energetic', 'Mark Ronson ', 'audio/mpeg', 'mp3', '/uploads/music/y2mate--Mark-Ronson-Uptown-Funk-Official-Video-ft-Bruno-Mars.mp3'),
(183, 'See You Again ', 'Sad', 'Wiz Khalifa ft Charlie Puth', 'audio/mpeg', 'mp3', '/uploads/music/y2mate--Wiz-Khalifa-See-You-Again-ft-Charlie-Puth-Official.mp3'),
(187, 'Sapphire', 'Energetic/Workout', 'ft Charlie Puth', 'audio/mpeg', 'mp3', '/uploads/music/y2mate--Ed-Sheeran-Sapphire-Official-Music-Video.mp3'),
(195, 'Main Rahun', 'Romantic', 'Samar Jafri', 'audio/mpeg', 'mp3', '/uploads/music/y2mate--Main-Rahun-From-Parwarish-Original-Motion-Picture-Soundtrack.mp3'),
(196, 'Deep Sleep in 10min', 'Sleep', 'Musicby Aishwarya Tripathi', 'audio/mpeg', 'mp3', '/uploads/music/y2mate--Deep-Sleep-in-10-Minutes-Sleep-Music-Relaxing-Music-Peaceful-Music-Sivananda.mp3');

-- --------------------------------------------------------

--
-- Table structure for table `MusicFiles`
--

CREATE TABLE `MusicFiles` (
  `id` int(11) NOT NULL,
  `music_id` int(11) NOT NULL,
  `data` longblob NOT NULL,
  `mime` varchar(50) DEFAULT 'audio/mpeg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `performs`
--

CREATE TABLE `performs` (
  `exercise_id` int(50) NOT NULL,
  `member_id` int(50) NOT NULL,
  `daily` varchar(255) NOT NULL,
  `ocassionaly` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `plan_bulking`
--

CREATE TABLE `plan_bulking` (
  `id` int(10) UNSIGNED NOT NULL,
  `day_no` tinyint(3) UNSIGNED NOT NULL,
  `focus` varchar(80) NOT NULL,
  `exercise` varchar(120) NOT NULL,
  `sets` tinyint(3) UNSIGNED DEFAULT NULL,
  `reps` varchar(20) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `plan_bulking`
--

INSERT INTO `plan_bulking` (`id`, `day_no`, `focus`, `exercise`, `sets`, `reps`, `notes`) VALUES
(1, 1, 'Upper Heavy', 'Bench Press', 5, '5', 'Main lift'),
(2, 1, 'Upper Heavy', 'Barbell Row', 5, '5', 'Main lift'),
(3, 1, 'Upper Heavy', 'Overhead Press', 4, '5-6', 'Accessory'),
(4, 1, 'Upper Heavy', 'Weighted Pull-ups', 4, '5-8', 'Accessory'),
(5, 2, 'Lower Heavy', 'Back Squat', 5, '5', 'Main lift'),
(6, 2, 'Lower Heavy', 'Deadlift (conv/sumo)', 3, '3', 'Main lift'),
(7, 2, 'Lower Heavy', 'Calf Raises', 4, '10-15', 'Accessory'),
(8, 2, 'Lower Heavy', 'Hanging Leg Raises', 3, '12-15', 'Core'),
(9, 3, 'Active', 'Walk / Mobility', 1, NULL, 'Active recovery'),
(10, 4, 'Upper Volume', 'Incline DB Press', 4, '8-10', 'Hypertrophy'),
(11, 4, 'Upper Volume', 'Lat Pulldown', 4, '8-10', 'Hypertrophy'),
(12, 4, 'Upper Volume', 'Chest Flyes', 3, '12-15', 'Pump'),
(13, 4, 'Upper Volume', 'Bicep Curls', 3, '12-15', 'Pump'),
(14, 5, 'Lower Volume', 'Front Squat', 4, '6-8', 'Hypertrophy'),
(15, 5, 'Lower Volume', 'Romanian Deadlift', 4, '6-8', 'Hypertrophy'),
(16, 5, 'Lower Volume', 'Lunges', 3, '10-12/leg', 'Accessory'),
(17, 5, 'Lower Volume', 'Leg Press', 3, '12-15', 'Accessory'),
(18, 6, 'Arms & Shoulders', 'Overhead Press', 4, '6-8', 'Strength'),
(19, 6, 'Arms & Shoulders', 'Close-Grip Bench', 4, '8-10', 'Triceps'),
(20, 6, 'Arms & Shoulders', 'Lateral Raises', 4, '12-15', 'Delts'),
(21, 6, 'Arms & Shoulders', 'Skull Crushers', 3, '10-12', 'Triceps'),
(22, 7, 'Rest', 'Mobility / Meal Prep', 1, NULL, 'Plan next week');

-- --------------------------------------------------------

--
-- Table structure for table `plan_muscle_gain`
--

CREATE TABLE `plan_muscle_gain` (
  `id` int(10) UNSIGNED NOT NULL,
  `day_no` tinyint(3) UNSIGNED NOT NULL,
  `focus` varchar(80) NOT NULL,
  `exercise` varchar(120) NOT NULL,
  `sets` tinyint(3) UNSIGNED DEFAULT NULL,
  `reps` varchar(20) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `plan_muscle_gain`
--

INSERT INTO `plan_muscle_gain` (`id`, `day_no`, `focus`, `exercise`, `sets`, `reps`, `notes`) VALUES
(1, 1, 'Push', 'Bench Press', 4, '6-8', 'Progressive overload'),
(2, 1, 'Push', 'Overhead Press', 3, '6-8', 'Keep core tight'),
(3, 1, 'Push', 'Incline DB Press', 3, '8-10', 'Controlled tempo'),
(4, 2, 'Pull', 'Pull-ups / Lat Pulldown', 4, '6-10', 'Full ROM'),
(5, 2, 'Pull', 'Barbell Row', 4, '6-8', 'Neutral spine'),
(6, 2, 'Pull', 'DB Curls', 3, '10-12', 'Squeeze at top'),
(7, 3, 'Legs+Core', 'Back Squat', 4, '6-8', 'Depth to parallel'),
(8, 3, 'Legs+Core', 'Romanian Deadlift', 4, '6-8', 'Hamstring focus'),
(9, 3, 'Legs+Core', 'Walking Lunges', 3, '10-12/leg', 'Slow steps'),
(10, 4, 'Recovery', 'Yoga / Walk', 1, NULL, 'Active recovery'),
(11, 5, 'Upper Strength', 'Bench Press', 5, '5', 'Heavier load'),
(12, 5, 'Upper Strength', 'Row (Barbell)', 5, '5', 'Strict form'),
(13, 6, 'Lower Strength', 'Deadlift', 3, '3-5', 'Heavy triples'),
(14, 6, 'Lower Strength', 'Front/Back Squat', 4, '5-6', 'Choose one'),
(15, 7, 'Rest', 'Mobility/Stretch', 1, NULL, 'Prep next week');

-- --------------------------------------------------------

--
-- Table structure for table `plan_weight_loss`
--

CREATE TABLE `plan_weight_loss` (
  `id` int(10) UNSIGNED NOT NULL,
  `day_no` tinyint(3) UNSIGNED NOT NULL,
  `exercise` varchar(120) NOT NULL,
  `duration_min` tinyint(3) UNSIGNED DEFAULT NULL,
  `sets` tinyint(3) UNSIGNED DEFAULT NULL,
  `reps` varchar(20) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `plan_weight_loss`
--

INSERT INTO `plan_weight_loss` (`id`, `day_no`, `exercise`, `duration_min`, `sets`, `reps`, `notes`) VALUES
(1, 1, 'Brisk Walking', 30, NULL, NULL, 'Warm-up week, easy pace'),
(2, 1, 'Jump Rope', 10, NULL, NULL, 'Short bursts'),
(3, 2, 'Cycling (moderate)', 40, NULL, NULL, 'Steady pace'),
(4, 3, 'Yoga + Core', 35, NULL, NULL, 'Mobility & recovery'),
(5, 4, 'Interval Running', 25, NULL, NULL, 'Alt. 1 min jog / 30s sprint'),
(6, 5, 'Swimming', 35, NULL, NULL, 'Full-body, low impact'),
(7, 6, 'Light Hike / Brisk Walk', 45, NULL, NULL, 'Keep HR in fat-burn zone'),
(8, 7, 'Rest / Light Stretch', 20, NULL, NULL, 'Active recovery');

-- --------------------------------------------------------

--
-- Table structure for table `Playlist`
--

CREATE TABLE `Playlist` (
  `Playlist_id` int(11) NOT NULL,
  `Playlist_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Playlist`
--

INSERT INTO `Playlist` (`Playlist_id`, `Playlist_name`) VALUES
(1, 'hapihapi'),
(2, 'hapihapi'),
(3, 'rain'),
(4, 'Bluebirds'),
(5, 'Bluebirds'),
(6, 'Bluebirds'),
(7, 'hapihapi'),
(8, 'hapihapi'),
(9, 'hapihapi'),
(10, 'hapihapi'),
(12, 'hapihapi'),
(13, 'Relax'),
(14, 'My Playlist'),
(16, 'relax'),
(17, 'Sleep_music'),
(18, 'Sleep_music'),
(19, 'chillbill'),
(20, 'chillbill'),
(21, ':D blabla'),
(22, 'michan'),
(23, 'michan');

-- --------------------------------------------------------

--
-- Table structure for table `Plays_during`
--

CREATE TABLE `Plays_during` (
  `music_id` int(50) NOT NULL,
  `exercise_id` int(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Sleep_track`
--

CREATE TABLE `Sleep_track` (
  `Sleep_id` int(50) NOT NULL,
  `Sleep_quality` varchar(255) NOT NULL,
  `time` int(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Sleep_track`
--

INSERT INTO `Sleep_track` (`Sleep_id`, `Sleep_quality`, `time`) VALUES
(1, 'Good', 6),
(2, 'Excellent', 8),
(3, 'Good', 6),
(4, 'Fair', 4),
(5, 'Fair', 6),
(6, 'Over slept', 10),
(7, 'Excellent', 7),
(8, 'Fair', 5);

-- --------------------------------------------------------

--
-- Table structure for table `water`
--

CREATE TABLE `water` (
  `intake_id` int(11) UNSIGNED NOT NULL,
  `member_id` int(11) UNSIGNED NOT NULL,
  `log_date` date NOT NULL,
  `reminder` varchar(255) NOT NULL,
  `target_intake` int(11) UNSIGNED NOT NULL,
  `water_intake` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `water`
--

INSERT INTO `water` (`intake_id`, `member_id`, `log_date`, `reminder`, `target_intake`, `water_intake`) VALUES
(1, 7, '2025-09-01', 'None', 2415, 7506),
(2, 7, '2025-09-02', 'None', 2415, 0),
(3, 8, '2025-09-02', 'None', 2800, 100),
(4, 8, '2025-09-05', 'None', 2800, 0),
(5, 8, '2025-09-06', 'None', 2800, 100),
(6, 1, '2025-09-06', 'Every 2 hours', 1955, 800),
(7, 1, '2025-09-08', 'Every 2 hours', 1955, 1000),
(8, 9, '2025-09-08', 'Hourly (light)', 2170, 2000);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `added_to`
--
ALTER TABLE `added_to`
  ADD KEY `Member_id` (`Member_id`),
  ADD KEY `music_id` (`music_id`),
  ADD KEY `playlist_id` (`playlist_id`);

--
-- Indexes for table `Admin`
--
ALTER TABLE `Admin`
  ADD PRIMARY KEY (`Admin_id`);

--
-- Indexes for table `blocked_users`
--
ALTER TABLE `blocked_users`
  ADD PRIMARY KEY (`member_id`);

--
-- Indexes for table `checks`
--
ALTER TABLE `checks`
  ADD KEY `Sleep_id` (`Sleep_id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `consums`
--
ALTER TABLE `consums`
  ADD KEY `Intake_id` (`Intake_id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `exercise`
--
ALTER TABLE `exercise`
  ADD PRIMARY KEY (`exercise_id`,`time_data`);

--
-- Indexes for table `food_allergen`
--
ALTER TABLE `food_allergen`
  ADD PRIMARY KEY (`allergen_id`);

--
-- Indexes for table `manages`
--
ALTER TABLE `manages`
  ADD KEY `Admin_id` (`Admin_id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `meal`
--
ALTER TABLE `meal`
  ADD PRIMARY KEY (`Intake_id`),
  ADD UNIQUE KEY `uniq_member_date` (`member_id`,`log_date`),
  ADD KEY `idx_meal_member_id` (`member_id`),
  ADD KEY `idx_meal_log_date` (`log_date`);

--
-- Indexes for table `member`
--
ALTER TABLE `member`
  ADD PRIMARY KEY (`Member_id`);

--
-- Indexes for table `Music`
--
ALTER TABLE `Music`
  ADD PRIMARY KEY (`music_id`);

--
-- Indexes for table `MusicFiles`
--
ALTER TABLE `MusicFiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `music_id` (`music_id`);

--
-- Indexes for table `plan_bulking`
--
ALTER TABLE `plan_bulking`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `plan_muscle_gain`
--
ALTER TABLE `plan_muscle_gain`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `plan_weight_loss`
--
ALTER TABLE `plan_weight_loss`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Playlist`
--
ALTER TABLE `Playlist`
  ADD PRIMARY KEY (`Playlist_id`);

--
-- Indexes for table `Plays_during`
--
ALTER TABLE `Plays_during`
  ADD KEY `music_id` (`music_id`),
  ADD KEY `plays_during_ibfk_2` (`exercise_id`);

--
-- Indexes for table `Sleep_track`
--
ALTER TABLE `Sleep_track`
  ADD PRIMARY KEY (`Sleep_id`);

--
-- Indexes for table `water`
--
ALTER TABLE `water`
  ADD PRIMARY KEY (`intake_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `food_allergen`
--
ALTER TABLE `food_allergen`
  MODIFY `allergen_id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `meal`
--
ALTER TABLE `meal`
  MODIFY `Intake_id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `member`
--
ALTER TABLE `member`
  MODIFY `Member_id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `Music`
--
ALTER TABLE `Music`
  MODIFY `music_id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=197;

--
-- AUTO_INCREMENT for table `MusicFiles`
--
ALTER TABLE `MusicFiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `plan_bulking`
--
ALTER TABLE `plan_bulking`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `plan_muscle_gain`
--
ALTER TABLE `plan_muscle_gain`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `plan_weight_loss`
--
ALTER TABLE `plan_weight_loss`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `Playlist`
--
ALTER TABLE `Playlist`
  MODIFY `Playlist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `Sleep_track`
--
ALTER TABLE `Sleep_track`
  MODIFY `Sleep_id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `water`
--
ALTER TABLE `water`
  MODIFY `intake_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `added_to`
--
ALTER TABLE `added_to`
  ADD CONSTRAINT `added_to_ibfk_1` FOREIGN KEY (`Member_id`) REFERENCES `member` (`Member_id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `added_to_ibfk_2` FOREIGN KEY (`music_id`) REFERENCES `Music` (`music_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `added_to_ibfk_3` FOREIGN KEY (`playlist_id`) REFERENCES `Playlist` (`Playlist_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `checks`
--
ALTER TABLE `checks`
  ADD CONSTRAINT `checks_ibfk_1` FOREIGN KEY (`Sleep_id`) REFERENCES `Sleep_track` (`Sleep_id`),
  ADD CONSTRAINT `checks_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `member` (`Member_id`);

--
-- Constraints for table `consums`
--
ALTER TABLE `consums`
  ADD CONSTRAINT `consums_ibfk_1` FOREIGN KEY (`Intake_id`) REFERENCES `meal` (`Intake_id`),
  ADD CONSTRAINT `consums_ibfk_2` FOREIGN KEY (`Intake_id`) REFERENCES `meal` (`Intake_id`),
  ADD CONSTRAINT `consums_ibfk_3` FOREIGN KEY (`member_id`) REFERENCES `member` (`Member_id`);

--
-- Constraints for table `MusicFiles`
--
ALTER TABLE `MusicFiles`
  ADD CONSTRAINT `musicfiles_ibfk_1` FOREIGN KEY (`music_id`) REFERENCES `Music` (`music_id`) ON DELETE CASCADE;

--
-- Constraints for table `Plays_during`
--
ALTER TABLE `Plays_during`
  ADD CONSTRAINT `plays_during_ibfk_1` FOREIGN KEY (`music_id`) REFERENCES `Music` (`music_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `plays_during_ibfk_2` FOREIGN KEY (`exercise_id`) REFERENCES `exercise` (`exercise_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
