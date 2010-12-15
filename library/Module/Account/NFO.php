CONFIG :
========
- hash, name of selected hashing algorithm (i.e. "md5", "sha256", "haval160,4", etc..). sha512 is used by default.

CREATE TABLE `accounts` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(255) NOT NULL default '',
  `password` varchar(128) NOT NULL,
  `email` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT;