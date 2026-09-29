--
-- SQL DDL Script for Payroll Schema
-- File: storage/db/backup/tugas.sql
--

CREATE SCHEMA IF NOT EXISTS "payroll";

CREATE TABLE IF NOT EXISTS payroll.salaries (
    id UUID NOT NULL DEFAULT gen_random_uuid(),
    employee_personnel_id UUID NOT NULL,
    organization_id UUID NOT NULL,
    salary BIGINT NOT NULL DEFAULT 0,
    benefits JSONB DEFAULT '[]'::jsonb,
    document_path VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP WITHOUT TIME ZONE DEFAULT NULL,
    CONSTRAINT pk_payroll_salaries PRIMARY KEY (id),
    CONSTRAINT fk_salaries_employee_personnel FOREIGN KEY (employee_personnel_id)
        REFERENCES employee.personnel(id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_salaries_organization FOREIGN KEY (organization_id)
        REFERENCES instance.organizations(id) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_payroll_salaries_emp ON payroll.salaries(employee_personnel_id);
CREATE INDEX IF NOT EXISTS idx_payroll_salaries_org ON payroll.salaries(organization_id);