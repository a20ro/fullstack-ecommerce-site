// Simple Framework - Main JavaScript
class SimpleFramework {
    constructor() {
        this.currentPage = 'home';
        this.pages = {
            'home': 'pages/home.php',
            'products': 'pages/products.php',
            'deals': 'pages/deals.php',
            'about': 'pages/about.php',
            'contact': 'pages/contact.php',
            'item': 'pages/item.php',
            'profile': 'pages/profile.php',
            'cart': 'pages/cart.php',
            'checkout': 'pages/checkout.php',
            'order_confirmation': 'pages/order_confirmation.php',
            'order_history': 'pages/order_history.php'
        };
        
        this.init();
    }
    
    init() {
        this.loadSidebar();
        this.loadFooter();
        this.setupNavigation();
    }
    
    async loadSidebar() {
        try {
            const response = await fetch('sidebar/sidebar.html');
            const sidebarHTML = await response.text();
            document.getElementById('sidebar-container').innerHTML = sidebarHTML;
            this.setupSidebarNavigation();
        } catch (error) {
            console.error('Error loading sidebar:', error);
        }
    }
    
    async loadFooter() {
        try {
            const response = await fetch('footer/footer.html');
            const footerHTML = await response.text();
            document.getElementById('footer-container').innerHTML = footerHTML;
        } catch (error) {
            console.error('Error loading footer:', error);
        }
    }
    
    async loadPage(pageName, params = {}) {
        try {
            const pagePath = this.pages[pageName];
            if (!pagePath) {
                throw new Error(`Page ${pageName} not found`);
            }
            
            // Build URL with parameters
            let url = pagePath;
            if (Object.keys(params).length > 0) {
                const queryString = new URLSearchParams(params).toString();
                url += `?${queryString}`;
            }
            
            const response = await fetch(url);
            const pageHTML = await response.text();
            document.getElementById('page-content').innerHTML = pageHTML;
            this.currentPage = pageName;
            this.updateActiveNavigation();
            this.setupHeaderNavigation();
            
            // Reset homeListenersAdded flag if loading home page
            if (pageName === 'home') {
                window.homeListenersAdded = false;
            }
            
            // Load page-specific scripts and initialize functionality
            this.loadPageScripts(pageName);
            
            // Update browser history
            const historyParams = { page: pageName, ...params };
            history.pushState(historyParams, '', this.buildURL(pageName, params));
        } catch (error) {
            console.error('Error loading page:', error);
            document.getElementById('page-content').innerHTML = `
                <h1>Page Not Found</h1>
                <p>The requested page could not be loaded.</p>
            `;
        }
    }
    
    loadPageScripts(pageName) {
        // Load page-specific scripts
        switch(pageName) {
            case 'home':
                this.loadHomeScripts();
                break;
            case 'item':
                this.loadItemScripts();
                break;
            case 'products':
                this.loadProductsScripts();
                break;
            case 'cart':
                this.initializeCartPage();
                break;
            case 'checkout':
                this.initializeCheckoutPage();
                break;
        }
    }
    
    loadHomeScripts() {
        // Load home.js if not already loaded
        if (!window.homeScriptsLoaded) {
            const script = document.createElement('script');
            script.src = 'assets/js/home.js';
            script.onload = () => {
                window.homeScriptsLoaded = true;
                this.initializeHomePage();
            };
            document.head.appendChild(script);
        } else {
            this.initializeHomePage();
        }
    }
    
    loadItemScripts() {
        // Initialize item page directly since we don't need external scripts
        this.initializeItemPage();
    }
    
    loadProductsScripts() {
        // Initialize products page functionality
        this.initializeProductsPage();
    }
    
    initializeHomePage() {
        // Prevent duplicate event listeners
        if (window.homeListenersAdded) return;
        window.homeListenersAdded = true;
        // Initialize banner rotation
        let currentBanner = 0;
        const banners = document.querySelectorAll('.hero-banner-image');
        if (banners.length > 0) {
            setInterval(() => {
                banners[currentBanner].classList.remove('active');
                currentBanner = (currentBanner + 1) % banners.length;
                banners[currentBanner].classList.add('active');
            }, 2000);
        }
        
        // Initialize cart count
        this.updateCartCount();
        
        // Add event listeners for quantity buttons
        const quantityBtns = document.querySelectorAll('.quantity-btn');
        quantityBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const productId = btn.getAttribute('data-product-id');
                const change = parseInt(btn.getAttribute('data-change'));
                this.changeHomeQuantity(productId, change);
            });
        });
        
        // Add event listeners for add to cart buttons
        const addToCartBtns = document.querySelectorAll('.quick-add-btn');
        addToCartBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const productId = btn.getAttribute('data-product-id');
                this.quickAddToCart(productId);
            });
        });
        
        // Add event listeners for product cards
        const productCards = document.querySelectorAll('.product-card');
        productCards.forEach(card => {
            card.addEventListener('click', (e) => {
                if (!e.target.closest('.quick-add-section')) {
                    const productId = card.getAttribute('data-product-id');
                    this.openProduct(productId);
                }
            });
        });
    }
    
    initializeItemPage() {
        console.log('Initializing item page...');
        
        // Initialize cart count
        this.updateCartCount();
        
        // Add event listeners for quantity buttons
        const quantityBtns = document.querySelectorAll('.quantity-btn');
        console.log('Found quantity buttons:', quantityBtns.length);
        
        quantityBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const change = parseInt(btn.getAttribute('data-change'));
                console.log('Quantity button clicked, change:', change);
                this.changeItemQuantity(change);
            });
        });
        
        // Add event listener for add to cart button
        const addToCartBtn = document.querySelector('.add-to-cart-btn');
        console.log('Found add to cart button:', addToCartBtn);
        
        if (addToCartBtn) {
            addToCartBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const productId = addToCartBtn.getAttribute('data-product-id');
                console.log('Add to cart clicked, product ID:', productId);
                this.addToCartFromItem(productId);
            });
        }
        
        // Add event listener for back button
        const backBtn = document.querySelector('.back-btn');
        console.log('Found back button:', backBtn);
        
        if (backBtn) {
            backBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                console.log('Back button clicked');
                // Clear URL parameters and navigate to home page
                window.history.pushState({}, '', 'index.php?page=products');
                this.loadPage('products');
            });
        }
    }
    
    initializeProductsPage() {
        console.log('Initializing products page...');
        
        // Initialize cart count
        this.updateCartCount();
        
        // Handle search form submission
        const searchForm = document.getElementById('search-form');
        if (searchForm) {
            searchForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const searchInput = document.getElementById('search-input');
                const searchTerm = searchInput ? searchInput.value.trim() : '';
                
                // Build URL with search parameter
                const url = new URL(window.location);
                url.searchParams.set('page', 'products');
                if (searchTerm) {
                    url.searchParams.set('search', searchTerm);
                } else {
                    url.searchParams.delete('search');
                }
                
                // Navigate to the search results
                window.history.pushState({}, '', url.toString());
                window.simpleFramework.loadPage('products', searchTerm ? { search: searchTerm } : {});
            });
        }
        
        // Handle clear search button
        const clearSearchBtn = document.getElementById('clear-search-btn');
        if (clearSearchBtn) {
            clearSearchBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const url = new URL(window.location);
                url.searchParams.set('page', 'products');
                url.searchParams.delete('search');
                
                window.history.pushState({}, '', url.toString());
                window.simpleFramework.loadPage('products');
            });
        }
        
        // Handle view details button clicks
        const viewDetailsBtns = document.querySelectorAll('.view-details-btn');
        viewDetailsBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const productId = this.getAttribute('data-product-id');
                if (productId && window.simpleFramework) {
                    window.simpleFramework.loadPage('item', { id: productId });
                }
            });
        });
        
        // Handle add to cart button clicks
        const addToCartBtns = document.querySelectorAll('.add-to-cart');
        console.log('Found add to cart buttons:', addToCartBtns.length);
        addToCartBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const productId = this.getAttribute('data-product-id');
                console.log('Add to cart button clicked, productId:', productId);
                if (productId && window.simpleFramework) {
                    window.simpleFramework.quickAddToCart(productId);
                }
            });
        });
    }
    
    initializeCartPage() {
        // Initialize cart page functionality
        this.updateCartCount();
        
        // Add event listeners for cart quantity buttons
        const cartQuantityBtns = document.querySelectorAll('.cart-item .quantity-btn');
        cartQuantityBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const cartId = btn.getAttribute('data-cart-id');
                const change = parseInt(btn.getAttribute('data-change'));
                this.changeCartQuantity(cartId, change);
            });
        });
        
        // Add event listeners for remove buttons
        const removeBtns = document.querySelectorAll('.remove-item-btn');
        removeBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const cartId = btn.getAttribute('data-cart-id');
                this.removeFromCart(cartId);
            });
        });
        
        // Add event listeners for cart item clicks (to go to product page)
        const cartItems = document.querySelectorAll('.cart-item');
        cartItems.forEach(item => {
            item.addEventListener('click', (e) => {
                if (!e.target.closest('.quantity-controls') && !e.target.closest('.cart-item-actions')) {
                    const productId = item.getAttribute('data-product-id');
                    this.openProduct(productId);
                }
            });
        });
    }
    
    initializeCheckoutPage() {
        console.log('Initializing checkout page...');
        
        // Handle billing address checkbox
        const sameAsShippingCheckbox = document.getElementById('same_as_shipping');
        const billingAddressDiv = document.getElementById('billing-address');
        
        if (sameAsShippingCheckbox && billingAddressDiv) {
            sameAsShippingCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    billingAddressDiv.style.display = 'none';
                    // Clear billing address fields
                    const billingFields = billingAddressDiv.querySelectorAll('input');
                    billingFields.forEach(field => field.value = '');
                } else {
                    billingAddressDiv.style.display = 'block';
                }
            });
        }
        
        // Handle card number formatting
        const cardNumberInput = document.getElementById('card_number');
        if (cardNumberInput) {
            cardNumberInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
                let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
                e.target.value = formattedValue;
            });
        }
        
        // Handle expiry date formatting
        const expiryInput = document.getElementById('expiry_date');
        if (expiryInput) {
            expiryInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length >= 2) {
                    value = value.substring(0, 2) + '/' + value.substring(2, 4);
                }
                e.target.value = value;
            });
        }
        
        // Handle CVV formatting
        const cvvInput = document.getElementById('cvv');
        if (cvvInput) {
            cvvInput.addEventListener('input', function(e) {
                e.target.value = e.target.value.replace(/[^0-9]/g, '');
            });
        }
        
        // Handle checkout form submission
        const checkoutForm = document.getElementById('checkout-form');
        if (checkoutForm) {
            checkoutForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.processCheckout();
            });
        }
    }
    
    // Home page functions
    openProduct(productId) {
        this.loadPage('item', { id: productId });
    }
    
    changeHomeQuantity(productId, change) {
        const quantityElement = document.getElementById(`quantity-${productId}`);
        if (quantityElement) {
            let currentQuantity = parseInt(quantityElement.textContent);
            currentQuantity = Math.max(1, Math.min(99, currentQuantity + change));
            quantityElement.textContent = currentQuantity;
        }
    }
    
    quickAddToCart(productId) {
        console.log('quickAddToCart called with productId:', productId);
        const quantityElement = document.getElementById(`quantity-${productId}`);
        const quantity = quantityElement ? parseInt(quantityElement.textContent) : 1;
        console.log('Using quantity:', quantity);
        
        // Add to cart via AJAX
        fetch('pages/cart_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=add&product_id=${productId}&quantity=${quantity}`
        })
        .then(response => {
            if (response.status === 401) {
                // User not logged in
                const shouldLogin = confirm('You need to be logged in to add items to your cart. Would you like to log in now?');
                if (shouldLogin) {
                    this.openAuthModal();
                }
                return null;
            }
            return response.json();
        })
        .then(data => {
            if (data === null) return; // User chose not to log in
            
            if (data.success) {
                // Show success message
                alert(`${quantity} item(s) added to cart!`);
                
                // Reset quantity to 1 if quantity element exists
                if (quantityElement) {
                    quantityElement.textContent = '1';
                }
                
                // Update cart count
                this.updateCartCount();
            } else {
                alert(data.error || 'Failed to add item to cart');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to add item to cart. Please try again.');
        });
    }
    
    updateCartCount() {
        // Get cart count via AJAX
        fetch('pages/cart_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=get_count'
        })
        .then(response => {
            if (response.status === 401) {
                // User not logged in, show 0
                const cartElement = document.querySelector('.header-actions span');
                if (cartElement) {
                    cartElement.textContent = `🛒 Cart (0)`;
                }
                return null;
            }
            return response.json();
        })
        .then(data => {
            if (data !== null) {
                const cartElement = document.querySelector('.header-actions span');
                if (cartElement) {
                    cartElement.textContent = `🛒 Cart (${data.count})`;
                }
            }
        })
        .catch(error => {
            console.error('Error updating cart count:', error);
            // Fallback to 0 if error
            const cartElement = document.querySelector('.header-actions span');
            if (cartElement) {
                cartElement.textContent = `🛒 Cart (0)`;
            }
        });
    }
    
    // Item page functions
    changeItemQuantity(change) {
        console.log('changeItemQuantity called with change:', change);
        const input = document.getElementById('quantity');
        console.log('Found quantity input:', input);
        
        if (input) {
            let value = parseInt(input.value) + change;
            value = Math.max(1, Math.min(99, value));
            console.log('New quantity value:', value);
            input.value = value;
        } else {
            console.error('Quantity input not found!');
        }
    }
    
    addToCartFromItem(productId) {
        const quantityInput = document.getElementById('quantity');
        if (!quantityInput) return;
        
        const quantity = parseInt(quantityInput.value);
        
        // Add to cart via AJAX
        fetch('pages/cart_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=add&product_id=${productId}&quantity=${quantity}`
        })
        .then(response => {
            if (response.status === 401) {
                // User not logged in
                const shouldLogin = confirm('You need to be logged in to add items to your cart. Would you like to log in now?');
                if (shouldLogin) {
                    this.openAuthModal();
                }
                return null;
            }
            return response.json();
        })
        .then(data => {
            if (data === null) return; // User chose not to log in
            
            if (data.success) {
                // Show success message
                alert(`${quantity} item(s) added to cart!`);
                
                // Update cart count
                this.updateCartCount();
            } else {
                alert(data.error || 'Failed to add item to cart');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to add item to cart. Please try again.');
        });
    }
    
    changeCartQuantity(cartId, change) {
        const quantityElement = document.getElementById(`cart-quantity-${cartId}`);
        if (!quantityElement) return;
        
        let currentQuantity = parseInt(quantityElement.textContent);
        let newQuantity = currentQuantity + change;
        
        // Ensure quantity is between 1 and 99
        newQuantity = Math.max(1, Math.min(99, newQuantity));
        
        if (newQuantity === currentQuantity) return;
        
        // Update cart via AJAX
        fetch('pages/cart_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=update&cart_id=${cartId}&quantity=${newQuantity}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the display
                quantityElement.textContent = newQuantity;
                
                // Reload cart page to update totals
                this.loadPage('cart');
            } else {
                alert(data.error || 'Failed to update cart');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to update cart');
        });
    }
    
    removeFromCart(cartId) {
        if (!confirm('Are you sure you want to remove this item from your cart?')) {
            return;
        }
        
        // Remove from cart via AJAX
        fetch('pages/cart_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=remove&cart_id=${cartId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload cart page
                this.loadPage('cart');
                
                // Update cart count
                this.updateCartCount();
            } else {
                alert(data.error || 'Failed to remove item from cart');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to remove item from cart');
        });
    }
    
    buildURL(pageName, params = {}) {
        const url = new URL(window.location);
        url.searchParams.set('page', pageName);
        
        // Add additional parameters
        Object.keys(params).forEach(key => {
            url.searchParams.set(key, params[key]);
        });
        
        return url.toString();
    }
    
    setupNavigation() {
        // Handle browser back/forward buttons
        window.addEventListener('popstate', (event) => {
            if (event.state && event.state.page) {
                const params = { ...event.state };
                delete params.page;
                this.loadPage(event.state.page, params);
            }
        });
        
        // Handle direct URL access
        const urlParams = new URLSearchParams(window.location.search);
        const page = urlParams.get('page') || 'home';
        const id = urlParams.get('id');
        const search = urlParams.get('search');
        const order_id = urlParams.get('order_id');
        
        if (page === 'item' && id) {
            this.loadPage('item', { id: id });
        } else if (page === 'products' && search) {
            this.loadPage('products', { search: search });
        } else if (page === 'order_confirmation' && order_id) {
            this.loadPage('order_confirmation', { order_id: order_id });
        } else {
            this.loadPage(page);
        }
    }
    
    setupSidebarNavigation() {
        const sidebarLinks = document.querySelectorAll('.sidebar-nav a');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const pageName = link.getAttribute('data-page');
                if (pageName) {
                    this.loadPage(pageName);
                }
            });
        });
    }
    
    updateActiveNavigation() {
        const sidebarLinks = document.querySelectorAll('.sidebar-nav a');
        sidebarLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('data-page') === this.currentPage) {
                link.classList.add('active');
            }
        });


    }
    
    setupHeaderNavigation() {
        const storeName = document.querySelector('.store-name');
        if (storeName) {
            storeName.addEventListener('click', (e) => {
                e.preventDefault();
                this.loadPage('home');
            });
        }
        
        // Setup cart link functionality
        const cartLink = document.querySelector('.header-actions span:first-child');
        if (cartLink && cartLink.textContent.includes('Cart')) {
            cartLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.loadPage('cart');
            });
        }
        
        // Setup profile logo functionality
        const profileLogo = document.querySelector('.header-actions span:last-child');
        if (profileLogo && profileLogo.textContent.includes('Profile')) {
            profileLogo.addEventListener('click', (e) => {
                e.preventDefault();
                this.loadPage('profile');
            });
        }
        
        // Setup authentication modal functionality
        this.setupAuthModal();
    }
    
    setupAuthModal() {
        // Make functions globally available
        window.openAuthModal = () => {
            const modal = document.getElementById('auth-modal');
            if (modal) {
                modal.style.display = 'block';
                this.showLoginForm();
            }
        };
        
        window.closeAuthModal = () => {
            const modal = document.getElementById('auth-modal');
            if (modal) {
                modal.style.display = 'none';
            }
        };
        
        window.showLoginForm = () => {
            const loginForm = document.getElementById('login-form');
            const signupForm = document.getElementById('signup-form');
            if (loginForm && signupForm) {
                loginForm.style.display = 'block';
                signupForm.style.display = 'none';
            }
        };
        
        window.showSignupForm = () => {
            const loginForm = document.getElementById('login-form');
            const signupForm = document.getElementById('signup-form');
            if (loginForm && signupForm) {
                loginForm.style.display = 'none';
                signupForm.style.display = 'block';
            }
        };
        
        // Close modal when clicking outside
        window.onclick = (event) => {
            const modal = document.getElementById('auth-modal');
            if (event.target == modal) {
                this.closeAuthModal();
            }
        };
        
        // Setup form handlers
        this.setupAuthForms();
    }
    
    setupAuthForms() {
        // Login Form Handler
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const formData = new FormData(loginForm);
                this.handleLogin(formData);
            });
        }
        
        // Signup Form Handler
        const signupForm = document.getElementById('signupForm');
        if (signupForm) {
            signupForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const formData = new FormData(signupForm);
                formData.append('action', 'signup');
                this.handleSignup(formData);
            });
        }
        
        // Profile Update Handler
        const profileForm = document.getElementById('profile-form');
        if (profileForm) {
            profileForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const formData = new FormData(profileForm);
                formData.append('action', 'update_profile');
                this.handleProfileUpdate(formData);
            });
        }
    }
    
    handleLogin(formData) {
        formData.append('action', 'login');
        fetch('pages/auth_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Login response status:', response.status);
            return response.text().then(text => {
                console.log('Login response text:', text);
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Login JSON parse error:', e);
                    throw new Error('Invalid JSON response: ' + text);
                }
            });
        })
        .then(data => {
            console.log('Login parsed data:', data);
            if (data.success) {
                window.closeAuthModal();
                location.reload();
            } else {
                alert(data.message || 'Login failed');
            }
        })
        .catch(error => {
            console.error('Login error:', error);
            alert('An error occurred during login: ' + error.message);
        });
    }
    
    handleSignup(formData) {
        fetch('pages/auth_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response ok:', response.ok);
            return response.text().then(text => {
                console.log('Response text:', text);
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('JSON parse error:', e);
                    throw new Error('Invalid JSON response: ' + text);
                }
            });
        })
        .then(data => {
            console.log('Parsed data:', data);
            if (data.success) {
                alert('Account created successfully! Please sign in.');
                window.showLoginForm();
                // Pre-fill email field
                const signupEmail = document.getElementById('signup-email');
                const loginEmail = document.getElementById('login-email');
                if (signupEmail && loginEmail) {
                    loginEmail.value = signupEmail.value;
                }
            } else {
                alert(data.message || 'Signup failed');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred during signup: ' + error.message);
        });
    }
    
    handleProfileUpdate(formData) {
        fetch('pages/auth_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Profile updated successfully!');
            } else {
                alert(data.message || 'Update failed');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating profile');
        });
    }
    
    logout() {
        fetch('pages/auth_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=logout'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred during logout');
        });
    }
    
    openAuthModal() {
        const modal = document.getElementById('auth-modal');
        if (modal) {
            modal.style.display = 'block';
            this.showLoginForm();
        }
    }
    
    showLoginForm() {
        const loginForm = document.getElementById('login-form');
        const signupForm = document.getElementById('signup-form');
        if (loginForm && signupForm) {
            loginForm.style.display = 'block';
            signupForm.style.display = 'none';
        }
    }
    
    processCheckout() {
        console.log('Processing checkout...');
        
        // Get form data
        const formData = new FormData(document.getElementById('checkout-form'));
        formData.append('action', 'process_order');
        
        // Disable submit button to prevent double submission
        const submitBtn = document.querySelector('.checkout-submit');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';
        }
        
        // Process checkout via AJAX
        fetch('pages/checkout_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Redirect to order confirmation page
                window.location.href = `index.php?page=order_confirmation&order_id=${data.order_id}`;
            } else {
                alert(data.error || 'Failed to process order');
            }
        })
        .catch(error => {
            console.error('Checkout error:', error);
            alert('Failed to process order. Please try again.');
        })
        .finally(() => {
            // Re-enable submit button
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Complete Order';
            }
        });
    }
}

// Order History Functions
window.viewOrderDetails = function(orderId) {
    console.log('viewOrderDetails called with orderId:', orderId);
    
    try {
        // Check if modal elements exist
        const modal = document.getElementById('order-details-modal');
        const body = document.getElementById('order-details-body');
        
        console.log('Modal element:', modal);
        console.log('Body element:', body);
        
        if (!modal) {
            alert('Modal element not found!');
            return;
        }
        
        if (!body) {
            alert('Modal body element not found!');
            return;
        }
        
        // Show loading state
        body.innerHTML = '<div style="text-align: center; padding: 20px;">Loading order details...</div>';
        modal.style.display = 'block';
        
        console.log('Modal should now be visible');
    } catch (error) {
        console.error('Error in viewOrderDetails:', error);
        alert('Error: ' + error.message);
        return;
    }
    
    // Fetch order details via AJAX
    fetch('pages/order_details_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'order_id=' + orderId
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            document.getElementById('order-details-body').innerHTML = data.html;
        } else {
            document.getElementById('order-details-body').innerHTML = '<div style="text-align: center; padding: 20px; color: red;">Error: ' + data.error + '</div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('order-details-body').innerHTML = '<div style="text-align: center; padding: 20px; color: red;">Error loading order details. Check console for details.</div>';
    });
}

window.closeOrderDetails = function() {
    document.getElementById('order-details-modal').style.display = 'none';
}

window.reorderItems = function(orderId) {
    if (confirm('Add all items from this order to your cart?')) {
        fetch('pages/reorder_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'order_id=' + orderId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Items added to cart successfully!');
                // Optionally redirect to cart
                window.location.href = 'index.php?page=cart';
            } else {
                alert('Error adding items to cart: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error adding items to cart');
        });
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('order-details-modal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}

// Initialize the framework and make it globally accessible
const framework = new SimpleFramework();
window.simpleFramework = framework; 