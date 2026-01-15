document.addEventListener("DOMContentLoaded", function () {
    const list = document.getElementById("converso-fields-table");
    const searchInput = document.getElementById('field-search');
    const sortSelect = document.getElementById('field-sort');
    const applyFilterBtn = document.getElementById('apply-filter');

    // Add Field Modal
    const addFieldModal = document.getElementById("addFieldModal");
    const modalBackdrop = document.getElementById("modalBackdrop");
    const addFieldBtn = document.getElementById("add-field");
    const closeFieldModalBtn = document.querySelector("#addFieldModal button");

    if (addFieldBtn) {
        addFieldBtn.addEventListener("click", () => {
            modalBackdrop.classList.remove("opacity-0", "pointer-events-none");
            addFieldModal.classList.remove("opacity-0", "scale-90");
        });
    }

    if (closeFieldModalBtn) {
        closeFieldModalBtn.addEventListener("click", () => {
            closeModal();
        });
    }

    // Close on backdrop click
    if (modalBackdrop) {
        modalBackdrop.addEventListener("click", (e) => {
            if (e.target === modalBackdrop) {
                closeModal();
                closeEditModal();
                closeDeleteModal();
            }
        });
    }

    function closeModal() {
        modalBackdrop.classList.add("opacity-0", "pointer-events-none");
        addFieldModal.classList.add("opacity-0", "scale-90");
        document.getElementById("fieldForm").reset();
    }

    // Edit Field Modal
    const editFieldModal = document.getElementById("edit-field-modal");
    const editBackdrop = document.getElementById("edit-field-modal-backdrop");
    const closeEditModalBtn = document.querySelector(".close-edit-modal");

    // Event Delegation for Edit Buttons
    // Event Delegation for Edit Buttons
    // Removed duplicate listener block as it is now handled at the end of the file.


    function openEditModal() {
        editBackdrop.classList.remove("opacity-0", "pointer-events-none");
        editFieldModal.classList.remove("opacity-0", "scale-90");
    }

    function closeEditModal() {
        editBackdrop.classList.add("opacity-0", "pointer-events-none");
        editFieldModal.classList.add("opacity-0", "scale-90");
    }

    if (closeEditModalBtn) {
        closeEditModalBtn.addEventListener("click", closeEditModal);
    }

    // Delete Field Modal
    const deleteBackdrop = document.getElementById("delete-field-modal-backdrop");
    const deleteModal = document.getElementById("delete-field-modal");
    const closeDeleteBtn = document.querySelector(".close-delete-modal");

    function openDeleteModal() {
        deleteBackdrop.classList.remove("opacity-0", "pointer-events-none");
        deleteModal.classList.remove("opacity-0", "scale-90");
    }

    function closeDeleteModal() {
        deleteBackdrop.classList.add("opacity-0", "pointer-events-none");
        deleteModal.classList.add("opacity-0", "scale-90");
    }

    if (closeDeleteBtn) {
        closeDeleteBtn.addEventListener("click", closeDeleteModal);
    }

    // Filter Logic
    if (applyFilterBtn) {
        applyFilterBtn.addEventListener('click', function() {
            const searchText = searchInput ? searchInput.value : '';
            const sortValue = sortSelect ? sortSelect.value : '';
            
            const url = new URL(window.location.href);
            
            if (searchText) {
                url.searchParams.set('s', searchText);
            } else {
                url.searchParams.delete('s');
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
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    applyFilterBtn.click();
                }
            });
        }
    }
    
    // Auto-generate Callable in Add Modal
    const addNameInput = document.getElementById("add--name");
    const addCallableInput = document.getElementById("add--callable");
    
    if (addNameInput && addCallableInput) {
        addNameInput.addEventListener("input", function() {
            const slug = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '');
            if (slug) {
                addCallableInput.value = `{${slug}}`;
            } else {
                addCallableInput.value = "";
            }
        });
    }

    // Global helper for opening modal from button (used in inline onclick)
    window.openFieldModal = function() {
        modalBackdrop.classList.remove("opacity-0", "pointer-events-none");
        addFieldModal.classList.remove("opacity-0", "scale-90");
    };

    window.closeFieldModal = function() {
        closeModal();
    };

    // Populate Edit Modal
    if (list) {
        list.addEventListener("click", function (e) {
            if (e.target.closest(".edit-field")) {
                const row = e.target.closest("tr");
                const id = row.getAttribute("id");
                
                const name = row.querySelector(".field-name").textContent;
                const value = row.querySelector(".field-value").textContent;
                const callable = row.querySelector(".field-callable").textContent;

                document.getElementById("edit--field_id").value = id;
                document.getElementById("edit--name").value = name;
                document.getElementById("edit--value").value = value;
                document.getElementById("edit--callable").value = callable;

                openEditModal();
            }

            if (e.target.closest(".remove-field")) {
                const row = e.target.closest("tr");
                const id = row.getAttribute("id");
                document.getElementById("delete-field-id").value = id;
                openDeleteModal();
            }
        });
    }
});