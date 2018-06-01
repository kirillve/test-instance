<?php
/**
 * Created by PhpStorm.
 * User: kvereshchagin
 * Date: 2018-05-14
 * Time: 11:24 AM
 */

require('/app/vendor/autoload.php');

$builder = new \DI\ContainerBuilder();
$builder->useAnnotations(false);
$builder->addDefinitions(require('/app/config/container.php'));

$app = new \App\App($builder->build());
$response = $app->run($_SERVER['REQUEST_URI'], 'App\Controllers');
echo $response->send();
