function formatCurrency(amount) {
  return "₱" + parseFloat(amount).toFixed(2);
}

function capitalize(str) {
  return str.charAt(0).toUpperCase() + str.slice(1);
}
