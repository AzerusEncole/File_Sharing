<?php

require 'Connection.php';

use Silex\Application;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class DataBaseServiceProvider implements ServiceProviderInterface {

    public function register(Container $app) {
        $app['db.create'] = $app->protect(function ($dns, $user, $pass, $options) {
            return new Connection($dns, $user, $pass, $options);
        });

        $app['db'] = function ($app) {
            return $app['db.create']($app['db.dns'],
                                     $app['db.user'],
                                     $app['db.pass'],
                                     $app['db.options']);
        };
    }
}