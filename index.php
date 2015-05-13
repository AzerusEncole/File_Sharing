<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'vendor/autoload.php';
require 'classes/DataBaseServiceProvider.php';
require 'classes/Thumb.php';

use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

$app = new Application();

$app->register(new TwigServiceProvider(), [
    'twig.path' => __DIR__ .'/templates'
]);

$app->register(new DataBaseServiceProvider(), [
    'db.dns'     => 'mysql:host=127.0.0.1;dbname=FilesDb',
    'db.user'    => 'root',
    'db.pass'    => '435555iei',
    'db.options' => [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
]);

$app['dir.uploads'] = __DIR__ . '/uploads/';
$app['dir.thumbs']  = __DIR__ . '/thumbs/';

$app->match('/', function (Request $req) use ($app) {
    $file = $req->files->get('file');

    if ($file === NULL || !$file->isValid()) {
        return $app['twig']->render('main.twig');
    }

    $id = uniqid();
    $name = $file->getClientOriginalName();
    $mime = $file->getClientMimeType();
    $size = $file->getClientSize() .' bytes';
    $date = date('Y:m:d H:i:s');
    $comment = $req->get('comment');

    $file->move($app['dir.uploads'], "$id$name");
    chmod("{$app['dir.uploads']}$id$name", 0777);

    $imgPath = "{$app['dir.uploads']}$id$name";

    $thumb = Thumb::create($imgPath, $app['dir.thumbs']);

    $app['db']->execute('add',[$id, $name, $mime, $thumb, $size, $date, $comment]);

    return $app->redirect("./$id");
}, 'GET|POST');

$app->get('/search', function (Request $req) use ($app) {
    $search = $req->get('search');
    $results = $app['db']->execute('search', ["%$search%"])->fetchAll();
    return $app['twig']->render('results.twig', ['results' => $results]);
});

$app->get('/download/{id}', function ($id) use ($app) {
    $file = $app['db']->execute('get', [$id])->fetch();

    $name = $file['name'];
    $path = "{$app['dir.uploads']}$id$name";
    return $app
        ->sendFile($path)
        ->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $name);
});

$app->get('/{id}', function ($id) use ($app) {
    $file = $app['db']->execute('get', [$id])->fetch();

    if ($file == false) {
        $app->abort(404);
    }

    return $app['twig']->render('file.twig', ['file' => $file]);
});

$app->run();