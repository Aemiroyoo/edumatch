// Login
document.getElementById('loginForm').addEventListener('submit', function(e) {
  e.preventDefault();
  
  fetch('/api/auth.php?action=login', {
    method: 'POST',
    body: new FormData(this)
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      window.location.href = 'dashboard.php';
    } else {
      alert(data.message);
    }
  });
});