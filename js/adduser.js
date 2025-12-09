const togglePassword = document.querySelector('#togglePassword');
const password = document.querySelector('#password');

togglePassword.addEventListener('click', () => {
  // Toggle the type attribute
  const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
  password.setAttribute('type', type);
  
  // Optionally toggle icon class
  togglePassword.classList.toggle('bx-show');
  togglePassword.classList.toggle('bx-hide');
});
