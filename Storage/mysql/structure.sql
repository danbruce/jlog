CREATE TABLE IF NOT EXISTS `${TABLE_PREFIX}_Messages` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `createDate` datetime NOT NULL,
  `transaction` int(255) NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `transaction` (`transaction`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `${TABLE_PREFIX}_Transactions` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `createDate` datetime NOT NULL,
  `modifyDate` datetime NOT NULL,
  `transactionID` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transactionID` (`transactionID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;