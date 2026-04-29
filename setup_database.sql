-- AAST Web Database Setup
-- Run this file to create all necessary tables

-- Create users table (if not exists)
CREATE TABLE IF NOT EXISTS users (
    UserID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Email VARCHAR(100) UNIQUE NOT NULL,
    Password VARCHAR(255) NOT NULL,
    Role ENUM('student', 'teacher', 'admin') DEFAULT 'student',
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create courses table
CREATE TABLE IF NOT EXISTS courses (
    CourseID INT AUTO_INCREMENT PRIMARY KEY,
    CourseName VARCHAR(255) NOT NULL,
    Instructor VARCHAR(100) NOT NULL,
    Description TEXT,
    Price DECIMAL(10, 2) NOT NULL,
    Rating FLOAT DEFAULT 0,
    ReviewCount INT DEFAULT 0,
    Level ENUM('Beginner', 'Intermediate', 'Advanced') DEFAULT 'Beginner',
    Category VARCHAR(100),
    ImageURL VARCHAR(255),
    Duration INT COMMENT 'Duration in hours',
    Badge VARCHAR(50) COMMENT 'Bestseller, New, etc.',
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create enrollments table
CREATE TABLE IF NOT EXISTS enrollments (
    EnrollmentID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    CourseID INT NOT NULL,
    EnrollmentDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Progress INT DEFAULT 0 COMMENT 'Percentage completed (0-100)',
    Status ENUM('Active', 'Completed', 'Dropped') DEFAULT 'Active',
    CompletionDate DATETIME,
    FOREIGN KEY (UserID) REFERENCES users(UserID) ON DELETE CASCADE,
    FOREIGN KEY (CourseID) REFERENCES courses(CourseID) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (UserID, CourseID)
);

-- Create lessons table
CREATE TABLE IF NOT EXISTS lessons (
    LessonID INT AUTO_INCREMENT PRIMARY KEY,
    CourseID INT NOT NULL,
    LessonTitle VARCHAR(255) NOT NULL,
    LessonDescription TEXT,
    VideoURL VARCHAR(255),
    Duration INT COMMENT 'Duration in minutes',
    LessonOrder INT,
    FOREIGN KEY (CourseID) REFERENCES courses(CourseID) ON DELETE CASCADE
);

-- Create progress table
CREATE TABLE IF NOT EXISTS lesson_progress (
    ProgressID INT AUTO_INCREMENT PRIMARY KEY,
    EnrollmentID INT NOT NULL,
    LessonID INT NOT NULL,
    Completed BOOLEAN DEFAULT FALSE,
    CompletedAt DATETIME,
    FOREIGN KEY (EnrollmentID) REFERENCES enrollments(EnrollmentID) ON DELETE CASCADE,
    FOREIGN KEY (LessonID) REFERENCES lessons(LessonID) ON DELETE CASCADE,
    UNIQUE KEY unique_progress (EnrollmentID, LessonID)
);

-- Insert sample courses
INSERT INTO courses (CourseName, Instructor, Description, Price, Rating, ReviewCount, Level, Category, Duration, Badge)
VALUES
('Mastering AutoCAD 2026', 'Eng. Ahmed Ali, AAST Engineering', 'Learn professional CAD design with industry-standard tools', 499.99, 4.9, 1240, 'Advanced', 'Engineering', 40, 'Bestseller'),
('Python for Data Analysis', 'AAST Computing Center', 'Master Python for data science and analysis', 799.99, 4.8, 3100, 'Intermediate', 'Technology', 50, NULL),
('Advanced Excel for Business', 'Dr. Nadia Hassan, AAST Management', 'Excel spreadsheets and business analytics', 399.99, 4.7, 950, 'Intermediate', 'Business', 30, NULL),
('Maritime Safety & Security', 'Capt. Sameh Omar, Maritime College', 'Essential maritime safety protocols', 120.00, 4.6, 420, 'Beginner', 'Maritime', 20, NULL),
('Web Development Mastery', 'Eng. Sara Mohamed, Computer Science', 'Full-stack web development from scratch', 650.00, 4.9, 520, 'Advanced', 'Technology', 60, 'New'),
('Digital Marketing Strategy', 'Dr. Hassan Khalil, Marketing Department', 'Modern digital marketing techniques', 299.99, 4.5, 810, 'Beginner', 'Business', 25, NULL),
('Financial Accounting Essentials', 'Prof. Ahmed Kamal, Business School', 'Fundamentals of financial accounting', 449.99, 4.7, 670, 'Beginner', 'Business', 35, NULL),
('Machine Learning Fundamentals', 'Dr. Fatima Al-Ansari, AI Lab', 'Introduction to machine learning and AI', 899.99, 4.9, 2350, 'Advanced', 'Technology', 70, 'Bestseller'),
('Project Management Pro', 'Eng. Mohammed Said, Engineering', 'Professional project management skills', 549.99, 4.6, 734, 'Intermediate', 'Engineering', 40, NULL),
('Public Speaking & Presentation', 'Dr. Layla Mansour, Communication', 'Master effective communication skills', 199.99, 4.8, 1220, 'Beginner', 'Business', 20, NULL),
('Mobile App Development', 'Eng. Omar Hassan, Tech Academy', 'Build professional mobile applications', 749.99, 4.7, 1890, 'Advanced', 'Technology', 55, NULL),
('Graphic Design Fundamentals', 'Prof. Hana Karim, Design School', 'Learn graphic design principles', 349.99, 4.5, 945, 'Beginner', 'Technology', 28, NULL);
