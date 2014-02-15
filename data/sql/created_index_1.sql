/*
ALTER TABLE alae_<table> ADD INDEX (<column_name>);
ALTER TABLE alae_<table> ADD UNIQUE INDEX (<column_name>);
*/

ALTER TABLE alae_sample_batch ADD INDEX (parameters);
ALTER TABLE alae_sample_batch ADD INDEX (fk_batch);