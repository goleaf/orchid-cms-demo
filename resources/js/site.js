import { initLeadForms } from './site/lead-form.js';

function initLocationFilters() {
    document.querySelectorAll('[data-location-filter]').forEach((form) => {
        const countrySelect = form.querySelector('[data-location-country]');
        const citySelect = form.querySelector('[data-location-city]');

        if (!(countrySelect instanceof HTMLSelectElement) || !(citySelect instanceof HTMLSelectElement)) {
            return;
        }

        const syncCities = () => {
            const selectedCountry = countrySelect.value;
            let selectedCityStillVisible = citySelect.value === '';

            Array.from(citySelect.options).forEach((option) => {
                const optionCountry = option.dataset.country || '';
                const isPlaceholder = option.value === '';
                const isVisible = isPlaceholder || selectedCountry === '' || optionCountry === selectedCountry;

                option.hidden = !isVisible;
                option.disabled = !isVisible;

                if (option.selected && isVisible) {
                    selectedCityStillVisible = true;
                }
            });

            if (!selectedCityStillVisible) {
                citySelect.value = '';
            }
        };

        countrySelect.addEventListener('change', () => {
            syncCities();
            form.submit();
        });
        syncCities();
    });
}

initLocationFilters();
initLeadForms();
