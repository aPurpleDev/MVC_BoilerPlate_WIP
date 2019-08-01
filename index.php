<?php

// Chargement de l'autoload de classes de Composer.
require './vendor/autoload.php';

// Chargement du fichier de configuration de l'application.
require 'config.php';


// Fonction exécutant une redirection HTTP vers une autre route.
function redirect_to_route($route)
{
    if($route == '_default')
    {
        $url = null;
    }
    else
    {
        if(CONFIG_URL_REWRITE == true)
        {
            $url = "/$route";
        }
        else
        {
            $url = "/index.php?r=$route";
        }
    }

    header('Location: http://'.$_SERVER['HTTP_HOST'].URL_ROOT.$url);
    exit();
}

// Fonction affichant une vue.
function render_view($view, array $app)
{
    if(is_array($app['view-data']) == true)
    {
        // Création des variables de la vue.
        extract($app['view-data'], EXTR_SKIP);
    }

    // Affichage de la vue spécifiée.
    require DIR_VIEWS."/$view";
}

// Fonction traduisant le nom d'une route en une URL.
function route_to_url($route)
{
    return CONFIG_URL_REWRITE == true ? URL_ROOT."/$route" : URL_ROOT."/index.php?r=$route";
}



// --------------------------------------------------------------------------------------------------------------------
// CODE PRINCIPAL
// --------------------------------------------------------------------------------------------------------------------

/*
 * Création du conteneur de l'application contenant toutes les informations nécessaires pour l'exécution.
 *
 * - $app['controller']         : instance du contrôleur
 * - $app['controller-class']   : nom complet de la classe du contrôleur
 * - $app['controller-method']  : nom de la méthode du contrôleur
 * - $app['route']              : nom de la route
 * - $app['route-match']        : informations de la route (nom du contrôleur, de la vue, etc)
 * - $app['view']               : nom du fichier de la vue
 * - $app['view-data']          : données renvoyées par le contrôleur transmis à chaque vue
 *
 * Ces informations sont enregistrées les unes après les autres par le code ci-dessous.
 */
$app = [];


/*
 * Recherche si une route a été spécifiée dans l'URL et si elle est bien implémentée par l'application.
 * Utilisation de la route '_default' si au moins l'une des deux conditions n'est pas remplie.
 */
$app['route'] = array_key_exists('r', $_GET) ? $_GET['r'] : '_default';
$app['route'] = array_key_exists($app['route'], $config['mvc.routes']) ? $app['route'] : '_default';

if(array_key_exists($app['route'], $config['mvc.routes']) == false)
{
    die("ERREUR FATALE : table des routes incorrecte, aucune correspondance pour la route '{$app['route']}'.");
}

// Enregistrement des informations de la route.
$app['route-match'] = $config['mvc.routes'][$app['route']];

if(array_key_exists('controller', $app['route-match']) == false)
{
    die("ERREUR FATALE : table des routes incorrecte, pas de contrôleur spécifié pour la route '{$app['route']}'.");
}

// Enregistrement du nom de la classe du contrôleur.
$app['controller-class'] = $config['app.namespace'].'\\Controller\\'.$app['route-match']['controller'];

// Création d'une instance du contrôleur spécifié par la route.
$app['controller'] = new $app['controller-class']($app);

// Sélection de la méthode du contrôleur qui va être appelée.
$app['controller-method'] = $_SERVER['REQUEST_METHOD'] == 'GET' ? 'httpGetRequest' : 'httpPostRequest';

if(method_exists($app['controller'], $app['controller-method']) == false)
{
    die
    (
        "ERREUR FATALE : requête HTTP de type '".$_SERVER['REQUEST_METHOD']."' ".
        "mais il n'y a pas de méthode correspondante dans le contrôleur '".get_class($app['controller'])."'."
    );
}

// Appel de la méthode du contrôleur et stockage du résultat éventuel pour transmission à chaque vue.
$app['view-data'] = call_user_func([ $app['controller'], $app['controller-method'] ]);

if(array_key_exists('view', $app['route-match']) == false)
{
    die("ERREUR FATALE : table des routes incorrecte, pas de vue spécifiée pour la route '{$app['route']}'.");
}

// Enregistrement du nom de fichier de la vue qui va être affichée.
$app['view'] = $app['route-match']['view'];

if(array_key_exists('view-layout', $app['route-match']) == true && $app['route-match']['view-layout'] === false)
{
    // Affichage direct de la vue (sans passer par le layout).
    render_view($app['view'], $app);
}
else
{
    // Affichage de la vue en commençant d'abord par le layout.
    render_view('layout.phtml', $app);
}
