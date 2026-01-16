/**
 * Modern JavaScript - Premium Industrial Design
 * SUNDARI TOP STAR S.R.L.
 * Piese Mașini de Cusut
 */

document.addEventListener('DOMContentLoaded', function() {

    // =====================================================
    // MOBILE MENU
    // =====================================================
    const mobileToggle = document.getElementById('mobile-toggle');
    const navMenu = document.querySelector('.nav-menu');
    const mobileOverlay = document.getElementById('mobile-overlay');
    const header = document.getElementById('main-header');

    function toggleMobileMenu() {
        const isActive = navMenu.classList.toggle('active');
        mobileToggle.classList.toggle('active');
        mobileOverlay.classList.toggle('active');

        // Prevent body scroll when menu is open
        document.body.style.overflow = isActive ? 'hidden' : '';
    }

    function closeMobileMenu() {
        navMenu.classList.remove('active');
        mobileToggle.classList.remove('active');
        mobileOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (mobileToggle && navMenu) {
        mobileToggle.addEventListener('click', toggleMobileMenu);

        // Close menu when clicking on overlay
        if (mobileOverlay) {
            mobileOverlay.addEventListener('click', closeMobileMenu);
        }

        // Close menu when clicking on nav links
        const navLinks = navMenu.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', closeMobileMenu);
        });

        // Close menu on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && navMenu.classList.contains('active')) {
                closeMobileMenu();
            }
        });
    }

    // =====================================================
    // HEADER SCROLL EFFECT
    // =====================================================
    if (header) {
        let lastScroll = 0;
        const scrollThreshold = 50;

        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;

            // Add shadow on scroll
            if (currentScroll > scrollThreshold) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }

            lastScroll = currentScroll;
        });
    }

    // =====================================================
    // SMOOTH SCROLL FOR ANCHOR LINKS
    // =====================================================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });

    // =====================================================
    // AUTO-HIDE FLASH MESSAGES
    // =====================================================
    const flashMessages = document.querySelectorAll('.flash-message');
    flashMessages.forEach(message => {
        // Auto-hide after 5 seconds
        setTimeout(() => {
            message.style.transition = 'opacity 0.5s, transform 0.5s';
            message.style.opacity = '0';
            message.style.transform = 'translateY(-20px)';
            setTimeout(() => message.remove(), 500);
        }, 5000);

        // Close on click
        const closeBtn = message.querySelector('.flash-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                message.style.transition = 'opacity 0.3s, transform 0.3s';
                message.style.opacity = '0';
                message.style.transform = 'translateY(-20px)';
                setTimeout(() => message.remove(), 300);
            });
        }
    });

    // =====================================================
    // CART FUNCTIONALITY
    // =====================================================
    // Update cart on page load
    updateCartDisplay();

    // Setup cart badge animation
    const cartBadge = document.getElementById('cart-count');
    if (cartBadge) {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'characterData' || mutation.type === 'childList') {
                    // Animate badge on count change
                    cartBadge.style.transform = 'scale(1.3)';
                    setTimeout(() => {
                        cartBadge.style.transform = 'scale(1)';
                    }, 200);
                }
            });
        });

        observer.observe(cartBadge, {
            childList: true,
            characterData: true,
            subtree: true
        });
    }

    // =====================================================
    // SEARCH FUNCTIONALITY
    // =====================================================
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        // Clear search on escape
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                searchInput.value = '';
                searchInput.blur();
            }
        });
    }

    // =====================================================
    // PRODUCT CARDS - ENHANCE HOVER EFFECTS
    // =====================================================
    const productCards = document.querySelectorAll('.product-card');
    productCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // =====================================================
    // ENHANCED LAZY LOADING
    // =====================================================
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;

                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                    }

                    if (img.dataset.srcset) {
                        img.srcset = img.dataset.srcset;
                        img.removeAttribute('data-srcset');
                    }

                    img.classList.add('loaded');
                    observer.unobserve(img);
                }
            });
        }, {
            rootMargin: '50px 0px',
            threshold: 0.01
        });

        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });

        // Add lazy loading attribute as fallback
        document.querySelectorAll('img:not([loading])').forEach(img => {
            img.setAttribute('loading', 'lazy');
        });
    } else {
        document.querySelectorAll('img[data-src]').forEach(img => {
            img.src = img.dataset.src;
        });
    }

});

/**
 * Update Cart Display
 * Updates cart count and total in header
 */
function updateCartDisplay() {
    fetch('/api/cart.php?action=count')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const cartCount = document.getElementById('cart-count');
                const cartTotal = document.getElementById('cart-total');

                if (cartCount) {
                    cartCount.textContent = data.count || 0;
                }

                if (cartTotal && data.total) {
                    cartTotal.textContent = formatPrice(data.total);
                }
            }
        })
        .catch(error => {
            // Error silently handled
        });
}

/**
 * Add to Cart Function
 * @param {number} productId
 * @param {number} quantity
 */
function addToCart(productId, quantity = 1) {
    // Show loading state
    const buttons = document.querySelectorAll(`button[onclick*="addToCart(${productId}"]`);
    buttons.forEach(btn => {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Se adaugă...';
    });

    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('product_id', productId);
    formData.append('quantity', quantity);

    fetch('/api/cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            updateCartDisplay();
            showNotification('success', data.message || 'Produs adăugat în coș!');
        } else {
            throw new Error(data.message || 'Eroare la adăugarea în coș');
        }
    })
    .catch(error => {
        showNotification('error', 'Eroare: ' + error.message);

        // Log to server for debugging
        fetch('/api/log_error.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                error: error.message,
                productId: productId,
                stack: error.stack
            })
        }).catch(err => {
            // Silently handle logging failure
        });
    })
    .finally(() => {
        // Reset buttons
        buttons.forEach(btn => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-shopping-cart"></i> Adaugă în Coș';
        });
    });
}

/**
 * Add to Cart with Quantity
 * @param {number} productId
 */
function addToCartWithQuantity(productId) {
    const quantityInput = document.getElementById('quantity');
    const quantity = quantityInput ? parseInt(quantityInput.value) || 1 : 1;
    addToCart(productId, quantity);
}

/**
 * Update Cart Item Quantity
 * @param {number} itemId
 * @param {number} quantity
 */
function updateCartItem(itemId, quantity) {
    if (quantity < 1) {
        removeFromCart(itemId);
        return;
    }

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
            updateCartDisplay();
            location.reload();
        } else {
            showNotification('error', data.message || 'Eroare la actualizare.');
        }
    })
    .catch(error => {
        showNotification('error', 'Eroare la comunicare cu serverul.');
    });
}

/**
 * Remove Item from Cart
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
            updateCartDisplay();
            location.reload();
        } else {
            showNotification('error', data.message || 'Eroare la ștergere.');
        }
    })
    .catch(error => {
        showNotification('error', 'Eroare la comunicare cu serverul.');
    });
}

/**
 * Delete Product (Admin)
 * @param {number} productId
 */
function deleteProduct(productId) {
    if (!confirm('Ești sigur că vrei să ștergi acest produs?')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('product_id', productId);

    fetch('/admin/products.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', data.message || 'Produs șters cu succes!');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('error', data.message || 'Eroare la ștergere.');
        }
    })
    .catch(error => {
        showNotification('error', 'Eroare la comunicare cu serverul.');
    });
}

/**
 * Delete Category (Admin)
 * @param {number} categoryId
 * @param {number} productsCount
 */
function deleteCategory(categoryId, productsCount) {
    if (productsCount > 0) {
        alert('Nu poți șterge o categorie care are produse!');
        return;
    }

    if (!confirm('Ești sigur că vrei să ștergi această categorie?')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('category_id', categoryId);

    fetch('/admin/categories.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', data.message || 'Categorie ștearsă cu succes!');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('error', data.message || 'Eroare la ștergere.');
        }
    })
    .catch(error => {
        showNotification('error', 'Eroare la comunicare cu serverul.');
    });
}

/**
 * Delete Shipping Rate (Admin)
 * @param {number} rateId
 */
function deleteRate(rateId) {
    if (!confirm('Ești sigur că vrei să ștergi această taxă de livrare?')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('rate_id', rateId);

    fetch('/admin/shipping.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', data.message || 'Taxă ștearsă cu succes!');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('error', data.message || 'Eroare la ștergere.');
        }
    })
    .catch(error => {
        showNotification('error', 'Eroare la comunicare cu serverul.');
    });
}

/**
 * Filter Products
 * Triggers form submission for filters
 */
function filterProducts() {
    const form = document.getElementById('filter-form');
    if (form) {
        form.submit();
    }
}

/**
 * Show Notification
 * Displays a temporary notification message
 * @param {string} type - success, error, warning, info
 * @param {string} message
 */
function showNotification(type, message) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `flash-message flash-${type}`;
    notification.innerHTML = `
        <div class="container">
            <div class="flash-content">
                <i class="fas fa-${type === 'success' ? 'check-circle' : (type === 'error' ? 'exclamation-circle' : 'info-circle')}"></i>
                <span>${message}</span>
            </div>
            <button class="flash-close" onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;

    // Insert at the top of the page
    const header = document.querySelector('.modern-header');
    if (header) {
        header.after(notification);
    } else {
        document.body.insertBefore(notification, document.body.firstChild);
    }

    // Auto-remove after 5 seconds
    setTimeout(() => {
        notification.style.transition = 'opacity 0.5s, transform 0.5s';
        notification.style.opacity = '0';
        notification.style.transform = 'translateY(-20px)';
        setTimeout(() => notification.remove(), 500);
    }, 5000);
}

/**
 * Format Price
 * Formats a number as Romanian currency
 * @param {number} amount
 * @returns {string}
 */
function formatPrice(amount) {
    return new Intl.NumberFormat('ro-RO', {
        style: 'currency',
        currency: 'RON'
    }).format(amount);
}

/**
 * Truncate Text
 * Truncates text to specified length
 * @param {string} text
 * @param {number} length
 * @returns {string}
 */
function truncate(text, length) {
    if (!text || text.length <= length) {
        return text;
    }
    return text.substring(0, length) + '...';
}

/**
 * Escape HTML
 * Escapes HTML special characters
 * @param {string} text
 * @returns {string}
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
