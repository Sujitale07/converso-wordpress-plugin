const ConnectapreNotification = (function() {
    
    function createIcon(type) {
        let path = '';
        if (type === 'success') path = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>';
        if (type === 'error')   path = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>';
        if (type === 'warning') path = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>';
        if (type === 'info')    path = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>';

        return `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">${path}</svg>`;
    }

    function show(type, message, title = '') {
        const container = document.getElementById('connectapre-toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = `connectapre-toast ${type}`;
        
        toast.innerHTML = `
            <div class="connectapre-toast-icon">
                ${createIcon(type)}
            </div>
            <div class="connectapre-toast-content">
                ${title ? `<div class="connectapre-toast-title">${title}</div>` : ''}
                <div class="connectapre-toast-message">${message}</div>
            </div>
            <button class="connectapre-toast-close" onclick="this.parentElement.remove()">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        `;

        container.appendChild(toast);

        // Animate in
        requestAnimationFrame(() => {
            toast.classList.add('show');
        });

        // Auto remove
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                if (toast.parentElement) toast.remove();
            }, 300);
        }, 5000);
    }

    return {
        show: show
    };

})();

