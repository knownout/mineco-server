-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Ноя 02 2021 г., 17:56
-- Версия сервера: 8.0.24
-- Версия PHP: 7.4.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `mineco`
--

-- --------------------------------------------------------

--
-- Структура таблицы `accounts`
--

CREATE TABLE `accounts` (
  `id` int NOT NULL COMMENT 'identifcator of the account, used only by db',
  `login` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'user login',
  `hash` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'user password md5 hash',
  `full_name` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Администратор' COMMENT 'name of the account user',
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'is account active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `accounts`
--

INSERT INTO `accounts` (`id`, `login`, `hash`, `full_name`, `active`) VALUES
(1, 'root-admin@s1604', '21232f297a57a5a743894a0e4a801fc3', 'Славинский Александр Романович', 1),
(2, 'lobodyuk', '98870fd11944dafbda9ee89c4724b22b', 'Лободюк Ирина Леонтьевна', 1),
(3, 'bondarenko', '2a5c21019981d6e80f5eac599d7b90f5', 'Бондаренко Сергей Николаевич', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `logs`
--

CREATE TABLE `logs` (
  `id` int NOT NULL COMMENT 'identifier of operation',
  `login` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'user login',
  `action` enum('update','remove','login','password') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'the action that the user took',
  `affect` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'operation timestamp'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Структура таблицы `materials`
--

CREATE TABLE `materials` (
  `identifier` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'identifier (folder name) of each material',
  `title` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'title of the current article',
  `tags` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'article tags list',
  `time` bigint NOT NULL COMMENT 'last article modification or publish time',
  `pinned` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'is material pinned at title page (as title page main article)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `materials`
--

INSERT INTO `materials` (`identifier`, `title`, `tags`, `time`, `pinned`) VALUES
('6mu9en9e53', 'На сельхозпредприятиях Приднестровья проходят практику по программе «Мехатроник» 10 студентов', 'Новости, Документы', 1635777293954, 0),
('829bpeuwd4', 'Оперативная информация о ходе уборочной кампании по состоянию на 29 октября 2021 года', 'Новости', 1635777245229, 1),
('ewfj2snxc4', 'Представители ветеринарии Приднестровья приняли участие в IХ ежегодной Национальной ветеринарной конференции NVC-2021', 'Документы', 1635777245229, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `tags`
--

CREATE TABLE `tags` (
  `identifier` int NOT NULL COMMENT 'id of the tag, not used by app',
  `name` tinytext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'name of the tag that will be displayed',
  `display` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'is tag displayed at tags list'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `tags`
--

INSERT INTO `tags` (`identifier`, `name`, `display`) VALUES
(1, 'Новости', 1),
(2, 'Документы', 1);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`);

--
-- Индексы таблицы `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`identifier`),
  ADD UNIQUE KEY `identifier` (`identifier`);

--
-- Индексы таблицы `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`identifier`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT COMMENT 'identifcator of the account, used only by db', AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT COMMENT 'identifier of operation';

--
-- AUTO_INCREMENT для таблицы `tags`
--
ALTER TABLE `tags`
  MODIFY `identifier` int NOT NULL AUTO_INCREMENT COMMENT 'id of the tag, not used by app', AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
