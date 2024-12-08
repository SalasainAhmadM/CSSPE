function login() {
    const loginContainer = document.querySelector('.loginContainer');
    const registerContainer = document.querySelector('.registerContainer');

    if (registerContainer.style.display === 'none') {
        registerContainer.style.display = 'block';
        loginContainer.style.display = 'none';
    } else {
        registerContainer.style.display = 'none';
        loginContainer.style.display = 'block';
    }
}