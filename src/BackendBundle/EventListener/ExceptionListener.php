<?php

namespace BackendBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;


/**
 * Created by PhpStorm.
 * User: danielsolis
 * Date: 07/08/17
 * Time: 18:50
 */
class ExceptionListener
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        // You get the exception object from the received event

        //var_dump($event->getRequestType());

        $exception = $event->getException();

        $request   = $event->getRequest();

        $message = sprintf(
            'My Error says: %s with code: %s',
            $exception->getMessage(),
            $exception->getCode()
        );

        $data = json_encode(array(
            "Error500"=>TRUE,
            "Msg" => $exception->getMessage(),
            "Code" => $exception->getCode(),
            "RequestType" => $event->getRequestType(),
            "Request" => $request

        ));


        // Customize your response object to display the exception details
        $response = new Response();
        $response->setContent($data);


        // HttpExceptionInterface is a special type of exception that
        // holds status code and header details
        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace($exception->getHeaders());
        } else {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Send the modified response object to the event
        $event->setResponse($response);
    }

}
