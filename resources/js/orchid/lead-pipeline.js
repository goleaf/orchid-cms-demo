const CARD_SELECTOR = '.lead-pipeline-card';
const DROPZONE_SELECTOR = '.lead-pipeline-dropzone';
const DRAG_OVER_CLASS = 'is-drag-over';

const bindCards = (root) => {
    root.querySelectorAll(CARD_SELECTOR).forEach((card) => {
        if (card.dataset.dragBound === 'true') {
            return;
        }

        card.dataset.dragBound = 'true';
        card.addEventListener('dragstart', (event) => {
            event.dataTransfer?.setData('text/plain', card.dataset.leadId || '');
        });
    });
};

const bindDropzones = (root, form, leadInput, statusInput, reasonInput) => {
    root.querySelectorAll(DROPZONE_SELECTOR).forEach((zone) => {
        if (zone.dataset.dropBound === 'true') {
            return;
        }

        zone.dataset.dropBound = 'true';
        zone.addEventListener('dragover', (event) => {
            event.preventDefault();
            zone.classList.add(DRAG_OVER_CLASS);
        });

        zone.addEventListener('dragleave', () => {
            zone.classList.remove(DRAG_OVER_CLASS);
        });

        zone.addEventListener('drop', (event) => {
            event.preventDefault();
            zone.classList.remove(DRAG_OVER_CLASS);

            leadInput.value = event.dataTransfer?.getData('text/plain') || '';
            statusInput.value = zone.dataset.status || '';
            reasonInput.value = '';

            if (leadInput.value && statusInput.value) {
                form.submit();
            }
        });
    });
};

export const initLeadPipeline = (root = document) => {
    if (! root?.querySelectorAll) {
        return false;
    }

    const scope = root.ownerDocument || root;
    const form = scope.getElementById('lead-pipeline-move');
    const leadInput = scope.getElementById('lead-pipeline-lead-id');
    const statusInput = scope.getElementById('lead-pipeline-status');
    const reasonInput = scope.getElementById('lead-pipeline-reason');

    if (! form || ! leadInput || ! statusInput || ! reasonInput) {
        return false;
    }

    bindCards(root);
    bindDropzones(root, form, leadInput, statusInput, reasonInput);

    return true;
};

const bootLeadPipeline = () => initLeadPipeline(document);

if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootLeadPipeline, { once: true });
    } else {
        bootLeadPipeline();
    }

    document.addEventListener('turbo:load', bootLeadPipeline);
}
