ALTER TABLE  `alae_sample_batch` 
    ADD  `acquisition_date` DATETIME NOT NULL AFTER  `use_record`,
    ADD  `analyte_integration_type` VARCHAR( 50 ) NOT NULL AFTER  `acquisition_date`,
    ADD  `is_integration_type` VARCHAR( 50 ) NOT NULL AFTER  `analyte_integration_type`,
    ADD  `record_modified` INT NOT NULL AFTER  `is_integration_type`;

UPDATE alae_sample_batch
INNER JOIN alae_sample_batch_other_columns ON alae_sample_batch.pk_sample_batch = alae_sample_batch_other_columns.fk_sample_batch
SET
    alae_sample_batch.acquisition_date          = alae_sample_batch_other_columns.acquisition_date,
    alae_sample_batch.analyte_integration_type  = alae_sample_batch_other_columns.analyte_integration_type,
    alae_sample_batch.is_integration_type       = alae_sample_batch_other_columns.is_integration_type,
    alae_sample_batch.record_modified           = alae_sample_batch_other_columns.record_modified
        
ALTER TABLE  `alae_sample_batch_other_columns`
    DROP COLUMN `acquisition_date`,
    DROP COLUMN `analyte_integration_type`,
    DROP COLUMN `is_integration_type`,
    DROP COLUMN `record_modified`;