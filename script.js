// Simple JavaScript for Courses page

document.addEventListener("DOMContentLoaded", function () {
  const courseButtons = document.querySelectorAll(".course-btn");

  courseButtons.forEach((button) => {
    button.addEventListener("click", function (event) {
      event.preventDefault();
      const courseTitle = this.parentElement.querySelector("h2").textContent;
      alert(`Starting ${courseTitle}! This feature is coming soon.`);
    });
  });
});
