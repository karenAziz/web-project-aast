# AAST Web Platform - Enrollment System Setup Guide

## 🎯 Project Overview

Complete PHP-based learning management system with enrollment, course management, and student tracking.

## 📋 Prerequisites

- **XAMPP** (or any Apache + MySQL + PHP server)
- **PHP 7.4+**
- **MySQL 5.7+**
- **Modern web browser**

---

## 🚀 Quick Setup Instructions

### Step 1: Database Setup

1. Open **phpMyAdmin** (typically `http://localhost/phpmyadmin`)
2. Create a new database named `aast_web`
3. Open the SQL editor and run the contents of **`setup_database.sql`** file
   - This creates all tables: `users`, `courses`, `enrollments`, `lessons`, `lesson_progress`
   - Automatically inserts 12 sample courses

### Step 2: Project Structure

```
web-project-aast/
├── index.html                 # Home page
├── Courses.php               # Main courses catalog (DYNAMIC)
├── enroll.php                # Enrollment handler
├── logout.php                # Logout handler
├── style.css                 # Main styles
├── login.html                # Login page
├── signup.html               # Signup page
├── DB/
│   └── db_connect.php        # Database connection
├── student/
│   ├── sdashboard.php        # Student dashboard
│   ├── my_courses.php        # Student's enrolled courses
│   ├── course_detail.php     # Course content viewer
│   └── drop_course.php       # Course drop handler
├── Teacher/
│   ├── Courses.html          # Teacher courses page (STATIC)
│   └── courses-page.css      # Courses page styles
└── admin/
    └── (admin files)
```

---

## 🔐 Database Connection

**File:** `DB/db_connect.php`

```php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "aast_web";
```

**Note:** These are XAMPP default credentials. Update if using different server.

---

## 👤 Authentication Flow

### User Registration (Signup)

1. User fills signup form → `admin/signup.php`
2. Password hashed with `PASSWORD_DEFAULT`
3. User data inserted into `users` table
4. Redirected to login page

### User Login

1. User enters email & password → `Login.php`
2. Email looked up in `users` table
3. Password verified with `password_verify()`
4. Session variables set:
   - `$_SESSION['UserID']`
   - `$_SESSION['Name']`
   - `$_SESSION['Role']` (student/teacher/admin)
5. Redirected to appropriate dashboard based on role

### Logout

- All session data destroyed
- Redirected to login page

---

## 📚 Enrollment System

### For Students:

**1. Browse Courses** (`Courses.php`)

- View all available courses
- Filter by Category (Engineering, Business, Technology, Maritime)
- Filter by Level (Beginner, Intermediate, Advanced)
- Sort by Newest, Popularity, Price, Rating
- Shows "Enroll Now" button if not enrolled
- Shows "Go to Course" if already enrolled

**2. Enroll in Course** (`enroll.php`)

- POST request with `course_id`
- Creates record in `enrollments` table
- Sets initial progress to 0%
- Status set to 'Active'
- Prevents duplicate enrollments

**3. View My Courses** (`student/my_courses.php`)

- Shows all enrolled courses
- Displays progress bar for each course
- Options to continue or drop course
- Shows empty state if no courses

**4. Learn** (`student/course_detail.php`)

- Access enrolled course content
- View lessons with duration
- Track lesson completion
- Update overall progress
- See course details

**5. Drop Course** (`student/drop_course.php`)

- Changes enrollment status to 'Dropped'
- Removes from active courses

---

## 📊 Database Schema

### users

```sql
UserID (int, PK)
Name (varchar)
Email (varchar, UNIQUE)
Password (varchar, hashed)
Role (enum: student/teacher/admin)
CreatedAt (timestamp)
UpdatedAt (timestamp)
```

### courses

```sql
CourseID (int, PK)
CourseName (varchar)
Instructor (varchar)
Description (text)
Price (decimal)
Rating (float)
ReviewCount (int)
Level (enum: Beginner/Intermediate/Advanced)
Category (varchar)
ImageURL (varchar)
Duration (int, hours)
Badge (varchar: Bestseller/New)
CreatedAt (timestamp)
```

### enrollments

```sql
EnrollmentID (int, PK)
UserID (int, FK)
CourseID (int, FK)
EnrollmentDate (timestamp)
Progress (int, 0-100)
Status (enum: Active/Completed/Dropped)
CompletionDate (datetime)
UNIQUE(UserID, CourseID) - prevents duplicate enrollments
```

### lessons

```sql
LessonID (int, PK)
CourseID (int, FK)
LessonTitle (varchar)
LessonDescription (text)
VideoURL (varchar)
Duration (int, minutes)
LessonOrder (int)
```

### lesson_progress

```sql
ProgressID (int, PK)
EnrollmentID (int, FK)
LessonID (int, FK)
Completed (boolean)
CompletedAt (datetime)
UNIQUE(EnrollmentID, LessonID)
```

---

## 🔄 User Journey

### Student:

1. Sign up → Login → Student Dashboard
2. Browse Courses (filter/sort)
3. Enroll in Course → My Courses
4. Click Course → View Lessons
5. Complete Lessons → Track Progress
6. (Optional) Drop Course
7. Logout

---

## 🛠️ File Actions Reference

| Action       | File                    | Method | Parameters                                    |
| ------------ | ----------------------- | ------ | --------------------------------------------- |
| Sign Up      | admin/signup.php        | POST   | fullname, emails, passwords, confirmpasswords |
| Login        | Login.php               | POST   | emaill, passwordl                             |
| Enroll       | enroll.php              | POST   | course_id                                     |
| View Courses | Courses.php             | GET    | category, level, sort, page                   |
| Drop Course  | student/drop_course.php | POST   | course_id                                     |
| Logout       | logout.php              | GET    | -                                             |

---

## ✅ Testing Checklist

- [ ] Database tables created successfully
- [ ] Can register new student account
- [ ] Can login with created account
- [ ] Student dashboard displays stats
- [ ] Can browse and filter courses
- [ ] Can enroll in course
- [ ] "Go to Course" button appears after enrollment
- [ ] Can view enrolled courses list
- [ ] Can view course details with lessons
- [ ] Can drop course
- [ ] Can logout and login again

---

## 🐛 Common Issues

### "Connection failed"

- Check `DB/db_connect.php` credentials
- Ensure MySQL is running
- Verify database `aast_web` exists

### "Session not working"

- Ensure `session_start()` is at top of every protected PHP file
- Clear browser cookies if needed

### "Can't enroll twice"

- This is by design (UNIQUE constraint on UserID, CourseID)
- User will see "Go to Course" instead of "Enroll"

### Database tables not created

- Run `setup_database.sql` through phpMyAdmin
- Or copy-paste each CREATE TABLE statement individually

---

## 🚀 Future Enhancements

1. **Lesson Video Upload** - Store videos and stream
2. **Quiz System** - Create quizzes for courses
3. **Certificate Generation** - Auto-generate upon completion
4. **Payment Gateway** - Process course purchases
5. **Teacher Dashboard** - Manage courses and student progress
6. **Admin Panel** - Manage users, courses, reports
7. **Notifications** - Email/SMS for course updates
8. **Reviews & Ratings** - Student feedback system
9. **Search Functionality** - Full-text search on courses
10. **Progress Tracking** - Detailed learning analytics

---

## 📝 Important Notes

- **Security:** Passwords are hashed before storage
- **Session Timeout:** Configure in PHP settings
- **XSS Prevention:** Use `htmlspecialchars()` for output
- **SQL Injection:** Use `real_escape_string()` (or prepared statements for production)
- **File Uploads:** Not implemented yet - add validation

---

## 📞 Support

For issues or questions, check:

1. Browser console for JavaScript errors
2. PHP error logs
3. MySQL error messages
4. Session validation

---

**Last Updated:** April 29, 2026
**Version:** 1.0.0
**Status:** ✅ Ready for Testing
