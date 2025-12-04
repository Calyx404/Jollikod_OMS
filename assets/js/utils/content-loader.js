document.addEventListener("DOMContentLoaded", () => {
  navigate(null, "../pages/home/home.php");
});

function navigate(event, page) {
  if (event) event.preventDefault();

  const app = document.querySelector("#app");
  app.src = page;

  app.onload = () => {
    try {
      const innerTitle = app.contentDocument.title;
      document.title = `Jollikod - ${innerTitle}`;
    } catch (error) {
      console.warn("Could not read iframe title:", error);
    }
  };
}
