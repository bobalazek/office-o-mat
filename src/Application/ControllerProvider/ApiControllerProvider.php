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
            '/me',
            'Application\Controller\ApiController::meAction'
        )
        ->bind('api.me');

        $controllers->match(
            '/logout',
            'Application\Controller\ApiController::logoutAction'
        )
        ->bind('api.logout');

        /***** Mobile *****/
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

        $controllers->match(
            '/mobile/login/employee',
            'Application\Controller\ApiController::mobileLoginEmployeeAction'
        )
        ->bind('api.mobile.login.employee');

        return $controllers;
    }
}
