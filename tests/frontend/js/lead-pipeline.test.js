import { describe, expect, it, vi, beforeEach } from 'vitest';
import { initLeadPipeline } from '../../../resources/js/orchid/lead-pipeline.js';

const eventWithTransfer = (name, transfer) => {
    const event = new Event(name, { bubbles: true, cancelable: true });

    Object.defineProperty(event, 'dataTransfer', {
        value: transfer,
    });

    return event;
};

beforeEach(() => {
    document.body.innerHTML = `
        <form id="lead-pipeline-move">
            <input id="lead-pipeline-lead-id">
            <input id="lead-pipeline-status">
            <input id="lead-pipeline-reason">
        </form>
        <article class="lead-pipeline-card" draggable="true" data-lead-id="42"></article>
        <div class="lead-pipeline-dropzone" data-status="won"></div>
    `;
});

describe('lead pipeline drag and drop', () => {
    it('writes the dragged lead and target status into the move form', () => {
        const form = document.getElementById('lead-pipeline-move');
        const card = document.querySelector('.lead-pipeline-card');
        const zone = document.querySelector('.lead-pipeline-dropzone');
        const setData = vi.fn();

        form.submit = vi.fn();

        expect(initLeadPipeline(document)).toBe(true);

        card.dispatchEvent(eventWithTransfer('dragstart', { setData }));
        zone.dispatchEvent(new Event('dragover', { bubbles: true, cancelable: true }));

        expect(setData).toHaveBeenCalledWith('text/plain', '42');
        expect(zone.classList.contains('is-drag-over')).toBe(true);

        zone.dispatchEvent(eventWithTransfer('drop', { getData: () => '42' }));

        expect(document.getElementById('lead-pipeline-lead-id').value).toBe('42');
        expect(document.getElementById('lead-pipeline-status').value).toBe('won');
        expect(document.getElementById('lead-pipeline-reason').value).toBe('');
        expect(zone.classList.contains('is-drag-over')).toBe(false);
        expect(form.submit).toHaveBeenCalledOnce();
    });

    it('does not double-bind when initialized more than once', () => {
        const form = document.getElementById('lead-pipeline-move');
        const zone = document.querySelector('.lead-pipeline-dropzone');

        form.submit = vi.fn();

        initLeadPipeline(document);
        initLeadPipeline(document);
        zone.dispatchEvent(eventWithTransfer('drop', { getData: () => '42' }));

        expect(form.submit).toHaveBeenCalledOnce();
    });
});
