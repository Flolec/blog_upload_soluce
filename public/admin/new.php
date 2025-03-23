<?php
require_once  '../../php/config_perso.inc.php';
require  '../../php/db_article.inc.php';
require  '../../php/utils.inc.php';
require_once  '../../php/utils_upload.inc.php';

use Blog\ArticleRepository;
use Blog\Article;

// Récupération de l'ID en GET
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$titre = nettoyage($_POST['titre'] ?? '');
$contenu = nettoyage($_POST['contenu'] ?? '');
$nomFichier = nettoyage($_POST['nomFichier'] ?? '');


$erreurs = [];
$messageErreur = $message  = '';

$extensionsAutorisees = ['jpg', 'png', 'gif'];

$articleRepository = new ArticleRepository();

// Chargement de l'article en mode modification
if ($id !== false && $id !== null) {
    $article = $articleRepository->getArticleById($id, $messageErreur);
    if ($article) {
        $titre = nettoyage($article->titre);
        $contenu = nettoyage($article->contenu);
        $nomFichier =  nettoyage($article->nom_img);
    } else {
        $messageErreur = "Article introuvable.";
    }
}

//soumission du formulaire
if (isset($_POST['btn_article'])) {

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    // Validation du titre
    if (empty($titre)) {
        $erreurs[] = 'Le titre ne peut pas être vide';
    } else if (mb_strlen($titre) > 100) {
        $erreurs[] = 'Le titre ne peut pas être excéder 100 caractères.';
    }

    // Validation du contenu
    if (empty($contenu)) {
        $erreurs[] = 'Le contenu ne peut pas être vide';
    }


    // Traitement du fichier uploadé
    if (isset($_FILES['photo_article']) && !empty($_FILES['photo_article']['name'])) {
        $verifUpload = verifierUpload($_FILES['photo_article'],  $extensionsAutorisees);
        if ($verifUpload !== true) {
            $erreurs[] = $verifUpload;
        } elseif (empty($erreurs)) {
            $uploadResult = deplacerFichier($_FILES['photo_article']);
            if ($uploadResult['statut']) {
                $nomFichier = $uploadResult['nomFichierServeur'];
            } else {
                $erreurs[] = $uploadResult['erreur'];
            }
        }
    } elseif (empty($nomFichier)) {
        $erreurs[] = 'image obligatoire';
    }

    //getion de l'insert et de la modif
    if (empty($erreurs)) {

        $article = new Article();
        $article->titre = $titre;
        $article->contenu = $contenu;
        $article->nom_img =   $nomFichier;

        if ($id !== false && $id !== null) {
            // Mode modification
            $article->id = $id;
            if ($articleRepository->updateArticle($article, $messageErreur)) {
                $message = "Article mis à jour avec succès.";
            } else {
                $erreurs[] = "Erreur technique lors de la mise à jour.";
                $erreurs[] = $messageErreur;
            }
        } else {
            // Mode ajout
            if ($articleRepository->insertArticle($article, $messageErreur)) {
                $message .= "Article correctement ajouté.";
                $titre = $contenu =  $nomFichier = '';
            } else {
                $erreurs[]  = "Erreur technique. Veuillez contacter l'administrateur.";
                $erreurs[] = $messageErreur;
            }
        }
    }
}
?>


<?php include   '../../inc/head.inc.php' ?>
<?php include   '../../inc/header.inc.php' ?>

<main class="centrage boxOmbre">

    <h1><?= $id ? 'Modifier' : 'Nouvel' ?> Article</h1>
    <ul class="containerFlex">
        <li><i class="fa fa-arrow-left"></i> <a href="<?= BASE_URL ?>"> vers la liste des articles</a></li>
    </ul>
    <form action="<?= nettoyage($_SERVER["PHP_SELF"]); ?>" method="post" class="formAdmin" enctype="multipart/form-data">
        <h2><?= $id ? 'Modifier' : 'Ajouter' ?> un article</h2>
        <?php
        afficherAlerte($message, 'success');
        afficherAlerte($erreurs, 'danger');
        ?>

        <input type="hidden" name="id" value="<?= $id ?>">
        <input type="hidden" name="nomFichier" value="<?= $nomFichier ?>">

        <!-- Pour tester, les attributs required ont été enlevés et autres validations maxlength="100" -->
        <label for id="titre">Titre *<br><small>100 caractères max</small></label><input type="text" size="50" id="titre" name="titre" value="<?= $titre ?>">
        <label for id="contenu">Contenu *</label><textarea name="contenu" id="contenu"><?= $contenu ?></textarea>

        <input type="hidden" name="MAX_FILE_SIZE" value="<?= MAX_FILE_SIZE ?>">
        <input type="file" name="photo_article" accept="image/*">
        <?php if (!empty($nomFichier)): ?>
            <p>Image actuelle : <?= $nomFichier ?></p>
        <?php endif; ?>
        <input type="submit" class="btn btn-theme" name="btn_article" value="<?= $id ? 'Modifier' : 'Ajouter' ?>">

    </form>
</main>


<?php include  '../../inc/footer.inc.php' ?>