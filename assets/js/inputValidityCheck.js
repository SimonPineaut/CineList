import { fetchData } from './utilities.js';

export const checkUsernameValidity = async (event) => {
    let usernameInput = event.target
    const usernameToCheck = event.target.value
    const validityMessageElement = document.getElementById('usernameValidity')
    if (usernameToCheck.length >= 2) {
        const isValid = await fetchData(`${origin}/verify/username`, {
            method: 'POST',
            body: JSON.stringify({ usernameToCheck }),
        });
        if (isValid) {
            usernameInput.style.outline = '4px solid #5fed2c'
            validityMessageElement.style.color = '#5fed2c'
            validityMessageElement.innerText = 'Disponible'
        } else {
            usernameInput.style.outline = '4px solid #FF0000'
            validityMessageElement.style.color = '#FF0000'
            validityMessageElement.innerText = 'Déjà pris'
        }
    } else {
        usernameInput.style.outline = '4px solid #FF0000'
        validityMessageElement.style.color = '#FF0000'
        validityMessageElement.innerText = '2 caractères minimum '
    }

    usernameInput.addEventListener('focusout', () => {
        validityMessageElement.innerText = ''
    })
}

export const checkEmailValidity = async (event) => {
    let emailInput = event.target;
    const emailToCheck = event.target.value;
    const validityMessageElement = document.getElementById('emailValidity')
    if (validateEmail(emailToCheck)) {
        emailInput.style.outline = '4px solid #5fed2c'
        validityMessageElement.style.color = '#5fed2c'
        validityMessageElement.innerText = 'Email valide'
    } else {
        emailInput.style.outline = '4px solid #FF0000'
        validityMessageElement.style.color = '#FF0000'
        validityMessageElement.innerText = 'Email invalide'
    }
    emailInput.addEventListener('focusout', () => {
        validityMessageElement.innerText = ''
    })
}

export const checkPasswordValidity = async (event) => {
    const passwordInput = event.target;
    const passwordToCheck = event.target.value;
    const validityMessageElement = document.getElementById('passwordValidity')
    if (validatePassword(passwordToCheck)) {
        passwordInput.style.outline = '4px solid #5fed2c'
        validityMessageElement.style.color = '#5fed2c'
        validityMessageElement.innerText = 'Mot de passe valide'
    } else {
        passwordInput.style.outline = '4px solid #FF0000'
        validityMessageElement.style.color = '#FF0000'
        validityMessageElement.innerText = 'Au moins 8 caractères dont un chiffre, une majuscule, une minuscule et un caractère spécial parmi @$!%*?&'
        
    }
    passwordInput.addEventListener('focusout', () => {
        validityMessageElement.innerText = ''
    })
}

export const comparePasswords = (event) => {
    const confirmPasswordInput = event.target;
    const confirmPassword = event.target.value;
    const newPasswordInput = document.getElementById('modify_password_form_newPassword')
    const newPassword = newPasswordInput.value;
    const validityMessageElement = document.getElementById('confirmPasswordValidity')
    if (confirmPassword.length > 0) {
        if (confirmPassword.length !== newPassword.length) {
            confirmPasswordInput.style.outline = '4px solid #ff0000'
            newPasswordInput.style.outline = '4px solid #ff0000'
            validityMessageElement.style.color = '#ff0000'
            validityMessageElement.innerText = 'Les mots de passe ne sont pas de même taille'
        } else {
            if (confirmPassword !== newPassword) {
                confirmPasswordInput.style.outline = '4px solid #ff0000'
                newPasswordInput.style.outline = '4px solid #ff0000'
                validityMessageElement.style.color = '#ff0000'
                validityMessageElement.innerText = 'Les mots de passe ne correspondent pas'
            } else {
                confirmPasswordInput.style.outline = '4px solid #5fed2c'
                newPasswordInput.style.outline = '4px solid #5fed2c'
                validityMessageElement.style.color = '#5fed2c'
                validityMessageElement.innerText = 'Mots de passe identiques'
            }
        }
    }
    confirmPasswordInput.addEventListener('focusout', () => {
        validityMessageElement.innerText = ''
    })
};

const validateEmail = (email) => {
    if (email.match(
        /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
    )) {
        return true;
    } else {
        return false;
    }
};

const validatePassword = (password) => {
    if (password.match(
        /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/
    )) {
        return true;
    } else {
        return false;
    }
};