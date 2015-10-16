<?php

namespace Application\ControllerProvider;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class ApiControllerProvider
    implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        // Middlewares
        $accessTokenMiddleware = function (Request $request, Application $app) {
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

            if (
                ! $user ||
                ! $user->getAccessToken()
            ) {
                return $app->json(array(
                    'error' => array(
                        'message' => 'No user with this access token found.',
                    ),
                ), 404);
            }

            if ($currentDatetime > $user->getTimeAccessTokenExpires()) {
                $user->setAccessToken(null);
                $user->setTimeAccessTokenExpires(null);

                $app['orm.em']->persist($user);
                $app['orm.em']->flush();

                return $app->json(array(
                    'error' => array(
                        'message' => 'This access token has expired.',
                    ),
                ), 404);
            }

            $app['user'] = $user;
        };

        // Controllers
        $controllers = $app['controllers_factory'];

        $controllers->match(
            '/',
            'Application\Controller\ApiController::indexAction'
        )
        ->bind('api');

        $controllers->get(
            '/me',
            'Application\Controller\ApiController::meAction'
        )
        ->bind('api.me')
        ->before($accessTokenMiddleware);

        /*** Working Times ***/
        $controllers->get(
            '/me/working-times',
            'Application\Controller\ApiController::meWorkingTimesAction'
        )
        ->bind('api.me.working-time')
        ->before($accessTokenMiddleware);

        $controllers->post(
            '/me/working-times',
            'Application\Controller\ApiController::meWorkingTimesNewAction'
        )
        ->bind('api.me.working-time.new')
        ->before($accessTokenMiddleware);

        $controllers->put(
            '/me/working-times/{id}',
            'Application\Controller\ApiController::meWorkingTimesEditAction'
        )
        ->bind('api.me.working-time.edit')
        ->before($accessTokenMiddleware);

        $controllers->delete(
            '/me/working-times/{id}',
            'Application\Controller\ApiController::meWorkingTimesRemoveAction'
        )
        ->bind('api.me.working-time.remove')
        ->before($accessTokenMiddleware);

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

        $controllers->match(
            '/mobile/logout',
            'Application\Controller\ApiController::mobileLogoutAction'
        )
        ->bind('api.mobile.logout')
        ->before($accessTokenMiddleware);

        return $controllers;
    }
}
