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

    function modifyPost($twig, $menu, $chemin, $n, $cat, $dpt): void{
        $this->annonce = Annonce::find($n);
        $this->annonceur = Annonceur::find($this->annonce->id_annonceur);
        $this->categItem = Categorie::find($this->annonce->id_categorie)->nom_categorie;
        $this->dptItem = Departement::find($this->annonce->id_departement)->nom_departement;

        $reponse = false;
        if(password_verify((string) $_POST["pass"],$this->annonce->mdp)){
            $reponse = true;

        }

        $template = $twig->load("modifyPost.html.twig");
        echo $template->render(["breadcrumb" => $menu, "chemin" => $chemin, "annonce" => $this->annonce, "annonceur" => $this->annonceur, "pass" => $reponse, "categories" => $cat, "departements" => $dpt, "dptItem" => $this->dptItem, "categItem" => $this->categItem]);
    }

    function edit($twig, $menu, $chemin, array $allPostVars, $id): void{

        date_default_timezone_set('Europe/Paris');

        function isEmail($email): int|false {
            return(preg_match("/^[-_.[:alnum:]]+@((([[:alnum:]]|[[:alnum:]][[:alnum:]-]*[[:alnum:]])\.)+(ad|ae|aero|af|ag|ai|al|am|an|ao|aq|ar|arpa|as|at|au|aw|az|ba|bb|bd|be|bf|bg|bh|bi|biz|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|com|coop|cr|cs|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|eh|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gh|gi|gl|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|in|info|int|io|iq|ir|is|it|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|mg|mh|mil|mk|ml|mm|mn|mo|mp|mq|mr|ms|mt|mu|museum|mv|mw|mx|my|mz|na|name|nc|ne|net|nf|ng|ni|nl|no|np|nr|nt|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|pro|ps|pt|pw|py|qa|re|ro|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tf|tg|th|tj|tk|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|uk|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw)$|(([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5])\.){3}([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5]))$/i", (string) $email));
        }

        /*
        * On récupère tous les champs du formulaire en supprimant
        * les caractères invisibles en début et fin de chaîne.
        */
        $nom = trim((string) $_POST['nom']);
        $email = trim((string) $_POST['email']);
        $phone = trim((string) $_POST['phone']);
        $ville = trim((string) $_POST['ville']);
        $departement = trim((string) $_POST['departement']);
        $categorie = trim((string) $_POST['categorie']);
        $title = trim((string) $_POST['title']);
        $description = trim((string) $_POST['description']);
        $price = trim((string) $_POST['price']);


        // Tableau d'erreurs personnalisées
        $errors = [];
        $errors['nameAdvertiser'] = '';
        $errors['emailAdvertiser'] = '';
        $errors['phoneAdvertiser'] = '';
        $errors['villeAdvertiser'] = '';
        $errors['departmentAdvertiser'] = '';
        $errors['categorieAdvertiser'] = '';
        $errors['titleAdvertiser'] = '';
        $errors['descriptionAdvertiser'] = '';
        $errors['priceAdvertiser'] = '';


        // On teste que les champs ne soient pas vides et soient de bons types
        if($nom === '' || $nom === '0') {
            $errors['nameAdvertiser'] = 'Veuillez entrer votre nom';
        }
        if(!isEmail($email)) {
            $errors['emailAdvertiser'] = 'Veuillez entrer une adresse mail correcte';
        }
        if(($phone === '' || $phone === '0') && !is_numeric($phone) ) {
            $errors['phoneAdvertiser'] = 'Veuillez entrer votre numéro de téléphone';
        }
        if($ville === '' || $ville === '0') {
            $errors['villeAdvertiser'] = 'Veuillez entrer votre ville';
        }
        if(!is_numeric($departement)) {
            $errors['departmentAdvertiser'] = 'Veuillez choisir un département';
        }
        if(!is_numeric($categorie)) {
            $errors['categorieAdvertiser'] = 'Veuillez choisir une catégorie';
        }
        if($title === '' || $title === '0') {
            $errors['titleAdvertiser'] = 'Veuillez entrer un titre';
        }
        if($description === '' || $description === '0') {
            $errors['descriptionAdvertiser'] = 'Veuillez entrer une description';
        }
        if($price === '' || $price === '0' || !is_numeric($price)) {
            $errors['priceAdvertiser'] = 'Veuillez entrer un prix';
        }

        // On vire les cases vides
        $errors = array_values(array_filter($errors));

        // S'il y a des erreurs on redirige vers la page d'erreur
        if ($errors !== []) {

            $template = $twig->load("add-error.html.twig");
            echo $template->render(["breadcrumb" => $menu, "chemin" => $chemin, "errors" => $errors]
            );
        }
        // sinon on ajoute à la base et on redirige vers une page de succès
        else{
            $this->annonce = Annonce::find($id);
            $idannonceur = $this->annonce->id_annonceur;
            $this->annonceur = Annonceur::find($idannonceur);


            $this->annonceur->email = htmlentities((string) $allPostVars['email']);
            $this->annonceur->nom_annonceur = htmlentities((string) $allPostVars['nom']);
            $this->annonceur->telephone = htmlentities((string) $allPostVars['phone']);
            $this->annonce->ville = htmlentities((string) $allPostVars['ville']);
            $this->annonce->id_departement = $allPostVars['departement'];
            $this->annonce->prix = htmlentities((string) $allPostVars['price']);
            $this->annonce->mdp = password_hash ((string) $allPostVars['psw'], PASSWORD_DEFAULT);
            $this->annonce->titre = htmlentities((string) $allPostVars['title']);
            $this->annonce->description = htmlentities((string) $allPostVars['description']);
            $this->annonce->id_categorie = $allPostVars['categorie'];
            $this->annonce->date = date('Y-m-d');
            $this->annonceur->save();
            $this->annonceur->annonce()->save($this->annonce);


            $template = $twig->load("modif-confirm.html.twig");
            echo $template->render(["breadcrumb" => $menu, "chemin" => $chemin]);
        }
    }
}
