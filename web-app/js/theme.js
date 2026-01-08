/**
 * Service de Thème - Application de Pointage RH
 * Gère le chargement et l'application dynamique du thème de l'entreprise
 */

const ThemeService = {
    // Configuration par défaut
    defaultConfig: {
        nom: 'Mon Entreprise',
        logo_url: null,
        couleur_primaire: '#1a73e8',
        couleur_secondaire: '#4285f4',
        couleur_accent: '#34a853',
        couleur_texte: '#333333'
    },

    // Configuration courante
    currentConfig: null,

    /**
     * Initialiser le service de thème
     */
    async init() {
        await this.loadTheme();
    },

    /**
     * Charger et appliquer le thème depuis l'API
     */
    async loadTheme() {
        try {
            const response = await fetch(CONFIG.API_URL + '/entreprise');
            const data = await response.json();

            if (data.success && data.data) {
                this.currentConfig = data.data;
                this.applyTheme(data.data);
                this.updateBranding(data.data);
            } else {
                this.applyTheme(this.defaultConfig);
            }
        } catch (error) {
            console.log('Utilisation du thème par défaut:', error.message);
            this.applyTheme(this.defaultConfig);
        }
    },

    /**
     * Appliquer les couleurs CSS dynamiquement
     */
    applyTheme(config) {
        const root = document.documentElement;

        // Appliquer les variables CSS
        if (config.couleur_primaire) {
            root.style.setProperty('--theme-primary', config.couleur_primaire);
            root.style.setProperty('--primary', config.couleur_primaire);
        }
        if (config.couleur_secondaire) {
            root.style.setProperty('--theme-secondary', config.couleur_secondaire);
            root.style.setProperty('--secondary', config.couleur_secondaire);
        }
        if (config.couleur_accent) {
            root.style.setProperty('--theme-accent', config.couleur_accent);
        }
        if (config.couleur_texte) {
            root.style.setProperty('--theme-text', config.couleur_texte);
        }

        // Appliquer directement sur les éléments clés pour compatibilité
        this.applyDirectStyles(config);
    },

    /**
     * Appliquer les styles directement sur les éléments (fallback)
     */
    applyDirectStyles(config) {
        // Sidebar
        const sidebar = document.querySelector('.sidebar');
        if (sidebar && config.couleur_primaire) {
            sidebar.style.background = config.couleur_primaire;
        }

        // Nav items actifs
        const activeNavItems = document.querySelectorAll('.nav-item.active');
        activeNavItems.forEach(item => {
            if (config.couleur_accent) {
                item.style.background = config.couleur_accent;
            }
        });

        // Boutons primaires
        const primaryBtns = document.querySelectorAll('.btn-primary');
        primaryBtns.forEach(btn => {
            if (config.couleur_primaire) {
                btn.style.background = config.couleur_primaire;
            }
        });

        // Tabs actifs
        const activeTabs = document.querySelectorAll('.tab.active');
        activeTabs.forEach(tab => {
            if (config.couleur_primaire) {
                tab.style.background = config.couleur_primaire;
            }
        });

        // Login container
        const loginContainer = document.querySelector('.login-container');
        if (loginContainer && config.couleur_primaire) {
            loginContainer.style.background = config.couleur_primaire;
        }

        // Spinner
        const spinner = document.querySelector('.spinner');
        if (spinner && config.couleur_primaire) {
            spinner.style.borderTopColor = config.couleur_primaire;
        }
    },

    /**
     * Mettre à jour le logo et le nom de l'entreprise
     */
    updateBranding(config) {
        // Mettre à jour tous les éléments de logo
        const logoElements = document.querySelectorAll('.company-logo');
        logoElements.forEach(el => {
            if (config.logo_url) {
                el.src = config.logo_url;
                el.style.display = 'block';
            } else {
                el.style.display = 'none';
            }
        });

        // Mettre à jour tous les éléments de nom
        const nameElements = document.querySelectorAll('.company-name');
        nameElements.forEach(el => {
            el.textContent = config.nom || 'Mon Entreprise';
        });

        // Mettre à jour le titre de la page
        if (config.nom) {
            const pageTitle = document.querySelector('.page-title');
            // Ne pas remplacer le titre de page, juste mettre à jour si nécessaire
        }
    },

    /**
     * Obtenir la configuration courante
     */
    getConfig() {
        return this.currentConfig || this.defaultConfig;
    },

    /**
     * Prévisualiser un thème sans le sauvegarder
     */
    previewTheme(config) {
        this.applyTheme(config);
        this.updateBranding(config);
    },

    /**
     * Réinitialiser au thème par défaut
     */
    resetToDefault() {
        this.applyTheme(this.defaultConfig);
    },

    /**
     * Convertir une couleur hex en RGB
     */
    hexToRgb(hex) {
        const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? {
            r: parseInt(result[1], 16),
            g: parseInt(result[2], 16),
            b: parseInt(result[3], 16)
        } : null;
    },

    /**
     * Calculer si une couleur est claire ou foncée
     */
    isLightColor(hex) {
        const rgb = this.hexToRgb(hex);
        if (!rgb) return true;
        // Formule de luminosité relative
        const luminance = (0.299 * rgb.r + 0.587 * rgb.g + 0.114 * rgb.b) / 255;
        return luminance > 0.5;
    }
};

// Charger le thème automatiquement au chargement de la page
document.addEventListener('DOMContentLoaded', () => {
    // Attendre que CONFIG soit défini
    if (typeof CONFIG !== 'undefined' && CONFIG.API_URL) {
        ThemeService.init();
    }
});
