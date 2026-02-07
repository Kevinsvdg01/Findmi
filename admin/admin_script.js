// admin/admin_script.js

// On englobe tout le code dans cet écouteur pour s'assurer
// que la page HTML est complètement chargée avant d'exécuter le script.
document.addEventListener('DOMContentLoaded', function() {

    /* ==========================================================================
       GESTION DE LA MODALE DE REJET
       ========================================================================== */
    const rejectModal = document.getElementById('rejectModal');
    const modalIdAnnonceInput = document.getElementById('modal_id_annonce');
    const modalMotifTextarea = document.getElementById('modal_motif_rejet');
    
    // La fonction 'openRejectModal' est maintenant définie dans la portée globale
    // pour être accessible depuis l'attribut 'onclick' du HTML.
    // Cependant, il est plus propre de lier les événements ici.
    // Nous le laissons comme ça pour correspondre au HTML fourni.

    // Fermeture de la modale
    const closeModalButton = rejectModal ? rejectModal.querySelector('.btn-secondary') : null;
    if (closeModalButton) {
        closeModalButton.addEventListener('click', closeRejectModal);
    }
    
    // Fermer la modale si on clique en dehors de la boîte de dialogue (sur l'overlay)
    if (rejectModal) {
        rejectModal.addEventListener('click', function(event) {
            // Si l'élément cliqué est le fond noir lui-même
            if (event.target === rejectModal) {
                closeRejectModal();
            }
        });
    }

    /* ==========================================================================
       GESTION DU MENU HAMBURGER POUR MOBILE
       ========================================================================== */
    const menuToggle = document.getElementById('adminMenuToggle');
    const sidebar = document.querySelector('.admin-sidebar');
    const overlay = document.querySelector('.admin-overlay');

    if (menuToggle && sidebar && overlay) {
        // Ouvre le menu au clic sur le bouton hamburger
        menuToggle.addEventListener('click', function() {
            sidebar.classList.add('active');
            overlay.classList.add('active');
        });

        // Ferme le menu au clic sur le fond sombre (l'overlay)
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        });
    }

}); // Fin du 'DOMContentLoaded'


/* ==========================================================================
   FONCTIONS GLOBALES (accessibles depuis le HTML)
   ========================================================================== */

// Fonction pour ouvrir la modale, appelée par onclick="openRejectModal(...)"
function openRejectModal(id_annonce) {
    const rejectModal = document.getElementById('rejectModal');
    const modalIdAnnonceInput = document.getElementById('modal_id_annonce');
    
    if (rejectModal && modalIdAnnonceInput) {
        // On remplit le champ caché avec l'ID de l'annonce
        modalIdAnnonceInput.value = id_annonce;
        // On affiche la modale en ajoutant la classe .show
        rejectModal.classList.add('show');
    }
}

// Fonction pour fermer la modale
function closeRejectModal() {
    const rejectModal = document.getElementById('rejectModal');
    const modalMotifTextarea = document.getElementById('modal_motif_rejet');

    if (rejectModal) {
        rejectModal.classList.remove('show');
        // On vide le textarea pour la prochaine utilisation, c'est plus propre.
        if (modalMotifTextarea) {
            modalMotifTextarea.value = '';
        }
    }
}

// Fonction de confirmation pour les actions destructrices, appelée par onsubmit="..."
function confirmAction(event, message) {
    if (!confirm(message)) {
        event.preventDefault(); // Annule la soumission du formulaire si l'utilisateur clique sur "Annuler"
        return false;
    }
    return true;
}