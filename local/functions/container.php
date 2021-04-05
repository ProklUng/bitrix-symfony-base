<?php

use Illuminate\Container\Container;
use Local\ServiceProvider\ServiceProvider;

if (!function_exists('containerLaravel')) {
    /**
     * Экземпляр сервис-контейнера Laravel.
     *
     * @return mixed
     */
    function containerLaravel()
    {
        return Container::getInstance();
    }
}

if (!function_exists('container')) {
    /**
     * Экземпляр сервис-контейнера Symfony.
     *
     * @return mixed
     */
    function container()
    {
        return ServiceProvider::instance();
    }
}
