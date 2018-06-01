<?php
/**
 * Created by PhpStorm.
 * User: kvereshchagin
 * Date: 2018-05-15
 * Time: 7:30 AM
 */

namespace App\Components\BooyaMaker;

use Faker\Factory;

/**
 * Class BooyaMaker
 * @package App\Components\BooyaMaker
 */
class BooyaMaker
{
    /**
     * @return BooyaItem
     */
    public function generate()
    {
        $faker = Factory::create();
        return new BooyaItem($faker->name, $faker->address, $faker->text);
    }
}
