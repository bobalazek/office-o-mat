<?php

namespace Application\ControllerProvider;

use Silex\Application;
use Silex\ControllerProviderInterface;

class ApiControllerProvider
    implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->match(
            '/',
            'Application\Controller\ApiController::indexAction'
        )
        ->bind('api');

        $controllers->match(
            '/mobile',
            'Application\Controller\ApiController::mobileAction'
        )
        ->bind('api.mobile');

        $controllers->match(
            '/mobile/employees',
            'Application\Controller\ApiController::mobileEmployeesAction'
        )
        ->bind('api.mobile.employees');

        return $controllers;
    }
}
