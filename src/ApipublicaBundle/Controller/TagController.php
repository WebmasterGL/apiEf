<?php

namespace ApipublicaBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;


class TagController extends Controller
{

    /**
     * Para acceder a este metodo, no se require autorizacion(token)
     * @ApiDoc(
     *     section = "Tags",
     *     description="Get Tag by Id",
     *     requirements={
     *      {"name"="id", "dataType"="string", "required"=true, "description"="Tag Id"}
     *    }
     * )
     */
    public function getTagAction(Request $request, $id)
    {
        $helpers = $this->get('app.helpers');
        $em = $this->getDoctrine()->getManager();
        $tag = $em->getRepository('BackendBundle:Tag')->find($id);

        if ($tag != null) {
            $data = array(
                "status" => "success",
                "data" => $tag
            );
            return $helpers->json($data, true);

        } else {
            $msg = "Tag Id not found in DB";
            $data = $helpers->responseData($code = 404, $msg);
            $response = $helpers->responseHeaders($code = 404, $data);

            return $response;
        }
    }
}
