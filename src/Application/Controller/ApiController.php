<?php

namespace Application\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class ApiController
{
    public function indexAction(Request $request, Application $app)
    {
        $data = array(
            'status' => 'ok',
            'status_code' => 200,
            'message' => 'Hello API!',
        );

        return $app->json(
            $data
        );
    }

    public function mobileAction(Request $request, Application $app)
    {
        $data = array(
            'status' => 'ok',
            'status_code' => 200,
            'message' => 'Hello Mobile API!',
        );

        return $app->json(
            $data
        );
    }

    public function mobileEmployeesAction(Request $request, Application $app)
    {
        $data = array();

        $employees = $app['orm.em']
            ->getRepository('Application\Entity\UserEntity')
            ->getEmployees()
        ;

        $data['employees'] = $employees;

        return $app->json(
            $data
        );
    }
}
