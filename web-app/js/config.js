/**
 * Configuration de l'application Pointage RH
 */

const CONFIG = {
    // URL de l'API
    API_URL: 'http://localhost:8000/api',

    // Nom de l'application
    APP_NAME: 'Pointage RH',

    // Version
    VERSION: '1.0.0',

    // Cles de stockage local
    STORAGE_KEYS: {
        TOKEN: 'auth_token',
        USER: 'user',
        REMEMBER: 'remember_me'
    },

    // Duree de validite du token (en heures)
    TOKEN_EXPIRY: 24,

    // Roles autorises pour l'interface web
    ALLOWED_ROLES: ['rh', 'directeur'],

    // Pagination par defaut
    DEFAULT_PAGE_SIZE: 10,

    // Format de date
    DATE_FORMAT: 'DD/MM/YYYY',
    TIME_FORMAT: 'HH:mm',
    DATETIME_FORMAT: 'DD/MM/YYYY HH:mm'
};

// Empecher la modification de la configuration
Object.freeze(CONFIG);
Object.freeze(CONFIG.STORAGE_KEYS);
