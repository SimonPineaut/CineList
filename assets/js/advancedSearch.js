export const showModal = async (event) => {
    event.preventDefault();
    const modal = document.getElementById('advancedSearchModal');
    modal.classList.toggle('d-none');
    const urlSearchParams = new URLSearchParams(window.location.search);
    const params = Object.fromEntries(urlSearchParams.entries());
    const queryString = new URLSearchParams(params).toString();

    if (!modal.classList.contains('d-none')) {
        await fetch(`${origin}/search/advanced/form?${queryString}`, {
            method: 'GET'
        })
            .then(response => response.text())
            .then(html => {
                document.getElementById('modalFormContainer').innerHTML = html;
            });

        const advancedSearchForm = document.getElementById('advancedSearchForm');
        $('.advanced-search-select').select2();
        advancedSearchForm?.addEventListener('submit', submitForm);
    }
};

export const closeModal = () => {
    document.getElementById('advancedSearchModal').classList.toggle('d-none');
}

export const submitForm = async (event) => {
    event.preventDefault();
    const urlSearchParams = new URLSearchParams(window.location.search);
    const params = Object.fromEntries(urlSearchParams.entries());
    const form = event.target;
    const formData = new FormData(form);

    const action = form.action;
    for (const key in params) {
        if (Object.hasOwnProperty.call(params, key)) {
            if (formData.get(`advanced_search[${key}]`) === '') {
                formData.append(`advanced_search[${key}]`, params[key])
            }

        }
    }

    await fetch(action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => response.json())
        .then(data => handleFormResponse(data))
        .catch(error => console.error('Error:', error));
};

const handleFormResponse = async (data) => {
    if (data.success) {
        const formValues = data.data;
        const queryString = new URLSearchParams(formValues).toString();
        window.location = `${origin}/search/advanced?${queryString}`

    } else {
        const errorsContainer = document.getElementById('formErrors');
        errorsContainer.innerHTML = '';
        data.errors.forEach(error => {
            const errorElement = document.createElement('div');
            errorElement.innerText = error;
            errorsContainer.appendChild(errorElement);
        });
    }
};