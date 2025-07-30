<?php

namespace BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints as Assert;
class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->redirect($this->generateUrl('nelmio_api_doc_index'));
    }

    /**
     * Descripcion especifica de este metdo
     *
     * @ApiDoc(
     *  description="Home del sitio"
     * )
     */
    public function testAction(){
        return new Response(
            '<html><body>HOLA TEst</body></html>'
        );
    }





}
