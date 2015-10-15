<?php

namespace Application\Controller\MembersArea;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MyController
{
    public function indexAction(Request $request, Application $app)
    {
        return $app->redirect(
            $app['url_generator']->generate('members-area.my.profile')
        );
    }

    public function profileAction(Request $request, Application $app)
    {
        $data = array();

        return new Response(
            $app['twig']->render(
                'contents/members-area/my/profile/index.html.twig',
                $data
            )
        );
    }

    public function profileSettingsAction(Request $request, Application $app)
    {
        $data = array();

        // Used for user action
        $userOld = $app['user']->toArray(true);
        $userOld['profile'] = $app['user']->getProfile()->toArray(true);

        $form = $app['form.factory']->create(
            new \Application\Form\Type\User\SettingsType(),
            $app['user']
        );

        // IMPORTANT Security fix!
        $currentUserUsername = $app['user']->getUsername();

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            // IMPORTANT Security fix!
            /*
             * Some weird bug here allows to impersonate to another user
             *   by just changing to his (like some admins) username
             *   (after failed "username already used" message)
             *   when the validation kicks in, and one refresh later,
             *   you're logged in as that user.
             */
             $app['user']->setUsername($currentUserUsername);

            if ($form->isValid()) {
                $userEntity = $form->getData();

                /*** Image ***/
                $userEntity
                    ->getProfile()
                    ->setImageUploadPath($app['baseUrl'].'/assets/uploads/')
                    ->setImageUploadDir(WEB_DIR.'/assets/uploads/')
                    ->imageUpload()
                ;

                $app['orm.em']->persist($userEntity);
                $app['orm.em']->flush();

                $app['flashbag']->add(
                    'success',
                    $app['translator']->trans(
                        'members-area.my.profile.settings.successText'
                    )
                );
            }
        }

        $data['form'] = $form->createView();

        return new Response(
            $app['twig']->render(
                'contents/members-area/my/profile/settings.html.twig',
                $data
            )
        );
    }

    public function profileSettingsPasswordAction(Request $request, Application $app)
    {
        $data = array();

        $userOriginal = $app['user'];
        $passwordOld = $userOriginal->getPassword();

        $form = $app['form.factory']->create(
            new \Application\Form\Type\User\Settings\PasswordType(),
            $app['user']
        );

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $userEntity = $form->getData();

                if ($userEntity->getPlainPassword()) {
                    $userEntity->setPlainPassword(
                        $userEntity->getPlainPassword(),
                        $app['security.encoder_factory']
                    );

                    $app['orm.em']->persist($userEntity);
                    $app['orm.em']->flush();

                    $app['flashbag']->add(
                        'success',
                        $app['translator']->trans(
                            'members-area.my.profile.settings.password.successText'
                        )
                    );
                }
            }
        }

        $data['form'] = $form->createView();

        return new Response(
            $app['twig']->render(
                'contents/members-area/my/profile/settings/password.html.twig',
                $data
            )
        );
    }

    /***** Working Times *****/
    public function workingTimesAction(Request $request, Application $app)
    {
        $data = array();

        $limitPerPage = $request->query->get('limit_per_page', 20);
        $currentPage = $request->query->get('page');

        $workingTimeResults = $app['orm.em']
            ->createQueryBuilder()
            ->select('wt')
            ->from('Application\Entity\WorkingTimeEntity', 'wt')
            ->where('wt.user = ?1')
            ->setParameter(1, $app['user'])
        ;

        $pagination = $app['paginator']->paginate(
            $workingTimeResults,
            $currentPage,
            $limitPerPage,
            array(
                'route' => 'members-area.my.working-times',
                'defaultSortFieldName' => 'wt.timeCreated',
                'defaultSortDirection' => 'desc',
            )
        );

        $data['pagination'] = $pagination;

        return new Response(
            $app['twig']->render(
                'contents/members-area/my/working-times/list.html.twig',
                $data
            )
        );
    }

    public function workingTimesNewAction(Request $request, Application $app)
    {
        $data = array();

        $form = $app['form.factory']->create(
            new \Application\Form\Type\User\WorkingTimeType(),
            new \Application\Entity\WorkingTimeEntity()
        );

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $workingTimeEntity = $form->getData();
                $workingTimeEntity
                    ->setUser($app['user'])
                ;

                $app['orm.em']->persist($workingTimeEntity);
                $app['orm.em']->flush();

                $app['flashbag']->add(
                    'success',
                    $app['translator']->trans(
                        'members-area.my.working-times.new.successText'
                    )
                );

                return $app->redirect(
                    $app['url_generator']->generate(
                        'members-area.my.working-times.edit',
                        array(
                            'id' => $workingTimeEntity->getId(),
                        )
                    )
                );
            }
        }

        $data['form'] = $form->createView();

        return new Response(
            $app['twig']->render(
                'contents/members-area/my/working-times/new.html.twig',
                $data
            )
        );
    }

    public function workingTimesEditAction($id, Request $request, Application $app)
    {
        $data = array();

        $workingTime = $app['orm.em']->find('Application\Entity\WorkingTimeEntity', $id);

        if (! $workingTime) {
            $app->abort(404);
        }

        if($workingTime->getUser() != $app['user']) {
            $app->abort(403);
        }

        $form = $app['form.factory']->create(
            new \Application\Form\Type\User\WorkingTimeType(),
            $workingTime
        );

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $workingTimeEntity = $form->getData();

                $app['orm.em']->persist($workingTimeEntity);
                $app['orm.em']->flush();

                $app['flashbag']->add(
                    'success',
                    $app['translator']->trans(
                        'members-area.my.working-times.edit.successText'
                    )
                );

                return $app->redirect(
                    $app['url_generator']->generate(
                        'members-area.my.working-times.edit',
                        array(
                            'id' => $workingTimeEntity->getId(),
                        )
                    )
                );
            }
        }

        $data['form'] = $form->createView();
        $data['workingTime'] = $workingTime;

        return new Response(
            $app['twig']->render(
                'contents/members-area/my/working-times/edit.html.twig',
                $data
            )
        );
    }

    public function workingTimesRemoveAction($id, Request $request, Application $app)
    {
        $data = array();

        $workingTime = $app['orm.em']->find('Application\Entity\WorkingTimeEntity', $id);

        if (! $workingTime) {
            $app->abort(404);
        }

        if($workingTime->getUser() != $app['user']) {
            $app->abort(403);
        }

        $confirmAction = $app['request']->query->has('action') &&
            $app['request']->query->get('action') == 'confirm'
        ;

        if ($confirmAction) {
            try {
                $app['orm.em']->remove($workingTime);
                $app['orm.em']->flush();

                $app['flashbag']->add(
                    'success',
                    $app['translator']->trans(
                        'members-area.my.working-times.remove.successText'
                    )
                );
            } catch (\Exception $e) {
                $app['flashbag']->add(
                    'danger',
                    $app['translator']->trans(
                        $e->getMessage()
                    )
                );
            }

            return $app->redirect(
                $app['url_generator']->generate(
                    'members-area.my.working-times'
                )
            );
        }

        $data['workingTime'] = $workingTime;

        return new Response(
            $app['twig']->render(
                'contents/members-area/my/working-times/remove.html.twig',
                $data
            )
        );
    }
}
