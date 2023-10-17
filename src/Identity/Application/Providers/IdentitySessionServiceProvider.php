<?php

namespace Identity\Application\Providers;

use Identity\Application\Http\Sessions\DatabaseSessionHandler;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;

class IdentitySessionServiceProvider extends ServiceProvider
{
    public function boot()
    {
//        $connection_name = $this->app->config->get('session.connection');
//        $table           = $this->app->config->get('session.table');
//        $lifetime        = $this->app->config->get('session.lifetime');
//        $app->app->db->connection($connection_name)
//
//        $this->app['session']->extend('database', function($app) use ($connection_name, $table){
//
//            return new DatabaseSessionHandler($connection, $table, $lifetime, $this->app);
////            return new \MyProject\Extension\CustomDatabaseSessionHandler(
////                $this->app['db']->connection($connection),
////                $table
////            );
//        });
//        dd($this->app->config->get('session.connection'));

        Session::extend('database', function ($app) {
            $connection_name = $this->app->config->get('session.connection');
            $connection      = $app->app->db->connection($connection_name );
            $table           = $this->app->config->get('session.table');
            $lifetime        = $this->app->config->get('session.lifetime');
            return new DatabaseSessionHandler($connection, $table, $lifetime, $this->app);
        });
    }
}
