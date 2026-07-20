-- Student Result Management System - Database Schema
-- Run this in Supabase SQL Editor

CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role VARCHAR(20) DEFAULT 'teacher',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS students (
    id SERIAL PRIMARY KEY,
    student_id VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    class VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS subjects (
    id SERIAL PRIMARY KEY,
    subject_code VARCHAR(20) UNIQUE NOT NULL,
    subject_name VARCHAR(100) NOT NULL,
    class VARCHAR(50) NOT NULL,
    semester VARCHAR(20) NOT NULL DEFAULT 'First Semester'
);

-- Add semester column to existing subjects table if missing
DO $$ BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='subjects' AND column_name='semester') THEN
        ALTER TABLE subjects ADD COLUMN semester VARCHAR(20) NOT NULL DEFAULT 'First Semester';
    END IF;
END $$;

CREATE TABLE IF NOT EXISTS results (
    id SERIAL PRIMARY KEY,
    student_id VARCHAR(20) REFERENCES students(student_id) ON DELETE CASCADE,
    subject_code VARCHAR(20) REFERENCES subjects(subject_code) ON DELETE CASCADE,
    term VARCHAR(20) NOT NULL,
    session VARCHAR(20) NOT NULL,
    ca_score NUMERIC(5,2) DEFAULT 0,
    exam_score NUMERIC(5,2) DEFAULT 0,
    total_score NUMERIC(5,2) DEFAULT 0,
    grade VARCHAR(2) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(student_id, subject_code, term, session)
);

CREATE TABLE IF NOT EXISTS settings (
    key VARCHAR(50) PRIMARY KEY,
    value VARCHAR(255) NOT NULL
);

INSERT INTO settings (key, value) VALUES ('institution_type', 'university')
ON CONFLICT (key) DO NOTHING;

INSERT INTO settings (key, value) VALUES ('institution_name', 'My University')
ON CONFLICT (key) DO NOTHING;

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, full_name, role)
VALUES ('admin', '$2y$10$pVxukWVniOlTxPVGQ61.BupVA52Xd4VhiEyw8nAmH0ke6xmSFH5kW', 'System Administrator', 'admin')
ON CONFLICT (username) DO NOTHING;
