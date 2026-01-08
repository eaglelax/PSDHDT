/**
 * Sidebar Component - Application de Pointage RH
 * Genere dynamiquement le sidebar avec icones SVG uniformes
 */

const SidebarComponent = {
    // Icones SVG
    icons: {
        dashboard: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>',
        users: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>',
        clock: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>',
        chart: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>',
        file: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>',
        settings: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>',
        logout: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>',
        menu: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>'
    },

    // Menu items
    menuItems: [
        { href: 'dashboard.html', icon: 'dashboard', text: 'Dashboard' },
        { href: 'employes.html', icon: 'users', text: 'Employes' },
        { href: 'pointages.html', icon: 'clock', text: 'Pointages' },
        { href: 'statistiques.html', icon: 'chart', text: 'Statistiques' },
        { href: 'bulletins.html', icon: 'file', text: 'Bulletins' }
    ],

    /**
     * Initialiser le sidebar
     */
    init(activePage) {
        this.renderSidebar(activePage);
        this.setupMenuToggle();
        this.showAdminLink();
    },

    /**
     * Generer le sidebar HTML
     */
    renderSidebar(activePage) {
        const sidebar = document.getElementById('sidebar');
        if (!sidebar) return;

        const menuHtml = this.menuItems.map(item => `
            <a href="${item.href}" class="nav-item${activePage === item.href ? ' active' : ''}">
                <span class="nav-icon">${this.icons[item.icon]}</span>
                <span class="nav-text">${item.text}</span>
            </a>
        `).join('');

        sidebar.innerHTML = `
            <div class="sidebar-header">
                <img src="" alt="Logo" class="company-logo" style="height: 40px; margin-bottom: 8px; display: none;">
                <h2 class="company-name">Pointage RH</h2>
            </div>

            <nav class="sidebar-nav">
                ${menuHtml}
                <div style="margin: 16px 12px; border-top: 1px solid rgba(255,255,255,0.1);"></div>
                <a href="admin-entreprise.html" class="nav-item${activePage === 'admin-entreprise.html' ? ' active' : ''}" id="nav-admin" style="display: none;">
                    <span class="nav-icon">${this.icons.settings}</span>
                    <span class="nav-text">Administration</span>
                </a>
            </nav>

            <div class="sidebar-footer">
                <a href="#" class="nav-item" onclick="logout(); return false;">
                    <span class="nav-icon">${this.icons.logout}</span>
                    <span class="nav-text">Deconnexion</span>
                </a>
            </div>
        `;
    },

    /**
     * Setup menu toggle button
     */
    setupMenuToggle() {
        const toggleBtn = document.querySelector('.menu-toggle');
        if (toggleBtn) {
            toggleBtn.innerHTML = this.icons.menu;
        }
    },

    /**
     * Afficher le lien admin si l'utilisateur a les droits
     */
    showAdminLink() {
        const user = getCurrentUser ? getCurrentUser() : null;
        if (user && ['directeur', 'rh'].includes(user.role)) {
            const adminLink = document.getElementById('nav-admin');
            if (adminLink) {
                adminLink.style.display = 'flex';
            }
        }
    },

    /**
     * Obtenir l'icone du menu
     */
    getMenuIcon() {
        return this.icons.menu;
    }
};

// Exporter pour utilisation globale
window.SidebarComponent = SidebarComponent;
