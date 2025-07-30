<?php

namespace BackendBundle\Controller;

use Doctrine\DBAL\VersionAwarePlatformDriver;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints as Assert;

class UserVideoController extends Controller
{
    /**
     * Descripcion especifica de este metdo
     *
     * @ApiDoc(
     *  section = "Bitacora Video Studio",
     *  description="Listado de la Bitacora de Video Studio",
     *  requirements={
     *      {"name"="page", "dataType"="int", "required"="true", "default"="1", "description"="numero de pagina, si se omite es 1"},
     *      {"name"="size", "dataType"="int", "required"="true", "default"="10", "description"="numero de items, si se omite es 10"}
     *   },
     *  headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function getDataAction(Request $request)
    {
        $helpers = $this->get('app.helpers');

        $page = $request->get("page", 1);
        $size = $request->get("size", 10);

        $em = $this->getDoctrine()->getManager();
        $bitacora = $em->getRepository('BackendBundle:UserVideo')->findAll();

        $paginator = $this->get("knp_paginator");
        $items_per_page = $size;

        $pagination = $paginator->paginate($bitacora, $page, $items_per_page);
        $total_items_count = $pagination->getTotalItemCount();

        $list = true;
        $data = $helpers->responseData(200, $msg = null, $list, $pagination, $total_items_count, $page, $items_per_page);

        return $helpers->json($data);
    }
    
}
