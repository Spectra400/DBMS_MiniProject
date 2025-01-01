Developers
      Jithesh P Shetty
      Dheeraj KB


Placement Management System
Introduction
The Placement Management System is a comprehensive digital platform designed to streamline the placement process in educational institutions. This system facilitates seamless interaction between students, placement officers, and Heads of Departments (HODs), automating tasks such as placement drive management, application tracking, and eligibility verification. Built with modern web technologies, the platform ensures efficiency, transparency, and reliability in placement workflows.

Features
  Students:
         View and apply for placement drives.
         Track participation status and update application details.
  Placement Officers:
         Create and manage placement drives.
         View eligible students for placement opportunities.
         Track and manage student applications.
   HODs:
        Monitor department-wise student participation.
        Grant attendance for placement activities.
       View reports and student performance.
       
Technologies Used
       Frontend: HTML, CSS
       Backend: PHP
       Database: MySQL
Development Environment: XAMPP

Automation: Stored Procedures and Triggers

Database Design
Tables:
Students
Placement Drives
Placement Participation
Departments
HODs
Stored Procedure: get_eligible_students
         Retrieves a list of students eligible for a specific placement drive.
Trigger: before_student_insert
        Ensures the department exists when adding a new student.
        
Installation and Setup
Clone the repository:

git clone https://github.com/your-repository-url.git

Import the database:
       Import the placement_management.sql file into your MySQL database.
Configure the database:
       Update the db.php file with your MySQL credentials.
Run the project:
         Start the XAMPP server and place the project folder in the htdocs directory.
        Access the project at http://localhost/placement_management.
Usage
Login:
     Students, placement officers, and HODs can log in using their credentials.
Placement Drive Management:
     Placement officers can add drives, set eligibility criteria, and view applicants.
Eligibility Verification:
     The system automatically checks eligibility based on CGPA using stored procedures.
Participation Tracking:
     Students can view their application status for each drive.
