DROP TABLE IF EXISTS `coups_personnages`;

CREATE TABLE `coups_personnages` (
  `idcoup_personnage` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idpersonnage` int(10) NOT NULL,
  `idpersonnage_frapper` int(10) NOT NULL,
  `degats` tinyint(3) DEFAULT NULL,
  `experience` tinyint(3) DEFAULT NULL,
  `niveau` tinyint(3) DEFAULT NULL,
  `date_coup` date DEFAULT NULL,
  PRIMARY KEY (`idcoup_personnage`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `personnages`;

CREATE TABLE `personnages` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) COLLATE latin1_general_ci NOT NULL,
  `degats` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `forcePerso` tinyint(3) NOT NULL DEFAULT '0',
  `niveau` tinyint(3) NOT NULL DEFAULT '1',
  `experience` tinyint(3) NOT NULL DEFAULT '0',
  `derniereConnexion` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;