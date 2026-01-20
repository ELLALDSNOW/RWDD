
const hamburger = document.getElementById("hamburger");
const dropdown = document.getElementById("dropdown");


hamburger.addEventListener("click", () => {
  dropdown.classList.toggle("show");
});


window.addEventListener("click", (e) => {
  if (!hamburger.contains(e.target) && !dropdown.contains(e.target)) {
    dropdown.classList.remove("show");
  }
});


window.addEventListener("keydown", (e) => {
  if (e.key === "Escape" && dropdown.classList.contains("show")) {
    dropdown.classList.remove("show");
  }
});
