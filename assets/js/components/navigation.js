window.navigate = function (target, triggerElement = null) {
  const iframe = document.querySelector("#page-frame");
  const pages = document.querySelectorAll(".page-container");

  if (!iframe || !target) return;

  iframe.src = target;

  if (triggerElement) {
    pages.forEach((p) => p.classList.remove("active-page"));
    triggerElement.classList.add("active-page");
  }
};

(function () {
  const pages = document.querySelectorAll(".page-container");

  pages.forEach((item) => {
    item.addEventListener("click", () => {
      const target = item.dataset.target;
      window.navigate(target, item);
    });
  });
})();
