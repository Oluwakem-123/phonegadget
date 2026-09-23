// Main Javascript file for Phone Marketplace

// ==========================================
// TOAST NOTIFICATIONS
// ==========================================
document.addEventListener('DOMContentLoaded', function() {
    const toasts = document.querySelectorAll('.toast-message');
    
    toasts.forEach((toast, index) => {
        // Stagger entrance if multiple
        setTimeout(() => {
            toast.classList.remove('translate-x-full');
            toast.classList.add('translate-x-0');
        }, index * 100);

        // Auto dismiss after 4 seconds
        setTimeout(() => {
            if (document.body.contains(toast)) {
                toast.classList.remove('translate-x-0');
                toast.classList.add('opacity-0', 'scale-95');
                setTimeout(() => toast.remove(), 300); // Wait for transition
            }
        }, 4000 + (index * 100));
    });
});


// ==========================================
// CUSTOM MODAL CONFIRMATIONS
// ==========================================
let currentModalAction = null;

function openModal(title, confirmText, cancelText, isErrorStyle, onConfirmCallback) {
    const modal = document.getElementById('custom-modal');
    const titleEl = document.getElementById('modal-title');
    const btnConfirm = document.getElementById('modal-btn-confirm');
    const btnCancel = document.getElementById('modal-btn-cancel');
    const iconContainer = document.getElementById('modal-icon-container');

    if (!modal) return;

    // Set text
    titleEl.textContent = title;
    btnConfirm.textContent = confirmText;
    btnCancel.textContent = cancelText;

    // Set styling based on type
    if (isErrorStyle) {
        btnConfirm.className = "inline-flex justify-center w-full px-4 py-3 text-base font-bold text-white bg-error border border-transparent rounded-xl shadow-sm hover:bg-red-700 focus:outline-none sm:text-sm mb-3 sm:mb-0";
        iconContainer.innerHTML = '<svg class="w-6 h-6 text-error" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>';
        iconContainer.className = "flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-red-100 rounded-full";
    } else {
        btnConfirm.className = "inline-flex justify-center w-full px-4 py-3 text-base font-bold text-white bg-primary border border-transparent rounded-xl shadow-sm hover:bg-gray-800 focus:outline-none sm:text-sm mb-3 sm:mb-0";
        iconContainer.innerHTML = '<svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
        iconContainer.className = "flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-gray-100 rounded-full";
    }

    // Handle Confirm action
    currentModalAction = function() {
        closeModal();
        if (onConfirmCallback) onConfirmCallback();
    };

    // Remove old listeners to avoid multiple fires, add new one
    btnConfirm.onclick = currentModalAction;

    // Show modal
    modal.classList.remove('hidden');
}

function closeModal() {
    const modal = document.getElementById('custom-modal');
    if (modal) {
        modal.classList.add('hidden');
    }
}


// ==========================================
// FORM DOUBLE SUBMIT PROTECTION
// ==========================================
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        // Skip forms that already have their own specific onsubmit in HTML
        if (!form.hasAttribute('data-custom-submit')) {
            form.addEventListener('submit', function(e) {
                // Find submit buttons
                const buttons = form.querySelectorAll('button[type="submit"]');
                buttons.forEach(btn => {
                    // Prevent multiple clicks by disabling button shortly after submit
                    setTimeout(() => {
                        btn.disabled = true;
                        btn.classList.add('opacity-50', 'cursor-not-allowed');
                    }, 10);
                });
            });
        }
    });
});
