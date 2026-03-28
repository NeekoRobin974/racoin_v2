<?php

namespace App\Controller;

use App\Model\ApiKey;

class ApiKeyController {

    function show($twig, $menu, string $chemin, $cat): void {
        $template = $twig->load("key-generator.html.twig");
        $menu = [['href' => $chemin, 'text' => 'Acceuil'], ['href' => $chemin."/search", 'text' => "Recherche"]];
        echo $template->render(["breadcrumb" => $menu, "chemin" => $chemin, "categories" => $cat]);
    }

    function generateKey($twig, $menu, string $chemin, $cat, $nom): void {
        $nospace_nom = str_replace(' ', '', $nom);

        if($nospace_nom === '') {
            $template = $twig->load("key-generator-error.html.twig");
            $menu = [['href' => $chemin, 'text' => 'Acceuil'], ['href' => $chemin."/search", 'text' => "Recherche"]];

            echo $template->render(["breadcrumb" => $menu, "chemin" => $chemin, "categories" => $cat]);
        } else {
            $template = $twig->load("key-generator-result.html.twig");
            $menu = [['href' => $chemin, 'text' => 'Acceuil'], ['href' => $chemin."/search", 'text' => "Recherche"]];

            // Génere clé unique de 13 caractères
            $key = uniqid();
            // Ajouter clé dans la base
            $apikey = new ApiKey();

            $apikey->id_apikey = $key;
            $apikey->name_key = htmlentities((string) $nom);
            $apikey->save();

            echo $template->render(["breadcrumb" => $menu, "chemin" => $chemin, "categories" => $cat, "key" => $key]);
        }

    }

}

?>
