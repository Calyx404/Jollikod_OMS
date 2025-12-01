const layer = document.querySelector(".layer");
const layerOpen = document.querySelector(".layer-open");
const layerClose = document.querySelector(".layer-close");

layerOpen.addEventListener("click", () => {
  layer.classList.add("layer-active");
});

layerClose.addEventListener("click", () => {
  layer.classList.remove("layer-active");
});
