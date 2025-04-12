-- Update student_registrations table with new fields
ALTER TABLE student_registrations
ADD birth_place VARCHAR(100) AFTER birth_date,
ADD religion VARCHAR(20) AFTER gender,
ADD parent_occupation VARCHAR(100) AFTER parent_name,
ADD parent_income VARCHAR(50) AFTER parent_occupation,
ADD documents JSON AFTER previous_school;

-- Create registration tracking table
CREATE TABLE IF NOT EXISTS registration_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    registration_id INT NOT NULL,
    status VARCHAR(20) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (registration_id) REFERENCES student_registrations(id)
);

-- Create registration documents table
CREATE TABLE IF NOT EXISTS registration_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    registration_id INT NOT NULL,
    document_type VARCHAR(50) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (registration_id) REFERENCES student_registrations(id)
);
