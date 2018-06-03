<?php
/**
 * Created by PhpStorm.
 * User: kvereshchagin
 * Date: 2018-05-14
 * Time: 3:23 PM
 */

namespace App;

use App\Base\Response;
use DI\Container;

/**
 * Class App
 * @package App
 */
class App
{
    /** @var Container */
    protected $container;

    /**
     * App constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param $uri
     * @param $namespace
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \Exception
     */
    public function run($uri, $namespace)
    {
                
        list($uri, $queryParam)    = explode('?',$uri);

        list($controller, $action) = explode('/', trim($uri, '/'));
        
        if (!$controller) {
            $controller = 'Default';
        }
        if (!$action) {
            $action = 'Index';
        }
        $controller_class_name = rtrim($namespace, '\\') . '\\' . ucfirst($controller) . 'Controller';

        $controller_object = $this->container->get($controller_class_name);
        $response = call_user_func_array([$controller_object, 'action' . ucfirst($action)], []); 
        if (!$response instanceof Response) {
            throw new \Exception('Unsupported response type');
        }

        if(isset($_GET['format']) && !empty($_GET['format']))
        {

            $response->setResponseFormat($_GET['format']);
        }

        if(isset($_GET['fields']) && !empty($_GET['fields']))
        {

            $response->setResponseFields(explode(',',$_GET['fields']));
        }

        return $response;
    }
}
