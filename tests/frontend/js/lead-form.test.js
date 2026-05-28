import { describe, expect, it, vi, beforeEach } from 'vitest';
import {
    bindLeadForm,
    clearField,
    normalizeField,
    showErrors,
} from '../../../resources/js/site/lead-form.js';

const flushPromises = () => new Promise((resolve) => {
    setTimeout(resolve, 0);
});

const buildForm = () => {
    document.body.innerHTML = `
        <form action="/website/leads" method="POST" data-ajax-lead-form data-error-message="Form error" data-submit-label="Sending">
            <p data-form-alert hidden></p>
            <input name="full_name">
            <span data-error-for="full_name first_name" hidden></span>
            <input name="phone">
            <span data-error-for="phone" hidden></span>
            <button type="submit">Send</button>
        </form>
    `;

    return document.querySelector('form');
};

beforeEach(() => {
    HTMLElement.prototype.scrollIntoView = vi.fn();
});

describe('lead form helpers', () => {
    it('normalizes Laravel wildcard validation field names', () => {
        expect(normalizeField('students.0.email')).toBe('students.*.email');
        expect(normalizeField('students.12')).toBe('students.*');
    });

    it('renders and clears field errors without querying Blade', () => {
        const form = buildForm();
        const input = form.elements.full_name;
        const target = form.querySelector('[data-error-for]');

        showErrors(form, { full_name: ['Name is required'] }, 'Form error');

        expect(target.hidden).toBe(false);
        expect(target.textContent).toBe('Name is required');
        expect(input.getAttribute('aria-invalid')).toBe('true');
        expect(form.querySelector('[data-form-alert]').hidden).toBe(false);

        clearField(form, 'full_name');

        expect(target.hidden).toBe(true);
        expect(input.hasAttribute('aria-invalid')).toBe(false);
    });

    it('submits through fetch and displays JSON validation errors', async () => {
        const form = buildForm();
        const fetcher = vi.fn().mockResolvedValue({
            ok: false,
            redirected: false,
            status: 422,
            json: () => Promise.resolve({
                errors: {
                    phone: ['Phone is required'],
                },
            }),
        });

        bindLeadForm(form, fetcher);
        form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));

        await flushPromises();

        expect(fetcher).toHaveBeenCalledWith(expect.stringContaining('/website/leads'), expect.objectContaining({
            method: 'post',
            credentials: 'same-origin',
        }));
        expect(form.querySelector('[data-error-for="phone"]').textContent).toBe('Phone is required');
        expect(form.elements.phone.getAttribute('aria-invalid')).toBe('true');
        expect(form.querySelector('[type="submit"]').disabled).toBe(false);
    });
});
