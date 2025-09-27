// Footer functionality
class FooterManager {
    constructor() {
        this.footer = null;
        this.init();
    }
    
    init() {
        this.setupFooterLinks();
        this.updateCopyrightYear();
    }
    
    setupFooterLinks() {
        // Add event listeners to footer links when footer is loaded
        document.addEventListener('click', (e) => {
            if (e.target.closest('.footer-links a')) {
                e.preventDefault();
                const link = e.target.closest('.footer-links a');
                const href = link.getAttribute('href');
                
                if (href.startsWith('#')) {
                    // Handle internal links
                    this.handleInternalLink(href);
                } else if (href.startsWith('mailto:')) {
                    // Handle email links
                    window.location.href = href;
                } else {
                    // Handle external links
                    window.open(href, '_blank');
                }
            }
        });
    }
    
    handleInternalLink(href) {
        const targetId = href.substring(1);
        const targetElement = document.getElementById(targetId);
        
        if (targetElement) {
            targetElement.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    }
    
    updateCopyrightYear() {
        const yearElements = document.querySelectorAll('.copyright-year');
        const currentYear = new Date().getFullYear();
        
        yearElements.forEach(element => {
            element.textContent = currentYear;
        });
    }
}

// Initialize footer manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.footerManager = new FooterManager();
}); 