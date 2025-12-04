document.querySelectorAll(".layer-open").forEach((openBtn) => {
  openBtn.addEventListener("click", () => {
    const targetId = openBtn.dataset.layerTarget;
    const targetLayer = document.getElementById(targetId);

    if (!targetLayer) return;

    // 1. Close ALL active layers
    document.querySelectorAll(".layer.layer-active").forEach((activeLayer) => {
      activeLayer.classList.remove("layer-active");
    });

    // 2. Open the target
    targetLayer.classList.add("layer-active");
  });
});

// Close handlers
document.querySelectorAll(".layer-close").forEach((closeBtn) => {
  closeBtn.addEventListener("click", () => {
    const parentLayer = closeBtn.closest(".layer");
    if (parentLayer) {
      parentLayer.classList.remove("layer-active");
    }
  });
});
