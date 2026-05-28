export const normalizeField = (field) => field.replace(/\.\d+\./g, '.*.').replace(/\.\d+$/, '.*');

export const errorTargets = (form) => Array.from(form.querySelectorAll('[data-error-for]'));

export const fieldNames = (target) => (target.dataset.errorFor || '').split(/\s+/).filter(Boolean);

export const targetFor = (form, field) => {
    const normalized = normalizeField(field);

    return errorTargets(form).find((target) => {
        const names = fieldNames(target);

        return names.includes(field) || names.includes(normalized);
    });
};

export const controlsFor = (form, field) => Array.from(form.elements)
    .filter((control) => control.name === field && control.type !== 'hidden');

export const clearErrors = (form) => {
    errorTargets(form).forEach((target) => {
        target.textContent = '';
        target.hidden = true;
    });

    form.querySelectorAll('[aria-invalid="true"]').forEach((control) => {
        control.removeAttribute('aria-invalid');
    });

    const alert = form.querySelector('[data-form-alert]');

    if (alert) {
        alert.textContent = '';
        alert.hidden = true;
    }
};

export const clearField = (form, field) => {
    if (! field) {
        return;
    }

    const target = targetFor(form, field);

    if (target) {
        target.textContent = '';
        target.hidden = true;
    }

    controlsFor(form, field).forEach((control) => control.removeAttribute('aria-invalid'));
};

export const showErrors = (form, errors, message) => {
    let firstControl = null;

    Object.entries(errors).forEach(([field, messages]) => {
        const target = targetFor(form, field);
        const text = Array.isArray(messages) ? messages[0] : messages;

        if (target && text) {
            target.textContent = text;
            target.hidden = false;
        }

        controlsFor(form, field).forEach((control) => {
            control.setAttribute('aria-invalid', 'true');
            firstControl ??= control;
        });
    });

    const alert = form.querySelector('[data-form-alert]');

    if (alert) {
        alert.textContent = message || form.dataset.errorMessage || '';
        alert.hidden = false;
    }

    if (firstControl) {
        firstControl.scrollIntoView({ block: 'center', behavior: 'smooth' });
        firstControl.focus({ preventScroll: true });
    }
};

export const submitLeadForm = async (form, fetcher = window.fetch.bind(window)) => {
    const response = await fetcher(form.action, {
        method: form.method || 'POST',
        body: new FormData(form),
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    });
    const payload = await response.json().catch(() => ({}));

    return { response, payload };
};

export const bindLeadForm = (form, fetcher) => {
    if (form.dataset.ajaxLeadFormBound === 'true') {
        return;
    }

    form.dataset.ajaxLeadFormBound = 'true';

    const submitButton = form.querySelector('[type="submit"]');
    const submitLabel = submitButton?.textContent;

    form.addEventListener('input', (event) => clearField(form, event.target?.name));
    form.addEventListener('change', (event) => clearField(form, event.target?.name));

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearErrors(form);

        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = form.dataset.submitLabel || submitLabel;
        }

        try {
            const { response, payload } = await submitLeadForm(form, fetcher);

            if (response.status === 422 && payload.errors) {
                showErrors(form, payload.errors, form.dataset.errorMessage);

                return;
            }

            if (response.ok && payload.redirect) {
                window.location.assign(payload.redirect);

                return;
            }

            if (response.redirected) {
                window.location.assign(response.url);

                return;
            }

            showErrors(form, {}, form.dataset.errorMessage);
        } catch {
            showErrors(form, {}, form.dataset.errorMessage);
        } finally {
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = submitLabel;
            }
        }
    });
};

export const initLeadForms = (root = document, fetcher = undefined) => {
    if (! root?.querySelectorAll) {
        return;
    }

    root.querySelectorAll('[data-ajax-lead-form]').forEach((form) => bindLeadForm(form, fetcher));
};
