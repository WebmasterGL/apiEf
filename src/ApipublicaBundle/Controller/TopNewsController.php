<?php

namespace ApipublicaBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Elastica\Query;

class TopNewsController extends Controller
{

    /**
     * Para acceder a este metodo, no se require autorizacion(token)
     * @ApiDoc(
     *     section = "TopNews",
     *     description="Get Top News Pages",
     * )
     */
    public function getTopNewsAction(Request $request)
    {
        $helpers = $this->get('app.helpers');
        $finder          = $this->get('fos_elastica.index.efredisenio.topnews');
        $query           = new Query();
        $losTopNews = array();
        $query->setSort( ["id" => ['order' => 'asc'] ] );

        $searchResults = $finder->search($query,20);



        foreach ($searchResults as $hybridResult) {
                $item                        = $hybridResult->getHit();
                array_push($losTopNews, array(
                   "id"=>$item["_source"]["id"],
                   "slug"=>$item["_source"]["slug"]
                 ));
        }

        if($losTopNews != null){
            $data = array(
                "code" => 200,
                "status" => "success",
                "data" => $losTopNews
            );
        }else{
            $pages_slugs = array();
            $data = array(
                "code" => 200,
                "status" => "success",
                "data" => $pages_slugs
            );
        }

        return $helpers->json($data, true);

    }
}
