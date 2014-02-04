UPDATE alae_parameter SET rule = "V12.1" WHERE pk_parameter = 34;
UPDATE alae_parameter SET rule = "V12.2" WHERE pk_parameter = 35;
UPDATE alae_parameter SET rule = "V12.3" WHERE pk_parameter = 36;
UPDATE alae_parameter SET rule = "V12.4" WHERE pk_parameter = 37;
UPDATE alae_parameter SET rule = "V12.5" WHERE pk_parameter = 38;
UPDATE alae_parameter SET rule = "V12.6" WHERE pk_parameter = 39;
UPDATE alae_parameter SET rule = "V12.7" WHERE pk_parameter = 40;
UPDATE alae_parameter SET rule = "V12.8" WHERE pk_parameter = 41;

ALTER TABLE  `alae_study` CHANGE  `status`  `status` TINYINT(1) NOT NULL DEFAULT  '0';
ALTER TABLE  `alae_study` ADD  `duplicate` TINYINT(1) NOT NULL DEFAULT  '0' AFTER  `status` ;
ALTER TABLE  `alae_study` ADD  `approve` TINYINT(1) NOT NULL DEFAULT  '0' AFTER  `status` ;