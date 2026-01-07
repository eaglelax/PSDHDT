/**
 * Service API pour l'application Pointage RH
 */

const API = {
    /**
     * Effectue une requete API
     */
    async request(endpoint, method = 'GET', data = null) {
        const url = `${CONFIG.API_URL}${endpoint}`;
        const token = localStorage.getItem(CONFIG.STORAGE_KEYS.TOKEN);

        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        };

        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        const options = {
            method: method,
            headers: headers
        };

        if (data && (method === 'POST' || method === 'PUT' || method === 'PATCH')) {
            options.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(url, options);
            const result = await response.json();

            // Si token expire, rediriger vers login
            if (response.status === 401) {
                localStorage.removeItem(CONFIG.STORAGE_KEYS.TOKEN);
                localStorage.removeItem(CONFIG.STORAGE_KEYS.USER);
                if (window.location.pathname !== '/login.html') {
                    window.location.href = 'login.html';
                }
            }

            return result;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    // ==================== AUTHENTIFICATION ====================

    /**
     * Connexion utilisateur
     */
    async login(email, password) {
        return this.request('/auth/login', 'POST', { email, password });
    },

    /**
     * Deconnexion
     */
    async logout() {
        const result = await this.request('/auth/logout', 'POST');
        localStorage.removeItem(CONFIG.STORAGE_KEYS.TOKEN);
        localStorage.removeItem(CONFIG.STORAGE_KEYS.USER);
        return result;
    },

    /**
     * Obtenir le profil de l'utilisateur connecte
     */
    async getProfile() {
        return this.request('/auth/me');
    },

    /**
     * Rafraichir le token
     */
    async refreshToken() {
        return this.request('/auth/refresh', 'POST');
    },

    /**
     * Changer le mot de passe
     */
    async changePassword(currentPassword, newPassword, confirmPassword) {
        return this.request('/auth/change-password', 'POST', {
            current_password: currentPassword,
            new_password: newPassword,
            new_password_confirmation: confirmPassword
        });
    },

    // ==================== UTILISATEURS ====================

    /**
     * Liste des utilisateurs
     */
    async getUsers(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/users${queryString ? '?' + queryString : ''}`);
    },

    /**
     * Liste des employes
     */
    async getEmployes() {
        return this.request('/employes');
    },

    /**
     * Obtenir un utilisateur
     */
    async getUser(id) {
        return this.request(`/users/${id}`);
    },

    /**
     * Creer un utilisateur
     */
    async createUser(userData) {
        return this.request('/users', 'POST', userData);
    },

    /**
     * Modifier un utilisateur
     */
    async updateUser(id, userData) {
        return this.request(`/users/${id}`, 'PUT', userData);
    },

    /**
     * Supprimer un utilisateur
     */
    async deleteUser(id) {
        return this.request(`/users/${id}`, 'DELETE');
    },

    /**
     * Activer/Desactiver un utilisateur
     */
    async toggleUserActive(id) {
        return this.request(`/users/${id}/toggle-active`, 'POST');
    },

    // ==================== POINTAGES ====================

    /**
     * Liste de tous les pointages (admin)
     */
    async getPointages(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/pointages${queryString ? '?' + queryString : ''}`);
    },

    /**
     * Pointages d'un employe specifique
     */
    async getPointagesEmploye(userId, params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/pointages/employe/${userId}${queryString ? '?' + queryString : ''}`);
    },

    /**
     * Sessions de travail d'un employe
     */
    async getSessionsEmploye(userId, params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/pointages/sessions/${userId}${queryString ? '?' + queryString : ''}`);
    },

    // ==================== STATISTIQUES ====================

    /**
     * Dashboard (stats generales)
     */
    async getDashboard() {
        return this.request('/stats/dashboard');
    },

    /**
     * Stats de presence
     */
    async getStatsPresences(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/stats/presences${queryString ? '?' + queryString : ''}`);
    },

    /**
     * Stats des heures
     */
    async getStatsHeures(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/stats/heures${queryString ? '?' + queryString : ''}`);
    },

    /**
     * Stats des salaires
     */
    async getStatsSalaires(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/stats/salaires${queryString ? '?' + queryString : ''}`);
    },

    // ==================== BULLETINS DE PAIE ====================

    /**
     * Generer un bulletin de paie
     */
    async generateBulletin(userId, mois, annee) {
        return this.request('/bulletins/generate', 'POST', {
            user_id: userId,
            mois: mois,
            annee: annee
        });
    },

    /**
     * Generer tous les bulletins du mois
     */
    async generateAllBulletins(mois, annee) {
        return this.request('/bulletins/generate-all', 'POST', {
            mois: mois,
            annee: annee
        });
    },

    /**
     * Liste des bulletins
     */
    async getBulletins(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/bulletins${queryString ? '?' + queryString : ''}`);
    },

    /**
     * Obtenir un bulletin
     */
    async getBulletin(id) {
        return this.request(`/bulletins/${id}`);
    },

    // ==================== QR CODES ====================

    /**
     * QR Code actuel
     */
    async getCurrentQrCode() {
        return this.request('/qrcode/current');
    },

    /**
     * Historique des QR codes
     */
    async getQrCodeHistory() {
        return this.request('/qrcode/history');
    }
};

// ==================== HELPERS ====================

/**
 * Verifier si l'utilisateur est connecte
 */
function isAuthenticated() {
    return !!localStorage.getItem(CONFIG.STORAGE_KEYS.TOKEN);
}

/**
 * Obtenir l'utilisateur connecte
 */
function getCurrentUser() {
    const userStr = localStorage.getItem(CONFIG.STORAGE_KEYS.USER);
    return userStr ? JSON.parse(userStr) : null;
}

/**
 * Verifier le role de l'utilisateur
 */
function hasRole(role) {
    const user = getCurrentUser();
    return user && user.role === role;
}

/**
 * Verifier si admin (RH ou Directeur)
 */
function isAdmin() {
    const user = getCurrentUser();
    return user && CONFIG.ALLOWED_ROLES.includes(user.role);
}

/**
 * Formater une date
 */
function formatDate(dateStr) {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return date.toLocaleDateString('fr-FR');
}

/**
 * Formater une heure
 */
function formatTime(timeStr) {
    if (!timeStr) return '-';
    return timeStr.substring(0, 5);
}

/**
 * Formater une date et heure
 */
function formatDateTime(dateTimeStr) {
    if (!dateTimeStr) return '-';
    const date = new Date(dateTimeStr);
    return date.toLocaleDateString('fr-FR') + ' ' + date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
}

/**
 * Formater un montant en FCFA
 */
function formatMoney(amount) {
    if (amount === null || amount === undefined) return '-';
    return new Intl.NumberFormat('fr-FR').format(amount) + ' FCFA';
}

/**
 * Afficher une alerte
 */
function showAlert(containerId, message, type = 'info') {
    const container = document.getElementById(containerId);
    if (container) {
        container.innerHTML = `
            <div class="alert alert-${type}">
                ${message}
            </div>
        `;
    }
}

/**
 * Vider les alertes
 */
function clearAlerts(containerId) {
    const container = document.getElementById(containerId);
    if (container) {
        container.innerHTML = '';
    }
}

/**
 * Afficher le loading
 */
function showLoading() {
    const loading = document.getElementById('loading');
    if (loading) {
        loading.classList.add('active');
    }
}

/**
 * Cacher le loading
 */
function hideLoading() {
    const loading = document.getElementById('loading');
    if (loading) {
        loading.classList.remove('active');
    }
}

/**
 * Proteger une page (redirection si non connecte)
 */
function requireAuth() {
    if (!isAuthenticated()) {
        window.location.href = 'login.html';
        return false;
    }

    // Verifier le role
    if (!isAdmin()) {
        localStorage.removeItem(CONFIG.STORAGE_KEYS.TOKEN);
        localStorage.removeItem(CONFIG.STORAGE_KEYS.USER);
        window.location.href = 'login.html';
        return false;
    }

    return true;
}
