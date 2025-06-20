CREATE DATABASE jru_pulse; --database


--office table
CREATE TABLE offices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    code VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO offices (name, code, description) VALUES
('Registrar\'s Office', 'REG', 'Handles student registration and academic records'),
('Student Accounts Office', 'SAO', 'Manages student financial accounts and billing'),
('Cashier', 'CASH', 'Responsible for payment processing and financial transactions'),
('Library', 'LIB', 'Provides library services and resources for students and faculty'),
('Information Technology Office', 'IT', 'Offers technical support and IT services'),
('Medical & Dental Clinic', 'MED', 'Provides health services to students'),
('Guidance & Testing Office', 'GTO', 'Offers student guidance and testing services'),
('Student Development Office', 'SDO', 'Focuses on student affairs and development programs'),
('Athletics Office', 'ATH', 'Manages sports and athletics programs'),
('Customer Advocacy Office', 'CAO', 'Handles customer service and advocacy issues'),
('Engineering & Maintenance Office', 'EMO', 'Responsible for facilities management and maintenance');


--services PK ofc_id
CREATE TABLE services (
    id INT PRIMARY KEY AUTO_INCREMENT,
    office_id INT NOT NULL,
    name VARCHAR(300) NOT NULL,
    code VARCHAR(100) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (office_id) REFERENCES offices(id) ON DELETE CASCADE,
    UNIQUE KEY unique_service_code (office_id, code)
);

-- Registrar's Office (ID = 1)
INSERT INTO services (office_id, name, code) VALUES
(1, 'Document request', 'reg-doc-req');

-- Student Accounts Office (ID = 2)
INSERT INTO services (office_id, name, code) VALUES
(2, 'Onsite inquiry', 'sao-ons-inq'),
(2, 'Online inquiry', 'sao-onl-inq');

-- Cashier (ID = 3)
INSERT INTO services (office_id, name, code) VALUES
(3, 'Onsite Payment', 'cash-ons-pay');

-- Library (ID = 4)
INSERT INTO services (office_id, name, code) VALUES
(4, 'Online Library Services (Email, social media platforms)', 'lib-onl-svc'),
(4, 'Face-to-Face Library Services', 'lib-ff-svc'),
(4, 'Borrowing of printed materials', 'lib-brw-mat'),
(4, 'Online Library Instructions', 'lib-onl-inst'),
(4, 'Participation on Library activities and programs', 'lib-acts-prog');

-- Information Technology Office (ID = 5)
INSERT INTO services (office_id, name, code) VALUES
(5, 'Online Inquiry / Technical assistance', 'ito-onl-asst'),
(5, 'Face-To-Face inquiry assistance', 'ito-ff-inq'),
(5, 'Technical Assistance during events', 'ito-evnt-asst'),
(5, 'Classroom/Office Technical Assistance', 'ito-room-asst');

-- Medical & Dental Clinic (ID = 6)
INSERT INTO services (office_id, name, code) VALUES
(6, 'Medical check-up/consultation', 'med-med-cons'),
(6, 'Dental check-up/consultation', 'med-den-cons'),
(6, 'Request for medical clearances', 'med-clr-req');

-- Guidance & Testing Office (ID = 7)
INSERT INTO services (office_id, name, code) VALUES
(7, 'Request for Good Moral Certificate', 'gto-gmc-req'),
(7, 'Request for Counseling', 'gto-coun-req'),
(7, 'Scholarship Inquiry', 'gto-schol-inq');

-- Student Development Office (ID = 8)
INSERT INTO services (office_id, name, code) VALUES
(8, 'Filing of a complaint', 'sdo-comp-file'),
(8, 'Request for ID Replacement Form', 'sdo-id-repl'),
(8, 'Request for Admission Slip', 'sdo-adm-slip'),
(8, 'Request for Temporary School ID', 'sdo-temp-id');

-- Athletics Office (ID = 9)
INSERT INTO services (office_id, name, code) VALUES
(9, 'Borrowing of sports equipment', 'ath-brw-equip');

-- Customer Advocacy Office (ID = 10)
INSERT INTO services (office_id, name, code) VALUES
(10, 'General Inquiries', 'cao-gen-inq');

-- Engineering and Maintenance Office (ID = 11)
INSERT INTO services (office_id, name, code) VALUES
(11, 'Request for vehicle', 'emo-veh-req'),
(11, 'Facility maintenance', 'emo-fac-maint'),
(11, 'Auditorium reservation', 'emo-aud-resv');

