<?php

namespace Blog;

require 'db_link.inc.php';

use DB\DBLink;
use PDO;

/**
 * Représente un article du blog
 */
class Article
{
    public $id;
    public $titre;
    public $contenu;
    public $nom_img;
}

/**
 * Classe ArticleRepository : gestionnaire des articles
 */

class ArticleRepository
{
    const TABLE_NAME = 'blog_article';
    /**
     * Récupère tous les articles depuis la base de données.
     *
     * @param string &$message Référence à une variable de message d'état
     * @return  objet de type PDOStatement | Tableau vide
     */
    public function getAllArticles(&$message)
    {
        $result = array();
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            if (!$bdd) return $result;
            $stmt  = $bdd->query("SELECT * FROM " . self::TABLE_NAME . " order by id", PDO::FETCH_CLASS, "Blog\Article");
            $result = $stmt->fetchAll();
        } catch (\Exception $e) {
            $message .= $e->getMessage() . '<br>';
        } finally {
            DBLink::disconnect($bdd);
        }
        return $result;
    }



    public function getArticleById($id, &$message)
    {
        $result = array();
        $bdd    = null;
        try {
            $bdd  = DBLink::connect2db(MYDB, $message);
            if (!$bdd) return $result;
            $stmt = $bdd->prepare("SELECT * FROM " . self::TABLE_NAME . " WHERE id = :id_article");
            $stmt->bindValue(':id_article', $id);
            if ($stmt->execute()) {
                $obj = $stmt->fetchObject('Blog\Article');
                $result = ($obj !== false ? $obj : null);
            } else {
                $message .= 'Une erreur système est survenue.<br> 
                    Veuillez essayer à nouveau plus tard ou contactez l\'administrateur du site. 
                    (Code erreur: ' . $stmt->errorCode() . ')<br>';
            }
        } catch (\Exception $e) {
            $message .= $e->getMessage() . '<br>';
        }
        DBLink::disconnect($bdd);
        return $result;
    }




    /**
     * Insère un nouvel article dans la base de données.
     *
     * @param Article $article Objet représentant l'article à insérer (doit contenir `titre` et `contenu`).
     * @param string &$message Référence à une variable de message d'état
     * @return bool Retourne `true` si l'insertion est réussie, sinon `false`.
     *
     */
    public function insertArticle($article, &$message)
    {
        $noError = false;
        $bdd   = null;
        try {
            $bdd  = DBLink::connect2db(MYDB, $message);
            if (!$bdd) return $noError;
            $stmt = $bdd->prepare("INSERT INTO " . self::TABLE_NAME . " (titre, contenu, nom_img ) VALUES (:titre, :contenu, :nom_img )");
            $stmt->bindValue(':titre', $article->titre);
            $stmt->bindValue(':contenu', $article->contenu);
            $stmt->bindValue(':nom_img', $article->nom_img);
            if ($stmt->execute()) {
                $noError = true;
            } else {
                $message .= 'Une erreur système est survenue.<br> 
                    Veuillez essayer à nouveau plus tard ou contactez l\'administrateur du site. 
                    (Code erreur: ' . $stmt->errorCode() . ')<br>';
            }
            $stmt = null;
        } catch (\Exception $e) {
            $message .= $e->getMessage() . '<br>';
        }
        DBLink::disconnect($bdd);
        return $noError;
    }

    /**
     * Supprime un article
     *
     * @param String $id Identifiant de l'article à supprimer
     * @param string &$message Référence à une variable de message d'état
     * @return bool Retourne `true` si l'insertion est réussie, sinon `false`.
     *
     */
    public static function deleteArticle($id, &$message)
    {
        $noError = false;
        $bdd   = null;
        try {
            $bdd  = DBLink::connect2db(MYDB, $message);
            if (!$bdd) return $noError;
            $stmt = $bdd->prepare("DELETE FROM  " . self::TABLE_NAME . " WHERE id = :id_article");
            $stmt->bindValue(':id_article', $id);
            if ($stmt->execute()) {
                $noError = true;
            } else {
                $message .= 'Une erreur système est survenue.<br> 
                    Veuillez essayer à nouveau plus tard ou contactez l\'administrateur du site. 
                    (Code erreur: ' . $stmt->errorCode() . ')<br>';
            }
            $stmt = null;
        } catch (\Exception $e) {
            $message .= $e->getMessage() . '<br>';
        }
        DBLink::disconnect($bdd);
        return $noError;
    }
    /**
     * Modifie un article
     *
     * @param String $id Identifiant de l'article à modifier
     * @param string &$message Référence à une variable de message d'état
     * @return bool Retourne `true` si la modification est réussie, sinon `false`.
     *
     */
    public static function updateArticle($article, &$message)
    {
        $noError = false;
        $bdd   = null;
        try {
            $bdd  = DBLink::connect2db(MYDB, $message);
            if (!$bdd) return $noError;
            $stmt = $bdd->prepare("UPDATE " . self::TABLE_NAME . " SET titre = :titre, contenu = :contenu, nom_img= :nom_img    WHERE id = :id_article");
            $stmt->bindValue(':id_article', $article->id);
            $stmt->bindValue(':titre', $article->titre);
            $stmt->bindValue(':contenu', $article->contenu);
            $stmt->bindValue(':nom_img', $article->nom_img);
            if ($stmt->execute()) {
                $noError = true;
            } else {
                $message .= 'Une erreur système est survenue.<br> 
                    Veuillez essayer à nouveau plus tard ou contactez l\'administrateur du site. 
                    (Code erreur: ' . $stmt->errorCode() . ')<br>';
            }
            $stmt = null;
        } catch (\Exception $e) {
            $message .= $e->getMessage() . '<br>';
        }
        DBLink::disconnect($bdd);
        return $noError;
    }
}
