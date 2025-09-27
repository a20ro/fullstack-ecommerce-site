// Sidebar functionality
class SidebarManager {
    constructor() {
        this.sidebar = null;
        this.toggleButton = null;
        this.isOpen = false;
        this.init();
    }
    
    init() {
        this.createToggleButton();
        this.setupEventListeners();
    }
    
    createToggleButton() {
        this.toggleButton = document.createElement('button');
        this.toggleButton.className = 'sidebar-toggle';
        this.toggleButton.innerHTML = '☰';
        this.toggleButton.setAttribute('aria-label', 'Toggle Sidebar');
        document.body.appendChild(this.toggleButton);
    }
    
    setupEventListeners() {
        this.toggleButton.addEventListener('click', () => {
            this.toggleSidebar();
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768) {
                if (!e.target.closest('#sidebar-container') && 
                    !e.target.closest('.sidebar-toggle') && 
                    this.isOpen) {
                    this.closeSidebar();
                }
            }
        });
        
        // Handle window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                this.closeSidebar();
            }
        });
    }
    
    toggleSidebar() {
        if (this.isOpen) {
            this.closeSidebar();
        } else {
            this.openSidebar();
        }
    }
    
    openSidebar() {
        const sidebar = document.getElementById('sidebar-container');
        if (sidebar) {
            sidebar.classList.add('open');
            this.isOpen = true;
            this.toggleButton.innerHTML = '✕';
        }
    }
    
    closeSidebar() {
        const sidebar = document.getElementById('sidebar-container');
        if (sidebar) {
            sidebar.classList.remove('open');
            this.isOpen = false;
            this.toggleButton.innerHTML = '☰';
        }
    }
}

// Initialize sidebar manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.sidebarManager = new SidebarManager();
}); 