<?php session_start();
use Doctrine\DBAL\Connection;
use Silex\Application;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
if (php_sapi_name() === 'cli-server' && is_file(__DIR__ . preg_replace('/(\?.*)$/', '', $_SERVER['REQUEST_URI']))) {
    return false;
}

require_once __DIR__.'/../vendor/autoload.php';
Request::enableHttpMethodParameterOverride();
$sapp = (new Application(['debug' => true]))
    ->register(new TwigServiceProvider(),
        ['twig.path' => __DIR__ . '\..\view'])
    ->register(new DoctrineServiceProvider(),
        ['db.options' => 
		['driver' => 'pdo_mysql', 
		'host'      => 'localhost',
		'dbname' => 'trainingcontrol',
		'user'      => 'root',
		'password'  => '09044032',	
		'charset' => 'utf8']]);
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$sapp->get('/', function (Application $app) {
    /**@var $conn Connection */
	return $app['twig']->render('login.twig');
});
$sapp->post('/go', function (Application $app,  Request $req) {
    /**@var $conn Connection */
	$conn = $app['db'];
    	$FIO = $req->get('FIO');
    	$Number = $req->get('number');
		
	$id_trainer = $conn->fetchColumn('select idTrainer from trainer where personal_id = ?', [$Number]);
	$FIO_trainer = $conn->fetchColumn('select FIO from trainer where personal_id = ?', [$Number]);
	
	
	if (!$id_trainer) {
		
            return $app->redirect("/login.php/$id_trainer");
		

    }
	if($FIO_trainer==$FIO)
	{
		return $app->redirect("/index.php/$id_trainer/trainer");
	}
	return $app->redirect("/login.php/");
});
//////////////////////////////////////////////////////////////////////////////////////////////////////
$sapp->run();
