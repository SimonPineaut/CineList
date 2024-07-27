import { fetchData } from './utilities.js';
import { fetchFlashMessages } from './flashMessages.js';
import { closePlaylistModal } from './modals.js';

const origin = window.location.origin;

export const toggleFavoriteStatus = async (event) => {
    event.preventDefault();
    const targetIcon = event.target;
    const movieElement = targetIcon.closest('.movie');
    const movieId = movieElement.dataset.movieId;
    const isAdding = targetIcon.classList.contains('not-favorite');
    const action = isAdding ? 'add' : 'remove';

    await fetchData(`${origin}/favorites/${action}/movie/${movieId}`);
    fetchFlashMessages();
    if (isAdding) {
        const favoriteCountElement = document.querySelector('#favoriteCount');
        let favoriteCountValue = parseInt(favoriteCountElement.innerHTML);
        favoriteCountValue++;
        favoriteCountElement.innerHTML = favoriteCountValue.toString();
    } else {
        const favoriteCountElement = document.querySelector('#favoriteCount');
        let favoriteCountValue = parseInt(favoriteCountElement.innerHTML);
        favoriteCountValue--;
        favoriteCountElement.innerHTML = favoriteCountValue.toString();
    }
    targetIcon.classList.toggle('favorite', isAdding);
    targetIcon.classList.toggle('not-favorite', !isAdding);
    targetIcon.src = targetIcon.src.replace(isAdding ? 'not-favorite' : 'favorite', isAdding ? 'favorite' : 'not-favorite');
    targetIcon.alt = isAdding ? 'Retirer des favoris' : 'Ajouter aux favoris';
};

export const handlePlaylistModal = async (event) => {
    event.preventDefault();
    const playlistIcon = event.currentTarget;
    const movieId = playlistIcon.closest('.movie').dataset.movieId;
    const playlistModal = document.querySelector(`#playlistModal${movieId}`);
    const closeModalBtn = document.querySelector(`#closePlaylistModalBtn${movieId}`);
    const existingPlaylistBlock = playlistModal.querySelector('#existingPlaylistBlock');
    const existingPlaylistRadio = playlistModal.querySelector('#existingPlaylistRadio');
    const newPlaylistBlock = playlistModal.querySelector('#newPlaylistBlock');
    const newPlaylistRadio = playlistModal.querySelector('#newPlaylistRadio');
    const input = newPlaylistBlock.querySelector('input');
    const select = existingPlaylistBlock.querySelector('select');
    let existingPlaylistLabel = playlistModal.querySelector('#existingLabel');

    input.value = '';
    select.innerHTML = '';

    const playlists = await fetchData(`${origin}/playlist/fetch-user-playlists`);
    if (playlists && Object.keys(playlists).length > 0) {
        Object.entries(playlists).forEach(([key, value]) => {
            const option = document.createElement('option');
            option.value = key;
            option.text = value;
            select.appendChild(option);
        });
        existingPlaylistRadio.disabled = false;
        existingPlaylistRadio.checked = true;
        existingPlaylistLabel.innerHTML = 'Playlist existante';
        existingPlaylistBlock.classList.remove('d-none');
        newPlaylistRadio.checked = false;
        newPlaylistBlock.classList.add('d-none');
    } else {
        existingPlaylistRadio.disabled = true;
        existingPlaylistLabel.innerHTML = '<s>Aucune playlist</s>';
        existingPlaylistBlock.classList.add('d-none');
        newPlaylistRadio.checked = true;
        newPlaylistBlock.classList.remove('d-none');
    }

    existingPlaylistRadio.addEventListener('click', () => {
        newPlaylistRadio.checked = false;
        existingPlaylistRadio.checked = true;
        newPlaylistBlock.classList.add('d-none');
        existingPlaylistBlock.classList.remove('d-none');
    });

    newPlaylistRadio.addEventListener('click', () => {
        existingPlaylistRadio.checked = false;
        newPlaylistRadio.checked = true;
        existingPlaylistBlock.classList.add('d-none');
        newPlaylistBlock.classList.remove('d-none');
    });

    closeModalBtn.addEventListener('click', () => playlistModal.classList.add('d-none'));
    playlistModal.classList.toggle('d-none');
};

export const handleNewPlaylistClick = (event) => {
    const movieElement = event.currentTarget.closest('.movie');
    const movieId = movieElement.dataset.movieId;
    const title = event.currentTarget.previousElementSibling.value || 'Playlist sans nom';
    createPlaylist(movieId, title);
    const playlistCountElement = document.querySelector('#playlistCount');
    let playlistCountValue = parseInt(playlistCountElement.innerHTML);
    playlistCountValue++;
    playlistCountElement.innerHTML = playlistCountValue.toString();
    setTimeout(() => closePlaylistModal(movieId), 500);
};

export const handleExistingPlaylistClick = (event) => {
    const movieElement = event.currentTarget.closest('.movie');
    const movieId = movieElement.dataset.movieId;
    const playlistId = event.target.previousElementSibling.value;
    addToPlaylist(movieId, playlistId);
    setTimeout(() => closePlaylistModal(movieId), 500);
};

export const addToPlaylist = async (movieId, playlistId) => {
    await fetchData(`${origin}/playlist/add-to-playlist`, {
        method: 'POST',
        body: JSON.stringify({ movieId, playlistId }),
    });
    fetchFlashMessages();
};

export const deletePlaylist = async () => {
    event.preventDefault();
    const playlistElement = event.target.closest('.playlist')
    const playlistId = playlistElement.dataset.playlistId;
    await fetchData(`${origin}/playlist/delete-playlist`, {
        method: 'POST',
        body: JSON.stringify({ playlistId }),
    });
    fetchFlashMessages();
    playlistElement.remove();
    const playlistCountElement = document.querySelector('#playlistCount');
    let playlistCountValue = parseInt(playlistCountElement.innerHTML);
    playlistCountValue--
    playlistCountElement.innerHTML = playlistCountValue.toString()
};

export const removeFromPlaylist = async (event) => {
    const element = event.target;
    const playlistElement = element.closest('.playlist-element');
    const movieId = element.dataset.movieId;
    const playlistId = playlistElement.dataset.playlistId;
    await fetchData(`${origin}/playlist/remove-from-playlist`, {
        method: 'POST',
        body: JSON.stringify({ movieId, playlistId }),
    });
    fetchFlashMessages();
    playlistElement.remove();
};

export const importPlaylist = async (event) => {
    const element = event.currentTarget;
    const playlistElement = element.closest('.playlist-edit');
    const playlistId = playlistElement.dataset.playlistId;
    await fetchData(`${origin}/playlist/import-playlist/${playlistId}`);
    const playlistCountElement = document.querySelector('#playlistCount');
    let playlistCountValue = parseInt(playlistCountElement.innerHTML);
    playlistCountValue++;
    playlistCountElement.innerHTML = playlistCountValue.toString();
    fetchFlashMessages();
};

export const createPlaylist = async (movieId, title) => {
    await fetchData(`${origin}/playlist/create-playlist`, {
        method: 'POST',
        body: JSON.stringify({ movieId, title }),
    });
    fetchFlashMessages();
};

export const handlePlaylistPublicity = async () => {
    const targetIcon = event.currentTarget;
    const targetIconImg = targetIcon.querySelector('img');
    const playlistElement = targetIcon.closest('.playlist');
    const playlistId = playlistElement.dataset.playlistId;
    const isPublic = targetIconImg.classList.contains('unlock');

    await fetchData(`${origin}/playlist/update-playlist-status`, {
        method: 'POST',
        body: JSON.stringify({playlistId }),
    });
    fetchFlashMessages();

    targetIconImg.classList.toggle('lock', isPublic);
    targetIconImg.classList.toggle('unlock', !isPublic);
    targetIconImg.src = targetIconImg.src.replace(isPublic ? 'unlock' : 'lock', isPublic ? 'lock' : 'unlock');
    targetIconImg.alt = isPublic ? 'Rendre publique' : 'Rendre privée';
};
