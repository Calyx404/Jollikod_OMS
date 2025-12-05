function navigate(event, page) {
  if (event) event.preventDefault();

  const app = document.querySelector("#app");
  app.src = page;

  app.onload = () => {
    try {
      const innerTitle = app.contentDocument.title;
      document.title = `Jollikod - ${innerTitle}`;
    } catch (error) {}
  };
}


// document.addEventListener("DOMContentLoaded", () => {
//   const app = document.querySelector("#app");

//   // 1. Check last visited iframe page
//   let lastPage = localStorage.getItem("last_iframe_page");

//   // 2. If no last page, load home
//   if (!lastPage) {
//     lastPage = "../pages/home/home.php";
//   }

//   loadFrame(lastPage);
// });

// // ---- NAVIGATION FUNCTION ----
// function navigate(event, page) {
//   if (event) event.preventDefault();
//   loadFrame(page);
// }

// // ---- LOAD IFRAME + REMEMBER PAGE ----
// function loadFrame(page) {
//   const app = document.querySelector("#app");

//   // prevent storing logout page
//   if (!page.includes("logout") && !page.includes("destroy-session")) {
//     localStorage.setItem("last_iframe_page", page);
//   }

//   app.src = page;

//   app.onload = () => {
//     try {
//       const innerTitle = app.contentDocument.title;
//       document.title = `Jollikod - ${innerTitle}`;
//     } catch (error) {
//       console.warn("Could not read iframe title:", error);
//     }
//   };
// }

// // ---- CLEAR SAVED PAGE WHEN LOGGING OUT ----
// function clearSavedPage() {
//   localStorage.removeItem("last_iframe_page");
// }
