<?php

namespace App\Controller;

use App\Model\Annonce;
use App\Model\Categorie;

class SearchController {

    public function show($twig, $menu, string $chemin, $cat): void {
        $template = $twig->load("search.html.twig");
        $menu = [['href' => $chemin, 'text' => 'Acceuil'], ['href' => $chemin."/search", 'text' => "Recherche"]];
        echo $template->render(["breadcrumb" => $menu, "chemin" => $chemin, "categories" => $cat]);
    }

    public function research(array $array, $twig, $menu, string $chemin, $cat): void {
        $template = $twig->load("index.html.twig");
        $menu = [['href' => $chemin, 'text' => 'Acceuil'], ['href' => $chemin."/search", 'text' => "Résultats de la recherche"]];

        $motClef    = trim((string)($array['motclef'] ?? ''));
        $codePostal = trim((string)($array['codepostal'] ?? ''));
        $categorie  = trim((string)($array['categorie'] ?? ''));
        $prixMin    = trim((string)($array['prix-min'] ?? ''));
        $prixMax    = trim((string)($array['prix-max'] ?? ''));

        $query = Annonce::query();

        if ($motClef !== '') {
            //recherche par mot clef (description ou titre pour que ça ait plus de sens)
            $query->where(function($q) use ($motClef) {
                $q->where('description', 'like', '%' . $motClef . '%')
                  ->orWhere('titre', 'like', '%' . $motClef . '%');
            });
        }

        if ($codePostal !== '') {
            $query->where('ville', '=', $codePostal);
        }

        //on cible que si la catégorie est pertinente (sans requête db inutile)
        if ($categorie !== "Toutes catégories" && $categorie !== "-----" && is_numeric($categorie)) {
            $query->where('id_categorie', '=', (int)$categorie);
        }

        if ($prixMin !== "Min" && is_numeric($prixMin)) {
            $query->where('prix', '>=', (float)$prixMin);
        }

        if ($prixMax !== "Max" && $prixMax !== "nolimit" && is_numeric($prixMax)) {
            $query->where('prix', '<=', (float)$prixMax);
        }

        $annonce = $query->get();

        echo $template->render(["breadcrumb" => $menu, "chemin" => $chemin, "annonces" => $annonce, "categories" => $cat]);
    }

}

?>
