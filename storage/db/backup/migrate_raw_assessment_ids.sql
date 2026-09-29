BEGIN;

-- =============================================================
-- 1. Backup raw_assessment sebelum remap
-- =============================================================
CREATE TABLE IF NOT EXISTS ukom.raw_assessment_bak_20260811 AS SELECT * FROM ukom.raw_assessment;

-- =============================================================
-- 2. Drop FK lama (referensi ke tabel ukom yang sudah dipindah)
-- =============================================================
ALTER TABLE ukom.raw_assessment DROP CONSTRAINT IF EXISTS raw_assessment_employee_id_foreign;
ALTER TABLE ukom.raw_assessment DROP CONSTRAINT IF EXISTS raw_assessment_user_id_foreign;
ALTER TABLE ukom.raw_assessment DROP CONSTRAINT IF EXISTS raw_assessment_aspect_question_id_foreign;
ALTER TABLE ukom.raw_assessment DROP CONSTRAINT IF EXISTS raw_assessment_question_instrument_id_foreign;
ALTER TABLE ukom.raw_assessment DROP CONSTRAINT IF EXISTS fk_raw_assessment_interview_participant;

-- =============================================================
-- 3. Mapping id lama (ukom) -> id baru (schema target)
-- =============================================================
CREATE TEMP TABLE map_aq AS
SELECT DISTINCT ON (u.id) u.id AS old_id, a.id AS new_id
FROM ukom.aspect_question u
JOIN assessment.aspect_question a
  ON a.question = u.question
 AND a.level = u.level
 AND a.is_active = u.is_active;

CREATE TEMP TABLE map_qi AS
SELECT DISTINCT ON (u.id) u.id AS old_id, a.id AS new_id
FROM ukom.question_instrument u
JOIN map_aq m ON m.old_id = u.aspect_question_id
JOIN assessment.question_instrument a
  ON a.aspect_question_id = m.new_id
 AND a.instrument = u.instrument
 AND a.value = u.value;

CREATE TEMP TABLE map_ip AS
SELECT DISTINCT ON (u.id) u.id AS old_id, a.id AS new_id
FROM ukom.interview_participants u
JOIN ukom.interview_groups ug ON ug.id = u.interview_group_id
JOIN ukom.employee ue ON ue.id = u.employee_id
JOIN employee.personnel ep ON ep.employee_number = ue.nip
JOIN assessment.interview_groups ag ON ag.name = ug.name
JOIN assessment.interview_participants a
  ON a.interview_group_id = ag.id
 AND a.employee_id = ep.id;

CREATE TEMP TABLE map_emp AS
SELECT DISTINCT ON (ue.id) ue.id AS old_id, ep.id AS new_id
FROM ukom.employee ue
JOIN employee.personnel ep ON ep.employee_number = ue.nip;

-- =============================================================
-- 4. Remap referensi di raw_assessment ke id baru
-- =============================================================
UPDATE ukom.raw_assessment ra
SET aspect_question_id = m.new_id::text
FROM map_aq m
WHERE ra.aspect_question_id = m.old_id;

UPDATE ukom.raw_assessment ra
SET question_instrument_id = m.new_id::text
FROM map_qi m
WHERE ra.question_instrument_id = m.old_id;

UPDATE ukom.raw_assessment ra
SET interview_participant_id = m.new_id::text
FROM map_ip m
WHERE ra.interview_participant_id = m.old_id;

UPDATE ukom.raw_assessment ra
SET employee_id = m.new_id::text
FROM map_emp m
WHERE ra.employee_id = m.old_id;

-- =============================================================
-- 5. Verifikasi sisa yang belum terpetakan
-- =============================================================
SELECT 'unmapped_aspect_question' AS cek, COUNT(*)::int AS n
FROM ukom.raw_assessment ra
LEFT JOIN assessment.aspect_question a ON a.id::text = ra.aspect_question_id
WHERE a.id IS NULL
UNION ALL
SELECT 'unmapped_question_instrument', COUNT(*)::int
FROM ukom.raw_assessment ra
LEFT JOIN assessment.question_instrument a ON a.id::text = ra.question_instrument_id
WHERE a.id IS NULL AND ra.question_instrument_id IS NOT NULL
UNION ALL
SELECT 'unmapped_employee', COUNT(*)::int
FROM ukom.raw_assessment ra
LEFT JOIN employee.personnel ep ON ep.id::text = ra.employee_id
WHERE ep.id IS NULL
UNION ALL
SELECT 'unmapped_interview_participant', COUNT(*)::int
FROM ukom.raw_assessment ra
LEFT JOIN assessment.interview_participants a ON a.id::text = ra.interview_participant_id
WHERE a.id IS NULL AND ra.interview_participant_id IS NOT NULL;

COMMIT;
