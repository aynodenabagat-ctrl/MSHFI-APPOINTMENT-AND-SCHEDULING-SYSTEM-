USE mindalano_hospital;

-- Sample Doctors
INSERT INTO users (username, email, password, first_name, last_name, role) VALUES
('dr.santos', 'dr.santos@mindalano.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Juan', 'Santos', 'doctor'),
('dr.cruz', 'dr.cruz@mindalano.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Maria', 'Cruz', 'doctor'),
('dr.reyes', 'dr.reyes@mindalano.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Pedro', 'Reyes', 'doctor'),
('dr.gonzales', 'dr.gonzales@mindalano.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ana', 'Gonzales', 'doctor');

INSERT INTO doctors (user_id, first_name, last_name, specialization, contact) VALUES
(2, 'Juan', 'Santos', 'Internal Medicine', '09171234567'),
(3, 'Maria', 'Cruz', 'Pediatrics', '09172345678'),
(4, 'Pedro', 'Reyes', 'General Surgery', '09173456789'),
(5, 'Ana', 'Gonzales', 'Obstetrics & Gynecology', '09174567890');

INSERT INTO users (username, email, password, first_name, last_name, role) VALUES
('secretary', 'secretary@mindalano.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Fatima', 'Mendoza', 'secretary');

INSERT INTO users (username, email, password, first_name, last_name, role) VALUES
('patient1', 'patient1@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jose', 'Rizal', 'patient');

INSERT INTO patients (user_id, first_name, last_name, contact, address, date_of_birth, blood_type) VALUES
(7, 'Jose', 'Rizal', '09175678901', 'Marawi City, Lanao del Sur', '1985-06-19', 'O+');

-- Doctor Schedules
INSERT INTO doctor_schedules (doctor_id, day_of_week, start_time, end_time) VALUES
(1, 1, '08:00', '12:00'), (1, 3, '08:00', '12:00'), (1, 5, '08:00', '12:00'),
(2, 2, '09:00', '13:00'), (2, 4, '09:00', '13:00'),
(3, 1, '13:00', '17:00'), (3, 3, '13:00', '17:00'), (3, 5, '13:00', '17:00'),
(4, 2, '08:00', '12:00'), (4, 4, '08:00', '12:00');

-- Sample Appointments
INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, status) VALUES
(1, 1, CURDATE(), '09:00', 'confirmed'),
(1, 2, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '10:00', 'pending'),
(1, 3, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '14:00', 'pending');

-- Sample Medical Record
INSERT INTO medical_records (patient_id, doctor_id, appointment_id, diagnosis, prescription) VALUES
(1, 1, 1, 'Hypertension, Stage 1', 'Losartan 50mg once daily. Follow up in 1 month.');
