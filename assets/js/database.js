const projects = JSON.parse(localStorage.getItem("projects")) || [];

function addProject(name, date) {
    projects.push({ name, date, status: "In Progress" });
    localStorage.setItem("projects", JSON.stringify(projects));
}

function getProjects() {
    return projects;
}
