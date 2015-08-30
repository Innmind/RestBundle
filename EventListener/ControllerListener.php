<?php

namespace Innmind\RestBundle\EventListener;

use Innmind\RestBundle\RouteKeys;
use Innmind\Rest\Server\Exception\ValidationException;
use Innmind\Rest\Server\Exception\PayloadException;
use Innmind\Rest\Server\Access;
use Innmind\Rest\Server\Collection;
use Innmind\Rest\Server\Validator;
use Innmind\Rest\Server\Request\Parser;
use Innmind\Rest\Server\Event\ResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpFoundation\Response;

class ControllerListener implements EventSubscriberInterface
{
    protected $validator;
    protected $requestParser;

    public function __construct(Validator $validator, Parser $parser)
    {
        $this->validator = $validator;
        $this->requestParser = $parser;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'decodeRequest',
            KernelEvents::VIEW => 'encodeResponse',
        ];
    }

    /**
     * Decode the content from the request (if necessary) and validate it
     *
     * @param FilterControllerEvent $event
     *
     * @return void
     */
    public function decodeRequest(FilterControllerEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->has(RouteKeys::DEFINITION)) {
            return;
        }

        $action = $request->attributes->get(RouteKeys::ACTION);

        switch ($action) {
            case 'index':
            case 'get':
            case 'delete':
            case 'options':
                $request->attributes->set(
                    'definition',
                    $request->attributes->get(RouteKeys::DEFINITION)
                );
                break;
            case 'create':
                $data = $this->requestParser->getData(
                    $request,
                    $request->attributes->get(RouteKeys::DEFINITION)
                );
                $this->validate($data, Access::CREATE);
                $request->attributes->set('resources', $data);
                break;
            case 'update':
                $resource = $this->requestParser->getData(
                    $request,
                    $request->attributes->get(RouteKeys::DEFINITION)
                );

                if ($resource instanceof Collection) {
                    throw new PayloadException(
                        'You can only update one resource at a time'
                    );
                }

                $this->validate($resource, Access::UPDATE);
                $request->attributes->set('resource', $resource);
                break;
        }
    }

    /**
     * Validate the content returned by the controller and build the response
     *
     * @param GetResponseForControllerResultEvent $event
     *
     * @return void
     */
    public function encodeResponse(GetResponseForControllerResultEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->has(RouteKeys::DEFINITION)) {
            return;
        }

        $action = $request->attributes->get(RouteKeys::ACTION);

        if (in_array($action, ['index', 'get', 'create', 'update'], true)) {
            $this->validate($event->getControllerResult(), Access::READ);
        }

        $response = new Response;
        $event->setResponse($response);
        $event = new ResponseEvent(
            $request->attributes->get(RouteKeys::DEFINITION),
            $response,
            $request,
            $event->getControllerResult(),
            $request->attributes->get(RouteKeys::ACTION)
        );
    }

    /**
     * Validate the given data for the given access
     *
     * @param Innmind\Rest\Resource|Collection $data
     * @param string $access
     *
     * @throws ValidationException
     *
     * @return void
     */
    protected function validate($data, $access)
    {
        $violations = $this->validator->validate($data, $access);

        if ($violations->count() > 0) {
            throw ValidationException::build(Access::READ, $violations);
        }
    }
}
