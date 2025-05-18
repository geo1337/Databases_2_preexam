
document.addEventListener('DOMContentLoaded', function () {
    const flipContainer = document.querySelector('.flip-container');
    const toRegister = document.querySelector('.sign-in .signup-image-link');
    const toLogin = document.querySelector('.signup .signup-image-link');

    toRegister.addEventListener('click', (e) => {
        e.preventDefault();
        flipContainer.classList.add('flip');
    });

    toLogin.addEventListener('click', (e) => {
        e.preventDefault();
        flipContainer.classList.remove('flip');
    });
});

