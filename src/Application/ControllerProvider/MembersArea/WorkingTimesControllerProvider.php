<?php

namespace Application\ControllerProvider\MembersArea;

use Silex\Application;
use Silex\ControllerProviderInterface;

class WorkingTimesControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->match(
            '',
            'Application\Controller\MembersArea\WorkingTimesController::indexAction'
        )
        ->bind('members-area.working-times');

        $controllers->match(
            '/new',
            'Application\Controller\MembersArea\WorkingTimesController::newAction'
        )
        ->bind('members-area.working-times.new');

        $controllers->match(
            '/{id}/edit',
            'Application\Controller\MembersArea\WorkingTimesController::editAction'
        )
        ->bind('members-area.working-times.edit');

        $controllers->match(
            '/{id}/remove',
            'Application\Controller\MembersArea\WorkingTimesController::removeAction'
        )
        ->bind('members-area.working-times.remove');

        return $controllers;
    }
}
