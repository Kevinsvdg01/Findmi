 <style>
    .footer-main {
    background-color: #0056b3;
    color: #e0e0e0;
    padding: 4rem 2.5rem;
}

.footer-grid {
    display: grid;
    /* CHANGEMENT PRINCIPAL : Grille à 3 colonnes. La 1ère est plus large. */
    grid-template-columns: 1.5fr 1fr 1fr;
    gap: 3rem;
}

/* --- Style du logo dans le footer --- */
.footer-logo {
    max-width: 160px;
    margin-bottom: 1rem;
}

.footer-col h4 {
    color: white;
    margin-bottom: 1.5rem;
    font-size: 1.2rem;
}

.footer-col p {
    line-height: 1.7;
    margin-bottom: 1rem;
}

/* --- Style des listes et liens --- */
.footer-col ul {
    list-style: none;
    padding: 0;
}

.footer-col li {
    margin-bottom: 0.75rem;
}

.footer-col a {
    color: #e0e0e0;
    transition: color 0.3s ease, padding-left 0.3s ease;
}

.footer-col a:hover {
    color: #FEBA00;
    padding-left: 5px;
}

.footer-col .social-icons a:hover {
    padding-left: 0;
}

/* --- Icônes (contact et réseaux sociaux) --- */
.footer-col p i {
    margin-right: 10px;
    color: #FEBA00;
    width: 20px;
    text-align: center;
}

.social-icons {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
}

.social-icons a {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    width: 40px;
    height: 40px;
    background-color: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    color: white;
    font-size: 1rem;
    transition: background-color 0.3s ease, transform 0.3s ease;
}

.social-icons a:hover {
    background-color: #dc3545;
    color: #ffff;
    transform: translateY(-3px);
}

/* --- Sous-pied de page (bande jaune) --- */
.footer-sub {
    background-color: #dc3545;
    color: #ffff;
    padding: 1rem 2.5rem;
    text-align: center;
    font-size: 0.9rem;
}

.footer-sub .container {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.footer-sub a {
    color: #ffff;
    font-weight: 500;
}

.footer-sub a:hover {
    text-decoration: underline;
}




/* ======================= RESPONSIVE POUR FOOTER ======================= */

@media (max-width: 992px) {
    .footer-grid {
        /* La grille à 3 colonnes reste acceptable sur tablette,
           mais on peut passer à 2 si on le souhaite :*/
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .footer-grid {
        /* 1 seule colonne sur mobile */
        grid-template-columns: 1fr;
        text-align: center;
    }

    .social-icons {
        justify-content: center;
    }

    .footer-sub .container {
        flex-direction: column;
        gap: 0.5rem;
    }
}
 </style>
 
 
 <footer class="main-footer">
        <div class="footer-main">
            <div class="container footer-grid">

                <div class="footer-col footer-about">
                    <img src="/images/logofindmi.jpg" alt="Logo Buculma" class="footer-logo">
                    <p>La promesse d'une agriculture à visage humain, pour des légumes de saison sains, savoureux, et
                        gorgés du soleil de notre terroir.</p>
                    <div class="social-icons">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>

                <div class="footer-col">
                    <h4>Liens Rapides</h4>
                    <ul>
                        <li><a href="index.html">Accueil</a></li>
                        <li><a href="a-propos.html">À Propos</a></li>
                        <li><a href="produits.html">Nos Produits</a></li>
                        <li><a href="contact.html">Contact</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Contactez-nous</h4>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Route de la Campagne<br>Ouagadougou, Burkina Faso</p>
                    <p><i class="fas fa-envelope"></i> contact@buculma-bf.com</p>
                    <p><i class="fas fa-phone"></i> +226 XX XX XX XX</p>
                </div>

            </div>
        </div>

        <div class="footer-sub">
            <div class="container">
                <p>&copy; 2025 Buculma. Tous droits réservés.</p>
                <p><a href="#">Mentions Légales</a> | <a href="#">Politique de Confidentialité</a></p>
            </div>
        </div>
    </footer>