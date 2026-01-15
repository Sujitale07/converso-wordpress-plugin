// Handle Button Style Selection
document.querySelectorAll('.button-style-card').forEach(function(card) {
    card.addEventListener('click', function(e) {
        if(e.target.tagName.toLowerCase() !== 'input') {
            const radio = card.querySelector('input[type="radio"]');
            if(radio) {
                radio.checked = true;
                radio.dispatchEvent(new Event('change', { bubbles: true }));
            }

            // Remove active class from all and add to clicked
            document.querySelectorAll('.button-style-card').forEach(c => c.classList.remove('active'));
            card.classList.add('active');
        }
    });
});

// Handle Position Card Selection
document.querySelectorAll('.position-card').forEach(function(card) {
    card.addEventListener('click', function(e) {
        if(e.target.tagName.toLowerCase() !== 'input') {
            const radio = card.querySelector('input[type="radio"]');
            if(radio) {
                radio.checked = true;
                radio.dispatchEvent(new Event('change', { bubbles: true }));
            }

            // Remove active class from all and add to clicked
            document.querySelectorAll('.position-card').forEach(c => c.classList.remove('active'));
            card.classList.add('active');
        }
    });
});

// Legacy support for old button-demo class (if any remain)
document.querySelectorAll('.button-demo').forEach(function(div) {
    div.addEventListener('click', function(e) {
        if(e.target.tagName.toLowerCase() !== 'input') {
            const radio = div.querySelector('input[type="radio"]');
            if(radio) {
                radio.checked = true;
                radio.dispatchEvent(new Event('change', { bubbles: true }));
            }

            // Remove active class from all and add to clicked
            document.querySelectorAll('.button-demo').forEach(d => d.classList.remove('active'));
            div.classList.add('active');
        }
    });
});
