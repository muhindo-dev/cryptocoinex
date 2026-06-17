// List of courses
const courses = [
  { title: "Programming Fundamentals", category: "programming", img: "images/courses/programming.jpg", description: "Learn logic, problem-solving, and Python basics.", link: "#apply" },
  { title: "Web Development", category: "web-dev", img: "images/courses/web-development.jpg", description: "Build responsive websites with HTML, CSS, JS.", link: "#apply" },
  { title: "Computer Repair & Maintenance", category: "hardware", img: "images/courses/hardware.jpg", description: "Diagnose, repair, and maintain computers.", link: "#apply" },
  { title: "AI & Machine Learning", category: "ai", img: "images/courses/ai.jpg", description: "Build intelligent systems using real datasets.", link: "#apply" },
  { title: "Networking Essentials", category: "networking", img: "images/courses/networking.jpg", description: "Learn networking fundamentals and configurations.", link: "#apply" },
  { title: "Mobile App Development", category: "mobile-apps", img: "images/courses/mobile-apps.jpg", description: "Create mobile apps for Android using Java/Kotlin.", link: "#apply" },
  { title: "Cloud Computing Basics", category: "cloud", img: "images/courses/cloud-computing.jpg", description: "Explore cloud concepts and services.", link: "#apply" },
  { title: "Internet of Things (IoT)", category: "iot", img: "images/courses/iot.jpg", description: "Connect devices and build smart IoT solutions.", link: "#apply" },
];

// Elements
const coursesContent = document.getElementById("coursesContent");
const categoryItems = document.querySelectorAll("#courseCategories li");

// Function to display courses
function displayCourses(category) {
  coursesContent.innerHTML = "";
  const filtered = category === "all" ? courses : courses.filter(c => c.category === category);
  filtered.forEach(course => {
    const card = document.createElement("div");
    card.className = "course-card";
    card.innerHTML = `
      <img src="${course.img}" alt="${course.title}">
      <h3>${course.title}</h3>
      <p>${course.description}</p>
      <a href="${course.link}" class="apply-btn">Apply Now</a>
    `;
    coursesContent.appendChild(card);
  });
}

// Initialize all courses
displayCourses("all");

// Category click event
categoryItems.forEach(item => {
  item.addEventListener("click", () => {
    categoryItems.forEach(i => i.classList.remove("active"));
    item.classList.add("active");
    displayCourses(item.dataset.category);
  });
});clear
