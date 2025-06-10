-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 26-Jun-2024 às 15:55
-- Versão do servidor: 8.3.0
-- versão do PHP: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `escola_conducao_pap`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `aula`
--

DROP TABLE IF EXISTS `aula`;
CREATE TABLE IF NOT EXISTS `aula` (
  `id_aula` int NOT NULL AUTO_INCREMENT,
  `titulo_aula` varchar(45) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Título da Aula',
  `data_aula` datetime NOT NULL COMMENT 'Data da aula',
  `notas_aula` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Possíveis notas a escrever',
  `presenca_aula` tinyint NOT NULL DEFAULT '0' COMMENT 'Marca a presença na aula.',
  `fk_tipo_aula` int NOT NULL COMMENT 'Foreign Key do tipo de aula.\\n0 = Prática\\n1 = Teórica\\n2 = Teórico-Prática.',
  `fk_id_aluno` int NOT NULL COMMENT 'Foreign key do tipo de aluno',
  `fk_id_instrutor` int NOT NULL COMMENT 'Foreign key do tipo de instrutor',
  `eliminado_aula` int NOT NULL DEFAULT '0' COMMENT 'Estado de Eliminação. 1 = Eliminado, 0 = Não Eliminado',
  PRIMARY KEY (`id_aula`),
  KEY `fk_id_instrutor` (`fk_id_instrutor`),
  KEY `fk_tipo_aula` (`fk_tipo_aula`),
  KEY `fk_id_aluno` (`fk_id_aluno`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `aula`
--

INSERT INTO `aula` (`id_aula`, `titulo_aula`, `data_aula`, `notas_aula`, `presenca_aula`, `fk_tipo_aula`, `fk_id_aluno`, `fk_id_instrutor`, `eliminado_aula`) VALUES
(32, '', '2024-06-27 00:00:00', '', 0, 0, 13, 12, 0);

-- --------------------------------------------------------

--
-- Estrutura da tabela `tipo_aula`
--

DROP TABLE IF EXISTS `tipo_aula`;
CREATE TABLE IF NOT EXISTS `tipo_aula` (
  `id_tipo_aula` int NOT NULL,
  `titulo_tipo_aula` varchar(45) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_tipo_aula`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `tipo_aula`
--

INSERT INTO `tipo_aula` (`id_tipo_aula`, `titulo_tipo_aula`) VALUES
(0, 'Prática'),
(1, 'Teórica'),
(2, 'Teórico-Prática');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tipo_utilizador`
--

DROP TABLE IF EXISTS `tipo_utilizador`;
CREATE TABLE IF NOT EXISTS `tipo_utilizador` (
  `id_tipo_utilizador` int NOT NULL,
  `titulo_tipo_utilizador` varchar(45) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_tipo_utilizador`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `tipo_utilizador`
--

INSERT INTO `tipo_utilizador` (`id_tipo_utilizador`, `titulo_tipo_utilizador`) VALUES
(1, 'Aluno(a)'),
(2, 'Professor(a)'),
(3, 'Secretário(a)');

-- --------------------------------------------------------

--
-- Estrutura da tabela `utilizador`
--

DROP TABLE IF EXISTS `utilizador`;
CREATE TABLE IF NOT EXISTS `utilizador` (
  `id_utilizador` int NOT NULL AUTO_INCREMENT,
  `username_utilizador` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Username do utilizador.',
  `email_utilizador` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Email do utilizador.',
  `password_utilizador` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Password encriptada do utilizador',
  `fk_tipoutilizador` int NOT NULL COMMENT 'Foreign key associada à tabela tipo_utilizador.',
  `eliminado_utilizador` tinyint NOT NULL DEFAULT '0' COMMENT 'Estado de Eliminação. | Eliminado = 1 | Não Eliminado = 0',
  `utilizadorcol` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id_utilizador`),
  KEY `fk_tipo` (`fk_tipoutilizador`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Dados sobre o utilizador.';

--
-- Extraindo dados da tabela `utilizador`
--

INSERT INTO `utilizador` (`id_utilizador`, `username_utilizador`, `email_utilizador`, `password_utilizador`, `fk_tipoutilizador`, `eliminado_utilizador`, `utilizadorcol`) VALUES
(11, 'admin', 'admin@admin.com', '123', 3, 0, NULL),
(12, 'professor', 'professor@professor.com', '123', 2, 0, NULL),
(13, 'user', 'user@user.com', '123', 1, 0, NULL);

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `aula`
--
ALTER TABLE `aula`
  ADD CONSTRAINT `fk_id_aluno` FOREIGN KEY (`fk_id_aluno`) REFERENCES `utilizador` (`id_utilizador`),
  ADD CONSTRAINT `fk_id_instrutor` FOREIGN KEY (`fk_id_instrutor`) REFERENCES `utilizador` (`id_utilizador`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_tipo_aula` FOREIGN KEY (`fk_tipo_aula`) REFERENCES `tipo_aula` (`id_tipo_aula`);

--
-- Limitadores para a tabela `utilizador`
--
ALTER TABLE `utilizador`
  ADD CONSTRAINT `fk_tipo` FOREIGN KEY (`fk_tipoutilizador`) REFERENCES `tipo_utilizador` (`id_tipo_utilizador`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
