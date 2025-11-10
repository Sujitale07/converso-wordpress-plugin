jQuery(document).ready(function ($) {

    // Handle pre-submit validation
    $('form').on('submit', function (e) {
        let isValid = true;
        let callables = [];

        // Remove any old warnings
        $('.converso-warning').remove();

        // Loop through each row
        $('#dynamic-fields-repeater tbody tr').each(function () {
            let $row = $(this);
            let name = $row.find('input[name*="[name]"]').val().trim();
            let value = $row.find('input[name*="[value]"]').val().trim();
            let callable = $row.find('input[name*="[callable]"]').val().trim();

            // Skip empty rows
            if (!name && !value) return;

            // If name exists but value is empty
            if (name && !value) {
                showWarning(`Value cannot be empty for field "<strong>${name}</strong>".`);
                $row.css('background', '#ffe5e5ff');
                isValid = false;
                return false; // stop loop
            }

            // Check duplicate callable
            if (callables.includes(callable)) {
                showWarning(`Duplicate callable <code>${callable}</code> found. Each callable must be unique.`);
                $row.css('background', '#fff8e5');
                isValid = false;
                return false; // stop loop
            }

            callables.push(callable);
            $row.css('background', ''); 
        });

        if (!isValid) {
            e.preventDefault();
        }
    });

    function showWarning(message) {
        let $notice = $(`
            <div class="notice notice-warning is-dismissible converso-warning">
                <p>${message}</p>
            </div>
        `);
        $('.toast-placeholder').prepend($notice);
        setTimeout(() => {
            $notice.fadeOut(400, function () {
                $(this).remove();
            });
        }, 5000);
    }

    // ====== ADD/REMOVE ROWS ======
    let index = $('#dynamic-fields-repeater tbody tr').length;

    // Add new row
    $('#add-dynamic-fields').on('click', function () {
        let row = `<tr>
            <td style="border:1px solid #ccc; padding:5px;">${index + 1}</td>
            <td style="border:1px solid #ccc; padding:5px;"><input type="text"  name="converso_dynamic_fields_data[${index}][name]" class="regular-text dynamic_fields_name"></td>
            <td style="border:1px solid #ccc; padding:5px;"><input type="text" name="converso_dynamic_fields_data[${index}][value]" class="regular-text"></td>
            <td style="border:1px solid #ccc; padding:5px;"><input type="text" name="converso_dynamic_fields_data[${index}][callable]" readonly class="regular-text dynamic_fields_callable"></td>
            <td style="border:1px solid #ccc; padding:5px;">
                <button type="button" class="button remove-dynamic-fields">Remove</button>
            </td>
        </tr>`;
        $('#dynamic-fields-repeater tbody').append(row);
        index++;
    });

    // Remove row
    $(document).on('click', '.remove-dynamic-fields', function () {
        $(this).closest('tr').remove();
    });

    // Update callable on input
    $(document).on('input', '.dynamic_fields_name', function () {
        let value = $(this).val().trim();
        let callableValue = `{${value}}`;
        let $currentRow = $(this).closest('tr');
        let $callableField = $currentRow.find('.dynamic_fields_callable');

        $('.converso-warning').remove();

        let existingCallables = $('.dynamic_fields_callable')
            .not($callableField)
            .map(function () { return $(this).val(); })
            .get();

        $callableField.val(callableValue);
    });

});
