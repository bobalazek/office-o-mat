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
}
