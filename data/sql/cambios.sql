ALTER TABLE  `alae_batch` ADD  `analyte_concentration_units` VARCHAR( 250 ) NOT NULL AFTER  `is_cs_qc_accepted_avg` ,
ADD  `calculated_concentration_units` VARCHAR( 250 ) NOT NULL AFTER  `analyte_concentration_units` ;

ALTER TABLE  `alae_batch` CHANGE  `analyte_concentration_units`  `analyte_concentration_units` VARCHAR( 250 ) CHARACTER SET utf8 COLLATE utf8_bin NULL ,
CHANGE  `calculated_concentration_units`  `calculated_concentration_units` VARCHAR( 250 ) CHARACTER SET utf8 COLLATE utf8_bin NULL ;

INSERT INTO alae_unit (name) VALUES
    ('ng/mL'),
    ('pg/mL'),
    ('µg/mL');