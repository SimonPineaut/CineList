export const fetchData = async (url, options = {}) => {
    try {
        const response = await fetch(url, {
            ...options,
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...options.headers,
            },
        });
        if (!response.ok) throw new Error('Erreur lors de la réponse réseau');

        const data = await response.json();

        if (data.redirect) {
            window.location.href = data.redirect;
            return;
        }

        return data;
    } catch (error) {
        console.error('Erreur de récupération:', error.message);
    }
};

export const logout = () => {
    fetchData(`${origin}/logout`);
}

export const deleteAccount = async () => {
    event.preventDefault()
    const btn = document.querySelector('#deleteAccountBtn')
    const userId = btn.dataset.userId
    const isDeleted = await fetchData(`${origin}/account/delete/${userId}`, {
        method: 'POST',
    });

    if (isDeleted) {
        window.location.href = `${origin}/login`
    }

}