<?php
/**
 * Created by PhpStorm.
 * User: kvereshchagin
 * Date: 2018-05-14
 * Time: 3:41 PM
 */

namespace App\Controllers;

use App\Base\Response;
use App\Components\BooyaMaker\BooyaMaker;

/**
 * Class BooyaController
 * @package App\Controllers
 */
class BooyaController
{
    /** @var BooyaMaker */
    protected $booya_maker;

    /**
     * BooyaController constructor.
     * @param BooyaMaker $booya_maker
     */
    public function __construct(BooyaMaker $booya_maker)
    {
        $this->booya_maker = $booya_maker;
    }

    /**
     * @return Response
     */
    public function actionIndex()
    {
        return new Response(['message' => 'Booya!']);
    }

    /**
     * @return Response
     */
    public function actionBooya()
    {
        echo '<pre>';
        return new Response($this->booya_maker->generate()->toArray());
    }
}
