try {
    jQuery(document).ready(function ($) {
        let index = $('#agents-repeater tbody tr').length;

        // Add new agent row
        $('#add-agent').on('click', function () {
            let row = `<tr>
                <td style="border:1px solid #ccc; padding:5px;">${index + 1}</td>
                <td style="border:1px solid #ccc; padding:5px; text-align:center;">
                    <input type="radio" name="converso_agents_default" value="${index}">
                </td>
                <td style="border:1px solid #ccc; padding:5px;">
                    <input type="text" name="converso_agents_data[${index}][name]" class="regular-text">
                </td>
                <td style="border:1px solid #ccc; padding:5px;">
                    <input type="text" name="converso_agents_data[${index}][phone]" class="regular-text">
                </td>
                <td style="border:1px solid #ccc; padding:5px;">
                    <div style="display:flex;align-items:center;gap:10px">
                        <img src="" alt="" class="agent-photo-preview" style="max-width:50px;max-height:50px;display:block;margin-bottom:5px;">
                        <div>
                            <input type="text" name="converso_agents_data[${index}][photo]" class="regular-text agent-photo-url">
                            <button type="button" class="button select-photo" style="margin-top:5px;">Select Image</button>
                        </div>
                    </div>
                </td>
                <td class="converso_location_parent">
                    <input type="text" name="converso_agents_data[${index}][location]" class="regular-text converso_location" ">
                </td>
                <td style="border:1px solid #ccc; padding:5px;">
                    <textarea name="converso_agents_data[${index}][greetings]" class="regular-text" rows="5"></textarea>
                </td>
                <td style="border:1px solid #ccc; padding:5px;">
                    <button type="button" class="button remove-agent">Remove</button>
                </td>
            </tr>`;
            $('#agents-repeater tbody').append(row);
            index++;
        });

        $(document).on('click', '.remove-agent', function () {
            $(this).closest('tr').remove();
        });

        $(document).on('click', '.select-photo', function (e) {
            e.preventDefault();

            const button = $(this);
            const container = button.closest('td');
            const input = container.find('.agent-photo-url');
            const img = container.find('.agent-photo-preview');

            const frame = wp.media({
                title: 'Select or Upload Image',
                button: { text: 'Use this image' },
                multiple: false
            });

            frame.on('select', function () {
                const attachment = frame.state().get('selection').first().toJSON();
                input.val(attachment.url);
                img.attr('src', attachment.url);
            });

            frame.open();
        });

        $(document).on('input', '.agent-photo-url', function () {
            const container = $(this).closest('td');
            const img = container.find('.agent-photo-preview');
            img.attr('src', $(this).val());
        });

    });
} catch (error) {
    console.log("Error in adding agent");
}
document.addEventListener("DOMContentLoaded", () => {
    const locationFields = document.querySelectorAll(".converso_location");

    // Make fields readonly so user must use the modal
    locationFields.forEach(field => field.setAttribute("readonly", true));

    locationFields.forEach((field) => {
        field.addEventListener("click", () => {
            const parent = field.closest(".converso_location_parent");

            // Remove any existing modal
            const existingModal = parent.querySelector(".converso_single_select_modal");
            if (existingModal) existingModal.remove();

            const defaultPlaces = ["Kathmandu", "Pokhara", "Lalitpur", "Biratnagar", "Butwal"];
            const field_modal = createSingleSelectModal(defaultPlaces, true, field);
            parent.appendChild(field_modal);
        });
    });
});

function createSingleSelectModal(options = [], active = false, targetField = null) {
    // Create wrapper div
    const modal = document.createElement("div");
    modal.className = "converso_single_select_modal";
    if (active) modal.classList.add("active");

    // Create search box
    const searchBox = document.createElement("input");
    searchBox.type = "text";
    searchBox.id = "converso_single_select_search_box";
    searchBox.placeholder = "Search city...";
    modal.appendChild(searchBox);

    // Create list
    const ul = document.createElement("ul");
    ul.className = "converso_list";
    modal.appendChild(ul);

    // Render default options immediately
    renderOptions(options, ul, targetField, modal);

    // Fetch from Nominatim when typing
    let debounceTimer;
    searchBox.addEventListener("input", () => {
        clearTimeout(debounceTimer);
        const query = searchBox.value.trim();
        if (!query) {
            // Reset to default places if search is cleared
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
                            
                            if (props.city && props.city !== props.name) {
                                label += `, ${props.city}`;
                            }
                            if (props.state) {
                                label += `, ${props.state}`;
                            }
                            if (props.country) {
                                label += `, ${props.country}`;
                            }
                            return label;
                        });
                    
                    // Remove duplicates
                    const uniqueCities = [...new Set(cityNames)];
                    renderOptions(uniqueCities, ul, targetField, modal);
                })
                .catch(err => {
                    console.error("Location fetch error:", err);
                });
        }, 300); // debounce 300ms
    });

    return modal;
}

// Helper to render list items
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
