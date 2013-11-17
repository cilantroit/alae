/*
ALTER TABLE alae_<table> ADD INDEX (<column_name>);
ALTER TABLE alae_<table> ADD UNIQUE INDEX (<column_name>);
*/
ALTER TABLE alae_user ADD INDEX (username, password);
ALTER TABLE alae_user ADD INDEX (active_flag);

ALTER TABLE alae_sample_batch ADD INDEX (sample_name);
ALTER TABLE alae_sample_batch ADD INDEX (sample_type);
ALTER TABLE alae_sample_batch ADD INDEX (accuracy);
ALTER TABLE alae_sample_batch ADD INDEX (use_record);
ALTER TABLE alae_sample_batch ADD INDEX (analyte_peak_area);
ALTER TABLE alae_sample_batch ADD INDEX (is_peak_area);

ALTER TABLE alae_audit_session ADD INDEX (created_at);

ALTER TABLE alae_audit_transaction ADD INDEX (created_at);
ALTER TABLE alae_audit_transaction ADD INDEX (section);

ALTER TABLE alae_audit_session_error ADD INDEX (created_at);

ALTER TABLE alae_audit_transaction_error ADD INDEX (created_at);
