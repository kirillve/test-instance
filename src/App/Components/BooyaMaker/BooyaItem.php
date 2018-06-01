<?php
/**
 * Created by PhpStorm.
 * User: kvereshchagin
 * Date: 2018-05-15
 * Time: 7:31 AM
 */

namespace App\Components\BooyaMaker;

class BooyaItem
{
    /** @var string */
    protected $name;
    /** @var string */
    protected $address;
    /** @var string */
    protected $text;

    /**
     * BooyaItem constructor.
     * @param string $name
     * @param string $address
     * @param string $text
     */
    public function __construct($name, $address, $text)
    {
        $this->name = $name;
        $this->address = $address;
        $this->text = $text;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $result = [];
        $reflection = new \ReflectionClass(get_class($this));
        foreach ($reflection->getProperties() as $property) {
            $property_name = $property->getName();
            $result [$property_name] = $this->{$property_name};
        }

        return $result;
    }
}
