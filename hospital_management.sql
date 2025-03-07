CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_name VARCHAR(255) NOT NULL,  -- Column for admin name
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin') NOT NULL DEFAULT 'admin'
);


CREATE TABLE doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('doctor') NOT NULL DEFAULT 'doctor'
);
ALTER TABLE doctors ADD specialist VARCHAR(100) NOT NULL;
ALTER TABLE doctors ADD department_id INT;
ALTER TABLE doctors ADD CONSTRAINT fk_department FOREIGN KEY (department_id) REFERENCES departments(id);



CREATE TABLE patients_registration (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    role ENUM('patient') NOT NULL DEFAULT 'patient'
);
ALTER TABLE patients_registration ADD mobile VARCHAR(15);


CREATE TABLE prescriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    prescription TEXT NOT NULL,
    date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients_registration(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
);


CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT,
    admin_id INT,
    amount DECIMAL(10, 2),
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    FOREIGN KEY (patient_id) REFERENCES patients_registration(id),
    FOREIGN KEY (admin_id) REFERENCES admins(id)
);
ALTER TABLE payments 
ADD card_type ENUM('debit', 'credit') NOT NULL;


CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department VARCHAR(100) NOT NULL,
    no_of_beds INT NOT NULL,
    ward_no VARCHAR(50) NOT NULL,
);

CREATE TABLE medical_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT,
    doctor_id INT,
    department_id INT,
    ward_no VARCHAR(10),
    bed_no VARCHAR(10),
    treatment TEXT,
    date_of_record DATE,
    FOREIGN KEY (patient_id) REFERENCES patients_registration(id),
    FOREIGN KEY (doctor_id) REFERENCES doctors(id),
    FOREIGN KEY (department_id) REFERENCES departments(id)
);

-- CREATE TABLE bills (
--     id INT AUTO_INCREMENT PRIMARY KEY,
--     payment_id INT NOT NULL,
--     bill_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     amount DECIMAL(10, 2) NOT NULL,
--     FOREIGN KEY (payment_id) REFERENCES payments(id)
-- );
