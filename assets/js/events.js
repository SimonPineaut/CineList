import { toggleFavoriteStatus, handlePlaylistModal, handleNewPlaylistClick, handleExistingPlaylistClick, removeFromPlaylist, importPlaylist, handlePlaylistPublicity } from './playlist.js';
import { displayLogoutModal, displayDeletePlaylistModal, displayDeleteAccountModal } from './modals.js';
import { togglePasswordVisibility } from './passwordVisibility.js';
import { displayFlashMessages } from './flashMessages.js';
import { showModal, closeModal } from './advancedSearch.js';
import { checkUsernameValidity, checkEmailValidity, checkPasswordValidity, comparePasswords } from './inputValidityCheck.js';

export const initializeEventListeners = () => {
    const favoriteIcons = document.querySelectorAll('.not-favorite, .favorite');
    const symfonyFlashMessages = document.querySelectorAll('#flashMessage');
    // advanced search
    const advancedSearchBtn = document.getElementById('advancedSearchBtn');
    const closeAdvancedSearchModalBtn = document.getElementById('closeModal');
    // playlist
    const playlistLinks = document.querySelectorAll('#playlistLink');
    const existingPlaylistBtns = document.querySelectorAll('#existingPlaylistBtn');
    const newPlaylistBtns = document.querySelectorAll('#newPlaylistBtn');
    const deletePlaylistBtns = document.querySelectorAll('#deletePlaylistBtn');
    const lockPlaylistBtns = document.querySelectorAll('#lockPlaylistBtn');
    const removeFromPlaylistBtns = document.querySelectorAll('#removeFromPlaylistBtn');
    const importPlaylistBtn = document.querySelector('#importPlaylistBtn');
    // user 
    const deleteAccountBtn = document.querySelector('#deleteAccountBtn');
    const logoutBtn = document.querySelector('#logoutBtn');
    const togglePasswordBtns = document.querySelectorAll('#togglePasswordVisibility');
    const registrationUsernameInput = document.querySelector('#registration_form_username');
    const registrationEmailInput = document.querySelector('#registration_form_email');
    const registrationPasswordInput = document.querySelector('#registration_form_plainPassword');
    const newPasswordInput = document.querySelector('#modify_password_form_newPassword');
    const confirmPasswordInput = document.querySelector('#modify_password_form_confirmPassword');

    logoutBtn?.addEventListener('click', () => displayLogoutModal());
    deleteAccountBtn?.addEventListener('click', () => displayDeleteAccountModal());
    favoriteIcons?.forEach(icon => icon.addEventListener('click', toggleFavoriteStatus));
    playlistLinks?.forEach(link => link.addEventListener('click', handlePlaylistModal));
    existingPlaylistBtns?.forEach(btn => btn.addEventListener('click', handleExistingPlaylistClick));
    newPlaylistBtns?.forEach(btn => btn.addEventListener('click', handleNewPlaylistClick));
    deletePlaylistBtns?.forEach(btn => btn.addEventListener('click', displayDeletePlaylistModal));
    lockPlaylistBtns?.forEach(btn => btn.addEventListener('click', handlePlaylistPublicity));
    removeFromPlaylistBtns?.forEach(btn => btn.addEventListener('click', removeFromPlaylist));
    importPlaylistBtn?.addEventListener('click', importPlaylist);
    advancedSearchBtn?.addEventListener('click', showModal);
    closeAdvancedSearchModalBtn.addEventListener('click', closeModal);
    symfonyFlashMessages?.forEach(message => displayFlashMessages(message));
    togglePasswordBtns?.forEach(btn =>btn.addEventListener('click', togglePasswordVisibility));
    registrationUsernameInput?.addEventListener('input', checkUsernameValidity);
    registrationEmailInput?.addEventListener('input', checkEmailValidity);
    registrationPasswordInput?.addEventListener('input', checkPasswordValidity);
    newPasswordInput?.addEventListener('input', checkPasswordValidity);
    confirmPasswordInput?.addEventListener('input', comparePasswords);
};
