import { authenticateLogin } from "../modules/auth.js";

document.getElementById('login-form').addEventListener('submit', login);

async function login(event) {
    event.preventDefault(); // Prevent default form submission

    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    const loginSuccessful = await authenticateLogin(email, password);

    if(loginSuccessful){
        window.location.pathname = window.location.pathname.replace("/login", "/home");
    }
}