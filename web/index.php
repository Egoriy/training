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
		
		
        
        
        /// получает список всех клиентов тренера, если нет клиентов, то перенаправляет на добавление, 
		//если нет id_trener, значит вход в систему не произведен, получаем перенаправление на Login.php
		//посредством маршрута /clientAdd/
$sapp->get('/', function (Application $app) {

    /**@var $conn Connection */
    $conn = $app['db'];
	$id_trainer = $_SESSION['idTrainer'];
	$trainer = $conn->fetchColumn('select FIO from trainer where idTrainer = ?', [$id_trainer]);

	//$id=1;
    $clients = $conn->fetchAll('select * from client where idTrainer = ?', [$id_trainer]);
	
	if (!$clients) 
	{
		return $app->redirect("/index.php/clientAdd/$id_trainer");
	}
	
    return $app['twig']->render('clients.twig', ['clients' => $clients, 'id_trainer'=>$id_trainer, 'trainer'=>$trainer]);
});
//запоминает id тренера в сессии, перенаправление на главную страницу
$sapp->get('/{idTrainer}/trainer', function (Application $app, $idTrainer) {
    /**@var $conn Connection */
    $conn = $app['db'];
	$_SESSION['idTrainer'] = "$idTrainer";
    return $app->redirect("/index.php/");
});
//завершение сессии пользователя, по нажатии кнопки выход
$sapp->get('/endSession', function (Application $app) {
    /**@var $conn Connection */
    session_destroy();
    return $app->redirect("/login.php/");
});
//попытка добавить клиента к тренеру без id, приводит к перенаправлению на login.php
$sapp->get('/clientAdd/', function (Application $app) {
    /**@var $conn Connection */
    return $app->redirect("/login.php/");
});
//функция замыкания, для вывода информации о клиенте, если нет программы, 
//получаем перенаправление на добавление новой программы
$sapp->get('/client/{id_client}', function (Application $app, $id_client) {
    /**@var $conn Connection */
    $conn = $app['db'];
	$id_trainer = $_SESSION['idTrainer'];
	$trainer = $conn->fetchColumn('select FIO from trainer where idTrainer = ?', [$id_trainer]);
	
	//$id = 1;
    $programs = $conn->fetchAll('select * from program where id_client = ?', [$id_client]);
	
	$client = $conn->fetchColumn('select FIO from client where id_client = ?', [$id_client]);
	if (!$programs) 
	{
		return $app->redirect("/index.php/add_programm/$id_client");
	}
	
    return $app['twig']->render('programs.twig', ['programs' => $programs, 'id_client'=>$id_client, 'client'=>$client, 'id_trainer'=>$id_trainer, 'trainer'=>$trainer]);
});
		
        /// функция обработки маршрута.
		///	функция обработки маршрута, служит для вызова представления общей информации о программе, 
		//если программа минимально не заполнена, получаем перенаправление на добавление новой
        
$sapp->get('/program/{id_prog}', function (Application $app, $id_prog) {
    /**@var $conn Connection */
    $conn = $app['db'];
	
	$days = $conn->fetchAssoc('select * from currentday where id_prog = ?', [$id_prog]);
	$id=$conn->fetchColumn('select idCurrentDay from currentday where id_prog = ?', [$id_prog]);
    $exercises = $conn->fetchAll('select * from exercise where idCurrentDay = ?', [$id]);
	$days = $conn->fetchAll('select * from currentday where id_prog = ?', [$id_prog]);
	$id_trainer = $_SESSION['idTrainer'];
	$trainer = $conn->fetchColumn('select FIO from trainer where idTrainer = ?', [$id_trainer]);

	
	if (!$days) 
	{
		return $app->redirect("/index.php/add_days/$id_prog");
	}
	if (!$exercises) 
	{
        return $app->redirect("/index.php/add_exercise/$id_prog");
    }
    return $app['twig']->render('exercises.twig', ['exercises' => $exercises, 'days'=>$days,'day'=>$id, 'id_prog'=>$id_prog,'idTrainer'=>$id_trainer, 'trainer'=>$trainer]);
});
//служит для подробного описания выбранной общей программы, если таковых нет, получаем перенаправление на добавление
$sapp->get('/program_common/{id_prog}', function (Application $app, $id_prog) {
    /**@var $conn Connection */
    $conn = $app['db'];
	$days = $conn->fetchAssoc('select * from currentdaycatalog where id_program_catalog = ?', [$id_prog]);//!< Дни, относящиется к текущей программе
	$id=$conn->fetchColumn('select id_cuurentDK from currentdaycatalog where id_program_catalog = ?', [$id_prog]);
    $exercises = $conn->fetchAll('select * from exercisecatalog where id_cuurentDK = ?', [$id]);//!< Список упражнений 1 дня тренировок
	$days = $conn->fetchAll('select * from currentdaycatalog where id_program_catalog = ?', [$id_prog]);//!< Дни, относящиется к текущей программе
	$id_trainer = $_SESSION['idTrainer'];
	$trainer = $conn->fetchColumn('select FIO from trainer where idTrainer = ?', [$id_trainer]);

	if (!$days) 
	{
		return $app->redirect("/index.php/add_common_days/$id_prog");
	}
	if (!$exercises) 
	{
        return $app->redirect("/index.php/add_common_exercise/$id_prog");
    }
    return $app['twig']->render('common_exercise.twig', ['exercises' => $exercises, 'days'=>$days,'day'=>$id, 'id_prog'=>$id_prog, 'trainer'=>$trainer]);
});
//Служит для вывода списка всех упражнений и работы с ними
$sapp->get('/allexercise/', function (Application $app) {
    /**@var $conn Connection */
    $conn = $app['db'];
    $exercises = $conn->fetchAll('select * from notesexercises');
	
	$id_trainer = $_SESSION['idTrainer'];
	$trainer = $conn->fetchColumn('select FIO from trainer where idTrainer = ?', [$id_trainer]);
	
    return $app['twig']->render('allexercises.twig', ['exercises' => $exercises, 'trainer'=>$trainer]);
});
//упражнения текущего дня, выбранной программы, определенного клиента, используется в AJAX 
//в качестве источника данных
$sapp->get('/exercises/{idCurrentDay}', function ($id) use ($sapp) {
    /**@var $conn Connection */
    $conn = $sapp['db'];
	$exercises = $conn->fetchAll('select * from exercise where idCurrentDay = ?', [$id]);
	$client = $conn->fetchColumn('select FIO from client where id_client = ?', [$id_client]);
	$id_trainer = $_SESSION['idTrainer'];
	$trainer = $conn->fetchColumn('select FIO from trainer where idTrainer = ?', [$id_trainer]);

    return $sapp->json(array('exercises'=>$exercises, 'client'=>$client));
});

//служит для отображения и редактирования списка всех общих программ
$sapp->get('/allprogram/', function (Application $app) {
    /**@var $conn Connection */
    $conn = $app['db'];
	$id_trainer = $_SESSION['idTrainer'];
	$trainer = $conn->fetchColumn('select FIO from trainer where idTrainer = ?', [$id_trainer]);
	
    $clients = $conn->fetchAll('select * from client');
	$programs = $conn->fetchAll('select * from programcatalog');
	$progSelect = $conn->fetchAll('select * from programm_selector');
	
	if (!$clients) {
        return $app->redirect("/index.php/add_programm/$id_client");
    }
	if (!$programs) {
        return $app->redirect("/index.php/add_common_programm");
    }
    return $app['twig']->render('allprograms.twig', ['programs' => $programs , 'clients'=>$clients, 'progSelects'=>$progSelect, 'trainer'=>$trainer]);
});

// служит для передачи информации о дне тренировки AJAX скрипту ввиде json файла
$sapp->get('/program/{id_prog}/{id_day}', function ($id_prog,$id_day) use ($sapp) {
    /**@var $conn Connection */
    $conn = $sapp['db'];
	$exercises = $conn->fetchAll('select * from exercise where idCurrentDay = ?', [$id_day]);
	$days = $conn->fetchAll('select * from currentday where id_prog = ?', [$id_prog]);
	//$client = $conn->fetchColumn('select FIO from client where id_client = ?', [$id_client]);
	
    return $sapp->json(['exercises' => $exercises, 'days'=>$days, 'day'=>$id_day]);
});
//////////////////////////////////////////////////////////////////////////////////////////////////////
// служит для передачи информации о дне тренировки(для общих программ)
// AJAX скрипту ввиде json файла
$sapp->get('/common_program/{id_prog}/{id_day}', function ($id_prog,$id_day) use ($sapp) {
    /**@var $conn Connection */
    $conn = $sapp['db'];
	$exercises = $conn->fetchAll('select * from exercisecatalog where id_cuurentDK = ?', [$id_day]);
	$days = $conn->fetchAll('select * from currentdaycatalog where id_program_catalog = ?', [$id_prog]);
	
	
    return $sapp->json(['exercises' => $exercises, 'days'=>$days, 'day'=>$id_day]);
});
// служит для передачи информации о программе тренировки AJAX скрипту ввиде json файла
$sapp->get('/programm/{id_client}', function ($id_client) use ($sapp) {
    /**@var $conn Connection */
    $conn = $sapp['db'];
	
	$programs = $conn->fetchAll('select * from program where id_client = ?', [$id_client]);
	$client = $conn->fetchColumn('select FIO from client where id_client = ?', [$id_client]);
	
    return $sapp->json(['programs' => $programs, 'client'=>$client]);
});
//////////////////////////////////////////////////////////////////////////////////////////////////////
// служит для передачи информации о питании  AJAX скрипту ввиде json файла
$sapp->get('/nutritionj/{id_client}', function ($id_client) use ($sapp) {
    /**@var $conn Connection */
    $conn = $sapp['db'];
	$nutritions = $conn->fetchAll('select * from nutrition where id_client = ?', [$id_client]);
	$client = $conn->fetchColumn('select FIO from client where id_client = ?', [$id_client]);
    return $sapp->json(['nutritions' => $nutritions, 'client'=>$client]);
});
//////////////////////////////////////////////////////////////////////////////////////////////////////


//////////////////////////////////////////////////////////////////////////////////////////////////////
// служит для передачи информации о антропометрических данных клиента
// AJAX скрипту ввиде json файла
$sapp->get('/anthropometricj/{id_client}', function ($id_client) use ($sapp) {
    /**@var $conn Connection */
    $conn = $sapp['db'];
	$anthropometrics = $conn->fetchAll('select * from anthropometricdata where id_client = ?', [$id_client]);
	$client = $conn->fetchColumn('select FIO from client where id_client = ?', [$id_client]);
    return $sapp->json(['anthropometrics' => $anthropometrics, 'client'=>$client]);
});
//служит для вывода результатов тренировки по выбранному упражнению
$sapp->get('/Approach/{id_exercise}/{id_prog}', function (Application $app, $id_exercise, $id_prog) {
    /**@var $conn Connection */
    $conn = $app['db'];
	
    $approaches = $conn->fetchAll('select * from approach where idExercise = ? ',[$id_exercise]);
	$exercise = $conn->fetchColumn('select NameExercise from exercise where idExercise = ? ',[$id_exercise]);
	
	$dates=$conn->fetchAll('select * from calendarexercise order By id_calendar DESC');
	if (!$approaches) {
        echo "<script>alert('Клиент еще не заплнял подходы');</script>";
		
    }
	$id_trainer = $_SESSION['idTrainer'];
	$trainer = $conn->fetchColumn('select FIO from trainer where idTrainer = ?', [$id_trainer]);
	
	
    return $app['twig']->render('approaches.twig', ['approaches' => $approaches,'exerciseName'=>$exercise, 'trainer'=>$trainer, 'dates'=>$dates, 'exercise'=>$id_exercise]);
});
//////////////////////////////////////////////////////////////////////////////////////////////////////
//служит для перехода на страницу добавления клиента, текущему тренеру
$sapp->get('/clientAdd/{id}', function (Application $app, $id) {
    /**@var $conn Connection */
    $conn = $app['db'];
	$id_trainer = $_SESSION['idTrainer'];
	$trainer = $conn->fetchColumn('select FIO from trainer where idTrainer = ?', [$id_trainer]);

	
    return $app['twig']->render('add_client.twig', ['trainer' => $trainer, 'id' => $id_trainer, 'trainer'=>$trainer]);
});
//////////////////////////////////////////////////////////////////////////////////////////////////////
//служит для непосредственного добавления клиента текущему тренеру, если клиент 
//уже существует, то осуществляется переход на страницу добавления клиента
$sapp->post('/client/{id}', function (Application $app, Request $req, $id) {
	/**@var $conn Connection */
	$conn = $app['db'];
	$number = $req->get('number');
	$FIO = $req->get('FIO');
	$check=$conn->fetchAll('select * from client');
	$id_trainer = $_SESSION['idTrainer'];
	$trainer = $conn->fetchColumn('select FIO from trainer where idTrainer = ?', [$id_trainer]);

	foreach($check as &$Client)
	{
		if($Client['Clients_number']==$number)
			return $app->redirect("/index.php/clientAdd/$id");
	}
	$conn->insert('client', ['Clients_number' => $number, 'FIO'=>$FIO, 'idTrainer'=>$id_trainer]);
	return $app->redirect('/');
});
//////////////////////////////////////////////////////////////////////////////////////////////////////
//Добавление новых антропометрических данных клиента, 
//остальные данные автоматически помечаются как старые
$sapp->post('/anthropometrics/{id_client}', function (Application $app, Request $req, $id_client) {
	/**@var $conn Connection */
	$conn = $app['db'];
	$Height = $req->get('height');
	$Weight = $req->get('weight');
	$BicepsVol = $req->get('BicepsVol');
	$WaistVol = $req->get('WaistVol');
	$hipsVol = $req->get('hipsVol');
	$BustVol = $req->get('BustVol');
	$Date = $req->get('Date');
	$flag=0;
	$conn->update('anthropometricdata', ['flag_archive'=>$flag],['id_client'=>$id_client]);
	$flag_archive = 1;
	
	$conn->insert('anthropometricdata', ['Height' => $Height, 'Weight'=>$Weight, 'BicepsVol'=>$BicepsVol,'WaistVol'=>$WaistVol,'hipsVol'=>$hipsVol, 'BustVol'=>$BustVol,'flag_archive'=>$flag_archive,'id_client'=>$id_client ,'Date'=>$Date]);
	return $app->redirect("/index.php/client/$id_client");
});
//осуществляет переход на страницу добавления новой программы, так же выводит список общих программ
$sapp->get('/add_programm/{id_client}', function (Application $app, $id_client) {
    /**@var $conn Connection */
    $conn = $app['db'];
	$client= $conn->fetchColumn('select FIO from client where id_client = ?', [$id_client]);
	$progSelects = $conn->fetchAll('select * from programcatalog');
	$id_trainer = $_SESSION['idTrainer'];
	$trainer = $conn->fetchColumn('select FIO from trainer where idTrainer = ?', [$id_trainer]);
    return $app['twig']->render('add_program.twig', ['client'=>$client, 'progSelects'=>$progSelects, 'id_client'=>$id_client, 'trainer'=>$trainer]);
});
//////////////////////////////////////////////////////////////////////////////////////////////////////
//Осуществляет переход на страницу добавления новой общей программы
$sapp->get('/add_common_programm', function (Application $app) {
    /**@var $conn Connection */
    $conn = $app['db'];
	$trainers = $conn->fetchAll('select * from trainer');
	$id_trainer = $_SESSION['idTrainer'];
	$trainer = $conn->fetchColumn('select FIO from trainer where idTrainer = ?', [$id_trainer]);
    return $app['twig']->render('add_common_program.twig', [ 'trainers'=>$trainers, 'trainer'=>$trainer]);
});
//////////////////////////////////////////////////////////////////////////////////////////////////////
$sapp->get('/addAnthropometric/{id_client}', function (Application $app, $id_client) {
    /**@var $conn Connection */
    $conn = $app['db'];
	$client = $conn->fetchColumn('select FIO from client where id_client = ?', [$id_client]);
	$id_trainer = $_SESSION['idTrainer'];
	$trainer = $conn->fetchColumn('select FIO from trainer where idTrainer = ?', [$id_trainer]);

    return $app['twig']->render('add_Anthropometric.twig', ['client' => $client, 'id_client' => $id_client, 'trainer'=>$trainer]);
});
//////////////////////////////////////////////////////////////////////////////////////////////////////
//осуществляет переход на страницу добавления тренировочных дней
//необходимо указать лишь их названия
$sapp->get('/add_days/{id_prog}', function (Application $app, $id_prog) {
    /**@var $conn Connection */
    $conn = $app['db'];
	//$client = $conn->fetchColumn('select FIO from client where id_client = ?', [$id_client]);
	$days = $conn->fetchColumn('select DayOfTheOneCycle from program where id_prog = ?', [$id_prog]);
	$id_trainer = $_SESSION['idTrainer'];
	$trainer = $conn->fetchColumn('select FIO from trainer where idTrainer = ?', [$id_trainer]);

    return $app['twig']->render('add_days.twig', ['id_prog' => $id_prog, 'days'=>$days, 'trainer'=>$trainer]);
});
//////////////////////////////////////////////////////////////////////////////////////////////////////
//осуществляет переход на страницу добавления тренировочных дней
//для общей программы, необходимо указать лишь их названия
$sapp->get('/add_common_days/{id_prog}', function (Application $app, $id_prog) {
    /**@var $conn Connection */
    $conn = $app['db'];
	$days = $conn->fetchColumn('select DayOfTheOneCycle from programcatalog where id_program_catalog = ?', [$id_prog]);
	$id_trainer = $_SESSION['idTrainer'];
	$trainer = $conn->fetchColumn('select FIO from trainer where idTrainer = ?', [$id_trainer]);

    return $app['twig']->render('add_common_days.twig', ['id_prog' => $id_prog, 'days'=>$days, 'trainer'=>$trainer]);
});
//////////////////////////////////////////////////////////////////////////////////////////////////////
////осуществляет переход на страницу добавления упражнений
// по тренировочным дням
$sapp->get('/add_exercise/{id_prog}', function (Application $app, $id_prog) {
    /**@var $conn Connection */
    $conn = $app['db'];
	$allExercise = $conn->fetchAll('select * from notesexercises');
	$Exercise = $conn->fetchAll('select * from exercise');
	$days = $conn->fetchAll('select * from currentday where id_prog = ?', [$id_prog]);
	$id_trainer = $_SESSION['idTrainer'];
	$trainer = $conn->fetchColumn('select FIO from trainer where idTrainer = ?', [$id_trainer]);

    return $app['twig']->render('add_exercise.twig', ['id_prog' => $id_prog, 'days'=>$days, 'allExercise'=>$allExercise, 'exercises'=>$Exercise, 'trainer'=>$trainer]);
});
//////////////////////////////////////////////////////////////////////////////////////////////////////
////осуществляет переход на страницу добавления упражнений
// для общих программ, по тренировочным дням
$sapp->get('/add_common_exercise/{id_prog}', function (Application $app, $id_prog) {
    /**@var $conn Connection */
    $conn = $app['db'];
	$id_trainer = $_SESSION['idTrainer'];
	$trainer = $conn->fetchColumn('select FIO from trainer where idTrainer = ?', [$id_trainer]);

	$allExercise = $conn->fetchAll('select * from notesexercises');
	$Exercise = $conn->fetchAll('select * from exercisecatalog');
	$days = $conn->fetchAll('select * from currentdaycatalog where id_program_catalog = ?', [$id_prog]);
    return $app['twig']->render('add_common_exercise.twig', ['id_prog' => $id_prog, 'days'=>$days, 'allExercise'=>$allExercise, 'exercises'=>$Exercise, 'trainer'=>$trainer]);
});
//////////////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////////////
$sapp->get('/allnutrition', function (Application $app) {
    /**@var $conn Connection */
    $conn = $app['db'];
	$id_trainer = $_SESSION['idTrainer'];
	$trainer = $conn->fetchColumn('select FIO from trainer where idTrainer = ?', [$id_trainer]);

	$nutrition = $conn->fetchColumn('select * from nutrition');
    return $app['twig']->render('allnutritiones.twig', ['nutritions'=>$nutrition, 'trainer'=>$trainer]);
});

//////////////////////////////////////////////////////////////////////////////////////////////////////
//служит для перехода на страницу добавления питания
$sapp->get('/addNutrition/{id_client}', function (Application $app, $id_client) {
    /**@var $conn Connection */
    $conn = $app['db'];
	$client = $conn->fetchColumn('select FIO from client where id_client = ?', [$id_client]);
	$id_trainer = $_SESSION['idTrainer'];
	
	$trainer = $conn->fetchColumn('select FIO from trainer where idTrainer = ?', [$id_trainer]);
    return $app['twig']->render('add_Nutrition.twig', ['client' => $client, 'id_client' => $id_client, 'trainer'=>$trainer]);
});
//////////////////////////////////////////////////////////////////////////////////////////////////////
// непосредственно добавляет предписания по питанию клиенту
$sapp->post('/nutritiones/{id_client}', function (Application $app, Request $req, $id_client) {
	/**@var $conn Connection */
	$conn = $app['db'];
	$NumberOfMeals = $req->get('NumberOfMeals');
	$Calorie = $req->get('Calorie');
	$Protein = $req->get('Protein');
	$Carbogidrates = $req->get('Carbogidrates');
	$fat = $req->get('fat');
	$date = $req->get('date');
	$flag_archive=0;
	$conn->update('nutrition', ['flag_archive'=>$flag_archive],['id_client'=>$id_client]);
	$flag_archive = 1;
	$conn->insert('nutrition', ['NumberOfMeals' => $NumberOfMeals, 'Calorie'=>$Calorie, 'Protein'=>$Protein,'Carbogidrates'=>$Carbogidrates,'fat'=>$fat,'flag_archive'=>$flag_archive,'id_client'=>$id_client, 'Date'=>$date ]);
	return $app->redirect("/index.php/client/$id_client");
});
/////////////////////////////////////////////////////////////////////////////////////////////////////
//служит для добавления упражнений в справочник упражнений
$sapp->post('/addAllExercises', function (Application $app, Request $req) {
    /**@var $conn Connection */
    $conn = $app['db'];
	$NameOfExercise = $req->get('exrcise_cat');
	$BriefDescription = $req->get('comment_exercise_cat');
	$check = $conn->fetchAll('select * from notesexercises');
	foreach($check as &$NameExercise)
	{
		if($NameExercise['NameOfExercise']==$NameOfExercise)
		{
			return $app->redirect("/index.php/allexercise");
		}
	}
	$conn->insert('notesexercises',['NameOfExercise'=>$NameOfExercise,'BriefDescription'=>$BriefDescription]);
    return $app->redirect("/index.php/allexercise");
});
///////////////////////////////////////////////////////////////////////////////////////////////////////
//служит для добавления программы клиенту
$sapp->post('/programm/{id_client}', function (Application $app, Request $req, $id_client) {
    /**@var $conn Connection */
    $conn = $app['db'];
	$ProgramName = $req->get('name_program');
	$day = $req->get('day');
	$date = $req->get('date');
	$comment = $req->get('comment');
	$flag=0;
	$conn->update('program', ['Archive_flag'=>$flag],['id_client'=>$id_client]);
	$flag =1;
	$idTrainer = $conn->fetchColumn('select idTrainer from client where id_client = ?', [$id_client]);
	$conn->insert('program',['NameProgram'=>$ProgramName,'DayOfTheOneCycle'=>$day,'Comment'=>$comment, 'Archive_flag'=>$flag, 'id_client'=>$id_client, 'idTrainer'=>$idTrainer, 'DateProgram'=>$date]);
    return $app->redirect("/index.php/client/$id_client");
});
///////////////////////////////////////////////////////////////////////////////////////////////////////////
//служит для добавления упражнения в программу, с учетом тренировочного дня
$sapp->post('/add_exercise/{id_prog}', function (Application $app, Request $req, $id_prog) {
    /**@var $conn Connection */
    $conn = $app['db'];
	$idExercise = $req->get('selectedExercise');
	$Exercise = $conn->fetchColumn('select NameOfExercise from notesexercises where id_notestExercise = ?', [$idExercise]);
	$day = $req->get('selectedDay');
	$comment = $req->get('comment_exercise');
	$approach = $req->get('CountApproach');
	$repeat = $req->get('CountRepeat');
	$conn->insert('exercise',['NameExercise'=>$Exercise,'CountApproaches'=>$approach,'CommentsExercise'=>$comment, 'CountRepeat'=>$repeat, 'idCurrentDay'=>$day]);
    return $app->redirect("/index.php/add_exercise/$id_prog");
});
///////////////////////////////////////////////////////////////////////////////////////////////////////////
//служит для добавления упражнения в справочник упражнений программ
$sapp->post('/add_common_exercise/{id_prog}', function (Application $app, Request $req, $id_prog) {
    /**@var $conn Connection */
    $conn = $app['db'];
	$idExercise = $req->get('selectedExercise');
	$Exercise = $conn->fetchColumn('select NameOfExercise from notesexercises where id_notestExercise = ?', [$idExercise]);
	$day = $req->get('selectedDay');
	$comment = $req->get('comment_exercise');
	$approach = $req->get('CountApproach');
	$repeat = $req->get('CountRepeat');
	$conn->insert('exercisecatalog',['NameExercise'=>$Exercise,'CountApproaches'=>$approach,'CommentsExercise'=>$comment, 'CountRepeat'=>$repeat, 'id_cuurentDK'=>$day, 'id_notesExercise'=>$idExercise]);
    return $app->redirect("/index.php/add_common_exercise/$id_prog");
});
///////////////////////////////////////////////////////////////////////////////////////////////////////////
//служит для добавления общей программы в каталог программ
$sapp->post('/add_common_progr/{id_client}', function (Application $app, Request $req, $id_client) {
    /**@var $conn Connection */
    $conn = $app['db'];
	$idProgram = $req->get('select_program');
	$Date = $req->get('date');
	$NameProgram=$conn->fetchColumn('select NameProgram from programcatalog where id_program_catalog = ?', [$idProgram]);
	$DayOfTheOneCycle=$conn->fetchColumn('select DayOfTheOneCycle from programcatalog where id_program_catalog= ?', [$idProgram]);
	$Comment=$conn->fetchColumn('select Comment from programcatalog where id_program_catalog= ?', [$idProgram]);
	$idTrainer=$conn->fetchColumn('select idTrainer from programcatalog where id_program_catalog= ?', [$idProgram]);
	$flag=0;
	$conn->update('program', ['Archive_flag'=>$flag],['id_client'=>$id_client]);
	$flag=1;
	$conn->insert('program',['NameProgram'=>$NameProgram,'DayOfTheOneCycle'=>$DayOfTheOneCycle,'Comment'=>$Comment ,'Archive_flag'=>$flag, 'idTrainer'=>$idTrainer, 'id_client'=>$id_client,'DateProgram'=>$Date]);
	$id = $conn->fetchColumn('select MAX(id_prog) from program' );
    return $app->redirect("/index.php/add_day_common/$id/$idProgram/$id_client");
});
///////////////////////////////////////////////////////////////////////////////////////////////////////
//служит для добавления тренировочных дней в соответсвующий каталог, для общей программы
$sapp->get('/add_day_common/{id_prog}/{id_prog_c}/{id_client}', function (Application $app, $id_prog, $id_prog_c,$id_client) {
    /**@var $conn Connection */
    $conn = $app['db'];
	$currentdayname = $conn->fetchAll('select * from currentdaycatalog where id_program_catalog =?',[$id_prog_c]);
	
	foreach($currentdayname as &$day)
	{
		$conn->insert('currentday',['NameOfDay'=>$day['CurrentDayName'], 'CommentsTraining'=>$day['CommentsTraining'], 'id_prog'=>$id_prog]);
		$exercises = $conn->fetchAll('select * from exercisecatalog where id_cuurentDK =?',[$day['id_cuurentDK']]);
		$idCurrentDay = $conn->fetchColumn('select MAX(idCurrentDay) from currentday' );
		
		foreach($exercises as &$exercise)
		{
			$conn->insert('exercise',['NameExercise'=>$exercise['NameExercise'], 'CountApproaches'=>$exercise['CountApproaches'], 'CountRepeat'=>$exercise['CountRepeat'], 'CommentsExercise'=>$exercise['CommentsExercise'], 'idCurrentDay'=>$idCurrentDay , 'id_notesExercise'=>$exercise['id_notesExercise'] ]);
		}
	}
	$id_trainer = $_SESSION['idTrainer'];
	$trainer = $conn->fetchColumn('select FIO from trainer where idTrainer = ?', [$id_trainer]);
    return $app->redirect("/index.php/client/$id_client");
});
///////////////////////////////////////////////////////////////////////////////////////////////////////
//служит для добавления тренировочного дня для программы
$sapp->post('/add_day/{id_prog}', function (Application $app, Request $req, $id_prog) {
    /**@var $conn Connection */
	$i=1;
    $conn = $app['db'];
	$daysCount = $conn->fetchColumn('select DayOfTheOneCycle from program where id_prog = ?', [$id_prog]);
	for ($i = 1; $i <= $daysCount; $i++)
	{
		$DayName = $req->get("$i");
		$comment = $req->get("$i$i");
		$conn->insert('currentday',['NameOfDay'=>$DayName,'CommentsTraining'=>$comment, 'id_prog'=>$id_prog]);
	}
    return $app->redirect("/index.php/program/$id_prog");
});
//////////////////////////////////////////////////////////////////////////////////////////////////////////
//служит для добавления тренировочного дня для общей программы
$sapp->post('/add_common_day/{id_prog}', function (Application $app, Request $req, $id_prog) {
    /**@var $conn Connection */
    $conn = $app['db'];
	$daysCount = $conn->fetchColumn('select DayOfTheOneCycle from programcatalog where id_program_catalog = ?', [$id_prog]);
	for ($i = 1; $i <= $daysCount; $i++)
	{
		$DayName = $req->get("$i");
		$comment = $req->get("$i$i");
		$conn->insert('currentdaycatalog',['CurrentDayName'=>$DayName, 'CommentsTraining'=>$comment, 'id_program_catalog'=>$id_prog]);
	}
    return $app->redirect("/index.php/allprogram/");
});
/////////////////////////////////////////////////////////////////////////////////////////////////////////
//служит для добавления общей программы в соответсвующий каталог
$sapp->post('/program_common/', function (Application $app, Request $req) {
    /**@var $conn Connection */
    $conn = $app['db'];
	$trainer = $req->get('selectedTrainer');
	$ProgramName = $req->get('name_program');
	$day = $req->get('day');
	$comment = $req->get('comment');
	$conn->insert('programcatalog',['NameProgram'=>$ProgramName,'DayOfTheOneCycle'=>$day,'Comment'=>$comment,'idTrainer'=>$trainer]);
    return $app->redirect("/index.php/allprogram/");
});
//////////////////////////////////////////////////////////////////////////////////////////////////////
//служит для удаления упражнения из программы
$sapp->delete('/exercises/{id_exercise}/{id_prog}', function (Application $app, $id_exercise, $id_prog) {
    /**@var $conn Connection */
    $conn = $app['db'];
    $conn->delete('exercisecatalog', ['id_exerciseCatalog' => $id_exercise]);
    return $app->redirect("/index.php/add_exercise/$id_prog");
});
//////////////////////////////////////////////////////////////////////////////////////////////////////
//служит для удаления упражнения из справочника
$sapp->delete('/allexercise/{id_exercise}', function (Application $app, $id_exercise) {
    /**@var $conn Connection */
    $conn = $app['db'];
    $conn->delete('notesexercises', ['id_notestExercise' => $id_exercise]);
    return $app->redirect("/index.php/allexercise");
});
//////////////////////////////////////////////////////////////////////////////////////////////////////
//служит для удаления упражнения из справочника программ
$sapp->delete('/common_exercise/{id_exercise}/{id_prog}', function (Application $app, $id_exercise, $id_prog) {
    /**@var $conn Connection */
    $conn = $app['db'];
    $conn->delete('exercisecatalog', ['id_exerciseCatalog' => $id_exercise]);
    return $app->redirect("/index.php/program_common/$id_prog");
});
//////////////////////////////////////////////////////////////////////////////////////////////////////
//служит для удаления упражнения из программы
$sapp->delete('/exercise/{id_exercise}/{id_prog}', function (Application $app, $id_exercise, $id_prog) {
    /**@var $conn Connection */
    $conn = $app['db'];
    $conn->delete('exercise', ['idExercise' => $id_exercise]);
    return $app->redirect("/index.php/program/$id_prog");
});
///////////////////////////////////////////////////////////////////////////////////////////////////////\
// служит для удаления программы у клиента/
$sapp->delete('/programs/{id_prog}/{id_client}', function (Application $app, $id_prog, $id_client) {
    /**@var $conn Connection */
    $conn = $app['db'];
    $conn->delete('program', ['id_prog' => $id_prog]);
    return $app->redirect("/index.php/client/$id_client");
});
//////////////////////////////////////////////////////////////////////////////////////////////////////
//служит для удаления общих программ
$sapp->delete('/programs_common/{id_prog}', function (Application $app, $id_prog) {
    /**@var $conn Connection */
    $conn = $app['db'];
    $conn->delete('programcatalog', ['id_program_catalog' => $id_prog]);
    return $app->redirect("/index.php/allprogram/");
});
//////////////////////////////////////////////////////////////////////////////////////////////////////
//служит для удаления предписаний по питанию
$sapp->delete('/nutrition/{id_client}/{id}', function (Application $app,  $id_client, $id) {
    /**@var $conn Connection */
    $conn = $app['db'];
    $conn->delete('nutrition', ['id_Nutrition' => $id]);
    return $app->redirect("/index.php/client/$id_client");
});
//////////////////////////////////////////////////////////////////////////////////////////////////////
//служит для удаления антропометрических данных
$sapp->delete('/anthropometric/{id_client}/{id}', function (Application $app,  $id_client, $id) {
    /**@var $conn Connection */
    $conn = $app['db'];
    $conn->delete('anthropometricdata', ['id_AntrData' => $id]);
    return $app->redirect("/index.php/client/$id_client");
});
//////////////////////////////////////////////////////////////////////////////////////////////////////
//служит для удаления клиента
$sapp->delete('/clients/{id}', function (Application $app, $id) {
    /**@var $conn Connection */
    $conn = $app['db'];
    $conn->delete('client', ['id_client' => $id]);
    return $app->redirect('/');
});
//////////////////////////////////////////////////////////////////////////////////////////////////////
$sapp->run();
