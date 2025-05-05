; (function ($) {
    'use strict';
    console.log('🟢 location-select.js loaded');

    const REGION_CODE = '1300000000';
    const $city = $('#citySelect');
    const $brgy = $('#barangaySelect');

    function populateSelect($select, items, placeholder) {
        // sort alphabetically
        items.sort((a, b) => a.name.localeCompare(b.name));

        $select.prop('disabled', false)
            .empty()
            .append(`<option value="">${placeholder}</option>`);
        items.forEach(i => $select.append(`<option value="${i.code}">${i.name}</option>`));
    }

    function handleError($select, msg, err) {
        console.error(msg, err);
        $select.prop('disabled', true)
            .empty()
            .append(`<option>${msg}</option>`);
    }

    $(function () {
        console.log('→ Fetching cities…');

        // city population logic here
        $city.prop('disabled', true)
            .html('<option>Loading…</option>');

        fetch(`https://psgc.cloud/api/regions/${REGION_CODE}/cities-municipalities`, { cache: 'no-store' })
            .then(r => {
                if (!r.ok) throw new Error(`HTTP ${r.status}`);
                return r.json();
            })
            .then(json => {
                populateSelect(
                    $city,
                    json.map(c => ({ code: c.code, name: c.name })),
                    'select City / Municipality'
                );
            })
            .catch(err => {
                handleError($city, 'Failed to load cities', err);
            });

        // barangay population logic here
        $city.on('change', function () {
            const cityCode = $(this).val();
            if (!cityCode) return;

            $brgy.prop('disabled', true).html('<option>Loading…</option>');

            fetch(`https://psgc.cloud/api/cities-municipalities/${cityCode}/barangays`, { cache: 'no-store' })
                .then(r => {
                    if (!r.ok) throw new Error(`HTTP ${r.status}`);
                    return r.json();
                })
                .then(json => {
                    populateSelect(
                        $brgy,
                        json.map(b => ({ code: b.code, name: b.name })),
                        'select Barangay'
                    );
                })
                .catch(err => {
                    handleError($brgy, 'Failed to load barangays', err);
                });

            $brgy.on('change', function () {
                const brgyCode = $(this).val();
                if (!brgyCode) return;

            });

        });
    });

})(jQuery);
