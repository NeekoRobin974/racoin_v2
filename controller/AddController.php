<?php

namespace App\Controller;

use App\Model\Annonce;
use App\Model\Annonceur;

class AddController {

    public function addItemView($twig, $menu, $chemin, $cat, $dpt): void {
        $template = $twig->load("add.html.twig");
        echo $template->render([
            "breadcrumb"   => $menu, 
            "chemin"       => $chemin, 
            "categories"   => $cat, 
            "departements" => $dpt
        ]);
    }

    public function addNewItem($twig, $menu, $chemin, array $allPostVars): void {
        date_default_timezone_set('Europe/Paris');

        $nom              = trim((string) ($allPostVars['nom'] ?? ''));
        $email            = trim((string) ($allPostVars['email'] ?? ''));
        $phone            = trim((string) ($allPostVars['phone'] ?? ''));
        $ville            = trim((string) ($allPostVars['ville'] ?? ''));
        $departement      = trim((string) ($allPostVars['departement'] ?? ''));
        $categorie        = trim((string) ($allPostVars['categorie'] ?? ''));
        $title            = trim((string) ($allPostVars['title'] ?? ''));
        $description      = trim((string) ($allPostVars['description'] ?? ''));
        $price            = trim((string) ($allPostVars['price'] ?? ''));
        $password         = trim((string) ($allPostVars['psw'] ?? ''));
        $password_confirm = trim((string) ($allPostVars['confirm-psw'] ?? ''));

        $errors = [];

        if (empty($nom) || $nom === '0') {
            $errors[] = 'Veuillez entrer votre nom';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Veuillez entrer une adresse mail correcte';
        }
        if (empty($phone) || !is_numeric($phone)) {
            $errors[] = 'Veuillez entrer votre numéro de téléphone';
        }
        if (empty($ville) || $ville === '0') {
            $errors[] = 'Veuillez entrer votre ville';
        }
        if (!is_numeric($departement)) {
            $errors[] = 'Veuillez choisir un département';
        }
        if (!is_numeric($categorie)) {
            $errors[] = 'Veuillez choisir une catégorie';
        }
        if (empty($title) || $title === '0') {
            $errors[] = 'Veuillez entrer un titre';
        }
        if (empty($description) || $description === '0') {
            $errors[] = 'Veuillez entrer une description';
        }
        if (empty($price) || !is_numeric($price)) {
            $errors[] = 'Veuillez entrer un prix';
        }
        if (empty($password) || $password !== $password_confirm) {
            $errors[] = 'Les mots de passes ne sont pas identiques';
        }

        if ($errors !== []) {
            echo $twig->load("add-error.html.twig")->render([
                "breadcrumb" => $menu, 
                "chemin"     => $chemin, 
                "errors"     => $errors
            ]);
        } else {
            $annonce   = new Annonce();
            $annonceur = new Annonceur();

            $annonceur->email         = htmlentities($email);
            $annonceur->nom_annonceur = htmlentities($nom);
            $annonceur->telephone     = htmlentities($phone);

            $annonce->ville          = htmlentities($ville);
            $annonce->id_departement = (int) $departement;
            $annonce->prix           = (float) $price;
            $annonce->mdp            = password_hash($password, PASSWORD_DEFAULT);
            $annonce->titre          = htmlentities($title);
            $annonce->description    = htmlentities($description);
            $annonce->id_categorie   = (int) $categorie;
            $annonce->date           = date('Y-m-d');

            $annonceur->save();
            $annonceur->annonce()->save($annonce);

            echo $twig->load("add-confirm.html.twig")->render([
                "breadcrumb" => $menu, 
                "chemin" => $chemin
            ]);
        }
    }
}
