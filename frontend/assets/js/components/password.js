document.addEventListener("DOMContentLoaded", () => {
  const hideBtns = document.querySelectorAll(".hide-password");
  const showBtns = document.querySelectorAll(".show-password");

  hideBtns.forEach((hideBtn) => {
    hideBtn.addEventListener("click", () => togglePassword(hideBtn));
  });

  showBtns.forEach((showBtn) => {
    showBtn.addEventListener("click", () => togglePassword(showBtn));
  });
});

function togglePassword(icon) {
  const container = icon.closest(".input-container");
  const input = container.querySelector(
    "input[type='password'], input[type='text']"
  );
  const hideIcon = container.querySelector(".hide-password");
  const showIcon = container.querySelector(".show-password");

  const isHidden = input.type === "password";

  if (isHidden) {
    input.type = "text";
    container.classList.add("password-active");
  } else {
    input.type = "password";
    container.classList.remove("password-active");
  }
}
