CREATE DATABASE IF NOT EXISTS moja_strona;


CREATE TABLE `page_list` (
  `id` int(11) NOT NULL,
  `page_title` varchar(255) DEFAULT NULL,
  `page_content` text DEFAULT NULL,
  `status` int(11) DEFAULT 1
);


CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT 0,
  `name` varchar(255) NOT NULL
);


INSERT INTO `page_list` (`id`, `page_title`, `page_content`, `status`) VALUES
(1, 'Strona główna', 'Parę słów o żółwiach.', 1),
(2, 'Gatunki żółwi wodnych', 'Popularne gatunki żółwi z ich opisami i zdjęciami: błotny, żółtobrzuchy, czerwonolicy, żółtolicy.', 1),
(3, 'Żywienie', 'Co je żólw?', 1),
(4, 'Terrarium', 'Zdjęcia przykładowych terrarium', 1),
(5, 'Oświetlenie', 'Opis oświetlenia terrarium', 1),
(6, 'Ogrzewanie', 'Grzanie żółwia- lampa, grzałka, mata grzewcza', 1),
(7, 'Filmy', 'Linki youtube do trzech filmów o żółwiach.', 1),
(8, 'Kontakt', 'Formularz kontaktowy', 1);


INSERT INTO `categories` (`id`, `parent_id`, `name`) VALUES
(1, 0, 'Terrarium'),
(2, 0, 'Żywność'),
(3, 2, 'Granulat'),
(4, 2, 'Zioła suszone'),
(5, 1, 'Terrarium szklane'),
(6, 2, 'Pokarm uzupełniający'),
(7, 0, 'Akcesoria'),
(8, 7, 'Podłoże'),
(9, 7, 'Ozdoby i kryjówki'),
(10, 0, 'Ogrzewanie'),
(11, 10, 'Grzałki'),
(12, 10, 'Termometry'),
(13, 10, 'Maty grzewcze'),
(14, 10, 'Lampy'),
(15, 1, 'Faunarium');
