// Remove global navigation functions and use SPA framework methods

// Banner rotation functionality

document.addEventListener('DOMContentLoaded', function() {
    // Banner rotation
    let currentBanner = 0;
    const banners = document.querySelectorAll('.hero-banner-image');
    if (banners.length > 0) {
        setInterval(() => {
            banners[currentBanner].classList.remove('active');
            currentBanner = (currentBanner + 1) % banners.length;
            banners[currentBanner].classList.add('active');
        }, 2000);
    }
    // Removed product card click, quick add to cart, and quantity button event listeners to avoid duplicate handling.
}); 