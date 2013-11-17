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


--  Section: units
CREATE TABLE IF NOT EXISTS alae_unit(
	pk_unit	int	NOT NULL auto_increment,
	name	varchar(25),
	PRIMARY KEY (pk_unit)
)ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;

--  OJO: Colocar todas las unidades
INSERT INTO alae_unit (name) VALUES
	('mg/mL');

--  Section: users and profiles
CREATE TABLE IF NOT EXISTS alae_profile(
	pk_profile	int			NOT NULL auto_increment,
	name		varchar(25)	NOT NULL,
	PRIMARY KEY (pk_profile)
)ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;

INSERT INTO alae_profile (name) VALUES
	('Sustancias'),
	('Laboratorio'),
	('Director Estudio'),
	('UGC'),
	('Administrador'),
        ('Cron');

CREATE TABLE IF NOT EXISTS alae_user(
	pk_user		bigint(20) 		unsigned NOT NULL auto_increment,
	username	varchar(25)		NOT NULL,
	email		varchar(50)		NOT NULL,
	password	varchar(50)		NOT NULL,
	active_flag	boolean			NOT NULL DEFAULT 0,
	fk_profile	int,
	PRIMARY KEY (pk_user),
	FOREIGN KEY (fk_profile) REFERENCES alae_profile (pk_profile)
)ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;

INSERT INTO alae_user (username, email, password, active_flag, fk_profile) VALUES
    ('alae_system', 'alae@cilantroit.com', 'c360723e2f01ccc2a7bd08176ac62d14', 1, 6);

--  Section: studies
CREATE TABLE IF NOT EXISTS alae_study(
	pk_study			bigint(20) 		unsigned NOT NULL auto_increment,
	code				varchar(20),
	created_at			timestamp 		NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at			timestamp,
	description			text,
	observation 		text,
	close_flag			boolean			NOT NULL DEFAULT 0,
	fk_user				bigint(20)		unsigned NOT NULL,
    fk_dilution_tree 	bigint(20)		NOT NULL DEFAULT 1,
	PRIMARY KEY (pk_study),
	FOREIGN KEY (fk_user) REFERENCES alae_user (pk_user) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS alae_analyte(
	pk_analyte	bigint(20) 		unsigned NOT NULL auto_increment,
	name 		varchar(30),
	shortening	varchar(15),
	updated_at	timestamp		NOT NULL ON UPDATE CURRENT_TIMESTAMP,
	fk_user		bigint(20)		unsigned NOT NULL,
	PRIMARY KEY (pk_analyte),
   	FOREIGN KEY (fk_user) REFERENCES alae_user (pk_user)
)ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;

--  OJO: Queda pendiente definicion de valores para los cs y qc. Si se cambia para que en la pantalla de definicion de estudios, se elijan las unidades.
CREATE TABLE IF NOT EXISTS alae_analyte_study(
	cs_number			int				NOT NULL DEFAULT 8,
	qc_number			int				NOT NULL DEFAULT 4,
	cs_values			varchar(100),
	qc_values			varchar(100),
	internal_standard	decimal(19,4)	NOT NULL DEFAULT 0,
	is_flag				boolean			NOT NULL DEFAULT 0,
	imported_flag		boolean			NOT NULL DEFAULT 0,
	fk_study			bigint(20)		unsigned NOT NULL,
	fk_analyte			bigint(20)		unsigned NOT NULL,
	fk_analyte_is		bigint(20)		unsigned NOT NULL,
	fk_unit				int				NOT NULL,
	PRIMARY KEY (fk_study, fk_analyte),
   	FOREIGN KEY (fk_study)      REFERENCES alae_study   (pk_study),
   	FOREIGN KEY (fk_analyte)    REFERENCES alae_analyte (pk_analyte),
	FOREIGN KEY (fk_analyte_is) REFERENCES alae_analyte (pk_analyte),
	FOREIGN KEY (fk_unit)       REFERENCES alae_unit    (pk_unit)
)ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;

--  Section: parameters
CREATE TABLE IF NOT EXISTS alae_parameter(
	pk_parameter		int			NOT NULL auto_increment,
	rule				varchar(10),
	verification		text,
	min_value			int 		NOT NULL DEFAULT 0,
	max_value			int 		NOT NULL DEFAULT 0,
	code_error			varchar(10),
	message_error		text,
	PRIMARY KEY (pk_parameter)
)ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;

--  Section: batch
CREATE TABLE IF NOT EXISTS alae_batch(
	pk_batch				bigint(20) 		unsigned NOT NULL auto_increment,
	serial					int,
	file_name				varchar(100),
	created_at				timestamp 		NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at				timestamp,
	valid_flag				boolean,
	accepted_flag			boolean,
	justification			varchar(250),
	validation_date 		timestamp,
	code_error				varchar(10),
	intercept				decimal(19,4)	NOT NULL  DEFAULT 0,
	slope					decimal(19,4)	NOT NULL  DEFAULT 0,
	correlation_coefficient decimal(19,4)	NOT NULL  DEFAULT 0,
	cs_total 				int 			NOT NULL  DEFAULT 0,
	qc_total 				int 			NOT NULL  DEFAULT 0,
	ldqc_total 				int 			NOT NULL  DEFAULT 0,
	hdqc_total 				int 			NOT NULL  DEFAULT 0,
	cs_accepted_total 		int 			NOT NULL  DEFAULT 0,
	qc_accepted_total 		int 			NOT NULL  DEFAULT 0,
	ldqc_accepted_total 	int 			NOT NULL  DEFAULT 0,
	hdqc_accepted_total 	int 			NOT NULL  DEFAULT 0,
	is_cs_qc_accepted_avg 	int 			NOT NULL  DEFAULT 0,
	fk_parameter			int,
	fk_analyte				bigint(20)		unsigned NOT NULL,
	fk_user					bigint(20)		unsigned NOT NULL,
	PRIMARY KEY (pk_batch),
	FOREIGN KEY (fk_parameter)  REFERENCES alae_parameter (pk_parameter),
	FOREIGN KEY (fk_analyte)    REFERENCES alae_analyte   (pk_analyte),
	FOREIGN KEY (fk_user) 		REFERENCES alae_user 	  (pk_user)
)ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;

--  NOTA: En cada una de las validaciones, debe descartar la fila (pasar el valid_flag = 0)
CREATE TABLE IF NOT EXISTS alae_sample_batch(
	pk_sample_batch						bigint(20) 		unsigned NOT NULL auto_increment,
	sample_name							varchar(250)	NOT NULL,
	analyte_peak_name					varchar(250)	NOT NULL,
	sample_type							varchar(250)	NOT NULL,
	file_name							varchar(250)	NOT NULL,
	dilution_factor						decimal(19,4)	NOT NULL,
	analyte_peak_area					int				NOT NULL,
	is_peak_name						varchar(250)	NOT NULL,
	is_peak_area						int				NOT NULL,
	analyte_concentration				decimal(19,4),
	analyte_concentration_units			varchar(250)	NOT NULL,
	calculated_concentration			decimal(19,4),
	calculated_concentration_units		varchar(250)	NOT NULL,
	accuracy							decimal(19,4),
	use_record							int		DEFAULT 0,
	valid_flag							boolean DEFAULT 1,
	code_error							varchar(50),
	parameters							varchar(50),
	created_at							timestamp 		NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at							timestamp,
	fk_batch 							bigint(20)		unsigned NOT NULL,
	fk_user								bigint(20)		unsigned NOT NULL,
	PRIMARY KEY (pk_sample_batch),
	FOREIGN KEY (fk_batch) REFERENCES alae_batch (pk_batch),
	FOREIGN KEY (fk_user)  REFERENCES alae_user  (pk_user)
)ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS alae_sample_batch_other_colums(
	sample_id							int,
	sample_comment						varchar(250),
	set_number							int,
	acquisition_method					varchar(50),
	rack_type							varchar(50),
	rack_position						int,
	vial_position						int,
	plate_type							varchar(50),
	plate_position						int,
	weight_to_volume_ratio				decimal(19,4),
	sample_annotation					varchar(50),
	disposition							varchar(50),
	analyte_units						varchar(50),
	acquisition_date					timestamp,
	analyte_peak_area_for_dad			varchar(50),
	analyte_peak_height					decimal(19,4),
	analyte_peak_height_for_dad			varchar(50),
	analyte_retention_time				decimal(19,4),
	analyte_expected_rt					decimal(19,4),
	analyte_rt_window					decimal(19,4),
	analyte_centroid_location			decimal(19,4),
	analyte_start_scan					decimal(19,4),
	analyte_start_time					decimal(19,4),
	analyte_stop_scan					int,
	analyte_stop_time					decimal(19,4),
	analyte_integration_type			varchar(50),
	analyte_signal_to_noise				varchar(50),
	analyte_peak_width					decimal(19,4),
	analyte_standar_query_status		varchar(50),
	analyte_mass_ranges					varchar(50),
	analyte_wavelength_ranges			varchar(50),
	height_ratio						decimal(19,4),
	analyte_annotation					varchar(50),
	analyte_channel						varchar(50),
	analyte_peak_width_at_50_height		decimal(19,4),
	analyte_slope_of_baseline			decimal(19,4),
	analyte_processing_alg				varchar(50),
	analyte_peak_asymmetry				decimal(19,4),
	is_units							varchar(50),
	is_peak_area_for_dad				varchar(50),
	is_peak_height						decimal(19,4),
	is_peak_height_for_dad				varchar(50),
	is_concentration					decimal(19,4),
	is_retention_time					decimal(19,4),
	is_expected_rt						decimal(19,4),
	is_rt_windows						decimal(19,4),
	is_centroid_location				decimal(19,4),
	is_start_scan						int,
	is_start_time						decimal(19,4),
	is_stop_scan						int,
	is_stop_time						decimal(19,4),
	is_integration_type					varchar(50),
	is_signal_to_noise					varchar(50),
	is_peak_width						decimal(19,4),
	is_mass_ranges						varchar(50),
	is_wavelength_ranges				varchar(50),
	is_channel							varchar(50),
	is_peak_width_al_50_height			decimal(19,4),
	is_slope_of_baseline				decimal(19,4),
	is_processing_alg					varchar(50),
	is_peak_asymemtry					decimal(19,4),
	record_modified						int,
	area_ratio							decimal(19,4),
	calculated_concentration_for_dad	varchar(50),
	relative_retention_time				decimal(19,4),
	response_factor						decimal(19,4),
	fk_sample_batch						bigint(20)	unsigned NOT NULL,
	PRIMARY KEY (fk_sample_batch),
	FOREIGN KEY (fk_sample_batch) REFERENCES alae_sample_batch (pk_sample_batch)
)ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;

-- 	Section: audit trail
-- 		NOTA: Las dos primeras tablas, son las que se mostrarán en los reportes.
-- 		Mostrando las transacciones realizadas por los usuarios del sistema.
--
-- 		Las ultimas dos tablas, son mas informativas para los administradores de sistemas,
-- 		para que puedan tener una traza de ¿cuando?, ¿que?, ¿como? y ¿quien? produjo el error en el sistema.

--  Audit session
CREATE TABLE IF NOT EXISTS alae_audit_session (
  	pk_audit_session 	bigint(20) 	unsigned NOT NULL auto_increment,
	created_at			timestamp	NOT NULL  DEFAULT CURRENT_TIMESTAMP,
	fk_user				bigint(20)	unsigned NOT NULL,
	PRIMARY KEY (pk_audit_session),
	FOREIGN KEY (fk_user)  REFERENCES alae_user  (pk_user)
);

--  Audit transaction
CREATE TABLE IF NOT EXISTS alae_audit_transaction (
  	pk_audit_session 	bigint(20) 		unsigned NOT NULL auto_increment,
	created_at			timestamp		NOT NULL  DEFAULT CURRENT_TIMESTAMP,
	section				varchar(50) 	NOT NULL,
	description			varchar(250)	NOT NULL,
	fk_user				bigint(20)		unsigned NOT NULL,
	PRIMARY KEY (pk_audit_session),
	FOREIGN KEY (fk_user) REFERENCES alae_user (pk_user)
);

-- Ejemplo: Colocar todas las tareas.
-- INSERT INTO alae_audit_task (section, description, fk_user) VALUES
-- 	('Administración de usuarios', 'activación del usuario XYZ', 3);

--  Audit session error
CREATE TABLE IF NOT EXISTS alae_audit_session_error (
  	pk_audit_session 	bigint(20) 		unsigned NOT NULL auto_increment,
	created_at			timestamp		NOT NULL  DEFAULT CURRENT_TIMESTAMP,
	username			varchar(25)		NOT NULL,
	message				varchar(500)	NOT NULL,
	PRIMARY KEY (pk_audit_session)
);

--  Audit transaction error
CREATE TABLE IF NOT EXISTS alae_audit_transaction_error (
  	pk_audit_session 	bigint(20) 		unsigned NOT NULL auto_increment,
	created_at			timestamp		NOT NULL  DEFAULT CURRENT_TIMESTAMP,
	section				varchar(50) 	NOT NULL,
	description			varchar(250)	NOT NULL,
	message				varchar(500)	NOT NULL,
	fk_user				bigint(20)		unsigned NOT NULL,
	PRIMARY KEY (pk_audit_session),
	FOREIGN KEY (fk_user) REFERENCES alae_user (pk_user)
);