# 🧪 Testing Guide - Database & API Verification

## Part 1: Execute Database Setup

### Step 1: Create Database

```sql
-- In phpMyAdmin SQL editor:
CREATE DATABASE aast_web;
```

### Step 2: Run Setup Script

- Open `setup_database.sql` from project folder
- Copy entire contents
- Paste into phpMyAdmin SQL editor
- Click "Go" to execute

### Step 3: Verify Tables Created

```sql
-- Run these queries to verify:
SHOW TABLES;

-- Should show 5 tables:
-- 1. courses
-- 2. enrollments
-- 3. lesson_progress
-- 4. lessons
-- 5. users
```

### Step 4: Verify Sample Data

```sql
-- Check courses inserted (should be 12)
SELECT COUNT(*) as total_courses FROM courses;

-- View all courses
SELECT CourseID, CourseName, Price, Level FROM courses;

-- Check specific course
SELECT * FROM courses WHERE CourseName LIKE '%AutoCAD%';
```

---

## Part 2: Test User Authentication

### Test 1: Create New User

```bash
# Browser: http://localhost/web-project-aast/signup.html

# Form data:
Full Name: John Doe
Email: john@example.com
Password: test123456
Confirm Password: test123456

# Result: Should redirect to login
```

### Test 2: Verify User in Database

```sql
-- Check user created
SELECT UserID, Name, Email, Role FROM users
WHERE Email = 'john@example.com';

-- Should show:
-- UserID: 1, Name: John Doe, Email: john@example.com, Role: student
```

### Test 3: Login with New User

```bash
# Browser: http://localhost/web-project-aast/login.html

# Form data:
Email: john@example.com
Password: test123456

# Result: Should redirect to student/sdashboard.php
```

### Test 4: Verify Session

```bash
# After login, in browser console:
document.cookie
# Should show: PHPSESSID=...

# Or check PHP:
# $_SESSION['UserID'] = 1
# $_SESSION['Name'] = 'John Doe'
# $_SESSION['Role'] = 'student'
```

---

## Part 3: Test Course Browsing

### Test 1: Load Courses Page

```bash
# Browser: http://localhost/web-project-aast/Courses.php

# Expected:
# - Grid of 12 courses
# - Filter sidebar with categories and levels
# - Sort dropdown
# - Each course shows: image, title, instructor, rating, price
# - "Enroll Now" button (if not logged in)
```

### Test 2: Test Filters

```bash
# Click Category: Engineering
# Expected: Shows only Engineering courses (AutoCAD, etc.)

# Click Level: Beginner
# Expected: Shows only Beginner level courses

# Combine filters
# Expected: Shows courses matching both filters
```

### Test 3: Test Sorting

```bash
# Sort by: Price (Low to High)
# Expected: Maritime (120) → Public Speaking (199) → ...

# Sort by: Rating
# Expected: Highest rated first (AutoCAD 4.9, Python 4.8, etc.)

# Sort by: Newest
# Expected: Web Development (New) first
```

### Test 4: Test Pagination

```bash
# If more than 12 courses:
# Click page 2
# Expected: Next 12 courses displayed

# Pagination buttons should be numbered
```

---

## Part 4: Test Enrollment System

### Test 1: Enroll in Course

```bash
# Browser: http://localhost/web-project-aast/Courses.php
# Logged in as: john@example.com

# Click "Enroll Now" on AutoCAD course
# Result: Should enroll successfully

# Check button: Should now say "Go to Course"
```

### Test 2: Verify Enrollment in Database

```sql
-- Check enrollment created
SELECT * FROM enrollments
WHERE UserID = 1 AND CourseID = 1;

-- Should show:
-- EnrollmentID: 1
-- UserID: 1
-- CourseID: 1 (AutoCAD)
-- Progress: 0
-- Status: Active
-- EnrollmentDate: [current timestamp]
```

### Test 3: Prevent Duplicate Enrollment

```bash
# Click "Go to Course" and go back to Courses.php
# Try clicking any course button again
# Try clicking same AutoCAD course again

# Expected: Either no change or error message
# Database should still show only 1 enrollment
```

### Test 4: Test Enrollment for Multiple Courses

```bash
# Enroll in 3 different courses:
# 1. AutoCAD
# 2. Python for Data Analysis
# 3. Advanced Excel

# Browser: my_courses.php

# Expected: All 3 courses show in "My Courses"
```

---

## Part 5: Test Student Dashboard

### Test 1: View Dashboard

```bash
# Browser: http://localhost/web-project-aast/student/sdashboard.php

# Expected to see:
# 1. Welcome message: "Welcome back, John Doe!"
# 2. Stats cards:
#    - Courses Enrolled: 3
#    - Completed: 0
#    - Average Progress: 0%
# 3. "Your Learning Journey" section
# 4. Course preview cards for enrolled courses
```

### Test 2: Click "Continue Learning"

```bash
# From dashboard preview card
# Click "Continue Learning" on AutoCAD course

# Expected: Redirects to course_detail.php?course_id=1
```

### Test 3: Click "Browse More"

```bash
# From dashboard preview card
# Click "Browse More"

# Expected: Redirects to Courses.php
```

---

## Part 6: Test My Courses Page

### Test 1: View My Courses

```bash
# Browser: http://localhost/web-project-aast/student/my_courses.php

# Expected:
# - Grid showing 3 enrolled courses
# - Each card shows:
#   - Course image
#   - Course title
#   - Instructor name
#   - Progress bar (0%)
#   - "Continue Learning" button
#   - "Drop Course" button
```

### Test 2: Course Progress Display

```bash
# All courses should show:
# - Progress bar (currently 0% - gray)
# - "0% Complete" text
# - "In Progress" badge (blue)
```

### Test 3: Drop a Course

```bash
# Click "Drop Course" on Python course
# Confirm: "Are you sure you want to drop this course?"
# Click OK

# Expected: Python course disappears from list
# Only 2 courses remain: AutoCAD and Excel
```

### Test 4: Verify Drop in Database

```sql
-- Check enrollment status changed
SELECT Status FROM enrollments
WHERE UserID = 1 AND CourseID = 2;

-- Should show: Dropped
```

---

## Part 7: Test Course Detail Page

### Test 1: Load Course Detail

```bash
# From My Courses: Click "Continue Learning" on AutoCAD
# URL: student/course_detail.php?course_id=1

# Expected:
# - Course title: "Mastering AutoCAD 2026"
# - Video player placeholder (black box)
# - Course description
# - "Mark as Complete" button
# - "Download Resources" button
# - Sidebar with lesson list
```

### Test 2: View Lessons

```bash
# In sidebar, should show:
# - Multiple lessons
# - Duration for each
# - Completed status indicator
# - Lesson is clickable

# Example:
# ▶ Introduction to CAD - 30 min
# ▶ Basic Tools & Navigation - 45 min
# etc.
```

### Test 3: Mark Lesson Complete

```bash
# Check "Mark this lesson as completed" checkbox
# Click "Mark as Complete" button

# Expected:
# - Lesson marked as done
# - Progress updates
# - Button changes to "Completed"
```

---

## Part 8: Test Logout

### Test 1: Logout

```bash
# Click "Logout" in navbar
# Result: Redirects to login.html

# URL: login.html?logged_out=true
```

### Test 2: Verify Session Cleared

```sql
-- Session should be destroyed
-- If you try to access student/sdashboard.php without login
-- Should redirect to login.html
```

### Test 3: Try Accessing Protected Page

```bash
# Directly visit: http://localhost/web-project-aast/student/my_courses.php
# Without being logged in
# Expected: Redirects to login.html
```

---

## Part 9: Database Query Reference

```sql
-- All courses
SELECT * FROM courses;

-- Courses by category
SELECT * FROM courses WHERE Category = 'Technology';

-- Courses by level
SELECT * FROM courses WHERE Level = 'Beginner';

-- User enrollments
SELECT c.CourseName, e.Progress, e.Status
FROM enrollments e
JOIN courses c ON e.CourseID = c.CourseID
WHERE e.UserID = 1;

-- Active enrollments only
SELECT c.CourseName, e.Progress
FROM enrollments e
JOIN courses c ON e.CourseID = c.CourseID
WHERE e.UserID = 1 AND e.Status = 'Active';

-- User completion stats
SELECT
  COUNT(*) as total_enrolled,
  SUM(CASE WHEN Status = 'Completed' THEN 1 ELSE 0 END) as completed,
  AVG(Progress) as avg_progress
FROM enrollments
WHERE UserID = 1 AND Status IN ('Active', 'Completed');

-- Check for duplicate enrollment attempts
SELECT * FROM enrollments
WHERE UserID = 1 AND CourseID = 1;

-- Lesson list for course
SELECT * FROM lessons WHERE CourseID = 1 ORDER BY LessonOrder;

-- Lesson progress
SELECT * FROM lesson_progress WHERE EnrollmentID = 1;
```

---

## 🔍 Expected Error Messages

### When Testing:

```
Already Enrolled:
- URL contains: error=already_enrolled
- Behavior: Button shows "Go to Course"

Not Logged In:
- Redirects to: login.html?error=not_logged_in
- Trying to enroll as guest

Course Not Found:
- URL contains: error=course_not_found
- Invalid course ID

Database Connection Error:
- "Connection failed: ..."
- Check DB/db_connect.php credentials
```

---

## ✅ Final Verification Checklist

- [ ] Database created successfully
- [ ] All 5 tables created
- [ ] 12 sample courses inserted
- [ ] Can create new user account
- [ ] Can login with new account
- [ ] Session variables set correctly
- [ ] Can browse courses with filters
- [ ] Can sort courses
- [ ] Can enroll in course
- [ ] Enrollment recorded in database
- [ ] "Enroll" button changes to "Go to Course"
- [ ] Dashboard shows correct stats
- [ ] My Courses page displays enrolled courses
- [ ] Can continue learning (course detail opens)
- [ ] Can drop course
- [ ] Dropped course no longer shows
- [ ] Can logout
- [ ] Protected pages redirect when not logged in
- [ ] All pages are responsive
- [ ] No database errors in logs

---

**Total Test Time:** ~45 minutes
**Success Indicator:** All checks pass ✅
**Status:** Ready for production testing
