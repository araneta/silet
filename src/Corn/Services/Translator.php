<?php
namespace Corn\Services;

trait Translator{
    public function transx($id, array $parameters = array()){
        return $this->app['translator']->trans($id, $parameters);
    }
}
