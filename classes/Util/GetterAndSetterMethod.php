<?php defined('SYSPATH') OR die('No direct script access.');


trait Util_GetterAndSetterMethod {

    /**
     * @param string $property
     * @param mixed|null $value
     * @param string|null $custom_getter_method
     * @return mixed
     */
    protected function getter_and_setter_method($property, $value = NULL, $custom_getter_method = NULL)
    {
        if ( $value !== NULL )
        {
            $this->$property = $value;
        }

        if ( $custom_getter_method AND $this->$property === NULL)
        {
            $this->$property = $this->$custom_getter_method();
        }

        return $this->$property;
    }

}