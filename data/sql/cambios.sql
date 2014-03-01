CREATE TABLE IF NOT EXISTS alae_error (
    pk_error        bigint(20) 	unsigned NOT NULL auto_increment,
    fk_parameter    int         NOT NULL,
    fk_sample_batch bigint(20)	unsigned NOT NULL,
    PRIMARY KEY (pk_error),
    FOREIGN KEY (fk_parameter)     REFERENCES alae_parameter     (pk_parameter),
    FOREIGN KEY (fk_sample_batch)  REFERENCES alae_sample_batch  (pk_sample_batch)
)ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;
