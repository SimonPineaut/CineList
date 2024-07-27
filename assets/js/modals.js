import { logout } from './utilities.js';
import { deletePlaylist } from './playlist.js';
import { deleteAccount } from './utilities.js';

const displayModal = (modalId, confirmAction) => {
    const modal = document.getElementById(modalId);
    modal.classList.remove('d-none');

    const closeModal = () => {
        modal.classList.add('d-none');
        confirmYesButton.removeEventListener('click', confirmYesHandler);
        confirmNoButton.removeEventListener('click', confirmNoHandler);
    };

    const confirmYesButton = modal.querySelector('.confirm-yes');
    const confirmNoButton = modal.querySelector('.confirm-no');

    const confirmYesHandler = async () => {
        await confirmAction();
        closeModal();
    };

    const confirmNoHandler = closeModal;

    confirmYesButton.addEventListener('click', confirmYesHandler);
    confirmNoButton.addEventListener('click', confirmNoHandler);
};

export const closePlaylistModal = (movieId) => {
    const modal = document.getElementById(`playlistModal${movieId}`);
    modal.classList.add('d-none');
};

export const displayLogoutModal = () => {
    displayModal('logoutModal', logout);
};

export const displayDeletePlaylistModal = () => {
    const playlistElement = event.target.closest('.playlist')
    const playlistId = playlistElement.dataset.playlistId;
    displayModal(`deletePlaylistModal${playlistId}`, deletePlaylist);
};

export const displayDeleteAccountModal = () => {
    displayModal('deleteAccountModal', deleteAccount);
};
