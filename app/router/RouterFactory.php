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

		$router[] = new Route('mail/confirm ? customer=<customer>', 'Mail:confirm');

        $router[] = new Route('search/submit ? city_from=<cityFrom> & city_to=<cityTo> & trip_type=<tripType> & departure_0=<departure0> & travel_type_0=<travelType0> & travel_provider_0=<travelProvider0> & departure_1=<departure1> & travel_type_1=<travelType1> & travel_provider_1=<travelProvider1> & departure_time_0=<departureTime0> & spots_0=<spots0> & departure_time_1=<departureTime1> & spots_1=<spots1> & email=<email> & phone=<phone> & supervisor=<supervisor>', 'Search:submit');
        $router[] = new Route('search/summary ? city_from=<cityFrom> & city_to=<cityTo> & trip_type=<tripType> & departure_0=<departure0> & travel_type_0=<travelType0> & travel_provider_0=<travelProvider0> & departure_1=<departure1> & travel_type_1=<travelType1> & travel_provider_1=<travelProvider1>', 'Search:summary');
        $router[] = new Route('search/show ? city_from=<cityFrom> & city_to=<cityTo> & trip_type=<tripType> & departure_0=<departure0> & travel_type_0=<travelType0> & travel_provider_0=<travelProvider0> & departure_1=<departure1> & travel_type_1=<travelType1> & travel_provider_1=<travelProvider1>', 'Search:show');
        $router[] = new Route('search/request ? city_from=<cityFrom> & city_to=<cityTo> & trip_type=<tripType>', 'Search:request');
        $router[] = new Route('search/processed ? city_from=<cityFrom> & city_to=<cityTo> & trip_type=<tripType>', 'Search:processed');

        $router[] = new Route('<presenter>/<action>', 'Search:request');
		return $router;
	}
}
