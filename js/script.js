// On attend que toute la page HTML soit chargée avant d'exécuter notre code.
// C'est la pratique la plus sûre.
document.addEventListener('DOMContentLoaded', function() {

    // --- 1. Gestion du menu hamburger sur mobile ---
    const menuToggle = document.getElementById('menuToggle');
    const navLinks = document.getElementById('navLinks');

    if (menuToggle && navLinks) {
        menuToggle.addEventListener('click', () => {
            navLinks.classList.toggle('active');
        });
    }

    // --- 2. Animation d'apparition au défilement (scroll) pour la page d'accueil ---
    const sectionsToFade = document.querySelectorAll('.fade-in-section');

    if (sectionsToFade.length > 0) {
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        sectionsToFade.forEach(section => {
            observer.observe(section);
        });
    }

    // --- 3. Gestion du fond interactif pour la page d'accueil ---
    const floatingContainer = document.querySelector('.floating-container');

    if (floatingContainer) {
        const items = document.querySelectorAll('.floating-item');
        const section = document.querySelector('.with-floating-background');

        items.forEach(item => {
            const x = Math.random() * (floatingContainer.offsetWidth - item.offsetWidth);
            const y = Math.random() * (floatingContainer.offsetHeight - item.offsetHeight);
            item.style.left = `${x}px`;
            item.style.top = `${y}px`;

            const duration = Math.random() * 10 + 15;
            const delay = Math.random() * -20;
            item.style.animationDuration = `${duration}s`;
            item.style.animationDelay = `${delay}s`;
            item.dataset.speed = (Math.random() - 0.5) * 4;
        });
        
        section.addEventListener('mousemove', (e) => {
            const { clientX, clientY } = e;
            const centerX = window.innerWidth / 2;
            const centerY = window.innerHeight / 2;
            const moveX = (clientX - centerX) * 0.01;
            const moveY = (clientY - centerY) * 0.01;

            items.forEach(item => {
                const speed = parseFloat(item.dataset.speed);
                item.style.transform = `translate(${speed * moveX}px, ${speed * moveY}px)`;
            });
        });
        
        section.addEventListener('mouseleave', () => {
             items.forEach(item => {
                item.style.transform = `translate(0px, 0px)`;
             });
        });
    }

    // --- 4. Gestion de l'accordéon du formulaire sur le tableau de bord ---
    const toggleFormBtn = document.getElementById('toggleFormBtn');
    const newAnnonceForm = document.getElementById('newAnnonceForm');

    if (toggleFormBtn && newAnnonceForm) {
        toggleFormBtn.addEventListener('click', () => {
            newAnnonceForm.classList.toggle('hidden');

            if (newAnnonceForm.classList.contains('hidden')) {
                toggleFormBtn.innerHTML = '<i class="fas fa-plus-circle"></i> Déclarer une nouvelle perte';
            } else {
                toggleFormBtn.innerHTML = '<i class="fas fa-times-circle"></i> Fermer le formulaire';
                newAnnonceForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    }

}); // Fin du bloc 'DOMContentLoaded'

// --- Fonctions Globales ---
// Les déclarations de fonctions peuvent être mises à l'extérieur,
// car elles ne s'exécutent pas immédiatement. C'est une bonne pratique.
function confirmAction(event, message) {
    if (!confirm(message)) {
        event.preventDefault(); // Annule la soumission du formulaire
        return false;
    }
    return true;
}



    // --- 5. Gestion des menus déroulants (dropdown) des actions ---
    const dropdowns = document.querySelectorAll('.actions-dropdown');

    dropdowns.forEach(dropdown => {
        const button = dropdown.querySelector('.actions-btn');
        const content = dropdown.querySelector('.dropdown-content');

        button.addEventListener('click', (event) => {
            // Empêche le clic de se propager à la fenêtre, ce qui fermerait le menu immédiatement
            event.stopPropagation();
            
            // Fermer tous les autres menus avant d'ouvrir celui-ci
            closeAllDropdowns(content);
            
            // Affiche ou cache le menu actuel
            content.classList.toggle('show');
        });
    });

    // Fonction pour fermer tous les menus, sauf celui qu'on veut garder ouvert
    function closeAllDropdowns(exceptThisOne = null) {
        document.querySelectorAll('.dropdown-content').forEach(content => {
            if (content !== exceptThisOne) {
                content.classList.remove('show');
            }
        });
    }

    // Ajoute un écouteur sur toute la page pour fermer les menus si on clique à l'extérieur
    window.addEventListener('click', () => {
        closeAllDropdowns();
    });

    

