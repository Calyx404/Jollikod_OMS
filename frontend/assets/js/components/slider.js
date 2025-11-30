const slider = document.querySelector(".slider");
const leftButton = document.querySelector(".slider-left-btn");
const rightButton = document.querySelector(".slider-right-btn");

leftButton.addEventListener("click", () => {
  slider.classList.add("active");
});

rightButton.addEventListener("click", () => {
  slider.classList.remove("active");
});
