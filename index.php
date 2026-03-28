<?php
require __DIR__ . '/vendor/autoload.php';

use App\Controller\CategoryController;
use App\Controller\DepartmentController;
use App\Controller\HomeController;
use App\Controller\ItemController;
use App\Controller\AddController;
use App\Controller\SearchController;
use App\Controller\AnnonceurController;
use App\Db\connection;

use App\Model\Annonce;
use App\Model\Categorie;
use App\Model\Annonceur;
use App\Model\Departement;
use Slim\Factory\AppFactory;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;


connection::createConn();

$logger = new Logger('http_logger');
$logger->pushHandler(new StreamHandler(__DIR__ . '/logs/app.log', Logger::INFO));

// Initialisation de Slim
$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$loader = new FilesystemLoader(__DIR__ . '/template');
$twig   = new Environment($loader);

$app->add(function (Request $request, $handler) use ($logger) {
    $method = $request->getMethod();
    $path = $request->getUri()->getPath();
    $ip = $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown';
    
    $logger->info("Reçu: $method $path depuis $ip");
    
    $response = $handler->handle($request);
    
    $status = $response->getStatusCode();
    $logger->info("Réponse: $status pour $method $path");
    
    return $response;
});

$app->add(function ($request, $handler) {
    ob_start();
    $response = $handler->handle($request);
    $output = ob_get_clean();
    if ($output) {
        $response->getBody()->write($output);
    }
    return $response;
});

$app->add(function (Request $request, $handler) {
    $uri = $request->getUri();
    $path = $uri->getPath();
    
    if ($path !== '/' && str_ends_with($path, '/')) {
        $uri = $uri->withPath(substr($path, 0, -1));
        if ($request->getMethod() === 'GET') {
            $response = new \Slim\Psr7\Response();
            return $response->withHeader('Location', (string)$uri)->withStatus(301);
        } else {
            $request = $request->withUri($uri);
        }
    }
    
    return $handler->handle($request);
});


if (!isset($_SESSION)) {
    session_start();
    $_SESSION['formStarted'] = true;
}

if (!isset($_SESSION['token'])) {
    $token                  = md5(uniqid(random_int(0, mt_getrandmax()), TRUE));
    $_SESSION['token']      = $token;
    $_SESSION['token_time'] = time();
} else {
    $token = $_SESSION['token'];
}

$menu = [
    [
        'href' => './index.php',
        'text' => 'Accueil'
    ]
];

$chemin = dirname((string) $_SERVER['SCRIPT_NAME']);

$cat = new CategoryController();
$dpt = new DepartmentController();

$app->get('/', function () use ($twig, $menu, $chemin, $cat): void {
    $index = new HomeController();
    $index->displayAllAnnonce($twig, $menu, $chemin, $cat->getCategories());
});

$app->get('/item/{n}', function ($request, $response, array $arg) use ($twig, $menu, $chemin, $cat): void {
    $n     = $arg['n'];
    $item = new ItemController();
    $item->afficherItem($twig, $menu, $chemin, $n, $cat->getCategories());
});

$app->get('/add', function () use ($twig, $app, $menu, $chemin, $cat, $dpt): void {
    $ajout = new AddController();
    $ajout->addItemView($twig, $menu, $chemin, $cat->getCategories(), $dpt->getAllDepartments());
});

$app->post('/add', function ($request) use ($twig, $app, $menu, $chemin): void {
    $allPostVars = $request->getParsedBody();
    $ajout       = new AddController();
    $ajout->addNewItem($twig, $menu, $chemin, $allPostVars);
});

$app->get('/item/{id}/edit', function ($request, $response, array $arg) use ($twig, $menu, $chemin): void {
    $id   = $arg['id'];
    $item = new ItemController();
    $item->modifyGet($twig, $menu, $chemin, $id);
});
$app->post('/item/{id}/edit', function ($request, $response, array $arg) use ($twig, $app, $menu, $chemin, $cat, $dpt): void {
    $id          = $arg['id'];
    $allPostVars = $request->getParsedBody();
    $item        = new ItemController();
    $item->modifyPost($twig, $menu, $chemin, $id, $allPostVars, $cat->getCategories());
});

$app->map(['GET', 'POST'], '/item/{id}/confirm', function ($request, $response, array $arg) use ($twig, $app, $menu, $chemin): void {
    $id   = $arg['id'];
    $allPostVars = $request->getParsedBody();
    $item        = new ItemController();
    $item->edit($twig, $menu, $chemin, $id, $allPostVars);
});

$app->get('/search', function () use ($twig, $menu, $chemin, $cat): void {
    $s = new SearchController();
    $s->show($twig, $menu, $chemin, $cat->getCategories());
});


$app->post('/search', function ($request, $response) use ($app, $twig, $menu, $chemin, $cat): void {
    $array = $request->getParsedBody();
    $s     = new SearchController();
    $s->research($array, $twig, $menu, $chemin, $cat->getCategories());

});

$app->get('/annonceur/{n}', function ($request, $response, array $arg) use ($twig, $menu, $chemin, $cat): void {
    $n         = $arg['n'];
    $annonceur = new AnnonceurController();
    $annonceur->afficherAnnonceur($twig, $menu, $chemin, $n, $cat->getCategories());
});

$app->get('/del/{n}', function ($request, $response, array $arg) use ($twig, $menu, $chemin): void {
    $n    = $arg['n'];
    $item = new ItemController();
    $item->supprimerItemGet($twig, $menu, $chemin, $n);
});

$app->post('/del/{n}', function ($request, $response, array $arg) use ($twig, $menu, $chemin, $cat): void {
    $n    = $arg['n'];
    $item = new ItemController();
    $item->supprimerItemPost($twig, $menu, $chemin, $n, $cat->getCategories());
});

$app->get('/cat/{n}', function ($request, $response, array $arg) use ($twig, $menu, $chemin, $cat): void {
    $n = $arg['n'];
    $categorie = new CategoryController();
    $categorie->displayCategorie($twig, $menu, $chemin, $cat->getCategories(), $n);
});

$app->get('/api(/)', function () use ($twig, $menu, $chemin, $cat): void {
    $template = $twig->load('api.html.twig');
    $menu     = [['href' => $chemin, 'text' => 'Acceuil'], ['href' => $chemin . '/api', 'text' => 'Api']];
    echo $template->render(['breadcrumb' => $menu, 'chemin' => $chemin]);
});

$app->group('/api', function () use ($app, $twig, $menu, $chemin, $cat): void {

    $app->group('/annonce', function () use ($app): void {

        $app->get('/{id}', function ($request, $response, array $arg) use ($app): void {
            $id          = $arg['id'];
            $annonceList = ['id_annonce', 'id_categorie as categorie', 'id_annonceur as annonceur', 'id_departement as departement', 'prix', 'date', 'titre', 'description', 'ville'];
            $return      = Annonce::select($annonceList)->find($id);

            if (isset($return)) {
                $response->headers->set('Content-Type', 'application/json');
                $return->categorie     = Categorie::find($return->categorie);
                $return->annonceur     = Annonceur::select('email', 'nom_annonceur', 'telephone')
                    ->find($return->annonceur);
                $return->departement   = Departement::select('id_departement', 'nom_departement')->find($return->departement);
                $links                 = [];
                $links['self']['href'] = '/api/annonce/' . $return->id_annonce;
                $return->links         = $links;
                echo $return->toJson();
            } else {
                throw new \Slim\Exception\HttpNotFoundException($request);
            }
        });
    });

    $app->group('/annonces(/)', function () use ($app): void {

        $app->get('/', function ($request, $response) use ($app): void {
            $annonceList = ['id_annonce', 'prix', 'titre', 'ville'];
            $response->headers->set('Content-Type', 'application/json');
            $a     = Annonce::all($annonceList);
            $links = [];
            foreach ($a as $ann) {
                $links['self']['href'] = '/api/annonce/' . $ann->id_annonce;
                $ann->links            = $links;
            }
            $links['self']['href'] = '/api/annonces/';
            $a->links              = $links;
            echo $a->toJson();
        });
    });


    $app->group('/categorie', function () use ($app): void {

        $app->get('/{id}', function ($request, $response, array $arg) use ($app): void {
            $id = $arg['id'];
            $response->headers->set('Content-Type', 'application/json');
            $a     = Annonce::select('id_annonce', 'prix', 'titre', 'ville')
                ->where('id_categorie', '=', $id)
                ->get();
            $links = [];

            foreach ($a as $ann) {
                $links['self']['href'] = '/api/annonce/' . $ann->id_annonce;
                $ann->links            = $links;
            }

            $c                     = Categorie::find($id);
            $links['self']['href'] = '/api/categorie/' . $id;
            $c->links              = $links;
            $c->annonces           = $a;
            echo $c->toJson();
        });
    });

    $app->group('/categories(/)', function () use ($app): void {
        $app->get('/', function ($request, $response, $arg) use ($app): void {
            $response->headers->set('Content-Type', 'application/json');
            $c     = Categorie::get();
            $links = [];
            foreach ($c as $cat) {
                $links['self']['href'] = '/api/categorie/' . $cat->id_categorie;
                $cat->links            = $links;
            }
            $links['self']['href'] = '/api/categories/';
            $c->links              = $links;
            echo $c->toJson();
        });
    });

    $app->get('/key', function () use ($app, $twig, $menu, $chemin, $cat): void {
        $kg = new App\Controller\ApiKeyController();
        $kg->show($twig, $menu, $chemin, $cat->getCategories());
    });

    $app->post('/key', function () use ($app, $twig, $menu, $chemin, $cat): void {
        $nom = $_POST['nom'];

        $kg = new App\Controller\ApiKeyController();
        $kg->generateKey($twig, $menu, $chemin, $cat->getCategories(), $nom);
    });
});

$app->run();
