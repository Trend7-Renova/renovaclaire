<?php
add_filter('timber/twig', function ($twig) {
    // Adding a function to Twig named 'svg'
    $twig->addFunction(new \Twig\TwigFunction('capcha', function () {

        print_r(tirerItemsDepuisJson());
        return '';
    }));
    return $twig;
});


function tirerItemsDepuisJson()
{

    $cheminFichierJson = __DIR__ . '/../capcha.json';
    if (!file_exists($cheminFichierJson)) {
        throw new Exception("Fichier JSON introuvable");
    }

    $contenu = file_get_contents($cheminFichierJson);
    $data = json_decode($contenu, true);

    if (!isset($data['categories']) || !is_array($data['categories'])) {
        throw new Exception("Format JSON invalide");
    }

    $categories = $data['categories'];

    // Choix d'une catégorie cible au hasard
    $categorieCible = $categories[array_rand($categories)];

    // 1 item au hasard dans la catégorie cible
    $itemCible = $categorieCible['items'][array_rand($categorieCible['items'])];

    $autresItems = [];

    foreach ($categories as $categorie) {
        if ($categorie['slug'] === $categorieCible['slug']) {
            continue;
        }

        if (count($categorie['items']) >= 2) {
            $cles = array_rand($categorie['items'], 2);
            foreach ($cles as $cle) {
                $autresItems[] = [
                    'categorie' => $categorie['slug'],
                    'item' => $categorie['items'][$cle]
                ];
            }
        }
    }

    return [
        'categorie_cible' => [
            'nom'  => $categorieCible['nom'],
            'slug' => $categorieCible['slug']
        ],
        'item_cible' => $itemCible,
        'items' => $autresItems + [$itemCible]
    ];
}
