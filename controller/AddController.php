<?php

namespace App\Controller;

use App\Model\Annonce;
use App\Model\Annonceur;

class AddController
{

    function addItemView($twig, $menu, $chemin, $cat, $dpt): void
    {
        $template = $twig->load("add.html.twig");
        echo $template->render(["breadcrumb"   => $menu, "chemin"       => $chemin, "categories"   => $cat, "departements" => $dpt]
        );

    }

    private function isEmail(string $email): int|false
    {
        return (preg_match("/^[-_.[:alnum:]]+@((([[:alnum:]]|[[:alnum:]][[:alnum:]-]*[[:alnum:]])\.)+(ad|ae|aero|af|ag|ai|al|am|an|ao|aq|ar|arpa|as|at|au|aw|az|ba|bb|bd|be|bf|bg|bh|bi|biz|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|com|coop|cr|cs|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|eh|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gh|gi|gl|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|in|info|int|io|iq|ir|is|it|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|mg|mh|mil|mk|ml|mm|mn|mo|mp|mq|mr|ms|mt|mu|museum|mv|mw|mx|my|mz|na|name|nc|ne|net|nf|ng|ni|nl|no|np|nr|nt|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|pro|ps|pt|pw|py|qa|re|ro|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tf|tg|th|tj|tk|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|uk|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw)$|(([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5])\.){3}([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5]))$/i", $email));
    }

    function addNewItem($twig, $menu, $chemin, array $allPostVars): void
    {

        date_default_timezone_set('Europe/Paris');

        /*
        * On récupère tous les champs du formulaire en supprimant
        * les caractères invisibles en début et fin de chaîne.
        */
        $nom              = trim((string) $_POST['nom']);
        $email            = trim((string) $_POST['email']);
        $phone            = trim((string) $_POST['phone']);
        $ville            = trim((string) $_POST['ville']);
        $departement      = trim((string) $_POST['departement']);
        $categorie        = trim((string) $_POST['categorie']);
        $title            = trim((string) $_POST['title']);
        $description      = trim((string) $_POST['description']);
        $price            = trim((string) $_POST['price']);
        $password         = trim((string) $_POST['psw']);
        $password_confirm = trim((string) $_POST['confirm-psw']);

        // Tableau d'erreurs personnalisées
        $errors                          = [];
        $errors['nameAdvertiser']        = '';
        $errors['emailAdvertiser']       = '';
        $errors['phoneAdvertiser']       = '';
        $errors['villeAdvertiser']       = '';
        $errors['departmentAdvertiser']  = '';
        $errors['categorieAdvertiser']   = '';
        $errors['titleAdvertiser']       = '';
        $errors['descriptionAdvertiser'] = '';
        $errors['priceAdvertiser']       = '';
        $errors['passwordAdvertiser']    = '';

        // On teste que les champs ne soient pas vides et soient de bons types
        if ($nom === '' || $nom === '0') {
            $errors['nameAdvertiser'] = 'Veuillez entrer votre nom';
        }
        if (!$this->isEmail($email)) {
            $errors['emailAdvertiser'] = 'Veuillez entrer une adresse mail correcte';
        }
        if (($phone === '' || $phone === '0') && !is_numeric($phone)) {
            $errors['phoneAdvertiser'] = 'Veuillez entrer votre numéro de téléphone';
        }
        if ($ville === '' || $ville === '0') {
            $errors['villeAdvertiser'] = 'Veuillez entrer votre ville';
        }
        if (!is_numeric($departement)) {
            $errors['departmentAdvertiser'] = 'Veuillez choisir un département';
        }
        if (!is_numeric($categorie)) {
            $errors['categorieAdvertiser'] = 'Veuillez choisir une catégorie';
        }
        if ($title === '' || $title === '0') {
            $errors['titleAdvertiser'] = 'Veuillez entrer un titre';
        }
        if ($description === '' || $description === '0') {
            $errors['descriptionAdvertiser'] = 'Veuillez entrer une description';
        }
        if ($price === '' || $price === '0' || !is_numeric($price)) {
            $errors['priceAdvertiser'] = 'Veuillez entrer un prix';
        }
        if ($password === '' || $password === '0' || ($password_confirm === '' || $password_confirm === '0') || $password !== $password_confirm) {
            $errors['passwordAdvertiser'] = 'Les mots de passes ne sont pas identiques';
        }

        // On vire les cases vides
        $errors = array_values(array_filter($errors));

        // S'il y a des erreurs on redirige vers la page d'erreur
        if ($errors !== []) {

            $template = $twig->load("add-error.html.twig");
            echo $template->render(["breadcrumb" => $menu, "chemin"     => $chemin, "errors"     => $errors]
            );
        } // sinon on ajoute à la base et on redirige vers une page de succès
        else {
            $annonce   = new Annonce();
            $annonceur = new Annonceur();

            $annonceur->email         = htmlentities((string) $allPostVars['email']);
            $annonceur->nom_annonceur = htmlentities((string) $allPostVars['nom']);
            $annonceur->telephone     = htmlentities((string) $allPostVars['phone']);

            $annonce->ville          = htmlentities((string) $allPostVars['ville']);
            $annonce->id_departement = $allPostVars['departement'];
            $annonce->prix           = htmlentities((string) $allPostVars['price']);
            $annonce->mdp            = password_hash((string) $allPostVars['psw'], PASSWORD_DEFAULT);
            $annonce->titre          = htmlentities((string) $allPostVars['title']);
            $annonce->description    = htmlentities((string) $allPostVars['description']);
            $annonce->id_categorie   = $allPostVars['categorie'];
            $annonce->date           = date('Y-m-d');


            $annonceur->save();
            $annonceur->annonce()->save($annonce);


            $template = $twig->load("add-confirm.html.twig");
            echo $template->render(["breadcrumb" => $menu, "chemin" => $chemin]);
        }
    }
}
