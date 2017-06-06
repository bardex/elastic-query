<?php

namespace Bardex\Elastic;


interface IListener
{
    public function onSuccess(array $query, array $response, $time);

    public function onError(array $query, \Exception $e);

}