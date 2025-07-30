<?php

namespace BackendBundle\Controller;

use Elastica\Transport\Null;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use BackendBundle\Entity\Tag;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use Elastica\Query\QueryString;
use Elastica\Query;
use \Elastica\Query\Terms;
use Elastica\Query\BoolQuery;
use Elastica\QueryBuilder;

use Gedmo\Sluggable\Util\Urlizer;


class TagController extends Controller
{
    var $max_rows = 3000;

    private function slugValidate($slug)
    {
        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository('BackendBundle:Tag')->findOneBySlug($slug);
        return (($page == NULL) ? FALSE : TRUE);
    }

    /**
     * Para acceder a este metodo, se require autorizacion(token)
     *
     * @ApiDoc(
     *  section = "Tag",
     *  description="Get Tag by ID, private method",
     *    requirements={
     *      {"name"="id", "dataType"="string", "required"=true, "description"="Tag Id"}
     *    },
     *   headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     * )
     */
    public function getTagAction(Request $request, $id)
    {
        $helpers = $this->get('app.helpers');
        $em = $this->getDoctrine()->getManager();
        $tag = $em->getRepository('BackendBundle:Tag')->find($id);

        if($tag != null){
            $data = array(
                "status" => "success",
                "data" => $tag,
            );
            return $helpers->json($data);

;        }else{
            $msg = "Tag Id not found in DB";
            $data = $helpers->responseData($code = 404, $msg);
            $response = $helpers->responseHeaders($code = 404, $data);

            return $response;
        }
    }

    /**
     * Para acceder a este metodo, se require autorizacion(token)
     *
     * @ApiDoc(
     *  section = "Tag",
     *  description="Creacion de tags, private method",
     *    requirements={
     *      {"name"="title",        "dataType"="string",    "required"=true, "description"="Title"},
     *      {"name"="type",         "dataType"="string",    "required"=true, "description"="Type Tag"},
     *      {"name"="slugRedirect", "dataType"="string",    "required"=true, "description"="slugRedirect"},
     *      {"name"="idRedirect",   "dataType"="string",    "required"=true, "description"="idRedirect"},
     *      {"name"="active",       "dataType"="boolean",   "required"=true, "description"="Active"}
     *    },
     *   headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     * )
     */
    public function newTagAction(Request $request)
    {
        $helpers = $this->get("app.helpers");

        $title        = $request->get('title');
        $type         = $request->get('type');
        $slugRedirect = $request->get('slugRedirect');
        $idRedirect   = $request->get('idRedirect');
        $active       = $request->get('active');
        $em           = $this->getDoctrine()->getManager();
        $tag          = $em->getRepository('BackendBundle:Tag')->findOneByTitle($title);

        if ($tag == null) {
            $tag = new Tag();

            $tag->setTitle($title);
            if ($type != null) {
                $tag->setType($type);
            } else {
                $tag->setType(NULL);
            }
            if ($active == 'true') {
                $tag->setActive(1);
            } else {
                $tag->setActive(0);
            }
            $tag->setSlugRedirect( $slugRedirect );
            $tag->setIdRedirect( $idRedirect );

            $tag->setCreatedAt(new \DateTime());
            $tag->setUpdatedAt(new \DateTime());

            $slug_create   = Urlizer::urlize($title);
            $slug_validate = $this->slugValidate($slug_create);
            if ($slug_validate == true) {
                $msg      = 'Error. Slug exist in DB';
                $data     = $helpers->responseData($code = 400, $msg);
                $response = $helpers->responseHeaders($code = 400, $data);

                return $response;
            } else {
                $tag->setSlug(Urlizer::urlize($title));
            }

            $validator = $this->get('validator');
            $errors    = $validator->validate($tag);

            $em->persist($tag);
            $em->flush();

            foreach ($errors as $error) {
                $messages[] = $error->getMessage();
            }

            if (count($errors) > 0) {
                $data     = $helpers->responseData($code = 400, $messages);
                $response = $helpers->responseHeaders($code = 400, $data);
            } else {
                $msg      = 'Tag created!';
                $data     = $helpers->responseData($code = 200, $msg);
                $response = $helpers->responseHeaders($code = 200, $data);
            }

        } else {
            $msg = 'Ya existe una Tag con el mismo título';
            $data = $helpers->responseData($code = 400, $msg);
            $response = $helpers->responseHeaders($code = 400, $data);
        }
        return $response;
    }

    /**
     * Metodo para editar un Tag
     *
     * @ApiDoc(
     *  section = "Tag",
     *  description="Metodo para editar un tag, private method",
     *  requirements={
     *      {"name"="id",           "dataType"="string",    "required"=true, "description"="id tag"},
     *      {"name"="title",        "dataType"="string",    "required"=true, "description"="Title"},
     *      {"name"="type",         "dataType"="string",    "required"=true, "description"="Type Tag"},
     *      {"name"="slugRedirect", "dataType"="string",    "required"=true, "description"="slugRedirect"},
     *      {"name"="idRedirect",   "dataType"="string",    "required"=true, "description"="idRedirect"},
     *      {"name"="active",       "dataType"="boolean",   "required"=true, "description"="Active"}
     *  },
     *  headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function editTagAction(Request $request, $id)
    {
        $helpers = $this->get("app.helpers");
        $em      = $this->getDoctrine()->getManager();

        $title        = $request->get('title');
        $type         = $request->get('type');
        $slugRedirect = $request->get('slugRedirect');
        $idRedirect   = $request->get('idRedirect');
        $active       = $request->get('active');

        $tag = $em->getRepository('BackendBundle:Tag')->find($id);

        if ($tag != null) {
            $tag->setCreatedAt(new \DateTime());
            $tag->setUpdatedAt(new \DateTime());

            if ($title != null) {
                $tag->setTitle($title);
                $slug_edit     = Urlizer::urlize($title);
                $slug_validate = $this->slugValidate($slug_edit);
                if ($slug_validate == true) {
                    $msg      = 'Error. Slug exist in DB';
                    $data     = $helpers->responseData($code = 400, $msg);
                    $response = $helpers->responseHeaders($code = 400, $data);

                    return $response;
                } else {
                    $tag->setSlug(Urlizer::urlize($title));
                }
            }

            $tag->setType($type);

            if ($active != null) {
                if ($active == 'true') {
                    $tag->setActive(1);
                } else {
                    $tag->setActive(0);
                }
            }

            $tag->setSlugRedirect( $slugRedirect );
            $tag->setIdRedirect( $idRedirect );

            $em->persist($tag);
            $em->flush();

            $msg = 'Tag updated.';
            $data = $helpers->responseData($code = 200, $msg);
            $response = $helpers->responseHeaders($code = 200, $data);

        } else {
            $msg = 'Tag not found in DB.';
            $data = $helpers->responseData($code = 404, $msg);
            $response = $helpers->responseHeaders($code = 404, $data);
        }

        return $response;

    }

    /**
     * Metodo para borrar un Tag
     * @ApiDoc(
     *     section = "Tag",
     *  description="Metodo para eliminar un tag, private method",
     *  requirements={
     *      {"name"="id", "dataType"="string", "required"=true, "description"="id user"}
     *  },
     *  headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function deleteTagAction(Request $request, $id)
    {
        $helpers = $this->get("app.helpers");
        $em = $this->getDoctrine()->getManager();

        $tag = $em->getRepository("BackendBundle:Tag")->find($id);

        if ($tag != null) {
            $em->remove($tag);
            $em->flush();

            $msg = 'Tag Deleted.';
            $data = $helpers->responseData($code = 200, $msg);
            $response = $helpers->responseHeaders($code = 200, $data);
        } else {
            $msg = 'Tag not found in DB.';
            $data = $helpers->responseData($code = 404, $msg);
            $response = $helpers->responseHeaders($code = 404, $data);
        }

        return $response;
    }


    /*  /**
       * Para acceder a este metodo, se require autorizacion(token)
       *
       * @ApiDoc(
       *  section = "Tag",
       *  description="Regresa el listado de tags sin type, private method",
       *  requirements={
       *      {"name"="page", "dataType"="int", "required"="false", "default"="1", "description"="Page number"},
       *      {"name"="itemsInPage", "dataType"="int", "required"="false", "default"="10", "description"="Items per page, default is 10"}
       *   },
       *   headers={
       *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
       *    }
       * )
       * )
       */
    public function tagsNtAction(Request $request)
    {


        //En este query, obtengo las tags donde 'type' sea NULL
        /*$qb = $tag_repo->createQueryBuilder('t');
        $qb->where(
            $qb->expr()->andx(
                $qb->expr()->isNull('t.type')
            ));
        $tags = $qb->getQuery()->getScalarResult();*/
        /*$query = $tag_repo->createQueryBuilder('t')
            ->where('t.type is NULL')
            ->getQuery();*/

        /*$finder = $this->container->get('fos_elastica.finder.efredisenio.tag');
        $boolQuery = new \Elastica\Query\BoolQuery();
        $categoryQuery = new \Elastica\Query\Terms();
        $categoryQuery->setTerms('type', 'company');
        $boolQuery->addMust($categoryQuery);

        $data = $finder->find($boolQuery);
        */

        //$sm = $this->get('fos_elastica.finder.manager');
        // $finder = $this->container->get('fos_elastica.finder.efredisenio.tag');
        //$searchTerm = $request->query->get('search');

        //$nameQuery = new \Elastica_Query_Text();
        //$nameQuery->setFieldQuery('type', 'company');
        //$nameQuery->setFieldParam('name', 'analyzer', 'snowball');

        //$boolQuery = new \Elastica_Query_Bool();
        //$boolQuery->addShould($nameQuery);

        //$data = $finder->search($boolQuery);

        //$sites = $sm->getRepository('ExampleBundle:Site')->find($boolQuery);

        //echo count($data);
        //die();

        //$repositoryManager = $this->container->get('fos_elastica.manager');


        //$data = $repositoryManager->getRepository('BackendBundle:Tag')->findByNotType('company');


        /*$helpers = $this->get("app.helpers");
        $maxItems = 100;

        $queryString = new QueryString();
        $queryString->setFields(array('_all'))
            ->setDefaultOperator('OR')
            ->setQuery('*');

        $query = new \Elastica\Query();
        $query->setSize($maxItems);
        $query->setFrom(($page - 1) * $maxItems);

        $index = $this->get('fos_elastica.index.efredisenio');
        $search = $index->createSearch();
        $search->addType('tag');

        $results = $search->search($query);

        $paginator = $this->get('knp_paginator');
        foreach ($results as $hybridResult) {
            $items[] = $hybridResult->getHit();
        }

        if (isset($items)) {
            $options = [
                'sortNestedPath' => 'owner',
                'sortNestedFilter' => new Query\Term(['type' => ['value' => 'company']]),
            ];
            $pagination = $paginator->paginate($items, $page, $size, $options);
            $page_items = $pagination->getItems();
            $total_items_count = count($items);
            $data = array(
                "status" => "success",
                "total_items_count" => $total_items_count,
                "page_actual" => $page,
                "items_per_page" => $size,
                "data" => $page_items
            );
        } else {
            $data = array(
                "status" => "No Data",
                "total_items_count" => 0,
                "page_actual" => 0,
                "items_per_page" => 0,
                "data" => "No Data"
            );
        }

        return $helpers->json($data);*/

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $items = array();


        $finder = $this->get('fos_elastica.finder.efredisenio.tag');

        $page = $request->query->get('page');

        $size = $request->query->get('itemsInPage');

        $boolQuery = new BoolQuery();
        $queryString = new QueryString();
        $query = new Query();
        $filter = new Terms();

        $queryString->setQuery("*");

        $boolQuery->addMust($queryString);

        $filter->setTerms('type', ['company']);

        $query->setSort(['createdAt' => ['order' => 'desc']]);

        $boolQuery->addMust($filter);

        $query->setQuery($boolQuery);

        $hybridResults = $finder->findHybrid($query, $this->max_rows);


        foreach ($hybridResults as $hybridResult) {
            $items[] = $hybridResult->getResult()->getHit();
        }

        $paginator = $this->get('knp_paginator');

        $pagination = $paginator->paginate($items, $page, $size);

        $total_items_count = $pagination->getTotalItemCount();

        if (isset($items)) {

            $data = array(
                "status" => "success",
                "page" => $page,
                "items_per_page" => $size,
                "total_items" => $total_items_count,
                "data" => $pagination,
            );

        } else {

            $data = array(
                "status" => "No Data",
                "total_items_count" => 0,
                "page_actual" => 0,
                "items_per_page" => 0,
                "data" => "No Data"
            );

        }

        $jsonContent = $serializer->serialize($data, 'json');
        $response = new Response($jsonContent);

        return $response;
    }

    /**
     * Para acceder a este metodo, se require autorizacion(token)
     *
     * @ApiDoc(
     *  section = "Tag",
     *  description="Regresa el listado de tags con type, private method",
     *   requirements={
     *      {"name"="page", "dataType"="int", "required"="false", "default"="1", "description"="Page number"},
     *      {"name"="itemsInPage", "dataType"="int", "required"="false", "default"="10", "description"="Items per page, default is 10"}
     *   },
     *   headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     * )
     */
    public function tagsTypeAction(Request $request)
    {
        //En este query, obtengo las tags donde 'type' NO sea NULL
        /*$qb = $tag_repo->createQueryBuilder('t');
        $qb->where(
            $qb->expr()->andx(
                $qb->expr()->isNotNull('t.type')
            ));
        $tags = $qb->getQuery()->getScalarResult();*/

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $items = array();


        $finder = $this->get('fos_elastica.finder.efredisenio.tag');

        $page = $request->query->get('page');

        $size = $request->query->get('itemsInPage');

        $boolQuery = new BoolQuery();
        $queryString = new QueryString();
        $query = new Query();
        $filter = new Terms();

        $queryString->setQuery("*");

        $boolQuery->addMust($queryString);

        $filter->setTerms('type', ['company']);

        $query->setSort(['createdAt' => ['order' => 'desc']]);

        $boolQuery->addMust($filter);

        $query->setQuery($boolQuery);

        $hybridResults = $finder->findHybrid($query, $this->max_rows);


        foreach ($hybridResults as $hybridResult) {
            $items[] = $hybridResult->getResult()->getHit();
        }

        $paginator = $this->get('knp_paginator');

        $pagination = $paginator->paginate($items, $page, $size);

        $total_items_count = $pagination->getTotalItemCount();

        if (isset($items)) {

            $data = array(
                "status" => "success",
                "page" => $page,
                "items_per_page" => $size,
                "total_items" => $total_items_count,
                "data" => $pagination,
            );

        } else {

            $data = array(
                "status" => "No Data",
                "total_items_count" => 0,
                "page_actual" => 0,
                "items_per_page" => 0,
                "data" => "No Data"
            );

        }

        $jsonContent = $serializer->serialize($data, 'json');
        $response = new Response($jsonContent);

        return $response;


    }

    private
    function minify_output($buffer)
    {
        $search = array(
            '/\>[^\S ]+/s',
            '/[^\S ]+\</s',
            '/(\s)+/s'
        );
        $replace = array(
            '>',
            '<',
            '\\1'
        );
        if (preg_match("/\<html/i", $buffer) == 1 && preg_match("/\<\/html\>/i", $buffer) == 1) {
            $buffer = preg_replace($search, $replace, $buffer);
        }
        return $buffer;
    }

}

