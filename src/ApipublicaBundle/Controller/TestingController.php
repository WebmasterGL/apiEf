<?php

namespace ApipublicaBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TestingController extends Controller
{

    /**
     * Para acceder a este metodo, se require autorizacion(token)
     * @ApiDoc(
     *     section = "Testing",
     *     description="Probando funcionalidad en ApiPublica",
     *     requirements={
     *      {"name"="param", "dataType"="array", "required"=true, "description"="slug"}
     *    }
     * )
     */
    public function indexAction($param){
        $helpers     = $this->get("app.helpers");

        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository('BackendBundle:Page')->find($param);

        //var_dump($page->getTitle());

        $msg = 'Page Obtenida!';
        $data = $helpers->responseData($code = 200, $msg);
        $data['titulo'] = $page->getTitle();
        $response = $helpers->responseHeaders($code = 200, $data);

        return $response;
    }
}
