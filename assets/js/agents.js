function openModal() {
    const backdrop = document.getElementById("modalBackdrop");
    const modal = document.getElementById("addAgentModal");

    backdrop.classList.add("show-modal");
    modal.classList.add("show-box");
}

function closeModal() {
    const backdrop = document.getElementById("modalBackdrop");
    const modal = document.getElementById("addAgentModal");

    modal.classList.remove("show-box");
    backdrop.classList.remove("show-modal");
}

function setEditFormValues({
    currentAgentID,
    currentImage,
    currentName,
    currentPhone,
    currentLocation,
    currentDefaultStatus,
    currentGreetings,
    currentStatus
}) {
    const imagePreviewField = document.getElementById("edit--photoPreview");
    const nameField = document.getElementById("edit--name");
    const phoneField = document.getElementById("edit--phone");
    const locationField = document.getElementById("edit--location");
    const isdefaultField = document.getElementById("edit--is_default");
    const greetingsField = document.getElementById("edit--greeting");
    const statusField = document.getElementById("edit--status");
    const agentIDField = document.getElementById("edit--agent_id");

    if (!currentAgentID) {
        console.warn("Could't find Agents !");
    }

    agentIDField.value = currentAgentID;

    if (!imagePreviewField) {
        console.warn("Could't find image image !");
    }

    console.log(isdefaultField, Number(currentDefaultStatus));


    imagePreviewField.innerHTML = `<img src="${currentImage}" class="w-full h-full object-cover" alt="Agent photo" />`;
    nameField.value = currentName;
    phoneField.value = currentPhone;
    locationField.value = currentLocation;
    isdefaultField.value = Number(currentDefaultStatus);
    greetingsField.value = currentGreetings;
    statusField.checked = !!currentStatus;

}

document.addEventListener('click', function (e) {

    const deleteBtn = e.target.closest('.remove-agent');
    const editBtn = e.target.closest(".edit-agent");

    if (deleteBtn) {
        const row = deleteBtn.closest('tr');
        if (!row) return;

        const agentId = row.getAttribute('id');
        document.getElementById('delete-agent-id').value = agentId;

        const modalbackdrop = document.getElementById('delete-agent-modal-backdrop');
        const modal = document.getElementById('delete-agent-modal');
        modal.classList.remove('hidden');
        modal.classList.add('show-box');
        modalbackdrop.classList.add('show-modal');
        return;
    }

    if (editBtn) {
        const row = editBtn.closest('tr');
        if (!row) return;

        const agentId = row.getAttribute('id');

        const agent_image = row.querySelector('.agent-image')?.src;
        const agent_name = row.querySelector('.agent-name')?.textContent.trim();
        const agent_phone = row.querySelector('.agent-phone')?.textContent.trim();
        const agent_location = row.querySelector('.agent-location')?.textContent.trim();
        const agent_greeting = row.querySelector('.agent-greeting')?.textContent.trim();
        const agent_status = row.querySelector('.agent-status')?.textContent?.trim() === "Online" ? true : false;
        const agent_is_default = row.querySelector('.agent-is-default')?.textContent?.trim() === "Default" ? true : false;


        setEditFormValues({
            currentAgentID: agentId,
            currentImage: agent_image,
            currentName: agent_name,
            currentLocation: agent_location,
            currentGreetings: agent_greeting,
            currentPhone: agent_phone,
            currentDefaultStatus: agent_is_default,
            currentStatus: agent_status,
        })

        const modalbackdrop = document.getElementById('edit-agent-modal-backdrop');
        const modal = document.getElementById('edit-agent-modal');


        modal.classList.remove('hidden');
        modal.classList.add('show-box');
        modalbackdrop.classList.add('show-modal');
    }

    if (
        e.target.classList.contains('close-delete-modal') ||
        e.target.id === 'delete-agent-modal'
    ) {
        const modal = document.getElementById('delete-agent-modal');
        const modalbackdrop = document.getElementById('delete-agent-modal-backdrop');
        modal.classList.remove('show-box');
        modalbackdrop.classList.remove('show-modal');
    }

    if (
        e.target.classList.contains('close-edit-modal') ||
        e.target.id === 'edit-agent-modal'
    ) {
        const modalbackdrop = document.getElementById('edit-agent-modal-backdrop');
        const modal = document.getElementById('edit-agent-modal');
        modal.classList.remove('show-box');
        modalbackdrop.classList.remove('show-modal');
    }

});

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

document.getElementById("edit--status").addEventListener("change", function () {
    console.log(this.checked ? "Enabled" : "Disabled");
});

function initAgentPhotoPickers() {
    document.querySelectorAll('.agent-photo-picker').forEach((picker) => {
        const btn = picker.querySelector('.choose-photo-btn');
        const preview = picker.querySelector('.photo-preview');
        const input = picker.querySelector('.photo-input');

        let uploader = null;

        btn.addEventListener('click', function () {
            if (uploader) {
                uploader.open();
                return;
            }

            uploader = wp.media({
                title: 'Choose Agent Photo',
                button: { text: 'Select Photo' },
                multiple: false,
                library: { type: 'image' }
            });

            uploader.on('select', function () {
                const attachment = uploader.state().get('selection').first().toJSON();

                preview.innerHTML = `
                    <img src="${attachment.url}" 
                         class="w-full h-full object-cover" 
                         alt="Agent photo">
                `;

                input.value = attachment.url; // or attachment.id (recommended)
            });

            uploader.open();
        });
    });
}

document.addEventListener("DOMContentLoaded", () => {
    initAgentPhotoPickers();
    initLocationSelect();
    
    // Filter Logic
    const applyFilterBtn = document.getElementById('apply-filter');
    const searchInput = document.getElementById('agent-search');
    const statusSelect = document.getElementById('agent-status');
    const sortSelect = document.getElementById('agent-sort');

    if (applyFilterBtn) {
        applyFilterBtn.addEventListener('click', function() {
            const searchText = searchInput.value;
            const statusValue = statusSelect.value;
            const sortValue = sortSelect.value;
            
            const url = new URL(window.location.href);
            
            if (searchText) {
                url.searchParams.set('s', searchText);
            } else {
                url.searchParams.delete('s');
            }

            if (statusValue) {
                url.searchParams.set('status', statusValue);
            } else {
                url.searchParams.delete('status');
            }

            if (sortValue) {
                url.searchParams.set('sort', sortValue);
            } else {
                url.searchParams.delete('sort');
            }

            // Reset page on filter change
            url.searchParams.set('paged', 1);

            window.location.href = url.toString();
        });

        // Trigger search on Enter
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                applyFilterBtn.click();
            }
        });
    }
});

let activeTargetInput = null;
let locationModal = null;

function createSingleSelectModal() {

    const modal = document.createElement("div");
    modal.className = `
        converso_single_select_modal
        hidden
        rounded-lg
        border border-gray-200
        bg-white
        shadow-lg
        text-sm
        overflow-hidden
    `;

    const searchWrapper = document.createElement("div");
    searchWrapper.className = "p-2 border-b border-gray-100";

    const searchBox = document.createElement("input");
    searchBox.type = "text";
    searchBox.id = "converso_single_select_search_box";
    searchBox.placeholder = "Search city...";
    searchBox.className = `
        !font-primary
        !w-full
        !rounded-md
        !border !border-gray-200
        !px-3 !py-1.5
        !text-sm
        focus:!outline-none
        !focus:!ring-2 focus:!ring-blue-500
    `;

    searchWrapper.appendChild(searchBox);
    modal.appendChild(searchWrapper);

    const ul = document.createElement("ul");
    ul.className = `
        !font-primary
        converso_list
        !max-h-56
        !overflow-y-auto
        !divide-y !divide-gray-100
    `;

    modal.appendChild(ul);

    let debounceTimer;

    searchBox.addEventListener("input", () => {
        clearTimeout(debounceTimer);
        const query = searchBox.value.trim();

        if (!query) {
            ul.innerHTML = `
                <li class="!px-3 !py-2 !text-xs !text-gray-400">
                    Start typing to search…
                </li>`;
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch(`https://photon.komoot.io/api/?q=${encodeURIComponent(query)}&limit=10&lang=en`)
                .then(res => res.json())
                .then(data => {
                    const features = data.features || [];
                    const allowedTypes = ['city', 'town', 'village', 'municipality', 'country'];

                    const results = features
                        .filter(f =>
                            f.properties.osm_key === 'place' &&
                            allowedTypes.includes(f.properties.osm_value)
                        )
                        .map(f => {
                            const p = f.properties;
                            let label = p.name;
                            if (p.state) label += `, ${p.state}`;
                            if (p.country) label += `, ${p.country}`;
                            return label;
                        });

                    renderOptions([...new Set(results)], ul, modal);
                });
        }, 250);
    });

    return modal;
}

function renderOptions(options, ul, modal) {
    ul.innerHTML = "";

    if (!options.length) {
        ul.innerHTML = `
            <li class="px-3 py-2 text-xs text-gray-400">
                No results found
            </li>`;
        return;
    }

    options.forEach(option => {
        const li = document.createElement("li");
        li.className = `
            px-3 py-2
            cursor-pointer
            transition
            hover:bg-gray-100
            active:bg-gray-200
        `;
        li.textContent = option;

        li.addEventListener("click", () => {
            if (activeTargetInput) {
                activeTargetInput.value = option;
            }
            modal.classList.add("hidden");
        });

        ul.appendChild(li);
    });
}

function positionModal(input, modal) {
    const rect = input.getBoundingClientRect();
    modal.style.top = `${rect.bottom + window.scrollY}px`;
    modal.style.left = `${rect.left + window.scrollX}px`;
    modal.style.width = `${rect.width}px`;
}

function initLocationSelect() {

    locationModal = createSingleSelectModal();
    document.body.appendChild(locationModal);

    document.addEventListener("focusin", (e) => {
        const input = e.target.closest(".location-select");
        if (!input) return;

        activeTargetInput = input;

        positionModal(input, locationModal);
        locationModal.classList.add("active");

        const searchBox = locationModal.querySelector("#converso_single_select_search_box");
        searchBox.value = "";
        searchBox.focus();
    });

    document.addEventListener("click", (e) => {
        if (
            !locationModal.contains(e.target) &&
            !e.target.closest(".location-select")
        ) {
            locationModal.classList.remove("active");
        }
    });

    window.addEventListener("resize", () => {
        if (activeTargetInput) {
            positionModal(activeTargetInput, locationModal);
        }
    });
}

document.addEventListener("DOMContentLoaded", initLocationSelect);