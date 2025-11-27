async function ajax({ method = "GET", url, data = null }) {
  const opts = { method };
  if (method === "POST" && data) {
    opts.headers = { "Content-Type": "application/json" };
    opts.body = JSON.stringify(data);
  }
  const res = await fetch(url, opts);
  return res.json();
}
