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
ALTER TABLE  `alae_audit_transaction` CHANGE  `description`  `description` VARCHAR( 500 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;
ALTER TABLE  `alae_study` 
ADD  `fk_user_approve` BIGINT( 20 ) UNSIGNED NULL DEFAULT NULL AFTER  `fk_user` ,
ADD  `fk_user_close` BIGINT( 20 ) UNSIGNED NULL DEFAULT NULL AFTER  `fk_user_approve` ;
ALTER TABLE alae_study
ADD FOREIGN KEY (fk_user_approve) REFERENCES alae_user(pk_user),
ADD FOREIGN KEY (fk_user_close) REFERENCES alae_user(pk_user);
ALTER TABLE  `alae_analyte_study` 
ADD  `fk_user_approve` BIGINT( 20 ) UNSIGNED NULL DEFAULT NULL AFTER  `fk_user`;
ALTER TABLE alae_study
ADD FOREIGN KEY (fk_user_approve) REFERENCES alae_user(pk_user);
ALTER TABLE  `alae_analyte_study` 
ADD  `hdqc_values` INT NULL;
ALTER TABLE  `alae_analyte_study` 
ADD  `ldqc_values` INT NULL;