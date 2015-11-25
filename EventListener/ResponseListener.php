<?php

namespace DemacMedia\Bundle\CustomSalesBundle\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class ResponseListener
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$this->request->getCurrentRequest()) {
            return;
        }

        $response = $event->getResponse();
        $statusCode = $response->getStatusCode();
        $pathInfo = $this->request->getCurrentRequest()->getPathInfo();

        if ($pathInfo === '/lead/create' && $statusCode === 500) {
            $response = new RedirectResponse('/lead');
            $event->setResponse($response);
        } else {
            $event->setResponse($response);
        }
    }
}
