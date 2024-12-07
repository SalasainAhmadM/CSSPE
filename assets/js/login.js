function login(){
    const loginButton = document.querySelector('.registerContainer');

    if(loginButton.style.display === 'none'){
        loginButton.style.display = 'block';
    } else{
        loginButton .style.display = 'none'
    }
}