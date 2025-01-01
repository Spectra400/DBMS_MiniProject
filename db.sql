CREATE DATABASE placement_management;

USE placement_management;

-- Table for students
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    department VARCHAR(255),
    cgpa FLOAT,
    usn VARCHAR(20) NOT NULL UNIQUE
);


CREATE TABLE IF NOT EXISTS hods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    DEPARTMENT VARCHAR(255)
);


-- Table for placement drives
CREATE TABLE placement_drives (
    drive_id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(100),
    date DATE,
    time TIME,
    eligibility_criteria FLOAT
);

DELIMITER $$

CREATE PROCEDURE get_eligible_students (IN driveID INT)
BEGIN
    SELECT s.student_id , s.name, s.department, s.cgpa,s.usn
    FROM students s
    INNER JOIN placement_drives d
    ON s.cgpa >= d.eligibility_criteria
    WHERE d.drive_id = driveID;
END$$

DELIMITER ;

-- Table for Placement Cells
CREATE TABLE placement_cells (
    placement_cell_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255)
);


CREATE TABLE placement_participation (
    participation_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    usn VARCHAR(20) NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    drive_id INT NOT NULL,
    cgpa FLOAT NOT NULL,
    status ENUM('Selected', 'Not Selected', 'Pending'),
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (usn) REFERENCES students(usn),
    FOREIGN KEY (drive_id) REFERENCES placement_drives(drive_id)
);

DELIMITER $$

CREATE TRIGGER set_pending_status
BEFORE INSERT ON placement_participation
FOR EACH ROW
BEGIN
    SET NEW.status = 'Pending';
END$$

DELIMITER ;

CREATE TABLE IF NOT EXISTS department (
    department_id INT AUTO_INCREMENT PRIMARY KEY,
    department_name VARCHAR(255) NOT NULL UNIQUE
);

ALTER TABLE students
ADD CONSTRAINT fk_students_department FOREIGN KEY (department) REFERENCES department(department_name);

ALTER TABLE hods
ADD CONSTRAINT fk_hods_department FOREIGN KEY (department) REFERENCES department(department_name);

DELIMITER $$

CREATE TRIGGER before_student_insert
BEFORE INSERT ON students
FOR EACH ROW
BEGIN
    IF NOT EXISTS (SELECT 1 FROM department WHERE department_name = NEW.department) THEN
        INSERT INTO department (department_name) VALUES (NEW.department);
    END IF;
END$$

DELIMITER ;

DELIMITER $$

CREATE TRIGGER before_hod_insert
BEFORE INSERT ON hods
FOR EACH ROW
BEGIN
    IF NOT EXISTS (SELECT 1 FROM department WHERE department_name = NEW.department) THEN
        INSERT INTO department (department_name) VALUES (NEW.department);
    END IF;
END$$

DELIMITER ;
