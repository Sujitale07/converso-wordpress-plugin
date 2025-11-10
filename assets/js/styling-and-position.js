document.querySelectorAll('.button-demo').forEach(function(div){
    div.addEventListener('click', function(e){
        if(e.target.tagName.toLowerCase() !== 'input') {
            const radio = div.querySelector('input[type="radio"]');
            if(radio) radio.checked = true;
            radio.dispatchEvent(new Event('change', { bubbles: true }));

            // Remove active class from all and add to clicked
            document.querySelectorAll('.button-demo').forEach(d => d.classList.remove('active'));
            div.classList.add('active');
        }
    })
});
