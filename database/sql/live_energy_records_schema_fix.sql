-- Live server schema fix for CPRF energy-record imports.
-- Run once against the production database.

ALTER TABLE energy_records
    MODIFY recorded_by BIGINT UNSIGNED NULL DEFAULT NULL;

ALTER TABLE energy_records
    MODIFY energy_cost DECIMAL(14,2) NULL DEFAULT NULL;

ALTER TABLE energy_records
    MODIFY rate_per_kwh DECIMAL(10,2) NULL DEFAULT NULL;

ALTER TABLE energy_records
    ADD COLUMN recorded_by_name VARCHAR(255) NULL AFTER recorded_by;
