// Configuration API
const API_BASE_URL = 'http://localhost:8000/api';

// Application principale
document.addEventListener('DOMContentLoaded', function() {
    console.log('Application Pointage RH initialisee');
});

// Fonctions utilitaires pour les appels API
async function apiRequest(endpoint, method = 'GET', data = null) {
    const config = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    };

    if (data) {
        config.body = JSON.stringify(data);
    }

    const token = localStorage.getItem('auth_token');
    if (token) {
        config.headers['Authorization'] = `Bearer ${token}`;
    }

    const response = await fetch(`${API_BASE_URL}${endpoint}`, config);
    return response.json();
}
