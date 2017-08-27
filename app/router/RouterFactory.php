<?php

namespace App;

use Nette;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;


class RouterFactory
{
	use Nette\StaticClass;

	/**
	 * @return Nette\Application\IRouter
	 */
	public static function createRouter()
	{
		$router = new RouteList;
        $router[] = new Route('search/show ? city_from=<cityFrom> & city_to=<cityTo> & trip_type=<tripType>', 'Search:show');
        $router[] = new Route('search/request ? city_from=<cityFrom> & city_to=<cityTo> & trip_type=<tripType>', 'Search:request');
        $router[] = new Route('<presenter>/<action>', 'Search:request');
		return $router;
	}
}
