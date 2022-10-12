-- Tiles
CREATE TABLE IF NOT EXISTS `tile` (
	`tile_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`resource` enum('ivory', 'ebony', 'marble', 'wheat', 'fish', 'livestock', 'fish-or-ebony', 'ebony-or-livestock', 'livestock-or-ivory', 'ivory-or-wheat', 'wheat-or-marble', 'marble-or-fish'),
	`displayed_resource` enum('ivory', 'ebony', 'marble', 'wheat', 'fish', 'livestock', 'fish-or-ebony', 'ebony-or-livestock', 'livestock-or-ivory', 'ivory-or-wheat', 'wheat-or-marble', 'marble-or-fish'),
	`deck` enum('starting', 'good', 'character', 'pharaoh') NOT NULL,
	`statue` tinyint(1) unsigned DEFAULT '0' NOT NULL,
	`direction` char(1),
	`scarabs` tinyint(1) unsigned DEFAULT '0' NOT NULL,
	`deben` tinyint(1) unsigned DEFAULT '0' NOT NULL,
	`ability` tinyint(1) unsigned,
	`location` enum('discard', 'deck', 'board', 'hand', 'sold', 'corruption', 'played') NOT NULL,
	`just_sold` tinyint(11) unsigned DEFAULT '0',
	`col` tinyint(11) unsigned,
	`row` tinyint(11) unsigned,
	`player_id` int(11) unsigned DEFAULT NULL,
	PRIMARY KEY (`tile_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- Deben
CREATE TABLE IF NOT EXISTS `deben` (
	`deben_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`value` tinyint(11),
	`location` enum('bag', 'player', 'discard') DEFAULT 'bag' NOT NULL,
	`player_id` int(11) unsigned DEFAULT NULL,
	PRIMARY KEY (`deben_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- Pirogue
CREATE TABLE IF NOT EXISTS `pirogue` (
	`pirogue_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`ability` tinyint(11) NOT NULL,
	`location` enum('bag', 'slot', 'board', 'player', 'soldset', 'discard') DEFAULT 'bag' NOT NULL,
	`resource` enum('ivory', 'ebony', 'marble', 'wheat', 'fish', 'livestock') DEFAULT NULL,
	`slot` tinyint(11) unsigned DEFAULT NULL,
	`col` tinyint(11) unsigned DEFAULT NULL,
	`row` tinyint(11) unsigned DEFAULT NULL,
	`player_id` int(11) unsigned DEFAULT NULL,
	PRIMARY KEY (`pirogue_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- Store whether players have seen Pirogues
ALTER TABLE `player` ADD `player_seen_pirogues` SMALLINT UNSIGNED NOT NULL DEFAULT '0';

-- Royal corruption
CREATE TABLE IF NOT EXISTS `royal_corruption` (
	`royal_corruption_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`value` tinyint(11),
	`location` enum('bag', 'player', 'discard') DEFAULT 'bag' NOT NULL,
	`player_id` int(11) unsigned DEFAULT NULL,
	PRIMARY KEY (`royal_corruption_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
