<?php

namespace Innmind\RestBundle\EventListener;

use Innmind\Rest\Server\Exception\PayloadException;
use Innmind\Rest\Server\Exception\ValidationException;
use Innmind\Rest\Server\Exception\ResourceNotFoundException;
use Innmind\Rest\Server\Exception\TooManyResourcesFoundException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ExceptionListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'buildHttpException',
        ];
    }

    /**
     * Transform an application exception into an
     * http exception so symfony can transform it
     *
     * @param GetResponseForExceptionEvent $event
     *
     * @return void
     */
    public function buildHttpException(GetResponseForExceptionEvent $event)
    {
        $ex = $event->getException();

        switch (true) {
            case $ex instanceof PayloadException:
            case $ex instanceof ValidationException:
                $ex = new BadRequestHttpException(null, $ex);
                break;
            case $ex instanceof ResourceNotFoundException:
                $ex = new NotFoundHttpException(null, $ex);
                break;
            case $ex instanceof TooManyResourcesFoundException:
                $ex = new ConflictHttpException(null, $ex);
                break;
        }

        $event->setException($ex);
    }
}
