<?php
/**
 * Created by PhpStorm.
 * User: alaim
 * Date: 25/04/2017
 * Time: 10:29
 */

namespace App\Models;


trait PresentUri
{

    public static function getRoute()
    {
        return self::$route;
    }

    public static function setRoute($route)
    {
        self::$route = $route;
    }

    /**
     * Accessor for the custom JSON attribute uri used in REST APIs
     *
     * @return string
     */
    public function getUriAttribute(): string
    {
        return route(self::$route, $this);
    }

}