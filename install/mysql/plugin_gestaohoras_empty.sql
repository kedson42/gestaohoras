-- Saldo de horas por grupo
CREATE TABLE IF NOT EXISTS `glpi_plugin_gestaohoras_balances_hours` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `total` int(11) NOT NULL,
  `daily` int(11) NOT NULL,
  `default` int(11) NOT NULL,
  `date_creation` datetime NOT NULL,
  `groups_id` int(11) NOT NULL,
  `users_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`groups_id`) REFERENCES glpi_groups(`id`),
  FOREIGN KEY (`users_id`) REFERENCES glpi_users(`id`),
  INDEX `idx_balance_hours_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Histórico de lançamentos
CREATE TABLE IF NOT EXISTS `glpi_plugin_gestaohoras_balances_historys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(10) NOT NULL, -- 'C (Crédito) ou D (Débito)'
  `quantity` int(11) NOT NULL,
  `date_operation` datetime NOT NULL,
  `plugin_gestaohoras_balances_hours_id` int(11) NOT NULL,
  `users_id` int(11),
  `tickets_id` int(11),
  `category` varchar(10),
  `justification` longtext,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`plugin_gestaohoras_balances_hours_id`) REFERENCES glpi_plugin_gestaohoras_balances_hours(`id`),
  FOREIGN KEY (`users_id`) REFERENCES glpi_users(`id`),
  INDEX `idx_balance_history_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Tabelas para inclusão do dados da Categoria
CREATE TABLE IF NOT EXISTS `glpi_plugin_gestaohoras_itilcategorycategorias` (
    `id` int(11) UNSIGNED  NOT NULL AUTO_INCREMENT,
    `items_id` int(11) NOT NULL,
    `itemtype` varchar(255) DEFAULT 'ITILCategory',
    `limitefield` int(11) UNSIGNED  NOT NULL DEFAULT 0,
    `debitofield` int(11) UNSIGNED  NOT NULL DEFAULT 0,
 PRIMARY KEY (`id`),
  FOREIGN KEY (`items_id`) REFERENCES glpi_itilcategories(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- INSERT PADRAO DAS CATEGORIAS
INSERT INTO glpi_plugin_gestaohoras_itilcategorycategorias (
	items_id, 
	itemtype, 
	limitefield, debitofield
) SELECT id, 'ITILCategory', 0, 0 FROM glpi_itilcategories;
