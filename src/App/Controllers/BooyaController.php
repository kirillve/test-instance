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
        return new Response($this->booya_maker->generate()->toArray());
    }

    /**
     * @return Response
     */
    public function actionList()
    {
        $booyaList      = [];
        $booyaData      = []; 
        $maxPerPage     = 50;
        $defaultPerPage = 20;

        for($i=0;$i<101;$i++)
        {
            $booyaList[] = $this->booya_maker->generate()->toArray();
        }
        
        $perPage     = isset($_GET['page_size']) && ($_GET['page_size']>0) ? $_GET['page_size'] : $defaultPerPage;
        $perPage     = ($perPage>$maxPerPage) ? $maxPerPage : $perPage;
        $totalRows   = count($booyaList);
        $pages       = ceil($totalRows / $perPage);
        $currentPage = isset($_GET['page']) && ($_GET['page']>0) ? $_GET['page'] : 1;
        if($currentPage>$pages) {
            $booyaData = array(
                'message' => 'No data found'
            );
        } else {
            $currentPage = ($totalRows > 0) ? min($pages, $currentPage) : 1;
            $start       = $currentPage * $perPage - $perPage;
            $slice       = array_slice($booyaList, $start, $perPage);

            foreach ($slice as $k => $v) {
                $booyaData[] = $v; 
            }
        }
        
        return new Response($booyaData);
    }
}
