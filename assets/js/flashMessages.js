import { fetchData } from './utilities.js';

export const fetchFlashMessages = async () => {
    const messages = await fetchData(`${origin}/ajax-flashes`);
    if (Object.entries(messages).length > 0) displayFlashMessages(messages);
};

export const displayFlashMessages = (messages) => {
    if (Object.entries(messages).length > 0) {
        Object.entries(messages).forEach(([key, value]) => {
            toastr[key](value);
        });
    }
};
