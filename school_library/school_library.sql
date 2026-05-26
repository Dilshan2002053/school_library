-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 29, 2026 at 07:24 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `school_library`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_issue_book` (IN `p_user_id` INT, IN `p_book_id` INT, IN `p_due_date` DATE)   BEGIN
  START TRANSACTION;

  INSERT INTO loans(book_id, user_id, issue_date, due_date, status)
  VALUES(p_book_id, p_user_id, CURDATE(), p_due_date, 'issued');

  COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_return_book` (IN `p_loan_id` INT)   BEGIN
  DECLARE d DATE;
  DECLARE late_days INT DEFAULT 0;

  SELECT due_date INTO d
  FROM loans
  WHERE id = p_loan_id;

  SET late_days = DATEDIFF(CURDATE(), d);
  IF late_days < 0 THEN SET late_days = 0; END IF;

  UPDATE loans
  SET return_date = CURDATE(),
      status = 'returned',
      fine = late_days * 10
  WHERE id = p_loan_id;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `isbn` varchar(30) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `author` varchar(150) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `publisher` varchar(150) DEFAULT NULL,
  `publish_year` int(11) DEFAULT NULL,
  `shelf` varchar(30) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `copies_total` int(11) NOT NULL DEFAULT 1,
  `copies_available` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `price` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `isbn`, `title`, `author`, `image_url`, `publisher`, `publish_year`, `shelf`, `category_id`, `copies_total`, `copies_available`, `created_at`, `price`) VALUES
(1, '9780001', 'Science Basics', 'A. Perera', NULL, 'School Press', 2018, 'S-01', 1, 5, 5, '2026-01-29 09:07:38', 1200.00),
(2, '9780002', 'Human Biology', 'N. Silva', NULL, 'Edu Lanka', 2019, 'S-02', 1, 3, 3, '2026-01-29 09:07:38', 950.00),
(3, '9780003', 'Physics for Kids', 'T. Fernando', NULL, 'Young Minds', 2017, 'S-03', 1, 4, 4, '2026-01-29 09:07:38', 1800.00),
(4, '9780004', 'Chemistry Simple', 'R. Jayasuriya', NULL, 'Edu Lanka', 2020, 'S-04', 1, 2, 2, '2026-01-29 09:07:38', 750.00),
(5, '9780005', 'Math Grade 10', 'D. Gunasekara', NULL, 'School Press', 2021, 'M-01', 2, 6, 5, '2026-01-29 09:07:38', 2100.00),
(6, '9780006', 'Algebra Easy', 'S. Nimal', NULL, 'MathWorld', 2018, 'M-02', 2, 4, 4, '2026-01-29 09:07:38', 2500.00),
(7, '9780007', 'Geometry Guide', 'P. Senanayake', NULL, 'MathWorld', 2016, 'M-03', 2, 3, 3, '2026-01-29 09:07:38', 2178.00),
(8, '9780008', 'Statistics Intro', 'K. Weerasinghe', NULL, 'Uni Print', 2022, 'M-04', 2, 2, 2, '2026-01-29 09:07:38', 2100.00),
(9, '9780009', 'Sri Lanka History', 'H. Perera', NULL, 'National Pub', 2015, 'H-01', 3, 5, 5, '2026-01-29 09:07:38', 5100.00),
(10, '9780010', 'World History', 'M. Silva', NULL, 'National Pub', 2014, 'H-02', 3, 2, 2, '2026-01-29 09:07:38', 600.00),
(11, '9780011', 'Ancient Kingdoms', 'J. Bandara', NULL, 'National Pub', 2013, 'H-03', 3, 2, 2, '2026-01-29 09:07:38', 2100.00),
(12, '9780012', 'The Lost Island', 'S. Wickrama', NULL, 'Novel House', 2020, 'N-01', 4, 4, 4, '2026-01-29 09:07:38', 0.00),
(13, '9780013', 'Blue Sky Story', 'K. Dissanayake', NULL, 'Novel House', 2019, 'N-02', 4, 3, 3, '2026-01-29 09:07:38', 0.00),
(14, '9780014', 'Adventure Night', 'I. Perera', NULL, 'Novel House', 2018, 'N-03', 4, 2, 2, '2026-01-29 09:07:38', 0.00),
(15, '9780015', 'ICT Fundamentals', 'R. Fernando', NULL, 'Tech Pub', 2022, 'I-01', 5, 6, 6, '2026-01-29 09:07:38', 0.00),
(16, 'ththfv', 'fSA', 'xZ', NULL, 'hij', 32, 'jij', 2, 1, 1, '2026-01-29 14:04:16', 4000.00);

-- --------------------------------------------------------

--
-- Table structure for table `book_orders`
--

CREATE TABLE `book_orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 1,
  `status` enum('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved_at` timestamp NULL DEFAULT NULL,
  `admin_note` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `book_purchases`
--

CREATE TABLE `book_purchases` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 1,
  `price_each` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `purchased_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book_purchases`
--

INSERT INTO `book_purchases` (`id`, `user_id`, `book_id`, `qty`, `price_each`, `total_amount`, `purchased_at`) VALUES
(8, 11, 3, 3, 1500.00, 4500.00, '2026-01-29 18:08:34');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(3, 'History'),
(5, 'ICT'),
(2, 'Math'),
(4, 'Novel'),
(1, 'Science');

-- --------------------------------------------------------

--
-- Table structure for table `loans`
--

CREATE TABLE `loans` (
  `id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `status` enum('issued','returned','overdue') NOT NULL DEFAULT 'issued',
  `fine` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loans`
--

INSERT INTO `loans` (`id`, `book_id`, `user_id`, `issue_date`, `due_date`, `return_date`, `status`, `fine`) VALUES
(1, 5, 2, '2026-01-29', '2026-02-05', NULL, 'issued', 0.00),
(2, 9, 3, '2026-01-29', '2026-02-08', NULL, 'returned', 0.00),
(4, 12, 5, '2026-01-14', '2026-01-22', '2026-01-29', 'overdue', 80.00);

--
-- Triggers `loans`
--
DELIMITER $$
CREATE TRIGGER `trg_after_loan_insert` AFTER INSERT ON `loans` FOR EACH ROW BEGIN
  UPDATE books
  SET copies_available = copies_available - 1
  WHERE id = NEW.book_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_loan_update` AFTER UPDATE ON `loans` FOR EACH ROW BEGIN
  IF OLD.status <> 'returned' AND NEW.status = 'returned' THEN
    UPDATE books
    SET copies_available = copies_available + 1
    WHERE id = NEW.book_id;
  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_before_loan_insert` BEFORE INSERT ON `loans` FOR EACH ROW BEGIN
  DECLARE avail INT;
  SELECT copies_available INTO avail
  FROM books
  WHERE id = NEW.book_id;

  IF avail IS NULL OR avail <= 0 THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Book not available right now.';
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `status` enum('active','blocked') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password_hash`, `role`, `status`, `created_at`) VALUES
(1, 'System Admin', 'admin@school.lk', '$2y$10$O7Yb1s4t9Wf2uQjQe9u9OOJk9S7yB0x0tX4L2Cjz6mJt5q6qQYc3W', 'admin', 'active', '2026-01-29 09:07:38'),
(2, 'Kavindu Perera', 'kavindu@gmail.com', '$2y$10$O7Yb1s4t9Wf2uQjQe9u9OOJk9S7yB0x0tX4L2Cjz6mJt5q6qQYc3W', 'user', 'active', '2026-01-29 09:07:38'),
(3, 'Sanduni Silva', 'sanduni@gmail.com', '$2y$10$O7Yb1s4t9Wf2uQjQe9u9OOJk9S7yB0x0tX4L2Cjz6mJt5q6qQYc3W', 'user', 'active', '2026-01-29 09:07:38'),
(5, 'Tharindu Fernando', 'tharindu@gmail.com', '$2y$10$O7Yb1s4t9Wf2uQjQe9u9OOJk9S7yB0x0tX4L2Cjz6mJt5q6qQYc3W', 'user', 'blocked', '2026-01-29 09:07:38'),
(11, 'INDUNIL SAMPATH', 'isbandara1022@gmail.com', '$2y$10$mh68z3zcjiRfagwMipMJzOWiaUqoGIj4PIM./7sr/OckzF1S9cc9W', 'user', 'active', '2026-01-29 17:56:26');

-- --------------------------------------------------------

--
-- Table structure for table `user_saved_books`
--

CREATE TABLE `user_saved_books` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_current_loans`
-- (See below for the actual view)
--
CREATE TABLE `v_current_loans` (
`loan_id` int(11)
,`title` varchar(200)
,`author` varchar(150)
,`full_name` varchar(100)
,`email` varchar(120)
,`issue_date` date
,`due_date` date
,`status` enum('issued','returned','overdue')
,`fine` decimal(10,2)
);

-- --------------------------------------------------------

--
-- Structure for view `v_current_loans`
--
DROP TABLE IF EXISTS `v_current_loans`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_current_loans`  AS SELECT `l`.`id` AS `loan_id`, `b`.`title` AS `title`, `b`.`author` AS `author`, `u`.`full_name` AS `full_name`, `u`.`email` AS `email`, `l`.`issue_date` AS `issue_date`, `l`.`due_date` AS `due_date`, `l`.`status` AS `status`, `l`.`fine` AS `fine` FROM ((`loans` `l` join `books` `b` on(`b`.`id` = `l`.`book_id`)) join `users` `u` on(`u`.`id` = `l`.`user_id`)) WHERE `l`.`status` in ('issued','overdue') ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `isbn` (`isbn`),
  ADD KEY `fk_books_category` (`category_id`);

--
-- Indexes for table `book_orders`
--
ALTER TABLE `book_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `book_purchases`
--
ALTER TABLE `book_purchases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `loans`
--
ALTER TABLE `loans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_loans_book` (`book_id`),
  ADD KEY `fk_loans_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_saved_books`
--
ALTER TABLE `user_saved_books`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_user_book` (`user_id`,`book_id`),
  ADD KEY `book_id` (`book_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `book_orders`
--
ALTER TABLE `book_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `book_purchases`
--
ALTER TABLE `book_purchases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `loans`
--
ALTER TABLE `loans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `user_saved_books`
--
ALTER TABLE `user_saved_books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `fk_books_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `book_orders`
--
ALTER TABLE `book_orders`
  ADD CONSTRAINT `book_orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `book_orders_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `book_purchases`
--
ALTER TABLE `book_purchases`
  ADD CONSTRAINT `book_purchases_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `book_purchases_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `loans`
--
ALTER TABLE `loans`
  ADD CONSTRAINT `fk_loans_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_loans_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_saved_books`
--
ALTER TABLE `user_saved_books`
  ADD CONSTRAINT `user_saved_books_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_saved_books_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
