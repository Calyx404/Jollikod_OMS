const slider = document.querySelector(".slider");
const sliderLeftButton = document.querySelector(".slider-left-btn");
const sliderRightButton = document.querySelector(".slider-right-btn");

sliderLeftButton.addEventListener("click", () => {
  slider.classList.add("active");
});

sliderRightButton.addEventListener("click", () => {
  slider.classList.remove("active");
});
