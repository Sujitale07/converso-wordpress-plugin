jQuery(document).ready(function($) {
    var currentStep = 1;

    function showStep(step) {
        $('.wizard-step-content').removeClass('active');
        $('.wizard-step-content[data-step="' + step + '"]').addClass('active');
        
        $('.connectapre-wizard-steps .step').removeClass('active');
        $('.connectapre-wizard-steps .step').each(function() {
            if ($(this).data('step') <= step) {
                $(this).addClass('active');
            }
        });
    }

    $('.next-step').on('click', function() {
        var $currentStepContent = $('.wizard-step-content[data-step="' + currentStep + '"]');
        var isValid = true;

        $currentStepContent.find('input[required], select[required]').each(function() {
            if (!$(this).val()) {
                isValid = false;
                $(this).css('border-color', 'red');
            } else {
                $(this).css('border-color', '');
            }
        });

        if (isValid) {
            currentStep++;
            showStep(currentStep);
        }
    });

    $('.prev-step').on('click', function() {
        currentStep--;
        showStep(currentStep);
    });

    // Image Upload
    $('.select-photo').on('click', function(e) {
        e.preventDefault();
        var button = $(this);
        var custom_uploader = wp.media({
            title: 'Select Agent Photo',
            button: {
                text: 'Use this photo'
            },
            multiple: false
        }).on('select', function() {
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            $('#agent_photo').val(attachment.url);
        }).open();
    });

    // Form Submission
    $('#connectapre-wizard-form').on('submit', function(e) {
        e.preventDefault();
        
        var $btn = $(this).find('.finish-wizard');
        $btn.prop('disabled', true).text('Saving...');

        var formData = $(this).serialize();
        formData += '&action=connectapre_save_wizard&nonce=' + connectapreWizard.nonce;

        $.ajax({
            url: connectapreWizard.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    window.location.href = connectapreWizard.redirect_url;
                } else {
                    alert('Error saving settings. Please try again.');
                    $btn.prop('disabled', false).text('Finish & Go to Dashboard');
                }
            },
            error: function() {
                alert('Error saving settings. Please try again.');
                $btn.prop('disabled', false).text('Finish & Go to Dashboard');
            }
        });
    });

    // Location Picker Logic (Adapted from agents.js)
    const locationFields = document.querySelectorAll(".connectapre_location");
    locationFields.forEach(field => field.setAttribute("readonly", true));

    locationFields.forEach((field) => {
        field.addEventListener("click", () => {
            const parent = field.closest(".connectapre_location_parent");
            const existingModal = parent.querySelector(".connectapre_single_select_modal");
            if (existingModal) existingModal.remove();

            const defaultPlaces = ["Kathmandu", "Pokhara", "Lalitpur", "Biratnagar", "Butwal"];
            const field_modal = createSingleSelectModal(defaultPlaces, true, field);
            parent.appendChild(field_modal);
        });
    });

    function createSingleSelectModal(options = [], active = false, targetField = null) {
        const modal = document.createElement("div");
        modal.className = "connectapre_single_select_modal";
        if (active) modal.classList.add("active");

        const searchBox = document.createElement("input");
        searchBox.type = "text";
        searchBox.id = "connectapre_single_select_search_box";
        searchBox.placeholder = "Search city...";
        modal.appendChild(searchBox);

        const ul = document.createElement("ul");
        ul.className = "connectapre_list";
        modal.appendChild(ul);

        renderOptions(options, ul, targetField, modal);

        let debounceTimer;
        searchBox.addEventListener("input", () => {
            clearTimeout(debounceTimer);
            const query = searchBox.value.trim();
            if (!query) {
                renderOptions(options, ul, targetField, modal);
                return;
            }
            debounceTimer = setTimeout(() => {
                fetch(`https://photon.komoot.io/api/?q=${encodeURIComponent(query)}&limit=10&lang=en`)
                    .then(res => res.json())
                    .then(data => {
                        const features = data.features || [];
                        const allowedTypes = ['city', 'town', 'village', 'municipality', 'country'];
                        
                        const cityNames = features
                            .filter(feature => {
                                const props = feature.properties;
                                return props.osm_key === 'place' && allowedTypes.includes(props.osm_value);
                            })
                            .map(feature => {
                                const props = feature.properties;
                                let label = props.name;
                                if (props.city && props.city !== props.name) label += `, ${props.city}`;
                                if (props.state) label += `, ${props.state}`;
                                if (props.country) label += `, ${props.country}`;
                                return label;
                            });
                        
                        const uniqueCities = [...new Set(cityNames)];
                        renderOptions(uniqueCities, ul, targetField, modal);
                    })
                    .catch(err => console.error("Location fetch error:", err));
            }, 300);
        });

        // Close modal when clicking outside
        document.addEventListener('click', function(e) {
            if (!modal.contains(e.target) && e.target !== targetField) {
                modal.remove();
            }
        }, { once: true, capture: true });

        return modal;
    }

    function renderOptions(options, ul, targetField, modal) {
        ul.innerHTML = "";
        options.forEach(option => {
            const li = document.createElement("li");
            li.textContent = option;
            li.addEventListener("click", () => {
                if (targetField) targetField.value = option;
                modal.remove();
            });
            ul.appendChild(li);
        });
    }
});

