<?php



// --- Validation de l'article ---
function validateArticle(string $titre, string $contenu): array
{
    $errors = [];
    if (empty($titre)) {
        $errors[] = 'Le titre ne peut pas être vide';
    } elseif (mb_strlen($titre) > 100) {
        $errors[] = 'Le titre ne peut pas excéder 100 caractères.';
    }
    if (empty($contenu)) {
        $errors[] = 'Le contenu ne peut pas être vide';
    }
    return $errors;
}
