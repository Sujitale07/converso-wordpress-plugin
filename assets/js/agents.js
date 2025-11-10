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
            <td style="border:1px solid #ccc; padding:5px;">
                <input type="text" name="converso_agents_data[${index}][location]" class="regular-text">
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
