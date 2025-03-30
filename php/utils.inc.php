<?php

/**
 * Nettoie une donnée en supprimant les espaces superflus et en échappant les caractères spéciaux HTML.
 *
 * @param string $data Donnée à nettoyer
 * @return string Donnée nettoyée
 */
function nettoyage(string $data): string
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Affiche un message sous forme d'alerte
 * Gère aussi bien un message simple qu'un tableau d'erreurs sous forme de liste
 * 
 * @param string|array $message Contenu du message à afficher (chaîne ou tableau d'erreurs)
 * @param string $type Type du message : 'success' ou 'danger' (par défaut)
 */
function afficherAlerte(string|array $message, string $type = 'danger'): void
{
    if (!empty($message)) {
        echo '<div class="box-alert color-' . nettoyage($type) . '">';

        if (is_array($message)) {
            echo '<ul>';
            foreach ($message as $erreur) {
                echo '<li>' . nettoyage($erreur) . '</li>';
            }
            echo '</ul>';
        } else {
            echo nettoyage($message);
        }

        echo '</div>';
    }
}
