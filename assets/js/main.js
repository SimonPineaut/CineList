import { initializeEventListeners } from './events.js';
import { fetchFlashMessages } from './flashMessages.js';

document.addEventListener('DOMContentLoaded', () => {
    initializeEventListeners();
    fetchFlashMessages();
});