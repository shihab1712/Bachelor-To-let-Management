// script.js

document.addEventListener("DOMContentLoaded", () => {
    const signupForm = document.querySelector("form[action='signup.php']");
    const loginForm = document.querySelector("form[action='login.php']");
  
    if (signupForm) {
      signupForm.addEventListener("submit", (e) => {
        const password = signupForm.querySelector("input[name='password']").value;
        if (password.length < 6) {
          alert("Password must be at least 6 characters long.");
          e.preventDefault();
        }
      });
    }
  
    if (loginForm) {
      loginForm.addEventListener("submit", (e) => {
        const username = loginForm.querySelector("input[name='username']").value;
        const password = loginForm.querySelector("input[name='password']").value;
        if (!username || !password) {
          alert("Please fill in both username and password.");
          e.preventDefault();
        }
      });
    }
  });
  