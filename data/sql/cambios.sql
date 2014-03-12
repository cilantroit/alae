CREATE TABLE IF NOT EXISTS temporary_alae_sample_batch(
	pk_sample_batch                     bigint(20) 	unsigned NOT NULL auto_increment,
	sample_name                         varchar(250)	NOT NULL,
	analyte_peak_name                   varchar(250)	NOT NULL,
	sample_type                         varchar(250)	NOT NULL,
	file_name                           varchar(250)	NOT NULL,
	dilution_factor                     decimal(19,4)	NOT NULL,
	analyte_peak_area                   int                 NOT NULL,
	is_peak_name                        varchar(250)	NOT NULL,
	is_peak_area                        int                 NOT NULL,
	analyte_concentration               decimal(19,4),
	analyte_concentration_units         varchar(250)	NOT NULL,
	calculated_concentration            decimal(19,4),
	calculated_concentration_units      varchar(250)	NOT NULL,
	accuracy                            decimal(19,4),
	use_record                          int                 DEFAULT 0,
	valid_flag                          boolean             DEFAULT 1,
	code_error                          varchar(50),
	parameters                          varchar(50),
	created_at                          timestamp           NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at                          timestamp,
	sample_id                           int,
	sample_comment                      varchar(250),
	set_number                          int,
	acquisition_method                  varchar(50),
	rack_type                           varchar(50),
	rack_position                       int,
	vial_position                       int,
	plate_type                          varchar(50),
	plate_position                      int,
	weight_to_volume_ratio              decimal(19,4),
	sample_annotation                   varchar(50),
	disposition                         varchar(50),
	analyte_units                       varchar(50),
	acquisition_date                    timestamp,
	analyte_peak_area_for_dad           varchar(50),
	analyte_peak_height                 decimal(19,4),
	analyte_peak_height_for_dad         varchar(50),
	analyte_retention_time              decimal(19,4),
	analyte_expected_rt                 decimal(19,4),
	analyte_rt_window                   decimal(19,4),
	analyte_centroid_location           decimal(19,4),
	analyte_start_scan                  decimal(19,4),
	analyte_start_time                  decimal(19,4),
	analyte_stop_scan                   int,
	analyte_stop_time                   decimal(19,4),
	analyte_integration_type            varchar(50),
	analyte_signal_to_noise             varchar(50),
	analyte_peak_width                  decimal(19,4),
	analyte_standar_query_status        varchar(50),
	analyte_mass_ranges                 varchar(50),
	analyte_wavelength_ranges           varchar(50),
	height_ratio                        decimal(19,4),
	analyte_annotation                  varchar(50),
	analyte_channel                     varchar(50),
	analyte_peak_width_at_50_height     decimal(19,4),
	analyte_slope_of_baseline           decimal(19,4),
	analyte_processing_alg              varchar(50),
	analyte_peak_asymmetry              decimal(19,4),
	is_units                            varchar(50),
	is_peak_area_for_dad                varchar(50),
	is_peak_height                      decimal(19,4),
	is_peak_height_for_dad              varchar(50),
	is_concentration                    decimal(19,4),
	is_retention_time                   decimal(19,4),
	is_expected_rt                      decimal(19,4),
	is_rt_windows                       decimal(19,4),
	is_centroid_location                decimal(19,4),
	is_start_scan                       int,
	is_start_time                       decimal(19,4),
	is_stop_scan                        int,
	is_stop_time                        decimal(19,4),
	is_integration_type                 varchar(50),
	is_signal_to_noise                  varchar(50),
	is_peak_width                       decimal(19,4),
	is_mass_ranges                      varchar(50),
	is_wavelength_ranges                varchar(50),
	is_channel                          varchar(50),
	is_peak_width_al_50_height          decimal(19,4),
	is_slope_of_baseline                decimal(19,4),
	is_processing_alg                   varchar(50),
	is_peak_asymemtry                   decimal(19,4),
	record_modified                     int,
	area_ratio                          decimal(19,4),
	calculated_concentration_for_dad    varchar(50),
	relative_retention_time             decimal(19,4),
	response_factor                     decimal(19,4),
        fk_batch                            bigint(20)	unsigned NOT NULL,
	PRIMARY KEY (pk_sample_batch)
)ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;

INSERT INTO temporary_alae_sample_batch (
sample_name,analyte_peak_name,sample_type,file_name,dilution_factor,analyte_peak_area,is_peak_name,is_peak_area,
analyte_concentration,analyte_concentration_units,calculated_concentration,calculated_concentration_units,accuracy,
use_record,valid_flag,code_error,parameters,created_at,updated_at,acquisition_date,
sample_id,sample_comment,set_number,acquisition_method,rack_type,rack_position,vial_position,plate_type,plate_position,
weight_to_volume_ratio,sample_annotation,disposition,analyte_units,analyte_peak_area_for_dad,analyte_peak_height,
analyte_peak_height_for_dad,analyte_retention_time,analyte_expected_rt,analyte_rt_window,analyte_centroid_location,
analyte_start_scan,analyte_start_time,analyte_stop_scan,analyte_stop_time,analyte_integration_type,analyte_signal_to_noise,
analyte_peak_width,analyte_standar_query_status,analyte_mass_ranges,analyte_wavelength_ranges,height_ratio,
analyte_annotation,analyte_channel,analyte_peak_width_at_50_height,analyte_slope_of_baseline,analyte_processing_alg,
analyte_peak_asymmetry,is_units,is_peak_area_for_dad,is_peak_height,is_peak_height_for_dad,is_concentration,is_retention_time,
is_expected_rt,is_rt_windows,is_centroid_location,is_start_scan,is_start_time,is_stop_scan,is_stop_time,is_integration_type,
is_signal_to_noise,is_peak_width,is_mass_ranges,is_wavelength_ranges,is_channel,is_peak_width_al_50_height,is_slope_of_baseline,
is_processing_alg,is_peak_asymemtry,record_modified,area_ratio,calculated_concentration_for_dad,relative_retention_time,
response_factor,fk_batch
)
    SELECT sample_name,analyte_peak_name,sample_type,file_name,dilution_factor,analyte_peak_area,is_peak_name,is_peak_area,
analyte_concentration,analyte_concentration_units,calculated_concentration,calculated_concentration_units,accuracy,
use_record,valid_flag,code_error,parameters,created_at,updated_at,acquisition_date,
sample_id,sample_comment,set_number,acquisition_method,rack_type,rack_position,vial_position,plate_type,plate_position,
weight_to_volume_ratio,sample_annotation,disposition,analyte_units,analyte_peak_area_for_dad,analyte_peak_height,
analyte_peak_height_for_dad,analyte_retention_time,analyte_expected_rt,analyte_rt_window,analyte_centroid_location,
analyte_start_scan,analyte_start_time,analyte_stop_scan,analyte_stop_time,analyte_integration_type,analyte_signal_to_noise,
analyte_peak_width,analyte_standar_query_status,analyte_mass_ranges,analyte_wavelength_ranges,height_ratio,
analyte_annotation,analyte_channel,analyte_peak_width_at_50_height,analyte_slope_of_baseline,analyte_processing_alg,
analyte_peak_asymmetry,is_units,is_peak_area_for_dad,is_peak_height,is_peak_height_for_dad,is_concentration,is_retention_time,
is_expected_rt,is_rt_windows,is_centroid_location,is_start_scan,is_start_time,is_stop_scan,is_stop_time,is_integration_type,
is_signal_to_noise,is_peak_width,is_mass_ranges,is_wavelength_ranges,is_channel,is_peak_width_al_50_height,is_slope_of_baseline,
is_processing_alg,is_peak_asymemtry,record_modified,area_ratio,calculated_concentration_for_dad,relative_retention_time,
response_factor,fk_batch
    FROM alae_sample_batch, alae_sample_batch_other_columns
    WHERE pk_sample_batch = fk_sample_batch;

DROP TABLE alae_sample_batch_other_columns;
DROP TABLE alae_error;
DROP TABLE alae_sample_batch;

CREATE TABLE IF NOT EXISTS alae_sample_batch(
	pk_sample_batch                     bigint(20) 	unsigned NOT NULL auto_increment,
	sample_name                         varchar(250)	NOT NULL,
	analyte_peak_name                   varchar(250)	NOT NULL,
	sample_type                         varchar(250)	NOT NULL,
	file_name                           varchar(250)	NOT NULL,
	dilution_factor                     decimal(19,4)	NOT NULL,
	analyte_peak_area                   int             NOT NULL,
	is_peak_name                        varchar(250)	NOT NULL,
	is_peak_area                        int             NOT NULL,
	analyte_concentration               decimal(19,4),
	analyte_concentration_units         varchar(250)	NOT NULL,
	calculated_concentration            decimal(19,4),
	calculated_concentration_units      varchar(250)	NOT NULL,
	accuracy                            decimal(19,4),
	use_record                          int             DEFAULT 0,
	valid_flag                          boolean         DEFAULT 1,
	code_error                          varchar(50),
	parameters                          varchar(50),
	created_at                          timestamp       NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at                          timestamp,
	sample_id                           int,
	sample_comment                      varchar(250),
	set_number                          int,
	acquisition_method                  varchar(50),
	rack_type                           varchar(50),
	rack_position                       int,
	vial_position                       int,
	plate_type                          varchar(50),
	plate_position                      int,
	weight_to_volume_ratio              decimal(19,4),
	sample_annotation                   varchar(50),
	disposition                         varchar(50),
	analyte_units                       varchar(50),
	acquisition_date                    timestamp,
	analyte_peak_area_for_dad           varchar(50),
	analyte_peak_height                 decimal(19,4),
	analyte_peak_height_for_dad         varchar(50),
	analyte_retention_time              decimal(19,4),
	analyte_expected_rt                 decimal(19,4),
	analyte_rt_window                   decimal(19,4),
	analyte_centroid_location           decimal(19,4),
	analyte_start_scan                  decimal(19,4),
	analyte_start_time                  decimal(19,4),
	analyte_stop_scan                   int,
	analyte_stop_time                   decimal(19,4),
	analyte_integration_type            varchar(50),
	analyte_signal_to_noise             varchar(50),
	analyte_peak_width                  decimal(19,4),
	analyte_standar_query_status        varchar(50),
	analyte_mass_ranges                 varchar(50),
	analyte_wavelength_ranges           varchar(50),
	height_ratio                        decimal(19,4),
	analyte_annotation                  varchar(50),
	analyte_channel                     varchar(50),
	analyte_peak_width_at_50_height     decimal(19,4),
	analyte_slope_of_baseline           decimal(19,4),
	analyte_processing_alg              varchar(50),
	analyte_peak_asymmetry              decimal(19,4),
	is_units                            varchar(50),
	is_peak_area_for_dad                varchar(50),
	is_peak_height                      decimal(19,4),
	is_peak_height_for_dad              varchar(50),
	is_concentration                    decimal(19,4),
	is_retention_time                   decimal(19,4),
	is_expected_rt                      decimal(19,4),
	is_rt_windows                       decimal(19,4),
	is_centroid_location                decimal(19,4),
	is_start_scan                       int,
	is_start_time                       decimal(19,4),
	is_stop_scan                        int,
	is_stop_time                        decimal(19,4),
	is_integration_type                 varchar(50),
	is_signal_to_noise                  varchar(50),
	is_peak_width                       decimal(19,4),
	is_mass_ranges                      varchar(50),
	is_wavelength_ranges                varchar(50),
	is_channel                          varchar(50),
	is_peak_width_al_50_height          decimal(19,4),
	is_slope_of_baseline                decimal(19,4),
	is_processing_alg                   varchar(50),
	is_peak_asymemtry                   decimal(19,4),
	record_modified                     int,
	area_ratio                          decimal(19,4),
	calculated_concentration_for_dad    varchar(50),
	relative_retention_time             decimal(19,4),
	response_factor                     decimal(19,4),
        fk_batch                            bigint(20)	unsigned NOT NULL,
	PRIMARY KEY (pk_sample_batch),
	FOREIGN KEY (fk_batch) REFERENCES alae_batch (pk_batch)
)ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS alae_error (
    pk_error        bigint(20) 	unsigned NOT NULL auto_increment,
    fk_parameter    int         NOT NULL,
    fk_sample_batch bigint(20)	unsigned NOT NULL,
    PRIMARY KEY (pk_error),
    FOREIGN KEY (fk_parameter)     REFERENCES alae_parameter     (pk_parameter),
    FOREIGN KEY (fk_sample_batch)  REFERENCES alae_sample_batch  (pk_sample_batch)
)ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;

INSERT INTO alae_sample_batch (
sample_name,analyte_peak_name,sample_type,file_name,dilution_factor,analyte_peak_area,is_peak_name,is_peak_area,
analyte_concentration,analyte_concentration_units,calculated_concentration,calculated_concentration_units,accuracy,
use_record,valid_flag,code_error,parameters,created_at,updated_at,acquisition_date,
sample_id,sample_comment,set_number,acquisition_method,rack_type,rack_position,vial_position,plate_type,plate_position,
weight_to_volume_ratio,sample_annotation,disposition,analyte_units,analyte_peak_area_for_dad,analyte_peak_height,
analyte_peak_height_for_dad,analyte_retention_time,analyte_expected_rt,analyte_rt_window,analyte_centroid_location,
analyte_start_scan,analyte_start_time,analyte_stop_scan,analyte_stop_time,analyte_integration_type,analyte_signal_to_noise,
analyte_peak_width,analyte_standar_query_status,analyte_mass_ranges,analyte_wavelength_ranges,height_ratio,
analyte_annotation,analyte_channel,analyte_peak_width_at_50_height,analyte_slope_of_baseline,analyte_processing_alg,
analyte_peak_asymmetry,is_units,is_peak_area_for_dad,is_peak_height,is_peak_height_for_dad,is_concentration,is_retention_time,
is_expected_rt,is_rt_windows,is_centroid_location,is_start_scan,is_start_time,is_stop_scan,is_stop_time,is_integration_type,
is_signal_to_noise,is_peak_width,is_mass_ranges,is_wavelength_ranges,is_channel,is_peak_width_al_50_height,is_slope_of_baseline,
is_processing_alg,is_peak_asymemtry,record_modified,area_ratio,calculated_concentration_for_dad,relative_retention_time,
response_factor,fk_batch
)
    SELECT sample_name,analyte_peak_name,sample_type,file_name,dilution_factor,analyte_peak_area,is_peak_name,is_peak_area,
analyte_concentration,analyte_concentration_units,calculated_concentration,calculated_concentration_units,accuracy,
use_record,valid_flag,code_error,parameters,created_at,updated_at,acquisition_date,
sample_id,sample_comment,set_number,acquisition_method,rack_type,rack_position,vial_position,plate_type,plate_position,
weight_to_volume_ratio,sample_annotation,disposition,analyte_units,analyte_peak_area_for_dad,analyte_peak_height,
analyte_peak_height_for_dad,analyte_retention_time,analyte_expected_rt,analyte_rt_window,analyte_centroid_location,
analyte_start_scan,analyte_start_time,analyte_stop_scan,analyte_stop_time,analyte_integration_type,analyte_signal_to_noise,
analyte_peak_width,analyte_standar_query_status,analyte_mass_ranges,analyte_wavelength_ranges,height_ratio,
analyte_annotation,analyte_channel,analyte_peak_width_at_50_height,analyte_slope_of_baseline,analyte_processing_alg,
analyte_peak_asymmetry,is_units,is_peak_area_for_dad,is_peak_height,is_peak_height_for_dad,is_concentration,is_retention_time,
is_expected_rt,is_rt_windows,is_centroid_location,is_start_scan,is_start_time,is_stop_scan,is_stop_time,is_integration_type,
is_signal_to_noise,is_peak_width,is_mass_ranges,is_wavelength_ranges,is_channel,is_peak_width_al_50_height,is_slope_of_baseline,
is_processing_alg,is_peak_asymemtry,record_modified,area_ratio,calculated_concentration_for_dad,relative_retention_time,
response_factor,fk_batch
    FROM temporary_alae_sample_batch;

DROP TABLE temporary_alae_sample_batch;

ALTER TABLE  `alae_analyte_study` CHANGE  `hdqc_values`  `hdqc_values` DECIMAL( 19, 4 ) NULL DEFAULT NULL ,
CHANGE  `ldqc_values`  `ldqc_values` DECIMAL( 19, 4 ) NULL DEFAULT NULL;
