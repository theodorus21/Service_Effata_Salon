const sidebar = document.querySelector("#sidebar");
const menuButton = document.querySelector("#mobile-menu-button");
const backdrop = document.querySelector("#sidebar-backdrop");
const dateLabel = document.querySelector("#current-date");

document.querySelectorAll(".nav-group-heading[aria-controls]").forEach((button) => {
    const submenu = document.getElementById(button.getAttribute("aria-controls"));

    if (!submenu) {
        return;
    }

    button.addEventListener("click", () => {
        const isExpanded = button.getAttribute("aria-expanded") === "true";
        button.setAttribute("aria-expanded", String(!isExpanded));
        submenu.hidden = isExpanded;
    });
});

const closeSidebar = () => {
    sidebar.classList.remove("sidebar-open");
    backdrop.classList.remove("backdrop-visible");
    menuButton.setAttribute("aria-expanded", "false");
};

menuButton.addEventListener("click", () => {
    const isOpen = sidebar.classList.toggle("sidebar-open");
    backdrop.classList.toggle("backdrop-visible", isOpen);
    menuButton.setAttribute("aria-expanded", String(isOpen));
});

backdrop.addEventListener("click", closeSidebar);

sidebar.querySelectorAll(".nav-item[href^='#']").forEach((link) => {
    link.addEventListener("click", closeSidebar);
});

dateLabel.textContent = new Intl.DateTimeFormat("id-ID", {
    weekday: "long",
    day: "numeric",
    month: "long",
    year: "numeric",
}).format(new Date()).toLocaleUpperCase("id-ID");
