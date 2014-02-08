-- Doctrine: ./vendor/bin/doctrine-module orm:convert-mapping --from-database annotation ./module/Alae/src --namespace="Alae\Entity\\" --force

-- Example:
-- CREATE TABLE IF NOT EXISTS alae_<table>(
-- 	pk_<table>	int	NOT NULL auto_increment,
-- 	fk_<table>,
-- 	fk_<column_name>_<table>,
-- 	PRIMARY KEY (pk_<table>),
-- 	FOREIGN KEY (fk_<table>) REFERENCES alae_<table> (pk_<table>)
--  	FOREIGN KEY (fk_<column_name>_<table>) REFERENCES alae_<table> (pk_<table>)
-- )ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;


CREATE TABLE IF NOT EXISTS alae_unit(
	pk_unit	int	NOT NULL auto_increment,
	name	varchar(25),
	PRIMARY KEY (pk_unit)
)ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;

INSERT INTO alae_unit (name) VALUES
	('mg/mL');

CREATE TABLE IF NOT EXISTS alae_profile(
	pk_profile	int		NOT NULL auto_increment,
	name		varchar(25)	NOT NULL,
	PRIMARY KEY (pk_profile)
)ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;

INSERT INTO alae_profile (name) VALUES
	('Sustancias'),
	('Laboratorio'),
	('Director Estudio'),
	('UGC'),
	('Administrador'),
        ('Cron'),
	('Sin asignar');

CREATE TABLE IF NOT EXISTS alae_user(
	pk_user		bigint(20) 		unsigned NOT NULL auto_increment,
	name		varchar(25)		NULL,
	username	varchar(25)		NOT NULL,
	email		varchar(50)		NOT NULL,
	password	varchar(50)		NOT NULL,
	active_code	varchar(50)		NOT NULL,
        verification	varchar(50),
	active_flag	boolean			NOT NULL DEFAULT 0,
	fk_profile	int,
	PRIMARY KEY (pk_user),
	FOREIGN KEY (fk_profile) REFERENCES alae_profile (pk_profile)
)ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;

INSERT INTO alae_user (name, username, email, password, active_flag, active_code, fk_profile) VALUES
    ('ALAE System','alae_system', 'alae@cilantroit.com', 'c360723e2f01ccc2a7bd08176ac62d14', 'c260184e2f01ccc2a7bd08176ac62d14', 1, 6);

CREATE TABLE IF NOT EXISTS alae_study(
	pk_study		bigint(20) 		unsigned NOT NULL auto_increment,
	code			varchar(20),
	created_at		timestamp 		NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at		timestamp,
	description		text,
	observation 		text,
	close_flag		boolean			NOT NULL DEFAULT 0,
        status  		boolean			NOT NULL DEFAULT 0,
        approve  		boolean			NOT NULL DEFAULT 0,
        duplicate		boolean			NOT NULL DEFAULT 0,
	fk_user			bigint(20)		unsigned NOT NULL,
        fk_user_approve         bigint(20)		unsigned,
        fk_user_close           bigint(20)		unsigned,
        fk_dilution_tree 	bigint(20)		NOT NULL DEFAULT 1,
	PRIMARY KEY (pk_study),
	FOREIGN KEY (fk_user) REFERENCES alae_user (pk_user) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS alae_analyte(
	pk_analyte	bigint(20) 	unsigned NOT NULL auto_increment,
	name 		varchar(30),
	shortening	varchar(15),
	updated_at	timestamp	NOT NULL ON UPDATE CURRENT_TIMESTAMP,
        status		boolean		NOT NULL DEFAULT 1,
	fk_user		bigint(20)	unsigned NOT NULL,
	PRIMARY KEY (pk_analyte),
   	FOREIGN KEY (fk_user) REFERENCES alae_user (pk_user)
)ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS alae_analyte_study(
        pk_analyte_study	bigint(20) 	unsigned NOT NULL auto_increment,
	cs_number		int		NOT NULL DEFAULT 8,
	qc_number		int		NOT NULL DEFAULT 4,
	cs_values		varchar(100),
	qc_values		varchar(100),
	internal_standard	decimal(19,4)	NOT NULL DEFAULT 0,
	status			boolean		NOT NULL DEFAULT 0,
	is_used                 boolean		NOT NULL DEFAULT 0,
        updated_at              timestamp	NOT NULL,
	fk_study		bigint(20)	unsigned NOT NULL,
	fk_analyte		bigint(20)	unsigned NOT NULL,
	fk_analyte_is		bigint(20)	unsigned NOT NULL,
	fk_unit			int		NOT NULL,
        fk_user                 bigint(20)	unsigned NOT NULL,
        fk_user_approve         bigint(20)      unsigned,
	PRIMARY KEY (pk_analyte_study),
   	FOREIGN KEY (fk_study)      REFERENCES alae_study   (pk_study),
   	FOREIGN KEY (fk_analyte)    REFERENCES alae_analyte (pk_analyte),
	FOREIGN KEY (fk_analyte_is) REFERENCES alae_analyte (pk_analyte),
	FOREIGN KEY (fk_unit)       REFERENCES alae_unit    (pk_unit),
        FOREIGN KEY (fk_user) REFERENCES alae_user (pk_user)
)ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS alae_parameter(
	pk_parameter		int             NOT NULL auto_increment,
	rule			varchar(10),
	verification		text,
	min_value		int 		NOT NULL DEFAULT 0,
	max_value		int 		NOT NULL DEFAULT 0,
	code_error		varchar(10),
	message_error		text,
        type_param              tinyint(1)      NOT NULL DEFAULT '1',
        fk_user			bigint(20)	unsigned,
	PRIMARY KEY (pk_parameter),
        FOREIGN KEY (fk_user) REFERENCES alae_user (pk_user)
)ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS alae_batch(
	pk_batch                bigint(20) 	unsigned NOT NULL auto_increment,
	serial			int,
	file_name		varchar(100),
	created_at		timestamp 	NOT NULL  DEFAULT CURRENT_TIMESTAMP,
	updated_at		timestamp       NULL      DEFAULT '0000-00-00 00:00:00',
	valid_flag		boolean,
	accepted_flag		boolean,
	justification		varchar(250),
	validation_date 	timestamp       NULL      DEFAULT '0000-00-00 00:00:00',
	code_error		varchar(10),
	intercept		decimal(19,4)	NOT NULL  DEFAULT 0,
	slope			decimal(19,4)	NOT NULL  DEFAULT 0,
	correlation_coefficient decimal(19,4)	NOT NULL  DEFAULT 0,
	cs_total 		int 		NOT NULL  DEFAULT 0,
	qc_total 		int 		NOT NULL  DEFAULT 0,
	ldqc_total 		int 		NOT NULL  DEFAULT 0,
	hdqc_total 		int 		NOT NULL  DEFAULT 0,
	cs_accepted_total 	int 		NOT NULL  DEFAULT 0,
	qc_accepted_total 	int 		NOT NULL  DEFAULT 0,
	ldqc_accepted_total 	int 		NOT NULL  DEFAULT 0,
	hdqc_accepted_total 	int 		NOT NULL  DEFAULT 0,
	is_cs_qc_accepted_avg 	int 		NOT NULL  DEFAULT 0,
	fk_parameter		int,
	fk_analyte		bigint(20)	unsigned,
	fk_user			bigint(20)	unsigned,
        fk_study		bigint(20)	unsigned,
	PRIMARY KEY (pk_batch),
	FOREIGN KEY (fk_parameter)  REFERENCES alae_parameter (pk_parameter),
	FOREIGN KEY (fk_analyte)    REFERENCES alae_analyte   (pk_analyte),
	FOREIGN KEY (fk_user)       REFERENCES alae_user      (pk_user),
        FOREIGN KEY (fk_study)      REFERENCES alae_study     (pk_study)
)ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS alae_sample_batch(
	pk_sample_batch			bigint(20) 	unsigned NOT NULL auto_increment,
	sample_name			varchar(250)	NOT NULL,
	analyte_peak_name		varchar(250)	NOT NULL,
	sample_type			varchar(250)	NOT NULL,
	file_name			varchar(250)	NOT NULL,
	dilution_factor			decimal(19,4)	NOT NULL,
	analyte_peak_area		int		NOT NULL,
	is_peak_name			varchar(250)	NOT NULL,
	is_peak_area			int		NOT NULL,
	analyte_concentration		decimal(19,4),
	analyte_concentration_units	varchar(250)	NOT NULL,
	calculated_concentration	decimal(19,4),
	calculated_concentration_units  varchar(250)	NOT NULL,
	accuracy			decimal(19,4),
	use_record			int		DEFAULT 0,
	valid_flag			boolean         DEFAULT 1,
	code_error			varchar(50),
	parameters			varchar(50),
	created_at			timestamp 	NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at			timestamp,
	fk_batch 			bigint(20)	unsigned NOT NULL,
	PRIMARY KEY (pk_sample_batch),
	FOREIGN KEY (fk_batch) REFERENCES alae_batch (pk_batch)
)ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS alae_sample_batch_other_columns(
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
	fk_sample_batch                     bigint(20)	unsigned NOT NULL,
	PRIMARY KEY (fk_sample_batch),
	FOREIGN KEY (fk_sample_batch) REFERENCES alae_sample_batch (pk_sample_batch)
)ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS alae_audit_session (
  	pk_audit_session 	bigint(20) 	unsigned NOT NULL auto_increment,
	created_at		timestamp	NOT NULL  DEFAULT CURRENT_TIMESTAMP,
	fk_user			bigint(20)	unsigned NOT NULL,
	PRIMARY KEY (pk_audit_session),
	FOREIGN KEY (fk_user)  REFERENCES alae_user  (pk_user)
);

CREATE TABLE IF NOT EXISTS alae_audit_transaction (
  	pk_audit_session 	bigint(20) 	unsigned NOT NULL auto_increment,
	created_at		timestamp	NOT NULL  DEFAULT CURRENT_TIMESTAMP,
	section			varchar(250) 	NOT NULL,
	description		varchar(500)	NOT NULL,
	fk_user			bigint(20)	unsigned NOT NULL,
	PRIMARY KEY (pk_audit_session),
	FOREIGN KEY (fk_user) REFERENCES alae_user (pk_user)
);

CREATE TABLE IF NOT EXISTS alae_audit_session_error (
  	pk_audit_session 	bigint(20) 	unsigned NOT NULL auto_increment,
	created_at		timestamp	NOT NULL  DEFAULT CURRENT_TIMESTAMP,
	username		varchar(25)	NOT NULL,
	message			varchar(500)	NOT NULL,
	PRIMARY KEY (pk_audit_session)
);

CREATE TABLE IF NOT EXISTS alae_audit_transaction_error (
  	pk_audit_session 	bigint(20) 	unsigned NOT NULL auto_increment,
	created_at		timestamp	NOT NULL  DEFAULT CURRENT_TIMESTAMP,
	section			varchar(50) 	NOT NULL,
	description		varchar(250)	NOT NULL,
	message			varchar(500)	NOT NULL,
	fk_user			bigint(20)	unsigned NOT NULL,
	PRIMARY KEY (pk_audit_session),
	FOREIGN KEY (fk_user) REFERENCES alae_user (pk_user)
);

INSERT INTO alae_parameter (rule, verification, min_value, max_value, code_error, message_error, fk_user) VALUES
('V1','Revisión del archivo exportado  (código de estudio)',0,0,null,'V1 - EXPORT ERRÓNEO', 1),
('V2','Revisión del archivo exportado (abreviatura analito)',0,0,null,'V2 - ANALITO ERRÓNEO', 1),
('V3','Revisión del archivo exportado  (nº de lote)',0,0,null,'V3 - EXPORT ERRÓNEO', 1),
('V4','Sample Type',0,0,null,'V4 - SAMPLE TYPE ERRÓNEO', 1),
('V5','Concentración nominal de CS/QC',0,0,null,'V5 - CONCENTRACIÓN NOMINAL ERRÓNEA', 1),
('V6.1','Replicados CS (mínimo)',2,0,null,'V6.1 - REPLICADOS INSUFICIENTES', 1),
('V6.2','Replicados QC (mínimo)',2,0,null,'V6.2 - REPLICADOS INSUFICIENTES', 1),
('V7','Sample Name repetido',0,0,null,'V7 - SAMPLE NAME REPETIDO', 1),
('V8','Búsqueda de Muestras reinyectadas',0,0,null,null, 1),
('V9.1','Accuracy (QCRx*)',85,115,'O','V9.1 - QCR* ACCURACY FUERA DE RANGO', 1),
('V9.2','Use record = 0 ( QCRx*)',0,0,'O','V9.2 - QCR* USE RECORD NO VALIDO', 1),
('V9.3','Que tanto V 9.1 como V 9.2 se cumplan',0,0,'O','V9.3 - QCR* NO VALIDO', 1),
('V10.1','Accuracy (CS1)',80,120,'O','V10.1 - NO CUMPLE ACCURACY', 1),
('V10.2','Accuracy (CS2-CSx)',85,115,'O','V10.2 - NO CUMPLE ACCURACY', 1),
('V10.3','Accuracy (QC)',85,115,'O','V10.3 - NO CUMPLE ACCURACY', 1),
('V10.4','Accuracy (DQC)',85,115,'O','V10.4 - NO CUMPLE ACCURACY', 1),
('V11','Revisión del dilution factor en HDQC / LDQC',0,0,'O','V11- FACTOR DILUCIÓN ERRÓNEO', 1),
('V12','Use record (CS/QC/DQC)',0,0,null,null, 1),
('V13.1','Selección manual de los CS válidos',0,0,null,null, 1),
('V13.2','Interf. Analito en BLK',20,0,'O','V13.2 - BLK NO CUMPLE', 1),
('V13.3','Interf. IS en BLK',5,0,'O','V13.3 - BLK NO CUMPLE', 1),
('V13.4','Interf. Analito en ZS',20,0,'O','V13.4 - ZS NO CUMPLE', 1),
('V15','75% CS',75,0,null,'V15 - LOTE RECHAZADO (75% CS)', 1),
('V16','CS consecutivos',0,0,null,'V16 - LOTE RECHAZADO (CS CONSECUTIVOS)', 1),
('V17','r > 0.99',99,0,null,'V17 - LOTE RECHAZADO (r< 0.99)', 1),
('V18','67% QC',67,0,null,'V18 - LOTE RECHAZADO (67% QC)', 1),
('V19','50% de cada nivel de QC',50,0,null,'V19 - LOTE RECHAZADO (50% QCx)', 1),
('V20.1','50% BLK',50,0,null,'V20.1 - LOTE RECHAZADO (INTERF. BLK)', 1),
('V20.2','50% ZS',50,0,null,'V20.2 - LOTE RECHAZADO (INTERF. ZS)', 1),
('V21','Conc. (unknown) > ULOQ ( E )',0,0,'E','V21 - CONC. SUPERIOR AL ULOQ', 1),
('V22','Variabilidad IS (unknown) ( H )',0,0,'H','V22 - VARIABILIDAD IS', 1),
('V23','< 5% respuesta IS (unknown) ( B )',0,0,'B','V23 - ERROR EXTRACCIÓN IS', 1),
('V24','Fuera rango recta truncada ( F )',0,0,'F','V24 - FUERA DE RANGO/RECTA TRUNCADA', 1);

INSERT INTO alae_parameter (rule, verification, min_value, max_value, code_error, message_error, type_param, fk_user) VALUES
('V12.1',	null, 0, 0, 'O', 'V12 - No cumple S/N', 0, 1),
('V12.2',	null, 0, 0, 'F', 'V12 - Recta truncada al CS2', 0, 1),
('V12.3',	null, 0, 0, 'O', 'V12 - Recta truncada al CS7', 0, 1),
('V12.4',	null, 0, 0, 'A', 'V12 - Muestra perdida durante la extracción', 0, 1),
('V12.5',	null, 0, 0, 'B', 'V12 - Error de extracción', 0, 1),
('V12.6',	null, 0, 0, 'C', 'V12 - Problemas de cromatografía', 0, 1),
('V12.7',	null, 0, 0, 'D', 'V12 - Fallos técnicos de equipos / software', 0, 1),
('V12.8',	null, 0, 0, null, 'V12 - Use Record Erróneo', 0, 1);