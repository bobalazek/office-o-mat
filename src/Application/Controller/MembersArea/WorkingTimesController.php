<?php

namespace Application\Controller\MembersArea;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WorkingTimesController
{
    public function indexAction(Request $request, Application $app)
    {
        $data = array();

        if (! $app['security']->isGranted('ROLE_WORKING_TIMES_EDITOR')
            && ! $app['security']->isGranted('ROLE_ADMIN')) {
            $app->abort(403);
        }

        $limitPerPage = $request->query->get('limit_per_page', 20);
        $currentPage = $request->query->get('page');

        $workingTimeResults = $app['orm.em']
            ->createQueryBuilder()
            ->select('wt')
            ->from('Application\Entity\WorkingTimeEntity', 'wt')
            ->leftJoin('wt.user', 'u')
        ;

        $pagination = $app['paginator']->paginate(
            $workingTimeResults,
            $currentPage,
            $limitPerPage,
            array(
                'route' => 'members-area.working-times',
                'defaultSortFieldName' => 'wt.timeCreated',
                'defaultSortDirection' => 'desc',
            )
        );

        $data['pagination'] = $pagination;

        return new Response(
            $app['twig']->render(
                'contents/members-area/working-times/index.html.twig',
                $data
            )
        );
    }

    public function newAction(Request $request, Application $app)
    {
        $data = array();

        if (! $app['security']->isGranted('ROLE_WORKING_TIMES_EDITOR')
            && ! $app['security']->isGranted('ROLE_ADMIN')) {
            $app->abort(403);
        }

        $form = $app['form.factory']->create(
            new \Application\Form\Type\WorkingTimeType(),
            new \Application\Entity\WorkingTimeEntity()
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
                        'members-area.working-times.new.successText'
                    )
                );

                return $app->redirect(
                    $app['url_generator']->generate(
                        'members-area.working-times.edit',
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
                'contents/members-area/working-times/new.html.twig',
                $data
            )
        );
    }

    public function editAction($id, Request $request, Application $app)
    {
        $data = array();

        if (! $app['security']->isGranted('ROLE_WORKING_TIMES_EDITOR')
            && ! $app['security']->isGranted('ROLE_ADMIN')) {
            $app->abort(403);
        }

        $workingTime = $app['orm.em']->find('Application\Entity\WorkingTimeEntity', $id);

        if (! $workingTime) {
            $app->abort(404);
        }

        $form = $app['form.factory']->create(
            new \Application\Form\Type\WorkingTimeType(),
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
                        'members-area.working-times.edit.successText'
                    )
                );

                return $app->redirect(
                    $app['url_generator']->generate(
                        'members-area.working-times.edit',
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
                'contents/members-area/working-times/edit.html.twig',
                $data
            )
        );
    }

    public function removeAction($id, Request $request, Application $app)
    {
        $data = array();

        if (! $app['security']->isGranted('ROLE_WORKING_TIMES_EDITOR')
            && ! $app['security']->isGranted('ROLE_ADMIN')) {
            $app->abort(403);
        }

        $workingTime = $app['orm.em']->find('Application\Entity\WorkingTimeEntity', $id);

        if (! $workingTime) {
            $app->abort(404);
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
                        'members-area.working-times.remove.successText'
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
                    'members-area.working-times'
                )
            );
        }

        $data['workingTime'] = $workingTime;

        return new Response(
            $app['twig']->render(
                'contents/members-area/working-times/remove.html.twig',
                $data
            )
        );
    }
}
