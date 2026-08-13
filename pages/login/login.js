function togglePassword() {
  const password = document.getElementById("loginPassword");
  const icon = document.getElementById("eyeIcon");

  if (password.type === "password") {
    password.type = "text";

    icon.classList.remove("bi-eye");
    icon.classList.add("bi-eye-slash");
  } else {
    password.type = "password";

    icon.classList.remove("bi-eye-slash");
    icon.classList.add("bi-eye");
  }
}

function loginUser(event) {
  event.preventDefault();

  const button = event.currentTarget;
  const username = document.getElementById("loginUsername").value.trim();
  const password = document.getElementById("loginPassword").value;

  if (username === "" || password === "") {
    alert("Please enter your username and password.");
    return;
  }

  // Show loading animation
  button.classList.add("loading");
  button.disabled = true;

  const loginText = document.getElementById("loginText");
  const loginIcon = document.getElementById("loginIcon");
  const loginLoader = document.getElementById("loginLoader");

  loginText.textContent = "Signing In...";
  loginIcon.style.display = "none";
  if (loginLoader) {
    loginLoader.style.display = "inline-block";
    loginLoader.style.visibility = "visible";
  }

  const form = document.createElement("form");

  form.method = "POST";
  form.action = window.location.href;

  const usernameField = document.createElement("input");

  usernameField.type = "hidden";
  usernameField.name = "username";
  usernameField.value = username;

  const passwordField = document.createElement("input");

  passwordField.type = "hidden";
  passwordField.name = "password";
  passwordField.value = password;

  form.appendChild(usernameField);
  form.appendChild(passwordField);

  document.body.appendChild(form);

  // Submit after loading state is displayed
  requestAnimationFrame(() => {
    setTimeout(() => form.submit(), 100);
  });
}
