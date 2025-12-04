function getFormData(form) {
  const data = {};
  new FormData(form).forEach((value, key) => {
    data[key] = value.trim();
  });
  return data;
}

// Customer Register
document.getElementById("customerRegisterForm").onsubmit = async (e) => {
  e.preventDefault();
  const msg = document.getElementById("customerMessage");
  msg.textContent = "Registering...";

  const data = getFormData(e.target);
  const res = await API.customerRegister(data);

  if (res.success) {
    msg.style.color = "green";
    msg.textContent = "Success! Redirecting to login...";
    setTimeout(() => {
      parent.navigate?.(event, "../pages/home/login.html") ||
        (window.location.href = "../pages/home/login.html");
    }, 1500);
  } else {
    msg.style.color = "red";
    msg.textContent = res.message || "Registration failed";
  }
};

// Branch Register
document.getElementById("branchRegisterForm").onsubmit = async (e) => {
  e.preventDefault();
  const msg = document.getElementById("branchMessage");
  msg.textContent = "Registering branch...";

  const data = getFormData(e.target);
  const res = await API.branchRegister(data);

  if (res.success) {
    msg.style.color = "green";
    msg.textContent = "Branch registered! Redirecting...";
    setTimeout(() => {
      parent.navigate?.(event, "../pages/home/login.html") ||
        (window.location.href = "../pages/home/login.html");
    }, 1500);
  } else {
    msg.style.color = "red";
    msg.textContent = res.message || "Failed to register branch";
  }
};
