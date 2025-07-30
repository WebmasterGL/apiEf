<?php
/**
 * Created by PhpStorm.
 * User: danielsolis
 * Date: 17/07/17
 * Time: 13:05
 */

namespace AppBundle\Security;

use \Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Symfony\Component\HttpFoundation\Response;




class MyAccessDeniedHandler implements AccessDeniedHandlerInterface

{
    public function handle(Request $request, AccessDeniedException $accessDeniedException)
    {
        // do something with your exception and return Response object (plain message of rendered template)


        $subject = (array)$accessDeniedException->getSubject();
        $info = $subject["attributes"]->all();

        $data = json_encode(array(
            "Message" => $accessDeniedException->getMessage(),
            "Code" => $accessDeniedException->getCode(),
            "Info" => $info

        ));

        return new Response($data, 403);
    }
}
