/**
 * Main JavaScript
 * SUNDARI TOP STAR S.R.L.
 */

document.addEventListener('DOMContentLoaded', function() {

    // Mobile menu toggle
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const navMenu = document.querySelector('.nav-menu');

    if (mobileMenuToggle && navMenu) {
        mobileMenuToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });
    }

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            }
        });
    });

    // Auto-hide flash messages after 5 seconds
    const flashMessages = document.querySelectorAll('.flash-message');
    flashMessages.forEach(message => {
        setTimeout(() => {
            message.style.transition = 'opacity 0.5s';
            message.style.opacity = '0';
            setTimeout(() => message.remove(), 500);
        }, 5000);
    });

});

/**
 * Add to cart function
 * @param {number} productId
 * @param {number} quantity
 */
function addToCart(productId, quantity = 1) {
    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('product_id', productId);
    formData.append('quantity', quantity);

    fetch('/api/cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update cart count
            const cartCount = document.getElementById('cart-count');
            if (cartCount) {
                cartCount.textContent = data.count;
            }

            // Show message
            alert(data.message || 'Produs adăugat în coș!');
        } else {
            alert(data.message || 'Eroare la adăugare în coș.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Eroare la comunicare cu serverul.');
    });
}

/**
 * Update cart item quantity
 * @param {number} itemId
 * @param {number} quantity
 */
function updateCartItem(itemId, quantity) {
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('item_id', itemId);
    formData.append('quantity', quantity);

    fetch('/api/cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Eroare la actualizare.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Eroare la comunicare cu serverul.');
    });
}

/**
 * Remove item from cart
 * @param {number} itemId
 */
function removeFromCart(itemId) {
    if (!confirm('Ești sigur că vrei să ștergi acest produs din coș?')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'remove');
    formData.append('item_id', itemId);

    fetch('/api/cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Eroare la ștergere.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Eroare la comunicare cu serverul.');
    });
}

/**
 * Filter products
 */
function filterProducts() {
    const form = document.getElementById('filter-form');
    if (form) {
        form.submit();
    }
}
