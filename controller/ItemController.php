<?php

namespace App\Controller;
use AllowDynamicProperties;
use App\Model\Annonce;
use App\Model\Annonceur;
use App\Model\Departement;
use App\Model\Photo;
use App\Model\Categorie;

#[AllowDynamicProperties] class ItemController {
    function afficherItem($twig, $menu, string $chemin, string $n, $cat): void
    {

        $this->annonce = Annonce::find($n);
        if(!property_exists($this, 'annonce') || $this->annonce === null){
            echo "404";
            return;
        }

        $menu = [['href' => $chemin, 'text' => 'Acceuil'], ['href' => $chemin."/cat/".$n, 'text' => Categorie::find($this->annonce->id_categorie)?->nom_categorie], ['href' => $chemin."/item/".$n, 'text' => $this->annonce->titre]];

        $this->annonceur = Annonceur::find($this->annonce->id_annonceur);
        $this->departement = Departement::find($this->annonce->id_departement );
        $this->photo = Photo::where('id_annonce', '=', $n)->get();
        $template = $twig->load("item.html.twig");
        echo $template->render(["breadcrumb" => $menu, "chemin" => $chemin, "annonce" => $this->annonce, "annonceur" => $this->annonceur, "dep" => $this->departement->nom_departement, "photo" => $this->photo, "categories" => $cat]);
    }

    function supprimerItemGet($twig, $menu, $chemin,$n): void{
        $this->annonce = Annonce::find($n);
        if(!property_exists($this, 'annonce') || $this->annonce === null){
            echo "404";
            return;
        }
        $template = $twig->load("delGet.html.twig");
        echo $template->render(["breadcrumb" => $menu, "chemin" => $chemin, "annonce" => $this->annonce]);
    }


    function supprimerItemPost($twig, $menu, $chemin, $n, $cat): void{
        $this->annonce = Annonce::find($n);
        $reponse = false;
        if(password_verify((string) $_POST["pass"],$this->annonce->mdp)){
            $reponse = true;
            photo::where('id_annonce', '=', $n)->delete();
            $this->annonce->delete();

        }

        $template = $twig->load("delPost.html.twig");
        echo $template->render(["breadcrumb" => $menu, "chemin" => $chemin, "annonce" => $this->annonce, "pass" => $reponse, "categories" => $cat]);
    }

    function modifyGet($twig, $menu, $chemin, $id): void{
        $this->annonce = Annonce::find($id);
        if(!property_exists($this, 'annonce') || $this->annonce === null){
            echo "404";
            return;
        }
        $template = $twig->load("modifyGet.html.twig");
        echo $template->render(["breadcrumb" => $menu, "chemin" => $chemin, "annonce" => $this->annonce]);
    }

    function modifyPost($twig, $menu, $chemin, $n, $cat, $dpt): void {
        $this->annonce = Annonce::find($n);
        $this->annonceur = Annonceur::find($this->annonce->id_annonceur);
        $this->categItem = Categorie::find($this->annonce->id_categorie)?->nom_categorie;
        $this->dptItem = Departement::find($this->annonce->id_departement)?->nom_departement;

        $reponse = false;
        if(isset($_POST['pass']) && password_verify((string) $_POST['pass'], $this->annonce->mdp)){
            $reponse = true;
        }

        $template = $twig->load("modifyPost.html.twig");
        echo $template->render(["breadcrumb" => $menu, "chemin" => $chemin, "annonce" => $this->annonce, "annonceur" => $this->annonceur, "pass" => $reponse, "categories" => $cat, "departements" => $dpt, "dptItem" => $this->dptItem, "categItem" => $this->categItem]);
    }

    function edit($twig, $menu, $chemin, array $allPostVars, $id): void{

        date_default_timezone_set('Europe/Paris');

        $nom         = trim((string) ($allPostVars['nom'] ?? ''));
        $email       = trim((string) ($allPostVars['email'] ?? ''));
        $phone       = trim((string) ($allPostVars['phone'] ?? ''));
        $ville       = trim((string) ($allPostVars['ville'] ?? ''));
        $departement = trim((string) ($allPostVars['departement'] ?? ''));
        $categorie   = trim((string) ($allPostVars['categorie'] ?? ''));
        $title       = trim((string) ($allPostVars['title'] ?? ''));
        $description = trim((string) ($allPostVars['description'] ?? ''));
        $price       = trim((string) ($allPostVars['price'] ?? ''));
        $password    = trim((string) ($allPostVars['psw'] ?? ''));

        $errors = [];

        if (empty($nom) || $nom === '0') {
            $errors[] = 'Veuillez entrer votre nom';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Veuillez entrer une adresse mail correcte';
        }
        if ((empty($phone) || $phone === '0') && !is_numeric($phone)) {
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

        if ($errors !== []) {
            $template = $twig->load("add-error.html.twig");
            echo $template->render(["breadcrumb" => $menu, "chemin" => $chemin, "errors" => $errors]);
        }
        else{
            $this->annonce = Annonce::find($id);
            $idannonceur = $this->annonce->id_annonceur;
            $this->annonceur = Annonceur::find($idannonceur);

            $this->annonceur->email         = htmlentities($email);
            $this->annonceur->nom_annonceur = htmlentities($nom);
            $this->annonceur->telephone     = htmlentities($phone);
            
            $this->annonce->ville          = htmlentities($ville);
            $this->annonce->id_departement = (int)$departement;
            $this->annonce->prix           = (float)$price;
            
            if (!empty($password)) {
                $this->annonce->mdp        = password_hash($password, PASSWORD_DEFAULT);
            }
            
            $this->annonce->titre          = htmlentities($title);
            $this->annonce->description    = htmlentities($description);
            $this->annonce->id_categorie   = (int)$categorie;
            $this->annonce->date           = date('Y-m-d');
            
            $this->annonceur->save();
            $this->annonceur->annonce()->save($this->annonce);


            $template = $twig->load("modif-confirm.html.twig");
            echo $template->render(["breadcrumb" => $menu, "chemin" => $chemin]);
        }
    }
}
