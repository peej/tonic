<?php

class Base extends Tonic\Resource
{

    protected function getRel($name)
    {
        return 'http://'.$_SERVER['HTTP_HOST'].$this->app->baseUri.'/rel/'.$name;
    }

    protected function getDB($database)
    {
        $dsn = sprintf($this->container['db_config']['dsn'], $database);
        try {
            return new PDO($dsn, $this->container['db_config']['username'], $this->container['db_config']['password']);
        } catch (Exception $e) {
            throw new Tonic\NotFoundException;
        }
    }

}