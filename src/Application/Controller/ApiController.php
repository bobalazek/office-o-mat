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

    public function meAction(Request $request, Application $app)
    {
        $currentDatetime = new \Datetime('now');
        $accessToken = $request->query->get('access_token', false);

        if (! $accessToken) {
            return $app->json(array(
                'error' => array(
                    'message' => 'No access token found.',
                ),
            ), 404);
        }

        $user = $app['orm.em']
            ->getRepository('Application\Entity\UserEntity')
            ->findOneByAccessToken($accessToken)
        ;

        if (! $user) {
            return $app->json(array(
                'error' => array(
                    'message' => 'No user with this access token found.',
                ),
            ), 404);
        }

        if ($currentDatetime > $user->getTimeAccessTokenExpires()) {
            return $app->json(array(
                'error' => array(
                    'message' => 'This access token has expired.',
                ),
            ), 404);
        }

        return $app->json(
            $user->toArray()
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

    public function mobileLoginEmployeeAction(Request $request, Application $app)
    {
        $userId = (int) $request->request->get('user_id', false);
        $pinNummber = (int) $request->request->get('pin_number', false);

        $user = $app['orm.em']
            ->find('Application\Entity\UserEntity', $userId)
        ;

        if (! $user) {
            return $app->json(array(
                'error' => array(
                    'message' => 'No user with this ID found.',
                )
            ), 404);
        }

        if (
            ! $pinNummber ||
            $user->getPinNumber() != $pinNummber
        ) {
            return $app->json(array(
                'error' => array(
                    'message' => 'Wrong PIN Number.',
                )
            ), 403);
        }

        $user
            ->setTimeAccessTokenExpires(
                new \Datetime('+ 10 minutes')
            )
        ;

        $app['orm.em']->persist($user);
        $app['orm.em']->flush();

        return $app->json(array(
            'id' => $user->getId(),
            'access_token' => $user->getAccessToken(),
            'time_access_token_expires' => $user->getTimeAccessTokenExpires()->format(DATE_ATOM),
        ));
    }
}
