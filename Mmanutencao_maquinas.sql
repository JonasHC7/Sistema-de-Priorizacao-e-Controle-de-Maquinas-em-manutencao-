-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 18/10/2025 às 21:45
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `manutencao_maquinas`
--
CREATE DATABASE IF NOT EXISTS `manutencao_maquinas` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `manutencao_maquinas`;

-- --------------------------------------------------------

--
-- Estrutura para tabela `maquinas`
--

CREATE TABLE `maquinas` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `nome_maquina` varchar(50) NOT NULL,
  `tipo_id` tinyint(3) UNSIGNED NOT NULL,
  `setor_id` tinyint(3) UNSIGNED NOT NULL,
  `status` enum('ATIVA','MANUTENCAO','PARADA','DESLIGADA') DEFAULT 'ATIVA',
  `saude` enum('OPERACIONAL','INTERMEDIARIA','CRITICA') DEFAULT 'OPERACIONAL',
  `assigned_user` int(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `maquinas`
--

INSERT INTO `maquinas` (`id`, `nome_maquina`, `tipo_id`, `setor_id`, `status`, `saude`, `assigned_user`, `updated_at`) VALUES
(1, 'KETTENS', 1, 1, 'ATIVA', 'OPERACIONAL', NULL, '2025-10-18 16:27:58'),
(2, 'RASCHEL', 2, 2, 'MANUTENCAO', 'INTERMEDIARIA', 2, '2025-10-18 16:28:12'),
(3, 'CIRCULAR', 3, 3, 'ATIVA', 'OPERACIONAL', NULL, '2025-10-18 16:28:24'),
(4, 'ACAB. RAMA', 4, 4, 'ATIVA', 'OPERACIONAL', NULL, '2025-10-18 16:28:35'),
(5, 'ACABAMENTO', 5, 5, 'PARADA', 'CRITICA', 2, '2025-10-18 16:28:55'),
(6, 'URDIMENTO', 6, 6, 'ATIVA', 'OPERACIONAL', NULL, '2025-10-18 16:29:29'),
(7, 'DUBLAGEM', 7, 7, 'MANUTENCAO', 'CRITICA', 1, '2025-10-18 16:29:42');

-- --------------------------------------------------------

--
-- Estrutura para tabela `relatorios`
--

CREATE TABLE `relatorios` (
  `id` int(11) NOT NULL,
  `maquina_id` tinyint(3) UNSIGNED NOT NULL,
  `criado_por` int(11) DEFAULT NULL,
  `criado_em` datetime DEFAULT current_timestamp(),
  `checklist` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`checklist`)),
  `status_final` enum('Consertada','Inutilizável','Aguardando Peça','Em Análise') DEFAULT 'Em Análise',
  `descricao` text DEFAULT NULL,
  `notificado` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `relatorios`
--

INSERT INTO `relatorios` (`id`, `maquina_id`, `criado_por`, `criado_em`, `checklist`, `status_final`, `descricao`, `notificado`) VALUES
(1, 2, 2, '2025-10-18 16:16:22', '{\"power\": true, \"noise\": true, \"vibration\": false}', 'Em Análise', 'Ruído elevado detectado', 0),
(2, 5, 2, '2025-10-18 16:16:22', '{\"power\": false, \"noise\": true, \"vibration\": true}', 'Aguardando Peça', 'Falha elétrica e vibração ', 0),
(3, 7, 1, '2025-10-18 16:16:22', '{\"power\": false, \"noise\": false, \"vibration\": true}', 'Em Análise', 'Vibração excessiva', 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `setores`
--

CREATE TABLE `setores` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `nome_setor` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `setores`
--

INSERT INTO `setores` (`id`, `nome_setor`) VALUES
(7, 'AUTOCLAVE'),
(1, 'CAPAS'),
(6, 'ESTIRADEIRA'),
(2, 'FIAÇÃO'),
(3, 'REVISÃO'),
(4, 'TEAR'),
(5, 'TEXTURIZAÇÃO');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tipos_maquinas`
--

CREATE TABLE `tipos_maquinas` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `nome_tipo` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tipos_maquinas`
--

INSERT INTO `tipos_maquinas` (`id`, `nome_tipo`) VALUES
(4, 'ACAB. RAMA'),
(5, 'ACABAMENTO'),
(3, 'CIRCULAR'),
(7, 'DUBLAGEM'),
(1, 'KETTENS'),
(2, 'RASCHEL'),
(6, 'URDIMENTO');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `senha` varchar(255) NOT NULL,
  `cargo` enum('GERENTE','OPERACIONAL') DEFAULT 'OPERACIONAL',
  `setor_id` tinyint(3) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `telefone`, `senha`, `cargo`, `setor_id`, `created_at`) VALUES
(1, 'Gerente ', 'gerente@example.com', '1199999001', '123', 'GERENTE', 1, '2025-10-18 16:16:21'),
(2, 'Operador ', 'operador@example.com', '1198888002', '321', 'OPERACIONAL', 2, '2025-10-18 16:16:21');

-- --------------------------------------------------------

--
-- Estrutura para tabela `ordens_servico`
--

CREATE TABLE IF NOT EXISTS `ordens_servico` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero_os` varchar(20) NOT NULL,
  `tipo_manutencao` enum('CORRETIVA','PREVENTIVA','PREDITIVA','MELHORIAS') NOT NULL,
  `equipamento` varchar(100) NOT NULL,
  `area` enum('ELETRICA','MECANICA','MARCENARIA','FUNILARIA','TORNEARIA','PREDIAL','OUTROS') NOT NULL,
  `setor_id` tinyint(3) UNSIGNED NOT NULL,
  `descricao_defeito` text NOT NULL,
  `causa_defeito` text DEFAULT NULL,
  `acao_corretiva` text DEFAULT NULL,
  `entrada_preventiva` enum('SIM','NAO') DEFAULT 'NAO',
  `data_programada` date DEFAULT NULL,
  `status` enum('ABERTA','EM_ANDAMENTO','CONCLUIDA','CANCELADA') DEFAULT 'ABERTA',
  `prioridade` enum('BAIXA','MEDIA','ALTA','URGENTE') DEFAULT 'MEDIA',
  `solicitado_por` int(11) NOT NULL,
  `recebido_por` int(11) DEFAULT NULL,
  `aceito_por` int(11) DEFAULT NULL,
  `analisado_por` int(11) DEFAULT NULL,
  `data_abertura` datetime DEFAULT current_timestamp(),
  `data_recebimento` datetime DEFAULT NULL,
  `data_aceite` datetime DEFAULT NULL,
  `data_analise` datetime DEFAULT NULL,
  `data_conclusao` datetime DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_os` (`numero_os`),
  KEY `setor_id` (`setor_id`),
  KEY `solicitado_por` (`solicitado_por`),
  KEY `recebido_por` (`recebido_por`),
  KEY `aceito_por` (`aceito_por`),
  KEY `analisado_por` (`analisado_por`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `ordens_servico`
--

INSERT INTO `ordens_servico` (`numero_os`, `tipo_manutencao`, `equipamento`, `area`, `setor_id`, `descricao_defeito`, `solicitado_por`, `prioridade`) VALUES
('OS-2024-0001', 'CORRETIVA', 'KETTENS', 'ELETRICA', 1, 'Máquina parada sem energia no painel principal', 1, 'ALTA'),
('OS-2024-0002', 'PREVENTIVA', 'RASCHEL', 'MECANICA', 2, 'Manutenção preventiva programada - troca de rolamentos', 1, 'MEDIA'),
('OS-2024-0003', 'MELHORIAS', 'CIRCULAR', 'OUTROS', 3, 'Melhoria no sistema de lubrificação automática', 1, 'BAIXA');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `maquinas`
--
ALTER TABLE `maquinas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tipo_id` (`tipo_id`),
  ADD KEY `setor_id` (`setor_id`),
  ADD KEY `assigned_user` (`assigned_user`),
  ADD KEY `idx_maquinas_status` (`status`),
  ADD KEY `idx_maquinas_saude` (`saude`);

--
-- Índices de tabela `relatorios`
--
ALTER TABLE `relatorios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `criado_por` (`criado_por`),
  ADD KEY `idx_relatorios_maquina` (`maquina_id`);

--
-- Índices de tabela `setores`
--
ALTER TABLE `setores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome_setor` (`nome_setor`);

--
-- Índices de tabela `tipos_maquinas`
--
ALTER TABLE `tipos_maquinas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome_tipo` (`nome_tipo`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `setor_id` (`setor_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `maquinas`
--
ALTER TABLE `maquinas`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `relatorios`
--
ALTER TABLE `relatorios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `setores`
--
ALTER TABLE `setores`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `tipos_maquinas`
--
ALTER TABLE `tipos_maquinas`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `ordens_servico`
--
ALTER TABLE `ordens_servico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `maquinas`
--
ALTER TABLE `maquinas`
  ADD CONSTRAINT `maquinas_ibfk_1` FOREIGN KEY (`tipo_id`) REFERENCES `tipos_maquinas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `maquinas_ibfk_2` FOREIGN KEY (`setor_id`) REFERENCES `setores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `maquinas_ibfk_3` FOREIGN KEY (`assigned_user`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `relatorios`
--
ALTER TABLE `relatorios`
  ADD CONSTRAINT `relatorios_ibfk_1` FOREIGN KEY (`maquina_id`) REFERENCES `maquinas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `relatorios_ibfk_2` FOREIGN KEY (`criado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`setor_id`) REFERENCES `setores` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `ordens_servico`
--
ALTER TABLE `ordens_servico`
  ADD CONSTRAINT `ordens_servico_ibfk_1` FOREIGN KEY (`setor_id`) REFERENCES `setores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ordens_servico_ibfk_2` FOREIGN KEY (`solicitado_por`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ordens_servico_ibfk_3` FOREIGN KEY (`recebido_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ordens_servico_ibfk_4` FOREIGN KEY (`aceito_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ordens_servico_ibfk_5` FOREIGN KEY (`analisado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


