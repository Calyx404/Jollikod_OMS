const iframe = document.querySelector("#page-frame");
const pages = document.querySelectorAll(".page-container");

pages.forEach((item) => {
  item.addEventListener("click", () => {
    const target = item.dataset.target;
    if (!target) return;

    iframe.src = target;

    pages.forEach((p) => p.classList.remove("active-page"));
    item.classList.add("active-page");
  });
});
