# 🚀 QUICK START CHECKLIST

## Step 1: Database Setup (5 minutes)

- [ ] Open phpMyAdmin (http://localhost/phpmyadmin)
- [ ] Create database named `aast_web`
- [ ] Click database → SQL tab
- [ ] Copy entire contents of `setup_database.sql` file
- [ ] Paste and execute
- [ ] Verify: 5 new tables created + 12 courses inserted

## Step 2: Test Authentication (5 minutes)

- [ ] Open http://localhost/web-project-aast/
- [ ] Click "Signup"
- [ ] Fill form and create student account
- [ ] Click "Login"
- [ ] Login with created credentials
- [ ] Should redirect to student dashboard

## Step 3: Test Enrollment (5 minutes)

- [ ] Click "Browse Courses" or go to Courses.php
- [ ] View all courses (should show 12)
- [ ] Try filters (Category, Level) - should work
- [ ] Try sorting dropdown
- [ ] Click "Enroll Now" on any course
- [ ] Should see "Go to Course" button instead
- [ ] Enrollment created in database ✓

## Step 4: Test Student Features (5 minutes)

- [ ] Click "My Courses" in navbar
- [ ] Should show enrolled courses
- [ ] Click "Continue Learning"
- [ ] Should open course detail page with lessons
- [ ] Go back to "My Courses"
- [ ] Try "Drop Course" button
- [ ] Course should disappear from list ✓

## Step 5: Test Navigation (3 minutes)

- [ ] Dashboard should show stats
  - Total enrolled
  - Completed courses
  - Average progress
- [ ] Recent courses preview should display
- [ ] All links should navigate correctly
- [ ] Logout should clear session ✓

---

## ✅ System Ready When:

- [x] Database created
- [x] Can signup/login
- [x] Can browse courses
- [x] Can enroll/unenroll
- [x] Can view enrolled courses
- [x] Dashboard displays properly
- [x] All pages are responsive

---

## 🔗 Important URLs:

```
Home: http://localhost/web-project-aast/
Courses: http://localhost/web-project-aast/Courses.php
Login: http://localhost/web-project-aast/login.html
Signup: http://localhost/web-project-aast/signup.html
Dashboard: http://localhost/web-project-aast/student/sdashboard.php
My Courses: http://localhost/web-project-aast/student/my_courses.php
phpMyAdmin: http://localhost/phpmyadmin
```

---

## 🐛 Troubleshooting:

**Can't connect to database?**

- Check XAMPP MySQL is running
- Check credentials in DB/db_connect.php
- Verify database `aast_web` exists

**Can't login?**

- Check email and password are correct
- Make sure account was created
- Clear browser cookies

**Courses page is blank?**

- Run setup_database.sql file
- Check courses table has data
- Check error logs

**Can't enroll?**

- Make sure you're logged in as student
- Check browser console for errors
- Verify enrollments table is created

---

## 📊 Database Quick Check:

Run these in phpMyAdmin SQL editor:

```sql
-- Check users
SELECT COUNT(*) FROM users;

-- Check courses (should be 12)
SELECT COUNT(*) FROM courses;

-- Check enrollments (should increase)
SELECT * FROM enrollments;

-- Check your enrollments
SELECT c.CourseName, e.Status FROM enrollments e
JOIN courses c ON e.CourseID = c.CourseID
WHERE e.UserID = 1;
```

---

## 🎯 Next Steps:

1. ✅ Get enrollment working (DONE)
2. Add lesson video uploads
3. Create quiz system
4. Add certificate generation
5. Implement payment gateway
6. Build teacher dashboard
7. Add review/rating system
8. Implement notifications

---

**Time to Complete Setup:** ~20 minutes
**Difficulty:** Easy ⭐⭐
**Status:** Ready to Deploy 🚀
