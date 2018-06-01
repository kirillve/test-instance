<?php
/**
 * Created by PhpStorm.
 * User: kvereshchagin
 * Date: 2018-05-14
 * Time: 3:41 PM
 */

namespace App\Controllers;

use App\Base\Response;

/**
 * Class DefaultController
 * @package App\Controllers
 */
class DefaultController
{
    /**
     * @return Response
     */
    public function actionIndex()
    {
        return new Response(['message' => 'Hello!']);
    }

    /**
     * @return Response
     */
    public function actionHello()
    {
        return new Response(['message' => 'Hello World!']);
    }
}
