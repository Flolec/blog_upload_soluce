<?php

/**
 * Convertit un code d'erreur de téléchargement en message explicatif 
 *
 *
 * @param int $error Code d'erreur du téléchargement.
 *                   - UPLOAD_ERR_NO_FILE : Fichier manquant.
 *                   - UPLOAD_ERR_INI_SIZE : Fichier dépassant la taille maximale autorisée par PHP.
 *                   - UPLOAD_ERR_FORM_SIZE : Fichier dépassant la taille maximale autorisée par le formulaire.
 *                   - UPLOAD_ERR_PARTIAL : Fichier transféré partiellement.
 *                   - Autre : Erreur inconnue.
 *
 * @return string|null Message d'erreur ou null si aucun problème.
 */

function obtenirMessageErreurTelechargement(int $codeErreur): ?string
{
    $message = null;

    if ($codeErreur > 0) {
        $message = 'Une erreur est survenue lors du téléchargement : ';
        switch ($codeErreur) {
            case UPLOAD_ERR_NO_FILE:
                $message .= "Fichier manquant";
                break;
            case UPLOAD_ERR_INI_SIZE:
                $message .= "Fichier dépassant la taille maximale autorisée par PHP";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $message .= "Fichier dépassant la taille maximale autorisée par le formulaire";
                break;
            case UPLOAD_ERR_PARTIAL:
                $message .= "Fichier transféré partiellement";
                break;
            default:
                $message .= "Erreur inconnue";
                break;
        }
    }
    return $message;
}

/**
 * Vérifie la validité d'un fichier uploadé.
 *
 * @param array $fichier Données du fichier ($_FILES).
 * @param array $extensionsAutorisees Extensions autorisées.
 * @return bool|string True si le fichier est valide, sinon un message d'erreur.
 */
function verifierUpload(array $fichier, array  $extensionsAutorisees): bool|string
{

    if (empty($fichier['name'])) {
        return  "Fichier manquant";
    }

    if ($erreur = obtenirMessageErreurTelechargement($fichier['error'])) {
        return $erreur;
    }


    $infosFichier = pathinfo($fichier['name']);
    $extension = strtolower($infosFichier['extension'] ?? '');

    if (!in_array($extension, $extensionsAutorisees)) {
        return "Type de fichier incorrect : $extension. Extensions autorisées : " . implode(', ', $extensionsAutorisees);
    }


    if ($fichier['size'] > MAX_FILE_SIZE) {
        return "Taille du fichier trop grande (" . afficherTailleFichier($fichier['size']) . " > " . afficherTailleFichier(MAX_FILE_SIZE) . ")";
    }

    return true;
}


/**
 * Formate une taille en octets en Mo ou ko.
 *
 * @param int $taille Taille en octets
 * @return string Taille formatée
 */
function afficherTailleFichier(int $taille): string
{
    return ($taille >= UN_MEGA_EN_OCTETS) ? round($taille / UN_MEGA_EN_OCTETS, 2) . ' Mo' :  round($taille / 1024, 2) . ' ko';
}



/**
 * Génère un nom de fichier unique en conservant l'extension d'origine.
 * Le nom d'origine (sans espaces) est tronqué à 5 caractères.
 * Retourne false si l'extension est absente.
 *
 * @param string $nom Nom original du fichier.
 * @return string|false Nom de fichier unique ou false.
 */
function genererNomUnique(string $nomOriginal): string|false
{
    $info = pathinfo($nomOriginal);
    if (empty($info['extension'])) return false;
    $base = str_replace(' ', '', $info['filename']);
    $base = strtolower((mb_strlen($base) > 5) ? substr($base, 0, 5) : $base);
    return uniqid() . '_' . $base . '.' . $info['extension'];
}


/**
 * Renomme et déplace le fichier
 *
 * @param array $fichier Données du fichier ($_FILES).
 * @return array{
 *   statut: bool,       // true en cas de succès, false sinon.
 *   filename?: string,  // Clé présente uniquement en cas de succès.
 *   erreur?: string     // Clé présente uniquement en cas d'échec.
 * } Tableau associatif contenant le résultat de l'opération.
 */
function deplacerFichier(array $fichier): array
{
    $nomFichierServeur = genererNomUnique($fichier['name']);

    if (!$nomFichierServeur) {
        return ['statut' => false, 'erreur' => "Impossible de générer un nom de fichier"];
    }

    // Vérification de l'existence et des permissions du dossier
    if (!is_dir('../../' . UPLOAD_DOSSIER) || !is_writable('../../' . UPLOAD_DOSSIER)) {
        return ['statut' => false, 'erreur' => "Le dossier d'upload n'existe pas ou n'est pas accessible en écriture."];
    }


    if (!move_uploaded_file($fichier['tmp_name'], '../../' . UPLOAD_DOSSIER . $nomFichierServeur)) {

        return ['statut' => false, 'erreur' => "Erreur lors de la copie sur le serveur"];
    }

    return ['statut' => true, 'nomFichierServeur' => $nomFichierServeur];
}
