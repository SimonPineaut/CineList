export const togglePasswordVisibility = (event) => {
    const btn = event.currentTarget
    const input = btn.previousElementSibling
    const inputImg = btn.querySelector('img');
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    inputImg.src = `${origin}/icons/pwd-${isHidden ? 'hide' : 'show'}.svg`;
};
