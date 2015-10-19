<?php

namespace Application\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IndexController
{
    public function indexAction(Request $request, Application $app)
    {
        $data = array();

        return new Response(
            $app['twig']->render(
                'contents/index.html.twig',
                $data
            )
        );
    }

    public function mobileAction(Request $request, Application $app)
    {
        $data = array();

        $data['isInsideCordova'] = $request->query->has('inside_cordova');

        return new Response(
            $app['twig']->render(
                'contents/mobile.html.twig',
                $data
            )
        );
    }
}
