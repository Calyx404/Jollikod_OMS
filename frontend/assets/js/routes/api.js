// frontend/assets/js/api/client.js
const API_BASE = "http://localhost/jollikod_oms/backend/routes/api.php";

/**
 * Universal API caller
 * Automatically handles FormData, JSON, GET/POST, and session cookies
 */
async function apiCall(action, data = {}, method = "POST") {
  const url =
    method === "GET"
      ? `${API_BASE}?action=${action}&${new URLSearchParams(data).toString()}`
      : API_BASE;

  const options = {
    method,
    credentials: "include", // Critical: sends PHP session cookie
  };

  if (method === "POST") {
    const formData = new FormData();
    formData.append("action", action);
    Object.keys(data).forEach((key) => {
      formData.append(key, data[key]);
    });
    options.body = formData;
  }

  try {
    const res = await fetch(url, options);
    const json = await res.json();
    return json;
  } catch (err) {
    console.error("API Error:", err);
    return { success: false, message: "Network error" };
  }
}

/* =============================================
   AUTH – CUSTOMER
   ============================================= */
async function customerRegister(formData) {
  return await apiCall("customer_register", formData);
}

async function customerLogin(email, password) {
  return await apiCall("customer_login", { email, password });
}

/* =============================================
   AUTH – BRANCH
   ============================================= */
async function branchRegister(formData) {
  return await apiCall("branch_register", formData);
}

async function branchLogin(email, password) {
  return await apiCall("branch_login", { email, password });
}

async function logout() {
  return await apiCall("logout");
}

/* =============================================
   PUBLIC
   ============================================= */
async function getBranches() {
  return await apiCall("getBranches", {}, "GET");
}

async function getMenuByBranch(branchId) {
  return await apiCall("getMenuByBranch", { branch_id: branchId }, "GET");
}

/* =============================================
   BRANCH ONLY
   ============================================= */
async function getBranchMenu() {
  return await apiCall("getBranchMenu", {}, "GET");
}

async function addMenuItem(formData) {
  return await apiCall("addMenuItem", formData);
}

async function updateMenuItem(formData) {
  return await apiCall("updateMenuItem", formData);
}

async function deleteMenuItem(itemId) {
  return await apiCall("deleteMenuItem", { id: itemId });
}

async function getBranchOrders(status = "all", date = null) {
  const params = { status };
  if (date) params.date = date;
  return await apiCall("getOrdersByStatus", params, "GET");
}

async function updateOrderStatus(orderId, newStatus) {
  return await apiCall("updateOrderStatus", {
    order_id: orderId,
    status: newStatus,
  });
}

/* =============================================
   CUSTOMER ONLY
   ============================================= */
async function placeOrder(branchId, itemsArray, destinationAddress) {
  const payload = {
    branch_id: branchId,
    destination_address: destinationAddress,
    items: JSON.stringify(itemsArray),
  };
  return await apiCall("placeOrder", payload);
}

async function getCustomerOrders() {
  return await apiCall("getCustomerOrders", {}, "GET");
}

async function confirmReceived(orderId) {
  return await apiCall("confirmReceived", { order_id: orderId });
}

async function submitFeedback(branchId, rating, message = "") {
  return await apiCall("submitFeedback", {
    branch_id: branchId,
    rating,
    message,
  });
}

// Export for use in pages
window.API = {
  customerRegister,
  customerLogin,
  branchRegister,
  branchLogin,
  logout,
  getBranches,
  getMenuByBranch,
  getBranchMenu,
  addMenuItem,
  updateMenuItem,
  deleteMenuItem,
  getBranchOrders,
  updateOrderStatus,
  placeOrder,
  getCustomerOrders,
  confirmReceived,
  submitFeedback,
};
