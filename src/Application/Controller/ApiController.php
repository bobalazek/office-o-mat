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
        return $app->json(
            $app['user']->toArray()
        );
    }

    public function meStatisticsAction(Request $request, Application $app)
    {
        $now = new \Datetime('now');
        $today = new \Datetime('today');
        $yesterday = new \Datetime('yesterday');
        $thisWeek = new \Datetime('last monday');
        $thisMonth = new \Datetime(date('Y-m-01 00:00:00'));
        $thisYear = new \Datetime(date('Y-01-01 00:00:00'));

        // Today
        $hoursWorkedToday = 0;
        $result = $app['orm.em']
            ->createQuery("SELECT SUM(TIMESTAMPDIFF(MINUTE, wt.timeStarted, wt.timeEnded)) AS minutesTotal
                FROM Application\Entity\WorkingTimeEntity wt
                WHERE wt.user = :user
                    AND wt.timeEnded IS NOT NULL
                    AND wt.timeStarted >= :timeFrom")
            ->setParameter(':user', $app['user'])
            ->setParameter(':timeFrom', $today->format(DATE_ATOM))
            ->getResult()
        ;
        if (isset($result[0]['minutesTotal'])) {
            $hoursWorkedToday = round($result[0]['minutesTotal'] / 60, 2);
        }

        // Yesterday
        $hoursWorkedYesterday = 0;
        $result = $app['orm.em']
            ->createQuery("SELECT SUM(TIMESTAMPDIFF(MINUTE, wt.timeStarted, wt.timeEnded)) AS minutesTotal
                FROM Application\Entity\WorkingTimeEntity wt
                WHERE wt.user = :user
                    AND wt.timeEnded IS NOT NULL
                    AND wt.timeStarted >= :timeFrom
                    AND wt.timeStarted <= :timeTo")
            ->setParameter(':user', $app['user'])
            ->setParameter(':timeFrom', $yesterday->format(DATE_ATOM))
            ->setParameter(':timeTo', $today->format(DATE_ATOM))
            ->getResult()
        ;
        if (isset($result[0]['minutesTotal'])) {
            $hoursWorkedYesterday = round($result[0]['minutesTotal'] / 60, 2);
        }

        // This Week
        $hoursWorkedThisWeek = 0;
        $result = $app['orm.em']
            ->createQuery("SELECT SUM(TIMESTAMPDIFF(MINUTE, wt.timeStarted, wt.timeEnded)) AS minutesTotal
                FROM Application\Entity\WorkingTimeEntity wt
                WHERE wt.user = :user
                    AND wt.timeEnded IS NOT NULL
                    AND wt.timeStarted >= :timeFrom")
            ->setParameter(':user', $app['user'])
            ->setParameter(':timeFrom', $thisWeek->format(DATE_ATOM))
            ->getResult()
        ;
        if (isset($result[0]['minutesTotal'])) {
            $hoursWorkedThisWeek = round($result[0]['minutesTotal'] / 60, 2);
        }

        // This Month
        $hoursWorkedThisMonth = 0;
        $result = $app['orm.em']
            ->createQuery("SELECT SUM(TIMESTAMPDIFF(MINUTE, wt.timeStarted, wt.timeEnded)) AS minutesTotal
                FROM Application\Entity\WorkingTimeEntity wt
                WHERE wt.user = :user
                    AND wt.timeEnded IS NOT NULL
                    AND wt.timeStarted >= :timeFrom")
            ->setParameter(':user', $app['user'])
            ->setParameter(':timeFrom', $thisMonth->format(DATE_ATOM))
            ->getResult()
        ;
        if (isset($result[0]['minutesTotal'])) {
            $hoursWorkedThisMonth = round($result[0]['minutesTotal'] / 60, 2);
        }

        // This Year
        $hoursWorkedThisYear = 0;
        $result = $app['orm.em']
            ->createQuery("SELECT SUM(TIMESTAMPDIFF(MINUTE, wt.timeStarted, wt.timeEnded)) AS minutesTotal
                FROM Application\Entity\WorkingTimeEntity wt
                WHERE wt.user = :user
                    AND wt.timeEnded IS NOT NULL
                    AND wt.timeStarted >= :timeFrom")
            ->setParameter(':user', $app['user'])
            ->setParameter(':timeFrom', $thisYear->format(DATE_ATOM))
            ->getResult()
        ;
        if (isset($result[0]['minutesTotal'])) {
            $hoursWorkedThisYear = round($result[0]['minutesTotal'] / 60, 2);
        }

        return $app->json(array(
            'hours_worked' => array(
                'today' => $hoursWorkedToday,
                'yesterday' => $hoursWorkedYesterday,
                'this_week' => $hoursWorkedThisWeek,
                'this_month' => $hoursWorkedThisMonth,
                'this_year' => $hoursWorkedThisYear,
            ),
        ));
    }

    /***** Working Times *****/
    public function meWorkingTimesAction(Request $request, Application $app)
    {
        $workingTimes = array();

        $workingTimesObjects = $app['orm.em']
            ->getRepository('Application\Entity\WorkingTimeEntity')
            ->findBy(array(
                'user' => $app['user'],
            ), array(
                'timeStarted' => 'DESC',
            ))
        ;

        if ($workingTimesObjects) {
            foreach ($workingTimesObjects as $workingTimesObject) {
                $workingTimes[] = $workingTimesObject->toArray();
            }
        }

        return $app->json(
            $workingTimes
        );
    }

    public function meWorkingTimesNewAction(Request $request, Application $app)
    {
        $workingTimeData = $request->request->all();
        $workingTime = new \Application\Entity\WorkingTimeEntity();

        $workingTime
            ->setTimeStarted(
                isset($workingTimeData['timeStarted']) && $workingTimeData['timeStarted']
                    ? new \Datetime($workingTimeData['timeStarted'])
                    : null
            )
            ->setTimeEnded(
                isset($workingTimeData['timeEnded']) && $workingTimeData['timeEnded']
                    ? new \Datetime($workingTimeData['timeEnded'])
                    : null
            )
            ->setNotes(
                isset($workingTimeData['notes'])
                    ? $workingTimeData['notes']
                    : null
            )
            ->setLocation(
                isset($workingTimeData['location'])
                    ? $workingTimeData['location']
                    : null
            )
            ->setUser($app['user'])
        ;

        $validationErrors = $app['validator']->validate($workingTime, 'newAndEdit');

        if (count($validationErrors) > 0) {
            $errors = array();

            foreach ($validationErrors as $validationError) {
                $errors[] = array(
                    'message' => $validationError->getMessage(),
                );
            }

            return $app->json(array(
                'errors' => $errors,
            ), 403);
        }

        $app['orm.em']->persist($workingTime);
        $app['orm.em']->flush();

        return $app->json(
            $workingTime->toArray()
        );
    }

    public function meWorkingTimesEditAction($id, Request $request, Application $app)
    {
        $workingTimeData = $request->request->all();
        $workingTime = $app['orm.em']->find('Application\Entity\WorkingTimeEntity', $id);

        if (! $workingTime) {
            return $app->json(array(
                'errors' => array(
                    array(
                        'message' => 'This working time does not exists!',
                    ),
                ),
            ), 404);
        }

        if ($workingTime->getUser() != $app['user']) {
            return $app->json(array(
                'errors' => array(
                    array(
                        'message' => 'You have no permissions to edit this working time!',
                    ),
                ),
            ), 403);
        }

        $workingTime
            ->setTimeStarted(
                isset($workingTimeData['timeStarted']) && $workingTimeData['timeStarted']
                    ? new \Datetime($workingTimeData['timeStarted'])
                    : null
            )
            ->setTimeEnded(
                isset($workingTimeData['timeEnded']) && $workingTimeData['timeEnded']
                    ? new \Datetime($workingTimeData['timeEnded'])
                    : null
            )
            ->setNotes(
                isset($workingTimeData['notes'])
                    ? $workingTimeData['notes']
                    : null
            )
            ->setLocation(
                isset($workingTimeData['location'])
                    ? $workingTimeData['location']
                    : null
            )
        ;

        $validationErrors = $app['validator']->validate($workingTime, 'newAndEdit');

        if (count($validationErrors) > 0) {
            $errors = array();

            foreach ($validationErrors as $validationError) {
                $errors[] = array(
                    'message' => $validationError->getMessage(),
                );
            }

            return $app->json(array(
                'errors' => $errors,
            ), 403);
        }

        $app['orm.em']->persist($workingTime);
        $app['orm.em']->flush();

        return $app->json(
            $workingTime->toArray()
        );
    }

    public function meWorkingTimesRemoveAction($id, Request $request, Application $app)
    {
        $workingTime = $app['orm.em']->find('Application\Entity\WorkingTimeEntity', $id);

        if (! $workingTime) {
            return $app->json(array(
                'errors' => array(
                    array(
                        'message' => 'This working time does not exists!',
                    ),
                ),
            ), 404);
        }

        if ($workingTime->getUser() != $app['user']) {
            return $app->json(array(
                'errors' => array(
                    array(
                        'message' => 'You have no permissions to remove this working time!',
                    ),
                ),
            ), 403);
        }

        $app['orm.em']->remove($workingTime);
        $app['orm.em']->flush();

        return $app->json(array(
            'success' => true,
        ));
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
            ->setAccessToken(
                md5(uniqid(null, true))
            )
            ->setTimeAccessTokenExpires(
                new \Datetime('+ 5 minutes')
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

    public function mobileLogoutAction(Request $request, Application $app)
    {
        $app['user']->setAccessToken(null);
        $app['user']->setTimeAccessTokenExpires(null);

        $app['orm.em']->persist($app['user']);
        $app['orm.em']->flush();

        return $app->json(array(
            'success' => true,
        ));
    }
}
