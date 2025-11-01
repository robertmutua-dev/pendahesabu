document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".confirmBtn, #confirmBtn").forEach(btn => {
    btn.addEventListener("click", e => {
      if (!confirm("Confirm Action")) e.preventDefault();
    });
  });
});
