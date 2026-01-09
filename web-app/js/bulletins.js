/**
 * Bulletins de Paie - JavaScript
 */

// Verifier l'authentification
if (!requireAuth()) {}

let currentBulletin = null;

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    loadUserInfo();
    initFilters();
    loadEmployesList();
    loadBulletins();
    // Afficher le lien admin si l'utilisateur est directeur ou RH
    const user = getCurrentUser();
    if (user && ['directeur', 'rh'].includes(user.role)) {
        document.getElementById('nav-admin').style.display = 'flex';
    }
});

// Charger les infos utilisateur
function loadUserInfo() {
    const user = getCurrentUser();
    if (user) {
        document.getElementById('user-info').textContent = user.prenom + ' ' + user.nom + ' (' + user.role.toUpperCase() + ')';
    }
}

// Initialiser les filtres
function initFilters() {
    const now = new Date();
    const currentMonth = now.getMonth() + 1;
    const currentYear = now.getFullYear();

    // Generation
    document.getElementById('gen-mois').value = currentMonth;

    // Annees pour generation
    const selectGenAnnee = document.getElementById('gen-annee');
    for (let y = currentYear; y >= currentYear - 2; y--) {
        const option = document.createElement('option');
        option.value = y;
        option.textContent = y;
        selectGenAnnee.appendChild(option);
    }
    selectGenAnnee.value = currentYear;

    // Annees pour filtre
    const selectFilterAnnee = document.getElementById('filter-annee');
    for (let y = currentYear; y >= currentYear - 2; y--) {
        const option = document.createElement('option');
        option.value = y;
        option.textContent = y;
        selectFilterAnnee.appendChild(option);
    }
    selectFilterAnnee.value = currentYear;

    // Mois par defaut pour filtre
    document.getElementById('filter-mois').value = currentMonth;
}

// Charger la liste des employes
async function loadEmployesList() {
    try {
        const response = await API.getEmployes();
        if (response.success) {
            const selectGen = document.getElementById('gen-employe');
            const selectFilter = document.getElementById('filter-employe');
            const employes = response.data.data || response.data || [];

            employes.forEach(function(e) {
                const option1 = document.createElement('option');
                option1.value = e.id;
                option1.textContent = e.prenom + ' ' + e.nom;
                selectGen.appendChild(option1);

                const option2 = document.createElement('option');
                option2.value = e.id;
                option2.textContent = e.prenom + ' ' + e.nom;
                selectFilter.appendChild(option2);
            });
        }
    } catch (error) {
        console.error('Erreur chargement employes:', error);
    }
}

// Generer les bulletins
async function generateBulletins() {
    const mois = document.getElementById('gen-mois').value;
    const annee = document.getElementById('gen-annee').value;
    const employe = document.getElementById('gen-employe').value;

    showLoading();
    try {
        let response;
        if (employe) {
            response = await API.generateBulletin(employe, mois, annee);
        } else {
            response = await API.generateAllBulletins(mois, annee);
        }

        if (response.success) {
            const count = response.data.bulletins_generes || 1;
            showAlert('alert-container', count + ' bulletin(s) genere(s) avec succes', 'success');
            loadBulletins();
        } else {
            showAlert('alert-container', response.message || 'Erreur lors de la generation', 'danger');
        }
    } catch (error) {
        showAlert('alert-container', 'Erreur de connexion au serveur', 'danger');
    } finally {
        hideLoading();
    }
}

// Charger les bulletins
async function loadBulletins() {
    showLoading();

    const params = {};
    const mois = document.getElementById('filter-mois').value;
    const annee = document.getElementById('filter-annee').value;
    const employe = document.getElementById('filter-employe').value;

    if (mois) params.mois = mois;
    if (annee) params.annee = annee;
    if (employe) params.user_id = employe;

    try {
        const response = await API.getBulletins(params);

        if (response.success) {
            // Gerer le format pagine de Laravel
            const bulletins = response.data.data || response.data.bulletins || response.data || [];
            renderBulletins(bulletins);
            updateResume(bulletins);
        } else {
            showAlert('alert-container', response.message || 'Erreur', 'danger');
        }
    } catch (error) {
        showAlert('alert-container', 'Erreur de connexion au serveur', 'danger');
    } finally {
        hideLoading();
    }
}

// Afficher les bulletins
function renderBulletins(bulletins) {
    const tbody = document.getElementById('table-bulletins');

    if (!bulletins || bulletins.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">Aucun bulletin trouve</td></tr>';
        return;
    }

    const moisNoms = ['', 'Janvier', 'Fevrier', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'Decembre'];

    let html = '';
    bulletins.forEach(function(b) {
        html += '<tr>';
        html += '<td>' + (b.user ? b.user.prenom + ' ' + b.user.nom : 'N/A') + '</td>';
        html += '<td>' + moisNoms[b.mois] + ' ' + b.annee + '</td>';
        html += '<td>' + formatMoney(b.salaire_base) + '</td>';
        html += '<td>' + (b.heures_normales || 0) + 'h</td>';
        html += '<td>' + (b.heures_supplementaires || 0) + 'h</td>';
        html += '<td>' + formatMoney(b.prime_supplementaires || 0) + '</td>';
        html += '<td><strong>' + formatMoney(b.salaire_net) + '</strong></td>';
        html += '<td>';
        html += '<button class="btn btn-sm btn-primary" onclick="viewBulletin(' + b.id + ')" title="Voir">';
        html += '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
        html += '</button>';
        html += '</td>';
        html += '</tr>';
    });

    tbody.innerHTML = html;
}

// Mettre a jour le resume
function updateResume(bulletins) {
    const total = bulletins.length;
    let masse = 0;
    let primes = 0;
    let net = 0;

    bulletins.forEach(function(b) {
        masse += parseFloat(b.salaire_base || 0);
        primes += parseFloat(b.prime_supplementaires || 0);
        net += parseFloat(b.salaire_net || 0);
    });

    document.getElementById('resume-bulletins').textContent = total;
    document.getElementById('resume-masse').textContent = formatMoney(masse);
    document.getElementById('resume-primes').textContent = formatMoney(primes);
    document.getElementById('resume-net').textContent = formatMoney(net);
}

// Voir un bulletin
async function viewBulletin(id) {
    showLoading();
    try {
        const response = await API.getBulletin(id);

        if (response.success) {
            currentBulletin = response.data;
            renderBulletinDetail(response.data);
            document.getElementById('modal-bulletin').classList.add('active');
        } else {
            showAlert('alert-container', response.message || 'Erreur', 'danger');
        }
    } catch (error) {
        showAlert('alert-container', 'Erreur de connexion', 'danger');
    } finally {
        hideLoading();
    }
}

// Afficher le detail du bulletin
function renderBulletinDetail(b) {
    const moisNoms = ['', 'Janvier', 'Fevrier', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'Decembre'];

    // Calcul du salaire brut si non present
    const salaireBrut = b.salaire_brut || (parseFloat(b.salaire_base) + parseFloat(b.montant_heures_sup || 0) + parseFloat(b.primes || 0));
    const totalRetenues = b.total_retenues || (parseFloat(b.cotisation_cnss || 0) + parseFloat(b.montant_irg || 0) + parseFloat(b.deductions || 0));

    let html = '<div class="bulletin-print" id="bulletin-print">';
    html += '<div class="text-center mb-2">';
    html += '<h2 style="color: #4f46e5; margin-bottom: 5px;">BULLETIN DE PAIE</h2>';
    html += '<p style="font-size: 16px; color: #666;">' + moisNoms[b.mois] + ' ' + b.annee + '</p>';
    html += '</div>';

    html += '<div class="mb-2" style="background: #f8f9fa; padding: 15px; border-radius: 8px;">';
    html += '<h4 style="color: #333; margin-bottom: 10px;">Informations Employe</h4>';
    html += '<p><strong>Nom:</strong> ' + (b.user ? b.user.prenom + ' ' + b.user.nom : 'N/A') + '</p>';
    html += '<p><strong>Matricule:</strong> ' + (b.user ? b.user.matricule : 'N/A') + '</p>';
    html += '<p><strong>Email:</strong> ' + (b.user ? b.user.email : 'N/A') + '</p>';
    html += '</div>';

    html += '<hr style="border: none; border-top: 2px solid #4f46e5; margin: 15px 0;">';

    html += '<div class="mb-2">';
    html += '<h4 style="color: #333; margin-bottom: 10px;">Gains</h4>';
    html += '<table class="table" style="width: 100%; border-collapse: collapse;">';
    html += '<tr><td style="padding: 8px; border-bottom: 1px solid #ddd;">Salaire de base</td>';
    html += '<td class="text-right" style="padding: 8px; border-bottom: 1px solid #ddd; text-align: right;">' + formatMoney(b.salaire_base) + '</td></tr>';
    html += '<tr><td style="padding: 8px; border-bottom: 1px solid #ddd;">Heures normales (' + (b.total_heures_normales || 0) + 'h)</td>';
    html += '<td class="text-right" style="padding: 8px; border-bottom: 1px solid #ddd; text-align: right;">-</td></tr>';
    html += '<tr><td style="padding: 8px; border-bottom: 1px solid #ddd;">Heures supplementaires (' + (b.total_heures_sup || 0) + 'h x 1.5)</td>';
    html += '<td class="text-right" style="padding: 8px; border-bottom: 1px solid #ddd; text-align: right;">+' + formatMoney(b.montant_heures_sup || 0) + '</td></tr>';
    html += '<tr><td style="padding: 8px; border-bottom: 1px solid #ddd;">Primes</td>';
    html += '<td class="text-right" style="padding: 8px; border-bottom: 1px solid #ddd; text-align: right;">+' + formatMoney(b.primes || 0) + '</td></tr>';
    html += '<tr style="background: #e3f2fd;"><td style="padding: 10px;"><strong>SALAIRE BRUT</strong></td>';
    html += '<td class="text-right" style="padding: 10px; text-align: right;"><strong>' + formatMoney(salaireBrut) + '</strong></td></tr>';
    html += '</table></div>';

    html += '<div class="mb-2">';
    html += '<h4 style="color: #333; margin-bottom: 10px;">Retenues</h4>';
    html += '<table class="table" style="width: 100%; border-collapse: collapse;">';
    html += '<tr><td style="padding: 8px; border-bottom: 1px solid #ddd;">Cotisation CNSS (' + (b.taux_cnss || 3.5) + '%)</td>';
    html += '<td class="text-right" style="padding: 8px; border-bottom: 1px solid #ddd; text-align: right; color: #c62828;">-' + formatMoney(b.cotisation_cnss || 0) + '</td></tr>';
    html += '<tr><td style="padding: 8px; border-bottom: 1px solid #ddd;">IRG - Impot sur le Revenu (' + (b.taux_irg || 10) + '%)</td>';
    html += '<td class="text-right" style="padding: 8px; border-bottom: 1px solid #ddd; text-align: right; color: #c62828;">-' + formatMoney(b.montant_irg || 0) + '</td></tr>';
    html += '<tr><td style="padding: 8px; border-bottom: 1px solid #ddd;">Autres deductions</td>';
    html += '<td class="text-right" style="padding: 8px; border-bottom: 1px solid #ddd; text-align: right; color: #c62828;">-' + formatMoney(b.deductions || 0) + '</td></tr>';
    html += '<tr style="background: #ffebee;"><td style="padding: 10px;"><strong>TOTAL RETENUES</strong></td>';
    html += '<td class="text-right" style="padding: 10px; text-align: right; color: #c62828;"><strong>-' + formatMoney(totalRetenues) + '</strong></td></tr>';
    html += '</table></div>';

    html += '<hr style="border: none; border-top: 2px solid #4f46e5; margin: 15px 0;">';

    html += '<div class="mb-2">';
    html += '<table class="table" style="width: 100%; border-collapse: collapse;">';
    html += '<tr style="background: #e8f5e9;"><td style="padding: 15px;"><strong style="font-size: 1.1em;">NET A PAYER</strong></td>';
    html += '<td class="text-right" style="padding: 15px; text-align: right;"><strong style="font-size: 1.3em; color: #2e7d32;">' + formatMoney(b.salaire_net) + '</strong></td></tr>';
    html += '</table></div>';

    if (b.commentaires) {
        html += '<div class="mb-2" style="background: #fff3e0; padding: 10px; border-radius: 5px; margin-top: 10px;">';
        html += '<strong>Commentaires:</strong> ' + b.commentaires;
        html += '</div>';
    }

    html += '<div class="text-center text-muted mt-2" style="font-size: 11px; color: #999; margin-top: 20px;">';
    html += '<p>Document genere le ' + formatDateTime(b.created_at || new Date().toISOString()) + '</p>';
    html += '<p>Pointage RH - Gestion des Presences</p>';
    html += '</div></div>';

    document.getElementById('bulletin-detail').innerHTML = html;
}

// Imprimer le bulletin
function printBulletin() {
    const content = document.getElementById('bulletin-print').innerHTML;
    const printWindow = window.open('', '_blank');
    printWindow.document.write('<!DOCTYPE html><html><head><title>Bulletin de Paie</title>');
    printWindow.document.write('<style>');
    printWindow.document.write('body { font-family: Arial, sans-serif; padding: 20px; }');
    printWindow.document.write('h2 { color: #2c3e50; }');
    printWindow.document.write('h4 { color: #34495e; margin-top: 20px; }');
    printWindow.document.write('table { width: 100%; border-collapse: collapse; }');
    printWindow.document.write('td { padding: 8px; border-bottom: 1px solid #ddd; }');
    printWindow.document.write('.text-right { text-align: right; }');
    printWindow.document.write('.text-center { text-align: center; }');
    printWindow.document.write('.text-muted { color: #7f8c8d; }');
    printWindow.document.write('hr { border: none; border-top: 1px solid #ddd; margin: 15px 0; }');
    printWindow.document.write('.mb-2 { margin-bottom: 15px; }');
    printWindow.document.write('.mt-2 { margin-top: 15px; }');
    printWindow.document.write('</style></head><body>');
    printWindow.document.write(content);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
}

// Telecharger le bulletin en PDF
function downloadPDF() {
    if (!currentBulletin) return;

    const jsPDF = window.jspdf.jsPDF;
    const moisNoms = ['', 'Janvier', 'Fevrier', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'Decembre'];

    const b = currentBulletin;
    const salaireBrut = b.salaire_brut || (parseFloat(b.salaire_base) + parseFloat(b.montant_heures_sup || 0) + parseFloat(b.primes || 0));
    const totalRetenues = b.total_retenues || (parseFloat(b.cotisation_cnss || 0) + parseFloat(b.montant_irg || 0) + parseFloat(b.deductions || 0));

    // Creer le document PDF
    const doc = new jsPDF();

    // En-tete
    doc.setFillColor(79, 70, 229);
    doc.rect(0, 0, 210, 35, 'F');
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(22);
    doc.text('BULLETIN DE PAIE', 105, 18, { align: 'center' });
    doc.setFontSize(12);
    doc.text(moisNoms[b.mois] + ' ' + b.annee, 105, 28, { align: 'center' });

    // Informations employe
    doc.setTextColor(0, 0, 0);
    doc.setFontSize(14);
    doc.text('Informations Employe', 15, 50);
    doc.setFontSize(11);
    doc.text('Nom: ' + (b.user ? b.user.prenom + ' ' + b.user.nom : 'N/A'), 15, 60);
    doc.text('Matricule: ' + (b.user ? b.user.matricule : 'N/A'), 15, 68);
    doc.text('Email: ' + (b.user ? b.user.email : 'N/A'), 15, 76);

    // Ligne separatrice
    doc.setDrawColor(79, 70, 229);
    doc.setLineWidth(0.5);
    doc.line(15, 85, 195, 85);

    // Gains
    doc.setFontSize(14);
    doc.text('Gains', 15, 95);

    let y = 105;
    doc.setFontSize(10);

    const gains = [
        ['Salaire de base', formatMoney(b.salaire_base)],
        ['Heures normales (' + (b.total_heures_normales || 0) + 'h)', '-'],
        ['Heures supplementaires (' + (b.total_heures_sup || 0) + 'h x 1.5)', '+' + formatMoney(b.montant_heures_sup || 0)],
        ['Primes', '+' + formatMoney(b.primes || 0)]
    ];

    gains.forEach(function(item) {
        doc.text(item[0], 15, y);
        doc.text(item[1], 195, y, { align: 'right' });
        y += 8;
    });

    // Salaire brut
    doc.setFillColor(227, 242, 253);
    doc.rect(15, y - 3, 180, 10, 'F');
    doc.setFontSize(11);
    doc.text('SALAIRE BRUT', 17, y + 4);
    doc.text(formatMoney(salaireBrut), 193, y + 4, { align: 'right' });
    y += 20;

    // Retenues
    doc.setFontSize(14);
    doc.text('Retenues', 15, y);
    y += 10;
    doc.setFontSize(10);

    const retenues = [
        ['Cotisation CNSS (' + (b.taux_cnss || 3.5) + '%)', '-' + formatMoney(b.cotisation_cnss || 0)],
        ['IRG - Impot sur le Revenu (' + (b.taux_irg || 10) + '%)', '-' + formatMoney(b.montant_irg || 0)],
        ['Autres deductions', '-' + formatMoney(b.deductions || 0)]
    ];

    retenues.forEach(function(item) {
        doc.setTextColor(0, 0, 0);
        doc.text(item[0], 15, y);
        doc.setTextColor(198, 40, 40);
        doc.text(item[1], 195, y, { align: 'right' });
        y += 8;
    });

    // Total retenues
    doc.setTextColor(0, 0, 0);
    doc.setFillColor(255, 235, 238);
    doc.rect(15, y - 3, 180, 10, 'F');
    doc.setFontSize(11);
    doc.text('TOTAL RETENUES', 17, y + 4);
    doc.setTextColor(198, 40, 40);
    doc.text('-' + formatMoney(totalRetenues), 193, y + 4, { align: 'right' });
    y += 20;

    // Ligne separatrice
    doc.setDrawColor(79, 70, 229);
    doc.line(15, y, 195, y);
    y += 10;

    // Net a payer
    doc.setFillColor(232, 245, 233);
    doc.rect(15, y - 5, 180, 15, 'F');
    doc.setTextColor(46, 125, 50);
    doc.setFontSize(14);
    doc.text('NET A PAYER', 17, y + 5);
    doc.setFontSize(16);
    doc.text(formatMoney(b.salaire_net), 193, y + 5, { align: 'right' });

    // Commentaires
    if (b.commentaires) {
        y += 25;
        doc.setTextColor(0, 0, 0);
        doc.setFontSize(10);
        doc.setFillColor(255, 243, 224);
        doc.rect(15, y - 5, 180, 15, 'F');
        doc.text('Commentaires: ' + b.commentaires, 17, y + 3);
    }

    // Pied de page
    doc.setTextColor(150, 150, 150);
    doc.setFontSize(9);
    doc.text('Document genere le ' + new Date().toLocaleDateString('fr-FR'), 105, 280, { align: 'center' });
    doc.text('Pointage RH - Gestion des Presences', 105, 286, { align: 'center' });

    // Telecharger
    const fileName = 'bulletin_' + (b.user ? b.user.matricule : 'employe') + '_' + b.mois + '_' + b.annee + '.pdf';
    doc.save(fileName);
}

// Exporter tous les bulletins en Excel
function exportBulletinsExcel() {
    // Recuperer les donnees du tableau
    const table = document.getElementById('bulletins-table');
    const rows = table.querySelectorAll('tbody tr');

    if (rows.length === 0) {
        showAlert('alert-container', 'Aucune donnee a exporter', 'warning');
        return;
    }

    const data = [
        ['Employe', 'Periode', 'Salaire Base', 'Heures Normales', 'Heures Sup', 'Prime Sup', 'Net a Payer']
    ];

    rows.forEach(function(row) {
        const cells = row.querySelectorAll('td');
        if (cells.length > 0) {
            data.push([
                cells[0].textContent,
                cells[1].textContent,
                cells[2].textContent,
                cells[3].textContent,
                cells[4].textContent,
                cells[5].textContent,
                cells[6].textContent
            ]);
        }
    });

    // Creer le workbook
    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet(data);

    // Largeur des colonnes
    ws['!cols'] = [
        { wch: 25 }, { wch: 15 }, { wch: 15 }, { wch: 15 }, { wch: 12 }, { wch: 15 }, { wch: 15 }
    ];

    XLSX.utils.book_append_sheet(wb, ws, 'Bulletins');

    // Telecharger
    XLSX.writeFile(wb, 'bulletins_' + new Date().toISOString().split('T')[0] + '.xlsx');
}

// Fermer modal
function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

// Toggle sidebar
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('active');
}

// Deconnexion
async function logout() {
    showLoading();
    try {
        await API.logout();
    } catch (e) {}
    window.location.href = 'login.html';
}
