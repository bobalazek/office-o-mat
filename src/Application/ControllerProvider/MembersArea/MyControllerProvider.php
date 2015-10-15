<?php

namespace Application\ControllerProvider\MembersArea;

use Silex\Application;
use Silex\ControllerProviderInterface;

class MyControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->match(
            '',
            'Application\Controller\MembersArea\MyController::indexAction'
        )
        ->bind('members-area.my');

        /*** Profile ***/
        $controllers->match(
            '/profile',
            'Application\Controller\MembersArea\MyController::profileAction'
        )
        ->bind('members-area.my.profile');

        $controllers->match(
            '/profile/settings',
            'Application\Controller\MembersArea\MyController::profileSettingsAction'
        )
        ->bind('members-area.my.profile.settings');

        $controllers->match(
            '/profile/settings/password',
            'Application\Controller\MembersArea\MyController::profileSettingsPasswordAction'
        )
        ->bind('members-area.my.profile.settings.password');

        /*** Working Times ***/
        $controllers->match(
            '/working-times',
            'Application\Controller\MembersArea\MyController::workingTimesAction'
        )
        ->bind('members-area.my.working-times');

        $controllers->match(
            '/working-times/{id}/edit',
            'Application\Controller\MembersArea\MyController::workingTimesEditAction'
        )
        ->bind('members-area.my.working-times.edit');

        $controllers->match(
            '/working-times/{id}/remove',
            'Application\Controller\MembersArea\MyController::workingTimesRemoveAction'
        )
        ->bind('members-area.my.working-times.remove');

        $controllers->match(
            '/working-times/new',
            'Application\Controller\MembersArea\MyController::workingTimesNewAction'
        )
        ->bind('members-area.my.working-times.new');

        return $controllers;
    }
}
