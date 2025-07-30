<?php

namespace BackendBundle\Controller;

use Elastica\Aggregation\DateRange;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use Elastica\Query\QueryString;
use Elastica\Query;
use \Elastica\Query\Terms;
use \Elastica\Query\Term;
use Elastica\Query\BoolQuery;
use Elastica\Aggregation\Filter;
use Elastica\Query\Nested;

class SearchController extends Controller
{
    public $max_rows        = 250;
    public $avoid_types     = array("tag");
    public $order_page_desc = true;

    /**
     * Para acceder a este metodo, se require autorizacion(token)
     *
     * @ApiDoc(
     *  section = "Search",
     *  description="Legacy news",
     *     requirements={
     *     {"name"="json",    "dataType"="array",   "required"=true,    "description"="Conditions: search, min_date, max_date, categoryId(str), author(str), (Only for tags->type:[''|'company']), status( default | published | scheduled ), portada(only for portadafolds). Don't use special chars and use just one word by criteria in no date type columns."},
     *     {"name"="type",    "dataType"="string",  "required"=true,    "default"="page", "description"="page"},
     *     {"name"="subtype", "dataType"="string",  "required"=true,    "default"="article | blogpost | tv | galeria | estatico | apunte", "description"="article | blogpost | tv | galeria | estatico | apunte"},
     *     {"name"="page",    "dataType"="int",     "required"="false", "default"="1",      "description"="Page"},
     *     {"name"="size",    "dataType"="int",     "required"="false", "default"="10",     "description"="Tamaño de la pagina, si se omite es 10"},
     *     {"name"="public",  "dataType"="boolean", "required"="false", "default"="false",  "description"="La consulta es publica"}
     *    },
     *   headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     * )
     */
    public function legacyAction(Request $request)
    {
        $encoders    = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer  = new Serializer($normalizers, $encoders);
        $page        = array();
        $result      = array();
        $json        = $request->get('json', "");
        $type        = $request->get('type', "page");
        $subtype     = $request->get('subtype', "");
        $page        = $request->get('page', 1);
        $size        = $request->get('size', 10);
        $public      = true;  //$request->get('public', false);
        $max_rows    = $request->get('max_rows', 0);

        if ($json != "") {
            $result = $this->broadLegacy($type, $subtype, $json, $page, $size, $public, $max_rows );
            $data   = array(
                "data" => $result
            );
        } else {
            $data = array(
                "data" => "No data"
            );
        }
        $jsonContent = $serializer->serialize($data, 'json');
        $response    = new Response($jsonContent);
        $response->headers->set('Content-Type', 'application/json');

        return $response;

    }
        /**
     * Para acceder a este metodo, se require autorizacion(token)
     *
     * @ApiDoc(
     *  section = "Search",
     *  description="Last News (published or scheduled or saved)",
     *     requirements={
     *     {"name"="type",        "dataType"="string",  "required"="false", "default"="published | scheduled | saved",  "description"="Set a note's state"},
     *     {"name"="page",        "dataType"="int",     "required"="false", "default"="1",                              "description"="Page number"},
     *     {"name"="itemsInPage", "dataType"="int",     "required"="false", "default"="10",                             "description"="Items per page, default is 10"}
     *    },
     *   headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     * )
     */
    public function lastNewsAction(Request $request)
    {
        $encoders    = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer  = new Serializer($normalizers, $encoders);
        $items       = array();
        $pagination  = array();
        $result      = array();

        $finder = $this->get('fos_elastica.finder.efredisenio.page');
        $type   = $request->get('type');
        $page   = $request->get('page');
        $size   = $request->get('itemsInPage');
        $public = $request->get('public');

        if ( $public === true || $public == "true" ) {
            $categories["fullAccess"] = true;
        } else {
            $users       = $this->get('app.users');
            $categories = $users->getCategoriesUserLogged();                         //get current user's cats
            $cats_ids   = array();
            foreach ($categories["categories"] as $l_cat)                               //get cat's ids
            {
                array_push($cats_ids, $l_cat->getId());
            }
        }

        $boolQuery   = new BoolQuery();
        $queryString = new QueryString();
        $query       = new Query();
        $filter      = new Terms();
        $queryString->setQuery("*");
        $boolQuery->addMust($queryString);

        if ( !$categories["fullAccess"] ) {
            $cat_filter = new \Elastica\Query\Terms();            //sections restrictions
            $cat_filter->setTerms("categoryId", $cats_ids);
            $boolQuery->addMust($cat_filter);
        }

        switch ($type) {
            case "published":       //publicados

                $scheduledAnormalIds = array();

                $a                   = $this->broad("page", "", "{\"nextPublishedAt\":\"\"}", 1, $this->max_rows, $public, 0, true );
                foreach ( $a as $k1 => $v1 ){
                    if( $v1["_source"]["status"] == "published" ){
                        $scheduledAnormalIds[] = $v1["_source"]["id"];
                    }
                }
                //$filter->setTerms('status', ["published"]);
                $query->setSort(['publishedAt' => ['order' => 'desc']]);

                //$boolQuery->addMust($filter);
                $query->setQuery($boolQuery);
                $hybridResults = $finder->findHybrid($query, $size);
                foreach ($hybridResults as $hybridResult) {
                    $item = $hybridResult->getResult()->getHit();
                    if ( !in_array( $item["_source"]["id"], $scheduledAnormalIds ) ){
                        $items[] = $item;
                    }
                    //$items[] = $hybridResult->getResult()->getHit();
                }
                break;
            case "scheduled":       //programados

                $q_filter = new QueryString();
                $q_filter->setQuery("*");
                $q_filter->setFields(array("nextPublishedAt"));
                $q_filter->setAnalyzeWildcard();
                //$filter->setTerms('status', ["scheduled"]);

                $query->setSort(['nextPublishedAt' => ['order' => 'desc']]);
                $boolQuery->addMust($q_filter);
                //$boolQuery->addMust($filter);
                $query->setQuery($boolQuery);
                $hybridResults = $finder->findHybrid($query, $size);
                foreach ($hybridResults as $hybridResult) {
                    $items[] = $hybridResult->getResult()->getHit();
                }
                break;
            default:                //guardados
                $filter->setTerms('status', ['default']);
                $query->setSort(['createdAt' => ['order' => 'desc']]);
                $boolQuery->addMust($filter);
                $query->setQuery($boolQuery);
                $hybridResults = $finder->findHybrid($query, $size);
                foreach ($hybridResults as $hybridResult) {
                    $items[] = $hybridResult->getResult()->getHit();
                }
        }


        foreach ($items as $item) {                                                                      //change display for page content
            //$item["_source"]["html_serialize"]        = $this->convertEspecialCols($item["_source"]["html_serialize"]);
            $item["_source"]["elementHtmlSerialized"] = json_decode($item["_source"]["elementHtmlSerialized"], true);

            $item["_source"]["subcategories"] = $item["_source"]["category"];
            unset($item["_source"]["category"]);
            $item["_source"]["subcategories"] = $this->convertArrayJsonToArray($item["_source"]["subcategories"]);

            $result[] = $item;
        }

        $paginator         = $this->get('knp_paginator');
        $pagination        = $paginator->paginate($result, $page, $size);
        $total_items_count = $pagination->getTotalItemCount();

        if (isset($items)) {
            $data = array(
                "status"         => "success",
                "page"           => $page,
                "items_per_page" => $size,
                "total_items"    => $total_items_count,
                "data"           => $pagination,
            );
        } else {
            $data = array(
                "status"            => "No Data",
                "total_items_count" => 0,
                "page_actual"       => 0,
                "items_per_page"    => 0,
                "data"              => "No Data"
            );
        }
        $jsonContent = $serializer->serialize($data, 'json');
        $response    = new Response($jsonContent);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Para acceder a este metodo, se require autorizacion(token)
     *
     * @ApiDoc(
     *  section = "Search",
     *  description="Search by types: page(article,column,blogpost), image, videos",
     *     requirements={
     *     {"name"="json",    "dataType"="array",   "required"=true,    "description"="Conditions: search, min_date, max_date, categoryId(str), author(str), (Only for tags->type:[''|'company']), status( default | published | scheduled ), portada(only for portadafolds). Don't use special chars and use just one word by criteria in no date type columns."},
     *     {"name"="type",    "dataType"="string",  "required"=true,    "default"="page | image | tag | folds | portada | author | category | flags | columna | blog | carton | portadafolds | wfuser", "description"="page | image | tag | folds | portada | author | category | flags | columna | blog | carton | portadafolds | wfuser"},
     *     {"name"="subtype", "dataType"="string",  "required"=true,    "default"="article | column | blogpost | carton | tv | nothing for other type", "description"="article | column | blogpost | carton | tv | nothing for other type"},
     *     {"name"="page",    "dataType"="int",     "required"="false", "default"="1",      "description"="Page"},
     *     {"name"="size",    "dataType"="int",     "required"="false", "default"="10",     "description"="Tamaño de la pagina, si se omite es 10"},
     *     {"name"="public",  "dataType"="boolean", "required"="false", "default"="false",  "description"="La consulta es publica"}
     *    },
     *   headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     * )
     */
    public function allAction(Request $request)
    {
        $encoders    = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer  = new Serializer($normalizers, $encoders);
        $page        = array();
        $result      = array();
        $json        = $request->get('json', "");
        $type        = $request->get('type', "page");
        $subtype     = $request->get('subtype', "");
        $page        = $request->get('page', 1);
        $size        = $request->get('size', 10);
        $public      = $request->get('public', false);
        $max_rows    = $request->get('max_rows', 0);

        if ($json != "") {
            $result = $this->broad($type, $subtype, $json, $page, $size, $public, $max_rows);
            $data = array(
                "data" => $result
            );
        } else {
            $data = array(
                "data" => "No data"
            );
        }
        $jsonContent = $serializer->serialize($data, 'json');
        $response    = new Response($jsonContent);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    private function alternateSearch($type = "page", $subtype = "", $json = "", $public = false){
        $finder       = $this->get('fos_elastica.finder.efredisenio.' . $type);
        $conditions   = json_decode($json);
        $boolQuery    = new Query\Bool();
        $subtypeQuery = new Query\Match();
        $publicQuery  = new Query\Match();

        if ( $subtype != "" ){
            $subtypeQuery->setFieldQuery('pageType', $subtype);
            $boolQuery->addMust($subtypeQuery);
        }
        foreach ($conditions as $key => $val) {
            if ($type == "page" && $key == "slug") {
                $slugQuery = new Query\Match();
                $a         = substr_replace( $val, "", -1);
                $slugQuery->setFieldQuery( $key, $a );
            }
        }
        if ( $public === true || $public == "true" ){
            $publicQuery->setFieldQuery("status", "published" );
            $boolQuery->addMust( $publicQuery );
        }

        $boolQuery->addMust($slugQuery);


        $q = new Query($boolQuery);
        $results = $finder->find($q,100);

        return $results;
    }

    private function broad( $type = "page", $subtype = "", $json = "", $page = 1, $size = 10, $public = false, $max_rows=0, $partial=false )
    {
        $notes_sections_canales=null;
        $data_most_viewed=null;
        $notes_channels_tv=null;
        $notes_cartoon=null;

        $users           = $this->get('app.users');
        $items           = array();
        $result          = array();
        $conditions      = array();
        $boolQuery       = new BoolQuery();
        $boolQueryOR     = new BoolQuery();
        $boolQueryOR2    = new BoolQuery();
        $query           = new Query();
        $pagination      = array();
        $min_date        = "";
        $max_date        = "";
        $date_page       = "createdAt";
        $date_asset      = "createdAt";
        $finder          = $this->get('fos_elastica.finder.efredisenio.' . $type);
        $paginator       = $this->get('knp_paginator');
        $cats_ids        = array();
        $fieldsToAvoid   = array(
            "id",
            "name",
            "aPaterno",
            "aMaterno",
            "email",
            "bio",
            "image",
            "imageSmall",
            "twitter",
            "facebook",
            "linkedin",
            "googlePlus",
            "corresponsal",
            "rss",
            "slug",
            "google"
        );
        $defaultOperator  = "AND";                                                                                      //Other option OR
        $jsonPropsToAvoid = array(
            "phrase",
            "operator"
        );                                                                                                              //json properties to avoid in filters adding

        if ( $max_rows != 0 ){
            $this->max_rows = $max_rows;
        }

        if ( $public === true || $public == "true" ) {
            $categories["fullAccess"] = true;
            $date_page                = "updatedAt";
        } else {

            $categories = $users->getCategoriesUserLogged();                                                            //get current user's cats
            foreach ($categories["categories"] as $l_cat)                                                               //get cat's ids
            {
                array_push($cats_ids, $l_cat->getId());
            }
        }

        if ( $json != "" ) {
            $q_type = new Query\Match();
            $conditions = json_decode($json);
            $min_date = isset($conditions->min_date) ? $conditions->min_date : "";
            $max_date = isset($conditions->max_date) ? $conditions->max_date : "";

            $conditions->search = isset($conditions->search) ? $conditions->search . "*" : "";                          //add wildcard

            if ( $type == "page" ) {
                if ( isset( $conditions->search ) && $conditions->search != "" ){
                    if ( isset( $conditions->phrase ) && $conditions->phrase == "true" ){                               //word search
                        $q_title  = new Query\Match();
                        $q_first  = new Query\Match();
                        $q_desc   = new Query\Match();
                        $q_tag    = new Query\Match();
                        $q_html   = new Query\Match();
                        $q_author = new Query\Match();

                        $q_title->setFieldQuery("title", $conditions->search );
                        $q_title->setFieldType("title", "phrase" );

                        $q_first->setFieldQuery("firstTitle", $conditions->search );
                        $q_first->setFieldType("firstTitle", "phrase" );

                        $q_desc->setFieldQuery("shortDescription", $conditions->search );
                        $q_desc->setFieldType("shortDescription", "phrase" );

                        $q_tag->setFieldQuery("tag", $conditions->search );
                        $q_tag->setFieldType("tag", "phrase" );

                        if ( $public === true || $public == "true" ) {
                            $q_html->setFieldQuery("html", $conditions->search);
                            $q_html->setFieldType("html", "phrase");
                        }

                        if ( !in_array( str_replace( "*", "", strtolower( $conditions->search ) ), $fieldsToAvoid ) ) {
                            $q_author->setFieldQuery("author", $conditions->search);
                            $q_author->setFieldType("author", "phrase");
                            $boolQueryOR->addShould($q_author);
                        }
                    }else{                                                                                              //phrase
                        $q_title  = new QueryString();
                        $q_first  = new QueryString();
                        $q_desc   = new QueryString();
                        $q_tag    = new QueryString();
                        $q_html   = new QueryString();
                        $q_author = new QueryString();
                        $operator = "";

                        if ( isset( $conditions->operator ) && strtoupper( $conditions->operator ) == "OR" ){           //word search
                            $operator = $conditions->operator;
                        }else{
                            $operator = $defaultOperator;
                        }

                        $q_title->setQuery($conditions->search);
                        $q_title->setFields(array('title'));
                        $q_title->setAnalyzeWildcard();
                        $q_title->setDefaultOperator( $operator );

                        $q_first->setQuery($conditions->search);
                        $q_first->setFields(array('firstTitle'));
                        $q_first->setAnalyzeWildcard();
                        $q_first->setDefaultOperator( $operator );

                        $q_desc->setQuery($conditions->search);
                        $q_desc->setFields(array('shortDescription'));
                        $q_desc->setAnalyzeWildcard();
                        $q_desc->setDefaultOperator( $operator );

                        $q_tag->setQuery($conditions->search);
                        $q_tag->setFields(array('tag'));
                        $q_tag->setAnalyzeWildcard();
                        $q_tag->setDefaultOperator( $operator );

                        if ( $public === true || $public == "true" ) {
                            $q_html->setQuery($conditions->search);
                            $q_html->setFields(array('html'));
                            $q_html->setDefaultOperator($operator);
                        }

                        if ( !in_array( str_replace( "*", "", strtolower( $conditions->search ) ), $fieldsToAvoid ) ){
                            $q_author->setQuery($conditions->search);
                            $q_author->setFields(array('author'));
                            $q_author->setAnalyzeWildcard();
                            $q_author->setDefaultOperator( $operator );
                            $boolQueryOR->addShould($q_author);
                        }
                    }
                    $boolQueryOR->addShould($q_title);
                    $boolQueryOR->addShould($q_first);
                    $boolQueryOR->addShould($q_desc);
                    $boolQueryOR->addShould($q_tag);
                    $boolQueryOR->addShould($q_html);

                    $boolQuery->addMust($boolQueryOR);
                }

                if (!$categories["fullAccess"]) {
                    $cat_filter = new \Elastica\Query\Terms();                                          //sections restrictions
                    $cat_filter->setTerms("categoryId", $cats_ids);
                    $boolQuery->addMust($cat_filter);
                }
            } else if ($type == "tag") {                                                                 //simple way

                if ( isset( $conditions->search ) ) {
                    $a = explode(' ', $conditions->search );
                    if ( count( $a ) == 1 ) {                                                             //single word
                        $q_title = new QueryString();

                        $q_title->setQuery($conditions->search);
                        $q_title->setFields(array('title'));
                        $q_title->setAnalyzeWildcard();
                    }else{
                        $q_title = new Query\Match();
                        $q_title->setFieldQuery("title", $conditions->search );
                        $q_title->setFieldType("title", "phrase" );
                    }
                    $boolQueryOR->addMust($q_title);
                }


                if (isset($conditions->id)) {
                    $q_id = new QueryString();
                    $q_id->setQuery($conditions->id);
                    $q_id->setFields(array('id'));
                    $q_id->setAnalyzeWildcard();

                    $boolQuery->addMust($q_id);
                }

                if ( isset( $conditions->active ) ) {
                    $q_active = new QueryString();

                    if ($conditions->active == "true") {
                        $q_active->setQuery($conditions->active . ",1");
                    } else {
                        $q_active->setQuery($conditions->active . ",0");
                    }

                    $q_active->setFields(array('active'));
                    $q_active->setAnalyzeWildcard();

                    $boolQuery->addMust($q_active);
                }

                $boolQuery->addMust($boolQueryOR);
                $query->setQuery($boolQuery);

                $hybridResults = $finder->findHybrid($query, $this->max_rows);

                foreach ($hybridResults as $hybridResult) {
                    $item = $hybridResult->getResult()->getHit();
                    if ($conditions->type == "ALL") {
                        $items[] = $item;
                    } else {
                        if ($item["_source"]["type"] == $conditions->type) {
                            $items[] = $item;
                        }
                    }
                }
                if (count($items) > 0) {
                    $pagination = $paginator->paginate($items, $page, $size, array('defaultSortFieldName' => '[_source][title]', 'defaultSortDirection' => 'asc') );

                    return array($pagination->getTotalItemCount(), $pagination);
                } else {
                    return array(array(), array());
                }
            } else if ($type == "folds") {                                                                 //simple way
                $q_desc = new QueryString();
                $q_cat = new QueryString();

                $q_desc->setQuery($conditions->search);
                $q_desc->setFields(array('descripcion'));
                $q_desc->setAnalyzeWildcard();

                $q_cat->setQuery($conditions->id);
                $q_cat->setFields(array('category'));
                $q_cat->setAnalyzeWildcard();

                $boolQueryOR->addMust($q_desc);
                $boolQueryOR->addMust($q_cat);
                $boolQuery->addMust($boolQueryOR);

                $query->setQuery($boolQuery);
                $hybridResults = $finder->findHybrid($query, $this->max_rows);

                foreach ($hybridResults as $hybridResult) {
                    $item             = $hybridResult->getResult()->getHit();
                    $a["id"]          = $item["_source"]["id"];
                    $a["description"] = $item["_source"]["descripcion"];
                    $a["category"]    = $item["_source"]["category"];
                    $a["type"]        = $item["_source"]["idtipo"];
                    $items[]          = $a;
                }
                if (count($items) > 0) {
                    $pagination = $paginator->paginate($items, $page, $size);

                    return array($pagination->getTotalItemCount(), $pagination);
                } else {
                    return array(array(), array());
                }
            } else if ($type == "portada") {                                                                            //simple way

                $finder          = $this->get('fos_elastica.index.efredisenio.' . $type);

                $q_name = new QueryString();
                $q_cat = new QueryString();

                $q_name->setQuery($conditions->search);
                $q_name->setFields(array('name'));
                $q_name->setAnalyzeWildcard();

                $q_cat->setQuery($conditions->id);
                $q_cat->setFields(array('category'));
                $q_cat->setAnalyzeWildcard();

                $boolQueryOR->addMust($q_name);
                $boolQueryOR->addMust($q_cat);
                $boolQuery->addMust($boolQueryOR);

                if ($public === true || $public == "true") {
                    $q_status = new Query\Match();
                    $q_status->setFieldQuery("status", "published");
                    $boolQuery->addMust($q_status);
                }

                $query->setSort( [$date_page => ['order' => 'desc'] ] );

                $query->setQuery($boolQuery);
                //$hybridResults = $finder->findHybrid($query, $this->max_rows);
                $hybridResults = $finder->search($query, $this->max_rows);
                foreach ($hybridResults as $hybridResult) {
                    //$item                        = $hybridResult->getResult()->getHit();
                    $item                        = $hybridResult->getHit();
                    $item["_source"]["category"] = $this->convertEspecialCols( $item["_source"]["category"] );
                    if (!$categories["fullAccess"]) {
                        if ( in_array( $item["_source"]["category"]["id"], $cats_ids ) ){
                            $items[] = $item;
                        }
                    }else{
                            $items[] = $item;
                            /*if($item["_source"]["category"]["id"]==$conditions->id)
                            {
                                $items[] = $item;
                            }*/
                    }
                }
                if (count($items) >= 1 && ($public === true || $public == "true") ) { //FIX PARA EVITAR QUE SE ENVIEN VARIAS INSTANCIAS DE PORTADAS
                    return $this->broad("portadafolds", "", "{\"portada\":\"" . $items[0]["_source"]["id"] . "\"}", $page, $size, $public);

                } else if (count($items) > 0) {
                    $pagination = $paginator->paginate($items, $page, $size);

                    return array($pagination->getTotalItemCount(), $pagination);
                } else {
                    return array(array(), array());
                }
            } else if ($type == "author") {                                                                 //simple way
                if ( isset( $conditions->slug ) ){
                    $q_slug = new QueryString();
                    $q_slug->setQuery($conditions->slug);
                    $q_slug->setFields(array('slug'));
                    $boolQuery->addMust($q_slug);
                }else{
                    $a = explode( " ", $conditions->search );
                    if ( count( $a ) > 1 ){
                        $q_name   = new QueryString();
                        $q_ln_2   = new QueryString();

                        $q_name->setQuery( $a[0] );
                        $q_name->setFields(array('name'));

                        $q_ln_2->setQuery( $a[1] );
                        $q_ln_2->setFields(array('aPaterno'));

                        $boolQueryOR->addMust($q_name);
                        $boolQueryOR->addMust($q_ln_2);

                        $boolQuery->addMust($boolQueryOR);
                    }else{
                        $q_name   = new QueryString();
                        $q_ln_1   = new QueryString();
                        $q_ln_2   = new QueryString();

                        $q_name->setQuery($a[0]);
                        $q_name->setFields(array('name'));
                        $q_name->setAnalyzeWildcard();

                        $q_ln_1->setQuery($a[0]);
                        $q_ln_1->setFields(array('aMaterno'));
                        $q_ln_1->setAnalyzeWildcard();

                        $q_ln_2->setQuery($a[0]);
                        $q_ln_2->setFields(array('aPaterno'));
                        $q_ln_2->setAnalyzeWildcard();

                        $boolQueryOR->addShould($q_name);
                        $boolQueryOR->addShould($q_ln_1);
                        $boolQueryOR->addShould($q_ln_2);

                        $boolQuery->addMust($boolQueryOR);
                    }
                }
                if (isset($conditions->active)) {
                    $q_active = new QueryString();
                    if ($conditions->active == "true") {
                        $q_active->setQuery($conditions->active . ",1");
                    } else {
                        $q_active->setQuery($conditions->active . ",0");
                    }
                    $q_active->setFields(array('active'));
                    $q_active->setAnalyzeWildcard();

                    $boolQuery->addMust($q_active);
                }

                $query->setQuery($boolQuery);
                $hybridResults = $finder->findHybrid($query, $this->max_rows);

                foreach ($hybridResults as $hybridResult) {
                    $item = $hybridResult->getResult()->getHit();
                    if ( isset( $conditions->slug ) ){
                        if ( $item["_source"]["slug"] == $conditions->slug ){
                            $items[] = $item;
                            break;
                        }
                    }else{
                        $items[] = $item;
                    }
                }
                if (count($items) > 0) {
                    $pagination = $paginator->paginate($items, $page, $size);

                    return array($pagination->getTotalItemCount(), $pagination);
                } else {
                    return array(array(), array());
                }
            } else if ($type == "category") {                                                                 //simple way

                $finder          = $this->get('fos_elastica.index.efredisenio.' . $type);

                $q_title = new QueryString();

                $q_title->setQuery($conditions->search);
                $q_title->setFields(array('title'));
                $q_title->setAnalyzeWildcard();

                $boolQueryOR->addMust($q_title);

                $boolQuery->addMust($boolQueryOR);

                if ( isset( $conditions->active ) ) {
                    $q_active = new QueryString();

                    if ($conditions->active == "true") {
                        $q_active->setQuery($conditions->active . ",1");
                    } else {
                        $q_active->setQuery($conditions->active . ",0");
                    }

                    $q_active->setFields(array('active'));
                    $q_active->setAnalyzeWildcard();

                    $boolQuery->addMust($q_active);
                }

                if ( isset( $conditions->id ) ) {
                    $q_id = new QueryString();
                    $q_id->setQuery( $conditions->id );
                    $q_id->setFields( array( 'id' ) );

                    $boolQuery->addMust($q_id);
                }

                if ( isset( $conditions->parentid ) ) {
                    $q_parentid = new QueryString();
                    $q_parentid->setQuery( $conditions->parentid );
                    $q_parentid->setFields( array( 'parentId' ) );

                    $boolQuery->addMust($q_parentid);
                }

                if ( isset( $conditions->slug ) ) {
                    $q_slug = new QueryString();
                    $q_slug->setQuery( $conditions->slug );
                    $q_slug->setFields( array( 'slug' ) );

                    $boolQuery->addMust($q_slug);
                }

                if ( $public === true || $public == "true" ) {                                                          //set permited categories
                    $categories["fullAccess"] = true;
                } else {
                    $categories = $users->getCategoriesUserLogged();                                                    //get current user's cats
                    foreach ($categories["categories"] as $l_cat)                                                       //get cat's ids
                    {
                        array_push($cats_ids, $l_cat->getId());
                    }
                }
                if (!$categories["fullAccess"]) {
                    $cat_filter = new \Elastica\Query\Terms();                                                          //sections restrictions
                    $cat_filter->setTerms("id", $cats_ids);
                    $boolQuery->addMust($cat_filter);
                }

                $query->setSort( ["id" => ['order' => 'asc'] ] );

                $query->setQuery($boolQuery);
                //$hybridResults = $finder->findHybrid($query, $this->max_rows);
                $hybridResults = $finder->search($query, $this->max_rows);

                foreach ($hybridResults as $hybridResult) {
                    //$item = $hybridResult->getResult()->getHit();
                    $item = $hybridResult->getHit();

                    if ( $item["_source"]["parentId"] != "1" && !isset( $conditions->slug ) ){                          //no slug y no hijo de home
                        if ( $public === false || $public === "false" ) {
                            $section = $this->broad("category", "", "{\"search\":\"*\",\"id\":\"" . $item["_source"]["parentId"] . "\"}", 1, $this->max_rows, $public);
                            if ( isset( $section[1] ) && gettype( $section[1] ) == "object" ) {
                                $sectionTitle = $section[1]->getItems()[0]["_source"]["title"];
                                $item["_source"]["title"] = $sectionTitle . " - " . $item["_source"]["title"];
                            }
                        }
                    }
                    if ( isset( $conditions->slug ) ) {
                        if ( $item["_source"]["slug"] == $conditions->slug ){                               //es slug, tre hijos
                            $children                    = $this->broad("category", "", "{\"search\":\"*\",\"parentid\":\"" . $item["_source"]["id"] . "\"}", 1, $this->max_rows, $public );
                            $item["_source"]["children"] = $children;
                            $items[]                     = $item;
                        }
                    }else{
                        $items[] = $item;
                    }
                }
                if (count($items) > 0) {
                    $pagination = $paginator->paginate($items, $page, $size);

                    $helpers = $this->get("app.helpers");
                    $em = $this->getDoctrine()->getManager();
                    if(isset($conditions->slug) or isset($conditions->id)){
                        // Get paramter host_name
                        if ($this->container->hasParameter('host_name') == true) {
                            $host = $this->container->getParameter('host_name');
                        } else {
                            $host = null;
                        }

                        if($public === false || $public === 'false'){
                            $microdata = null;
                        }else{
                            $home_canales = false;

                            if($conditions->slug != null){
                                $category =  $em->getRepository("BackendBundle:Category")->findOneBy(array("slug" => $conditions->slug));
                                // Get section portada
                                $folds_published_seccion = $this->broad("portada", "", "{\"search\":\"*\",\"id\":\"" . $category->getId() . "\"}", 1, $this->max_rows, $public);

                                // Get last 10 notes section
                                $last_notes_section = $this->broad("page", "", "{\"search\":\"*\",\"categoriesslug\":\"" . $category->getSlug() . "\"}", 1, 10, true);
                                
                                // If slug is 'tv'
                                if($conditions->slug == 'tv'){
                                    $portada_tv = $this->broad("portada", "", "{\"search\":\"*\",\"id\":\"" . $category->getId() . "\"}", 1, 10, true);
                                    $folds_published_seccion = $portada_tv;

                                    $channels_id = $helpers->getChannelsPortada($portada_tv);
                                    
                                    // fix temporal channels tv
                                    //$notes_channels_tv = null;
                                
                                   if(!empty($channels_id)){
                                        $notes_channels_tv = [];

                                       $channel_filter = new \Elastica\Query\Terms();
                                        foreach($channels_id as $channel_id){

                                            $channel_filter->setTerms("id", array($channel_id));

                                            $elChannel = $finder->search($channel_filter,1);

                                            $category_channel=null;

                                            foreach ($elChannel as $channel) {
                                                $category_channel = $channel->getHit();
                                            }


                                            //$category_channel =  $em->getRepository("BackendBundle:Category")->find($channel_id);
                                            //$search_last_notes_channel = $this->broad("page", "tv", "{\"search\":\"*\",\"categoriesslug\":\"" . $category_channel->getSlug() . "\"}", 1, 3, true);
                                            $search_last_notes_channel = $this->broad("page", "tv", "{\"search\":\"*\",\"categoriesslug\":\"" . $category_channel["_source"]["slug"] . "\"}", 1, 3, true);
                                            $notes_last_channels = $helpers->getNotesSearchSpecial($search_last_notes_channel, $host);
                                            array_push($notes_channels_tv, $notes_last_channels);
                                        }
                                   }

                                }

                                // if 'slug' is 'opinion'
                                if($conditions->slug == 'opinion'){   
                                    // Get opinion portada 
                                    $folds_published_seccion = $this->broad("portada", "", "{\"search\":\"*\",\"id\":\"" . $category->getId() . "\"}", 1, 10, true);
                                    // Get cartoon portada -->Id Carton section(67) 
                                    $id_cartoon = 67;
                                    $folds_published_cartoon = $this->broad("portada", "", "{\"search\":\"*\",\"id\":\"" . $id_cartoon . "\"}", 1, 10, true);
                                    $notes_cartoon = $helpers->getNotesSearch($folds_published_cartoon, $host);
                                }

                                // Get most viewed for section
                                $data_most_viewed = '';
                                try{
                                    $most_viewed = $this->forward('ApipublicaBundle:Analytics:section', array('section' => $conditions->slug)); 
                                    $notes_most_viewed = json_decode($most_viewed->getContent(), true);
                                    $data_most_viewed =  $helpers->getNotesMostViewed($notes_most_viewed, $host);
                                } catch(Exception $e){
                                    //echo $e->getMessage();
                                    $data_most_viewed = null;
                                }
                            }else{
                                $category =  $em->getRepository("BackendBundle:Category")->find($conditions->id);
                                // Get section portada
                                $folds_published_seccion = $this->broad("portada", "", "{\"search\":\"*\",\"id\":\"" . $conditions->id . "\"}", 1, $this->max_rows, $public);

                                // Unicamente si la categoria no es Home
                                if($category->getId() != 1){
                                    // Get last 10 notes section
                                    $last_notes_section = $this->broad("page", "", "{\"search\":\"*\",\"categoriesslug\":\"" . $category->getSlug() . "\"}", 1, 10, true);
                                }else{
                                    $last_notes_section = null;
                                }
                                
                                // Only if 'id' is 1(Home)
                                if($conditions->id == 1){
                                    $notes_sections_canales = [];
                                    
                                    // Get array with slugs sections of canales fold
                                    $sections_canales_array = $helpers->getSectionsCanales($folds_published_seccion);
                                    foreach($sections_canales_array as $section){
                                        // Get last 3 section notes for canales
                                        $search_last_notes_section = $this->broad("page", "", "{\"search\":\"*\",\"categoriesslug\":\"" . $section . "\"}", 1, 3, true);
                                        $notes_last_section_canal = $helpers->getNotesSearch($search_last_notes_section, $host);
                                        array_push($notes_sections_canales, $notes_last_section_canal);

                                        $home_canales = true;
                                    }

                                    // Get most viewed for home
                                    $data_most_viewed = '';
                                    try{
                                        $most_viewed = $this->forward('ApipublicaBundle:Analytics:extended'); 
                                        $notes_most_viewed = json_decode($most_viewed->getContent(), true);
                                        $data_most_viewed =  $helpers->getNotesMostViewed($notes_most_viewed, $host);
                                    } catch(Exception $e){
                                        //echo $e->getMessage();
                                        $data_most_viewed = null;
                                    }
                                }elseif ($conditions->id == 81) {
                                    $channels_id = $helpers->getChannelsPortada($folds_published_seccion);
                                
                                   if(!empty($channels_id)){
                                        $notes_channels_tv = [];

                                        foreach($channels_id as $channel_id){
                                            
                                            $category_channel =  $em->getRepository("BackendBundle:Category")->find($channel_id);
                                            $search_last_notes_channel = $this->broad("page", "tv", "{\"search\":\"*\",\"categoriesslug\":\"" . $category_channel->getSlug() . "\"}", 1, 3, true);
                                            $notes_last_channels = $helpers->getNotesSearchSpecial($search_last_notes_channel, $host);
                                            array_push($notes_channels_tv, $notes_last_channels);
                                        }
                                   }
                                }
                            }
                            
                            if($home_canales == true){
                                //$microdata = null;
                                $microdata  = $helpers->microData($category, $from = "redisenio_portada", $folds_published_seccion, $last_notes_section, $notes_sections_canales, null, null, $data_most_viewed);
                            }else{
                                $microdata  = $helpers->microData($category, $from = "redisenio_portada", $folds_published_seccion, $last_notes_section, null, $notes_channels_tv, $notes_cartoon, $data_most_viewed);
                            }
                            
                            ( $microdata != null ) ? $microdata : null;
                        }

                    }else{
                        $microdata = null;
                    }

                    return array($pagination->getTotalItemCount(), $pagination, $microdata);
                } else {
                    return array();
                }
            } else if ($type == "flags") {                                                                 //simple way
                $q_name = new QueryString();
                $q_active = new QueryString();

                $q_name->setQuery($conditions->search);
                $q_name->setFields(array('name'));
                $q_name->setAnalyzeWildcard();

                $boolQueryOR->addShould($q_name);

                $boolQuery->addMust($boolQueryOR);
                if (isset($conditions->active)) {
                    $q_active->setQuery($conditions->active);
                    $q_active->setFields(array('active'));
                    $q_active->setAnalyzeWildcard();

                    $boolQuery->addMust($q_active);
                }

                $query->setQuery($boolQuery);
                $hybridResults = $finder->findHybrid($query, $this->max_rows);

                foreach ($hybridResults as $hybridResult) {
                    $item = $hybridResult->getResult()->getHit();
                    $items[] = $item;
                }
                if (count($items) > 0) {
                    $pagination = $paginator->paginate($items, $page, $size);

                    return array($pagination->getTotalItemCount(), $pagination);
                } else {
                    return array(array(), array());
                }
            } else if ($type == "columna") {                                                                 //simple way
                if ( isset( $conditions->slug ) ){
                    $q_slug = new QueryString();
                    $q_slug->setQuery($conditions->slug);
                    $q_slug->setFields(array('slug'));
                    $boolQuery->addMust($q_slug);
                }else {
                    $q_name = new QueryString();
                    $q_active = new QueryString();
                    $q_author = new QueryString();

                    $q_name->setQuery($conditions->search);
                    $q_name->setFields(array('nombreSistema'));
                    $q_name->setAnalyzeWildcard();

                    $boolQueryOR->addShould($q_name);

                    $boolQuery->addMust($boolQueryOR);
                    if (isset($conditions->active)) {
                        $q_active->setQuery($conditions->active);
                        $q_active->setFields(array('active'));
                        $q_active->setAnalyzeWildcard();

                        $boolQuery->addMust($q_active);
                    }

                    if (isset($conditions->author)) {
                        $q_author->setQuery($conditions->author);
                        $q_author->setFields(array('authors'));
                        $q_author->setAnalyzeWildcard();

                        $boolQuery->addMust($q_author);
                    }
                }

                $query->setQuery($boolQuery);
                $hybridResults = $finder->findHybrid($query, $this->max_rows);

                foreach ($hybridResults as $hybridResult) {
                    $item = $hybridResult->getResult()->getHit();
                    if ( isset( $conditions->slug ) ){
                        if ( $item["_source"]["slug"] == $conditions->slug ){
                            $item["_source"]["authors"] = $this->convertArrayJsonToArray($item["_source"]["authors"]);
                            $items[] = $item;
                            break;
                        }
                    }else{
                        $item["_source"]["authors"] = $this->convertArrayJsonToArray($item["_source"]["authors"]);
                        $items[] = $item;
                    }
                }
                if (count($items) > 0) {
                    $pagination = $paginator->paginate($items, $page, $size);

                    return array($pagination->getTotalItemCount(), $pagination);
                } else {
                    return array(array(), array());
                }
            } else if ($type == "blog") {                                                                 //simple way
                if ( isset( $conditions->slug ) ){
                    $q_slug = new QueryString();
                    $q_slug->setQuery($conditions->slug);
                    $q_slug->setFields(array('slug'));
                    $boolQuery->addMust($q_slug);
                }else {
                    $q_name = new QueryString();
                    $q_active = new QueryString();
                    $q_author = new QueryString();

                    $q_name->setQuery($conditions->search);
                    $q_name->setFields(array('title'));
                    $q_name->setAnalyzeWildcard();

                    $boolQueryOR->addShould($q_name);

                    $boolQuery->addMust($boolQueryOR);
                    if (isset($conditions->active)) {
                        $q_active->setQuery($conditions->active);
                        $q_active->setFields(array('active'));
                        $q_active->setAnalyzeWildcard();

                        $boolQuery->addMust($q_active);
                    }

                    if (isset($conditions->author)) {
                        $q_author->setQuery($conditions->author);
                        $q_author->setFields(array('author'));
                        $q_author->setAnalyzeWildcard();

                        $boolQuery->addMust($q_author);
                    }
                }

                $query->setQuery($boolQuery);
                $hybridResults = $finder->findHybrid($query, $this->max_rows);

                foreach ($hybridResults as $hybridResult) {
                    $item = $hybridResult->getResult()->getHit();
                    $item["_source"]["author"] = $this->convertArrayJsonToArray($item["_source"]["author"]);
                    $items[] = $item;
                }
                if (count($items) > 0) {
                    $pagination = $paginator->paginate($items, $page, $size);

                    return array($pagination->getTotalItemCount(), $pagination);
                } else {
                    return array(array(), array());
                }
            } else if ($type == "portadafolds") {                                                                 //simple way

                $finder          = $this->get('fos_elastica.index.efredisenio.' . $type);

                $q_portada = new QueryString();

                $q_portada->setQuery($conditions->portada);
                $q_portada->setFields(array('idportada'));
                $q_portada->setAnalyzeWildcard();

                $boolQueryOR->addMust($q_portada);
                $boolQuery->addMust($boolQueryOR);

                if ($public === true || $public == "true") {
                    $q_status = new Query\Match();
                    $q_status->setFieldQuery("status", "published");
                    $boolQuery->addMust($q_status);
                }

                $query->setQuery($boolQuery);
                $query->setSort( ["orden" => ['order' => 'asc'] ] );                                       //Order

                //$hybridResults = $finder->findHybrid($query, $this->max_rows);
                $hybridResults = $finder->search($query, $this->max_rows);

                foreach ($hybridResults as $hybridResult) {
                    //$item = $hybridResult->getResult()->getHit();
                    $item = $hybridResult->getHit();

                    $item["_source"]["idportada"] = json_decode($item["_source"]["idportada"], true);
                    $item["_source"]["idfold"]    = json_decode($item["_source"]["idfold"], true);
                    $items[] = $item;
                }
                if (count($items) > 0) {
                    $pagination = $paginator->paginate($items, $page, $size);

                    return array($pagination->getTotalItemCount(), $pagination);
                } else {
                    return array(array(), array());
                }
            } else if ($type == "wfuser") {                                                                 //simple way
                $a = explode( " ", $conditions->search );
                if ( count( $a ) > 1 ){
                    $q_name   = new QueryString();
                    $q_ln_2   = new QueryString();

                    $q_name->setQuery( $a[0] );
                    $q_name->setFields(array('firstName'));
                    $q_name->setAnalyzeWildcard();

                    $q_ln_2->setQuery( $a[1] );
                    $q_ln_2->setFields(array('aPaterno'));
                    $q_ln_2->setAnalyzeWildcard();

                    $boolQueryOR->addMust($q_name);
                    $boolQueryOR->addMust($q_ln_2);

                    $boolQuery->addMust($boolQueryOR);
                }else{
                    $q_name      = new QueryString();
                    $q_email     = new QueryString();
                    $q_firstname = new QueryString();

                    $q_name->setQuery($conditions->search);
                    $q_name->setFields(array('username'));
                    $q_name->setAnalyzeWildcard();

                    $q_email->setQuery($conditions->search);
                    $q_email->setFields(array('email'));
                    $q_email->setAnalyzeWildcard();

                    $q_firstname->setQuery($conditions->search);
                    $q_firstname->setFields(array('firstName'));
                    $q_firstname->setAnalyzeWildcard();

                    $boolQueryOR->addShould($q_name);
                    $boolQueryOR->addShould($q_email);
                    $boolQueryOR->addShould($q_firstname);

                    $boolQuery->addMust($boolQueryOR);
                }

                $query->setQuery($boolQuery);
                $hybridResults = $finder->findHybrid($query, $this->max_rows);

                foreach ($hybridResults as $hybridResult) {
                    $item = $hybridResult->getResult()->getHit();
                    $items[] = $item;
                }
                if (count($items) > 0) {
                    $pagination = $paginator->paginate($items, $page, $size);

                    return array($pagination->getTotalItemCount(), $pagination);
                } else {
                    return array(array(), array());
                }
            } else {
                $q_title = new QueryString();
                $q_desc = new QueryString();
                $q_tag = new QueryString();

                $q_title->setQuery($conditions->search);
                $q_title->setFields(array('title'));
                $q_title->setAnalyzeWildcard();

                $q_desc->setQuery($conditions->search);
                $q_desc->setFields(array('description'));
                $q_desc->setAnalyzeWildcard();

                $q_tag->setQuery($conditions->search);
                $q_tag->setFields(array('tag'));
                $q_tag->setAnalyzeWildcard();

                $boolQueryOR->addShould($q_title);
                $boolQueryOR->addShould($q_desc);
                $boolQueryOR->addShould($q_tag);

                $boolQuery->addMust($boolQueryOR);
            }
            foreach ($conditions as $key => $val) {
                if ($key == "min_date" || $key == "max_date" ) {
                    break;
                }
                $val = isset($val) ? $val . "*" : "";                                                  //add wildcard
                if ($key != "search") {
                    if ($type == "image" && $key == "author") {
                        $q_title = new QueryString();
                        $q_desc = new QueryString();

                        $q_title->setQuery($val);
                        $q_title->setFields(array('credito'));
                        $q_title->setAnalyzeWildcard();

                        $q_desc->setQuery($val);
                        $q_desc->setFields(array('sourcecat'));
                        $q_desc->setAnalyzeWildcard();

                        $boolQueryOR2->addShould($q_title);
                        $boolQueryOR2->addShould($q_desc);

                        $boolQuery->addMust($boolQueryOR2);
                    } else if ($type == "page" && $key == "authorslug") {
                        $autor = $this->broad("author", "", "{\"slug\":\"" . $conditions->authorslug . "\"}", 1, $this->max_rows, $public);
                        if ( isset( $autor[1] ) && gettype( $autor[1]) == "object" ){
                            $authorId       = $autor[1]->getItems()[0]["_source"]["id"];
                            $q_filter       = new QueryString();
                            $authorslugEval = false;

                            $q_filter->setQuery((string)$authorId);
                            $q_filter->setFields(array("author"));
                            $q_filter->setAnalyzeWildcard();

                            $boolQuery->addMust($q_filter);
                            if($public === true || $public === 'true'){                           //In public search avoid display authors signed with
                                $authorslugEval = true;
                            }

                            $this->order_page_desc = true;
                            $orderAuthorSlug       = true;
                        }else{
                            return array();
                        }
                    } else if ($type == "page" && $key == "columnslug") {
                        $columna = $this->broad("columna", "", "{\"slug\":\"" . $conditions->columnslug . "\"}", 1, $this->max_rows, $public);
                        if ( isset( $columna[1] ) && gettype( $columna[1]) == "object" ){
                            $columnaId = $columna[1]->getItems()[0]["_source"]["id"];
                            $q_filter  = new QueryString();

                            $q_filter->setQuery( (string)$columnaId );
                            $q_filter->setFields(array("columna"));
                            $q_filter->setAnalyzeWildcard();

                            $boolQuery->addMust($q_filter);
                            $this->order_page_desc = true;
                            $orderColumnSlug       = true;
                        }else{
                            return array();
                        }
                    } else if ($type == "page" && $key == "tagslug") {
                        $q_filter = new QueryString();

                        $q_filter->setQuery( $val) ;
                        $q_filter->setFields(array("tag"));
                        $q_filter->setAnalyzeWildcard();

                        $boolQuery->addMust($q_filter);
                        $tagEval  = true;
                        $this->order_page_desc = false;
                    } else if ($type == "page" && $key == "maincategoryslug") {
                        $q_filter = new QueryString();

                        $q_filter->setQuery( $val );
                        $q_filter->setFields( array( "categoryId" ) );
                        $q_filter->setAnalyzeWildcard();
                        $boolQuery->addMust( $q_filter );

                        $maincategoryslugEval  = true;
                        $this->order_page_desc = true;
                    } else if ($type == "page" && $key == "slug") {
                        $q_filter = new QueryString();
                        $a        = substr_replace( $val, "", -1);
                        $a        = $this->toStringComparisson($a);
                        $q_filter->setQuery($a);
                        $q_filter->setFields(array("slug"));

                        $boolQuery->addMust($q_filter);

                        $slugEval              = true;
                        $this->order_page_desc = false;
                    } else if ($type == "page" && $key == "rss") {
                        $q_filter = new QueryString();

                        $q_filter->setQuery( $val );
                        $q_filter->setFields( array( $key ) );
                        $boolQuery->addMust($q_filter);

                        $rssEval  = true;
                        $this->order_page_desc = true;
                        $orderColumnSlug       = true;
                    } else if ($type == "page" && $key == "categoryId") {
                        $q_filter = new QueryString();

                        $q_filter->setQuery( $val );
                        $q_filter->setFields( array( $key ) );
                        $boolQuery->addMust($q_filter);

                        $categoryIdEval        = true;
                        $this->order_page_desc = true;
                    } else if ($type == "page" && $key == "blogslug") {
                        $blog = $this->broad("blog", "", "{\"slug\":\"" . $conditions->blogslug . "\"}", 1, $this->max_rows, $public);
                        if ( isset( $blog[1] ) && gettype( $blog[1]) == "object" ){
                            $blogId    = $blog[1]->getItems()[0]["_source"]["id"];
                            $q_filter  = new QueryString();
                            $q_filter->setQuery( (string)$blogId );
                            $q_filter->setFields( array( "blog" ) );
                            $q_filter->setAnalyzeWildcard();

                            $boolQuery->addMust($q_filter);
                            $this->order_page_desc = true;
                        }else{
                            return array();
                        }
                    } else if ($type == "page" && $key == "tag") {
                        $tagPageEval           = true;
                        $this->order_page_desc = false;
                    } else if ($type == "page" && $key == "columnaId") {
                        $q_filter = new QueryString();

                        $q_filter->setQuery($val);
                        $q_filter->setFields(array("columna"));

                        $boolQuery->addMust($q_filter);

                        $columnaIdEval         = true;
                        $this->order_page_desc = false;
                    } else if ($type == "page" && $key == "subcategory") {
                        $q_filter = new QueryString();
                        $b        = array( "/", "-" );
                        $a = str_replace( $b, " ", $val );
                        $q_filter->setQuery($a);
                        $q_filter->setFields(array("category"));

                        $boolQuery->addMust($q_filter);

                        $subcategoryEval       = true;
                        $this->order_page_desc = false;
                    } else if ($type == "page" && $key == "categoryname") {
                        $q_filter = new QueryString();

                        $q_filter->setQuery($val);
                        $q_filter->setFields(array("categoryId"));

                        $boolQuery->addMust($q_filter);

                        $categorynameEval       = true;
                        $this->order_page_desc  = true;

                    } else if ($type == "page" && $key == "categoriesslug") {
                        $slash_pos = strpos( $val, "/" );
                        $dash_pos  = strpos( $val, "-" );

                        if ( $slash_pos === false && $dash_pos === false ){
                            $q_filter_mainc = new QueryString();
                            $q_filter_subc  = new QueryString();

                            $q_filter_mainc->setQuery($val);
                            $q_filter_mainc->setFields(array("categoryId"));
                            $q_filter_mainc->setAnalyzeWildcard();

                            $q_filter_subc->setQuery($val);
                            $q_filter_subc->setFields(array("category"));
                            $q_filter_subc->setAnalyzeWildcard();
                        }else{
                            $q_filter_mainc = new Query\Match();
                            $q_filter_subc  = new Query\Match();

                            $q_filter_mainc->setFieldQuery("categoryId", $val );
                            $q_filter_mainc->setFieldType("categoryId", "phrase" );

                            $q_filter_subc->setFieldQuery("category", $val );
                            $q_filter_subc->setFieldType("category", "phrase" );
                        }

                        $boolQueryOR2->addShould($q_filter_mainc);
                        $boolQueryOR2->addShould($q_filter_subc);
                        $boolQuery->addMust($boolQueryOR2);

                        $categoriesslugEval     = true;
                        $this->order_page_desc  = true;

                    } else if ($type == "page" && $key == "status") {
                        $q_filter = new QueryString();

                        $q_filter->setQuery($val);
                        $q_filter->setFields(array($key));
                        $q_filter->setAnalyzeWildcard();

                        $boolQuery->addMust($q_filter);

                    } else if ($type == "page" && $key == "slug2") {
                        $result = $this->alternateSearch( "page", $subtype, "{\"slug\":\"" . $val . "\"}", $public );

                        return $result;

                    } else {
                        if ( !in_array( $key, $jsonPropsToAvoid ) ){
                            $q_filter = new QueryString();

                            $q_filter->setQuery($val);
                            $q_filter->setFields(array($key));
                            $q_filter->setAnalyzeWildcard();

                            $boolQuery->addMust($q_filter);
                        }
                    }
                }
            }

            if ( $type == "page" && $this->order_page_desc === true ) {                                                 //Order
                $query->setSort( [$date_page => ['order' => 'desc'] ] );                                                //(DSG,130318)
            }else{
                switch( $type ){
                    case "image":
                        $query->setSort( [$date_asset => ['order' => 'desc'] ] );
                }
            }
            if ($type == "page" && $subtype != "null" && $subtype != "" ) {
                $q_type->setFieldQuery("pageType", $subtype);
                $boolQuery->addMust($q_type);
            }
            $query->setQuery($boolQuery);

            //$hybridResults = $finder->findHybrid($query, $this->max_rows);
            $finder          = $this->get('fos_elastica.index.efredisenio.' . $type);
            $hybridResults = $finder->search($query, $this->max_rows);


            foreach ($hybridResults as $hybridResult) {                                                                 //Eval dates
                //$item = $hybridResult->getResult()->getHit();
                $item = $hybridResult->getHit();
                if ($type == "page") {                                                                                  //Extract date from Page
                    $d_comp = $item["_source"][$date_page];
                } else {                                                                                                //Extract date from Asset
                    $d_comp = $item["_source"][$date_asset];
                }
                if ($min_date != "" && $max_date != "") {                                                               //Date comparisson
                    if ((string)gettype($d_comp) != "NULL") {
                        if ($this->getIfDateIsBetweenDates($min_date, $max_date, $d_comp)) {
                            $items[] = $item;
                        }
                    }
                } else {
                    if ( $type == "page" ) {
                        if ( $item["_source"]["status"] != "trash" ){
                            $items[] = $item;
                        }
                    }else{
                        $items[] = $item;
                    }
                }
            }

        } else {
            return null;
        }
        foreach ($items as $item) {                                                                      //Add items to result array. Check avoid rules. Then, change display for page content
            if ($item["_type"] == "page") {
                if ( ( isset( $rssEval ) && $rssEval == true ) &&
                   ( gettype( $item["_source"]["rss"] ) == "array" && !in_array( $conditions->rss, $item["_source"]["rss"] ) )
                ) {                                                                                     //if is page rss, verify rss
                    continue;
                }
                if ( isset($item["_source"]["content"] ) ) {                                            //formating page
                    $item = $this->getItemFormated($item);
                }
                if ( ( isset( $tagEval ) && $tagEval == true ) &&
                     ( !$this->in_array_asoc( $conditions->tagslug, "slug", $item["_source"]["tag"] ) )
                ) {                                                                                     //if is page tag, verify tag
                    continue;
                }
                if ( ( isset( $maincategoryslugEval ) && $maincategoryslugEval == true ) &&
                     ( $item["_source"]["categoryId"]["slug"] != $conditions->maincategoryslug )
                ) {                                                                                     //if is page maincategoryslug, verify slug
                    continue;
                }
                if ( ( isset( $categoryIdEval ) && $categoryIdEval == true ) &&
                    ( $item["_source"]["categoryId"]["id"] != $conditions->categoryId )
                ) {                                                                                     //if is page categoriId, verify cat id
                    continue;
                }
                if ( ( isset( $tagPageEval ) && $tagPageEval == true ) &&
                    ( !$this->in_array_asoc( $conditions->tag, "title", $item["_source"]["tag"], false ) )
                ) {                                                                                     //if is page tag, verify tag
                    continue;
                }
                if ( ( isset( $columnaIdEval ) && $columnaIdEval == true ) &&
                    ( $conditions->columnaId != $item["_source"]["columna"]["id"] )
                ) {                                                                                     //if is page tag, verify tag
                    continue;
                }
                if ( ( isset( $subcategoryEval ) && $subcategoryEval == true ) &&
                    ( !$this->in_array_asoc( $conditions->subcategory, "slug", $item["_source"]["subcategories"], false ) )
                ) {                                                                                     //if is page subcategory, verify name
                    continue;
                }
                if ( ( isset( $categorynameEval ) && $categorynameEval == true ) &&
                    ( $this->toStringComparisson( $conditions->categoryname ) != $this->toStringComparisson( $item["_source"]["categoryId"]["title"] ) )
                ) {                                                                                     //if is page categoryname, verify name
                    continue;
                }
                if ( ( isset( $slugEval ) && $slugEval == true ) &&
                    ( $conditions->slug != $item["_source"]["slug"] )
                ) {                                                                                     //if is page categoryname, verify name
                    continue;
                }
                if ( ( isset( $categoriesslugEval ) && $categoriesslugEval == true ) &&
                     (  ( $item["_source"]["categoryId"]["slug"] != $conditions->categoriesslug ) &&
                        ( !$this->in_array_asoc( $conditions->categoriesslug, "slug", $item["_source"]["subcategories"], false ) )
                     )
                ) {                                                                                     //if is page maincategoryslug, verify slug
                    continue;
                }
                if ( ( isset( $authorslugEval ) && $authorslugEval == true ) &&
                     //Is public and authorslug, then check if note is signed by editorial staff
                     ( $this->is_editorial_staff( $item["_source"]["author"], $authorId, $item["_source"]["content"] ) )
                ) {                                                                                     //if is page maincategoryslug, verify slug
                    continue;
                }
            }
            if (count(array_intersect(array($item["_type"]), $this->avoid_types)) == 0) {               //avoid no permited types
                $result[] = $item;
            }
        }
        if (count($result) > 0) {

            //$result = array_map("unserialize", array_unique(array_map("serialize", $result)));      //avoid duplicated data

            if ( $partial ){                                                                             //partial result (no pagination)
                return $result;
            }

            if (
                ( isset( $orderColumnSlug ) && $orderColumnSlug === true ) ||
                ( isset( $orderAuthorSlug ) && $orderAuthorSlug === true )
            ){
                if ( $public == "true" || $public === true ){
                    $pagination = $paginator->paginate($result, $page, $size, array('defaultSortFieldName' => '[_source][publishedAt]', 'defaultSortDirection' => 'desc') );
                }else{
                    $pagination = $paginator->paginate($result, $page, $size, array('defaultSortFieldName' => '[_source][createdAt]', 'defaultSortDirection' => 'desc') );
                }
            }else{
                $pagination = $paginator->paginate($result, $page, $size);
            }

            //If subtype is 'tv' and categoryId EXIST, return microdata
            if(isset($subtype) && isset($conditions->categoryId) && $subtype != null && $subtype != "" && $subtype == 'tv'){

                $helpers = $this->get("app.helpers");
                $em = $this->getDoctrine()->getManager();

                   if($public === false || $public === 'false'){
                        $microdata = null;
                   }else{
                       $category =  $em->getRepository("BackendBundle:Category")->find($conditions->categoryId);
                       if($category != null){
                           $microdata = $helpers->microData($category, $from = "redisenio_portada");
                           ($microdata != null) ? $microdata : null;
                       }else{
                           $microdata = null;
                       }
                    }
            }elseif (isset($conditions->columnslug)){

                $helpers = $this->get("app.helpers");
                $em = $this->getDoctrine()->getManager();


                $columna =  $em->getRepository("BackendBundle:Columna")->findOneBy(array("slug"=>$conditions->columnslug));


                if($columna != null){
                    $microdata = $helpers->microDataColumna($columna, $pagination);
                    ($microdata != null) ? $microdata : null;
                }else{
                    $microdata = null;
                }

            }
            else{
                $microdata = null;
            }

            if ( $microdata == null ){
                return array($pagination->getTotalItemCount(), $pagination);
            }else{
                return array($pagination->getTotalItemCount(), $pagination, $microdata);
            }
        } else {
            return array(array(), $pagination);
        }
    }

    /**
     * Para acceder a este metodo, se require autorizacion(token)
     *
     * @ApiDoc(
     *  section = "Search",
     *  description="Search by all types at the same time",
     *     requirements={
     *     {"name"="json",    "dataType"="array",   "required"=true,    "description"="Conditions props: default, min_date, max_date, categoryId(str), author(str), (Only for tags->type:[''|'company']), status( default | published | scheduled ). Don't use special chars and use just one word by criteria in no date type columns."},
     *     {"name"="page",    "dataType"="int",     "required"="false", "default"="1",      "description"="Page"},
     *     {"name"="size",    "dataType"="int",     "required"="false", "default"="10",     "description"="Tamaño de la pagina, si se omite es 10"},
     *     {"name"="public",  "dataType"="boolean", "required"="false", "default"="false",  "description"="La consulta es publica"}
     *    },
     *   headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     * )
     */
    public function alltypesAction(Request $request)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $page = array();
        $result = array();
        $json = $request->get('json', "");
        $page = $request->get('page', 1);
        $size = $request->get('size', 10);
        $public = $request->get('public', false);

        if ($json != "") {
            $result = $this->wide($json, $page, $size, $public);
            $data = array(
                "data" => $result
            );
        } else {
            $data = array(
                "data" => "No data"
            );
        }
        $jsonContent = $serializer->serialize($data, 'json');
        $response = new Response($jsonContent);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    private function wide($json = "", $page = 1, $size = 10, $public = false)
    {
        $users = $this->get('app.users');
        $items = array();
        $result = array();
        $conditions = array();
        $boolQuery = new BoolQuery();
        $boolQueryOR = new BoolQuery();
        $boolQueryOR2 = new BoolQuery();
        $query = new Query();
        $pagination = array();
        $min_date = "";
        $max_date = "";
        $date_pivot = "createdAt";
        $finder = $this->get('fos_elastica.finder.efredisenio');
        $paginator = $this->get('knp_paginator');
        $cats_ids = array();

        if ( $public === true || $public == "true" ) {
            $categories["fullAccess"] = true;
        } else {
            $categories = $users->getCategoriesUserLogged();                                            //get current user's cats
            foreach ($categories["categories"] as $l_cat)                                               //get cat's ids
            {
                array_push($cats_ids, $l_cat->getId());
            }
        }
        if ($json != "") {
            $conditions = json_decode($json);
            $min_date = isset($conditions->min_date) ? $conditions->min_date : "";
            $max_date = isset($conditions->max_date) ? $conditions->max_date : "";

            $conditions->search = isset($conditions->search) ? $conditions->search . "*" : "";            //add wildcard

            //search first
            $a = explode(' ', $conditions->search );
            if ( count( $a ) == 1 ){                                                             //single word
                $q_title = new QueryString();
                $q_first = new QueryString();
                $q_desc = new QueryString();
                $q_tag = new QueryString();

                $q_title->setQuery($conditions->search);
                $q_title->setFields(array('title'));
                $q_title->setAnalyzeWildcard();

                $q_first->setQuery($conditions->search);
                $q_first->setFields(array('firstTitle'));
                $q_first->setAnalyzeWildcard();

                $q_desc->setQuery($conditions->search);
                $q_desc->setFields(array('shortDescription'));
                $q_desc->setAnalyzeWildcard();

                $q_tag->setQuery($conditions->search);
                $q_tag->setFields(array('tag'));
                $q_tag->setAnalyzeWildcard();
            }else{                                                                              //phrase
                $q_title = new Query\Match();
                $q_first = new Query\Match();
                $q_desc = new  Query\Match();
                $q_tag = new   Query\Match();

                $q_title->setFieldQuery("title", $conditions->search );
                $q_title->setFieldType("title", "phrase" );

                $q_first->setFieldQuery("firstTitle", $conditions->search );
                $q_first->setFieldType("firstTitle", "phrase" );

                $q_desc->setFieldQuery("shortDescription", $conditions->search );
                $q_desc->setFieldType("shortDescription", "phrase" );

                $q_tag->setFieldQuery("tag", $conditions->search );
                $q_tag->setFieldType("tag", "phrase" );
            }

            $boolQueryOR->addShould($q_title);
            $boolQueryOR->addShould($q_first);
            $boolQueryOR->addShould($q_desc);
            $boolQueryOR->addShould($q_tag);

            $boolQuery->addMust($boolQueryOR);

            foreach ($conditions as $key => $val) {
                if ($key == "min_date" || $key == "max_date") {
                    break;
                }
                $val = isset($val) ? $val . "*" : "";                                              //add wildcard
                if ($key != "search") {
                    if ($key == "author") {                //For author
                        $q_title = new QueryString();
                        $q_desc = new QueryString();
                        $q_author = new QueryString();

                        $q_title->setQuery($val);
                        $q_title->setFields(array('credito'));
                        $q_title->setAnalyzeWildcard();

                        $q_desc->setQuery($val);
                        $q_desc->setFields(array('sourcecat'));
                        $q_desc->setAnalyzeWildcard();

                        $q_author->setQuery($val);
                        $q_author->setFields(array('author'));
                        $q_author->setAnalyzeWildcard();

                        $boolQueryOR2->addShould($q_title);
                        $boolQueryOR2->addShould($q_desc);
                        $boolQueryOR2->addShould($q_author);
                        $boolQuery->addMust($boolQueryOR2);
                    } else {
                        $q_filter = new QueryString();
                        $q_filter->setQuery($val);
                        $q_filter->setFields(array($key));
                        $q_filter->setAnalyzeWildcard();

                        $boolQuery->addMust($q_filter);
                    }
                }
                $query->setSort([$date_pivot => ['order' => 'desc']]);         //Order
            }

            if (!$categories["fullAccess"]) {
                $cat_filter = new \Elastica\Query\Terms();                                                      //sections restrictions
                $cat_filter->setTerms("categoryId", $cats_ids);
                $boolQuery->addMust($cat_filter);
            }

            $query->setQuery($boolQuery);
            $hybridResults = $finder->findHybrid($query, $this->max_rows);
            foreach ($hybridResults as $hybridResult) {                                                         //extract results
                $item = $hybridResult->getResult()->getHit();
                $d_comp = isset($item["_source"][$date_pivot]) ? $item["_source"][$date_pivot] : null;        //get date
                if ($min_date != "" && $max_date != "") {
                    if ((string)gettype($d_comp) != "NULL") {
                        if ($this->getIfDateIsBetweenDates($min_date, $max_date, $d_comp)) {                //compare dates
                            $items[] = $item;
                        }
                    }
                } else {
                    $items[] = $item;
                }
            }
        } else {
            return null;
        }
        foreach ($items as $item) {                                                                              //change display for page content & avoid types
            if ($item["_type"] == "page") {
                $item = $this->getItemFormated($item);
                $result[] = $item;
            } else {
                if (count(array_intersect(array($item["_type"]), $this->avoid_types)) == 0 && $item["_type"] != "category") {            //Avoid prohibited types
                    $result[] = $item;
                }
            }
        }
        if (count($result) > 0) {
            $pagination = $paginator->paginate($result, $page, $size);

            return array($pagination->getTotalItemCount(), $pagination);
        } else {
            return array(array(), $pagination);
        }
    }

    private function getIfDateIsBetweenDates($min_date, $max_date, $date_to_compare)
    {
        $date = date('Y-m-d', strtotime($date_to_compare));
        $min = date('Y-m-d', strtotime($min_date));
        $max = date('Y-m-d', strtotime($max_date));

        if (($date >= $min) && ($date <= $max)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @desc remove double slash and jsondecode
     * @param $val json
     * @return array
     */
    private function convertEspecialCols($val)
    {
        $result = array();

        $a = str_replace("\\", "", $val);
        $result = json_decode($a, true);

        return $result;
    }

    /**
     * @desc format item for output
     * @param $item array
     * @return array
     */
    private function getItemFormated($item)
    {
        $item["_source"]["content"]               = $this->convertEspecialCols($item["_source"]["content"]);

        $item["_source"]["subcategories"]         = $item["_source"]["category"];
        unset($item["_source"]["category"]);
        $item["_source"]["subcategories"]         = $this->convertArrayJsonToArray($item["_source"]["subcategories"]);

        $item["_source"]["author"]                = $this->convertArrayJsonToArray($item["_source"]["author"]);
        $item["_source"]["columna"]               = json_decode($item["_source"]["columna"], true);
        $item["_source"]["creator"]               = json_decode($item["_source"]["creator"], true);
        //$item["_source"]["html_serialize"]        = json_decode( $item["_source"]["html_serialize"], true );
        $item["_source"]["elementHtmlSerialized"] = json_decode($item["_source"]["elementHtmlSerialized"], true);
        $item["_source"]["tag"]                   = $this->convertArrayJsonToArray($item["_source"]["tag"]);
        $item["_source"]["categoryId"]            = json_decode( $item["_source"]["categoryId"], true );
        $item["_source"]["blog"]                  = json_decode( $item["_source"]["blog"], true );

        return $item;
    }

    /**
     * @desc format item for output
     * @param $item array
     * @return array
     */
    private function getLegacyItemFormated($item)
    {
        //$item["_source"]["categoryId"]            = $item["_source"]["category_id"];
        //unset($item["_source"]["category_id"]);
        //$item["_source"]["categoryId"]            = json_decode( $item["_source"]["categoryId"], true );
        $item["_source"]["category_id"]           = json_decode( $item["_source"]["category_id"], true );

        $item["_source"]["content"]               = json_decode( $item["_source"]["content"], true );
        //$item["_source"]["content"]               = $this->convertEspecialCols($item["_source"]["content"]);

        $item["_source"]["subcategories"]         = $item["_source"]["category"];
        unset($item["_source"]["category"]);
        $item["_source"]["subcategories"]         = $this->convertArrayJsonToArray($item["_source"]["subcategories"]);

        $item["_source"]["author"]                = $this->convertArrayJsonToArray($item["_source"]["author"]);
        //$item["_source"]["columna"]               = json_decode($item["_source"]["columna"], true);
        //$item["_source"]["creator"]               = json_decode($item["_source"]["creator"], true);
        //$item["_source"]["html_serialize"]        = $this->convertEspecialCols($item["_source"]["html_serialize"]);
        //$item["_source"]["elementHtmlSerialized"] = json_decode($item["_source"]["elementHtmlSerialized"], true);
        $item["_source"]["tag"]                   = $this->convertArrayJsonToArray($item["_source"]["tag"]);

        //$item["_source"]["blog"]                  = json_decode( $item["_source"]["blog"], true );

        return $item;
    }

    /**
     * @desc convert array elements from json to array
     * @param $val array of json
     * @return array
     */
    private function convertArrayJsonToArray($val)
    {
        $result = array();

        foreach ($val as $v) {
            $result[] = json_decode($v, true);
        }

        return $result;
    }


    public function broadPublic($type = "page", $subtype, $json, $page, $size, $public, $max_size)
    {
        return  $this->broad($type, $subtype, $json, $page, $size, $public, $max_size);
    }

    private function in_array_asoc($needle, $asoc, $haystack, $strictComparisson=true) {
        foreach( $haystack as $item ){
            if ( $strictComparisson ){
                if ( isset( $item[$asoc] ) && $item[$asoc] == $needle ){
                    return true;
                }
            }else{
                if ( isset( $item[$asoc] ) && $this->toStringComparisson( $item[$asoc] ) == $this->toStringComparisson( $needle ) ){
                    return true;
                }
            }
        }

        return false;
    }

    private function in_array_html($needle, $haystack ) {
        foreach( $haystack as $item => $key ){
                if ( isset( $key ) && $this->toStringComparisson( strip_tags( $key ) ) == $this->toStringComparisson( $needle ) ){
                    return true;
                }
        }

        return false;
    }

    /**
     * @desc
     * @param $a
     * @return string
     */
    private function toStringComparisson( $a ){
        return strtolower(trim(preg_replace('~[^0-9a-z]+~i', '-', preg_replace('~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($a, ENT_QUOTES, 'UTF-8'))), ' '));
    }

    private function broadLegacy( $type = "page", $subtype = "", $json = "", $page = 1, $size = 10, $public = false, $max_rows=0, $partial=false )
    {
        $users           = $this->get('app.users');
        $items           = array();
        $result          = array();
        $conditions      = array();
        $boolQuery       = new BoolQuery();
        $boolQueryOR     = new BoolQuery();
        $boolQueryOR2    = new BoolQuery();
        $query           = new Query();
        $pagination      = array();
        $min_date        = "";
        $max_date        = "";
        $date_page       = "createdAt";
        $date_asset      = "createdAt";
        $finder          = $this->get('fos_elastica.finder.efxalok.' . $type);
        $paginator       = $this->get('knp_paginator');
        $cats_ids        = array();

        $defaultOperator  = "AND";                                                                                      //Other option OR
        $jsonPropsToAvoid = array(
            "phrase",
            "operator"
        );                                                                                                              //json properties to avoid in filters adding


        if ( $max_rows != 0 ){
            $this->max_rows = $max_rows;
        }

        if ( $public === true || $public == "true" ) {
            $categories["fullAccess"] = true;
            $date_page                = "updatedAt";
        }

        if ( $json != "" ) {
            $q_type     = new Query\Match();
            $q_subtype  = new Query\Match();
            $conditions = json_decode($json);
            $min_date   = isset($conditions->min_date) ? $conditions->min_date : "";
            $max_date   = isset($conditions->max_date) ? $conditions->max_date : "";

            $conditions->search = isset($conditions->search) ? $conditions->search . "*" : "";                          //add wildcard

            if ( $type == "page" ) {
                if ( isset( $conditions->search ) && $conditions->search != "" ){

                    if ( isset( $conditions->phrase ) && $conditions->phrase == "true" ){                               //word search
                        $q_title  = new Query\Match();
                        $q_desc   = new Query\Match();
                        $q_tag    = new Query\Match();
                        $q_html   = new Query\Match();

                        $q_title->setFieldQuery("title", $conditions->search );
                        $q_title->setFieldType("title", "phrase" );

                        $q_desc->setFieldQuery("shortDescription", $conditions->search );
                        $q_desc->setFieldType("shortDescription", "phrase" );

                        $q_tag->setFieldQuery("tag", $conditions->search );
                        $q_tag->setFieldType("tag", "phrase" );

                        if ( $public === true || $public == "true" ) {
                            $q_html->setFieldQuery("content", $conditions->search);
                            $q_html->setFieldType("content", "phrase");
                        }
                    }else{                                                                                              //phrase
                        $q_title  = new QueryString();
                        $q_desc   = new QueryString();
                        $q_tag    = new QueryString();
                        $q_html   = new QueryString();
                        $operator = "";

                        if ( isset( $conditions->operator ) && strtoupper( $conditions->operator ) == "OR" ){           //word search
                            $operator = $conditions->operator;
                        }else{
                            $operator = $defaultOperator;
                        }


                        $q_title->setQuery($conditions->search);
                        $q_title->setFields(array('title'));
                        $q_title->setAnalyzeWildcard();
                        $q_title->setDefaultOperator( $operator );

                        $q_desc->setQuery($conditions->search);
                        $q_desc->setFields(array('shortDescription'));
                        $q_desc->setAnalyzeWildcard();
                        $q_desc->setDefaultOperator( $operator );

                        $q_tag->setQuery($conditions->search);
                        $q_tag->setFields(array('tag'));
                        $q_tag->setAnalyzeWildcard();
                        $q_tag->setDefaultOperator( $operator );

                        if ( $public === true || $public == "true" ) {
                            $q_html->setQuery($conditions->search);
                            $q_html->setFields(array('content'));
                            $q_html->setDefaultOperator($operator);
                        }
                    }
                    $boolQueryOR->addShould($q_title);
                    $boolQueryOR->addShould($q_desc);
                    $boolQueryOR->addShould($q_tag);
                    $boolQueryOR->addShould($q_html);

                    $boolQuery->addMust($boolQueryOR);
                }
            } else {
                $q_title = new QueryString();

                $q_title->setQuery($conditions->search);
                $q_title->setFields(array('title'));
                $q_title->setAnalyzeWildcard();

                $boolQueryOR->addShould($q_title);

                $boolQuery->addMust($boolQueryOR);
            }

            foreach ($conditions as $key => $val) {
                if ($key == "min_date" || $key == "max_date" ) {
                    break;
                }
                $val = isset($val) ? $val . "*" : "";                                                  //add wildcard
                if ($key != "search") {
                    if ($type == "page" && $key == "author") {
                        if (isset($conditions->author)) {
                            $q_filter = new QueryString();
                            $q_filter->setQuery($conditions->author);
                            $q_filter->setFields(array("content"));
                            $q_filter->setAnalyzeWildcard();

                            $boolQuery->addShould($q_filter);

                            $this->order_page_desc = true;
                            $authorEval            = true;
                        }
                    }else if ($type == "page" && $key == "user") {
                        if (isset($conditions->user)) {
                            $q_filter = new QueryString();
                            $q_filter->setQuery($conditions->user);
                            $q_filter->setFields(array("author"));
                            $q_filter->setAnalyzeWildcard();

                            $boolQuery->addShould($q_filter);

                            $this->order_page_desc = true;
                        }
                    } else if ($type == "page" && $key == "tagslug") {
                        $q_filter = new QueryString();

                        $q_filter->setQuery( $val) ;
                        $q_filter->setFields(array("tag"));
                        $q_filter->setAnalyzeWildcard();

                        $boolQuery->addMust($q_filter);
                        $tagEval  = true;
                        $this->order_page_desc = false;
                    } else if ($type == "page" && $key == "maincategoryslug") {
                        $q_filter = new QueryString();

                        $q_filter->setQuery( $val );
                        $q_filter->setFields( array( "category_id" ) );
                        $q_filter->setAnalyzeWildcard();
                        $boolQuery->addMust( $q_filter );

                        $maincategoryslugEval  = true;
                        $this->order_page_desc = true;
                    } else if ($type == "page" && $key == "slug") {
                        $q_filter = new QueryString();
                        $a        = substr_replace( $val, "", -1);
                        $a        = $this->toStringComparisson($a);
                        $q_filter->setQuery($a);
                        $q_filter->setFields(array("slug"));

                        $boolQuery->addMust($q_filter);

                        $slugEval              = true;
                        $this->order_page_desc = false;
                    } else if ($type == "page" && $key == "categoryId") {
                        $q_filter  = new QueryString();

                        $q_filter->setQuery( $val );
                        $q_filter->setFields( array( "category_id" ) );
                        $boolQuery->addMust($q_filter);

                        $categoryIdEval        = true;
                        $this->order_page_desc = true;
                    } else if ($type == "page" && $key == "tag") {
                        $q_filter  = new QueryString();

                        $q_filter->setQuery( $val );
                        $q_filter->setFields( array( "tag" ) );
                        $boolQuery->addMust($q_filter);

                        $tagPageEval           = true;
                        $this->order_page_desc = false;
                    } else if ($type == "page" && $key == "subcategory") {
                        $q_filter = new QueryString();
                        $b        = array( "/", "-" );
                        $a = str_replace( $b, " ", $val );
                        $q_filter->setQuery($a);
                        $q_filter->setFields(array("category"));

                        $boolQuery->addMust($q_filter);

                        $subcategoryEval       = true;
                        $this->order_page_desc = false;
                    } else if ($type == "page" && $key == "categoryname") {
                        $q_filter = new QueryString();

                        $q_filter->setQuery($val);
                        $q_filter->setFields(array("category_id"));

                        $boolQuery->addMust($q_filter);

                        $categorynameEval       = true;
                        $this->order_page_desc  = true;

                    } else if ($type == "page" && $key == "categoriesslug") {
                        $q_filter_mainc = new QueryString();
                        $q_filter_subc  = new QueryString();

                        $q_filter_mainc->setQuery($val);
                        $q_filter_mainc->setFields(array("category_id"));
                        $q_filter_mainc->setAnalyzeWildcard();

                        $q_filter_subc->setQuery($val);
                        $q_filter_subc->setFields(array("category"));
                        $q_filter_subc->setAnalyzeWildcard();

                        $boolQueryOR2->addShould($q_filter_mainc);
                        $boolQueryOR2->addShould($q_filter_subc);
                        $boolQuery->addMust($boolQueryOR2);

                        $categoriesslugEval     = true;
                        $this->order_page_desc  = true;

                    } else if ($type == "page" && $key == "status") {
                        $q_filter = new QueryString();

                        $q_filter->setQuery($val);
                        $q_filter->setFields(array($key));
                        $q_filter->setAnalyzeWildcard();

                        $boolQuery->addMust($q_filter);
                    } else {
                        if ( !in_array( $key, $jsonPropsToAvoid ) ){
                            $q_filter = new QueryString();
                            //$val      = substr($val, 0, -1);

                            $q_filter->setQuery($val);
                            $q_filter->setFields(array($key));
                            $q_filter->setAnalyzeWildcard();

                            $boolQuery->addMust($q_filter);
                        }
                    }
                }
            }

            if ( $type == "page" && $this->order_page_desc === true ) {                                                 //Order
                $query->setSort( [$date_page => ['order' => 'desc'] ] );                                                //(DSG,130318)
            }

            if ($type == "page" && $subtype != "null" && $subtype != "" ) {
                switch( $subtype ){
                    case "blogspot":
                        $subtype = "blog_default";
                        break;
                    case "article":
                        $subtype = "default";
                        break;
                    case "galeria":
                        $subtype = "photogallery";
                        break;
                    case "estatico":
                        $subtype = "static";
                        break;
                    case "tv":
                        $subtype = "video";
                        break;
                }
                $q_type->setFieldQuery("pageType", "article");
                $q_subtype->setFieldQuery("template", $subtype);
                $boolQuery->addMust($q_type);
                $boolQuery->addMust($q_subtype);
            }

            $query->setQuery($boolQuery);
            $hybridResults = $finder->findHybrid($query, $this->max_rows);

            foreach ($hybridResults as $hybridResult) {                                                                 //Eval dates
                $item = $hybridResult->getResult()->getHit();
                if ($type == "page") {                                                                                  //Extract date from Page
                    $d_comp = $item["_source"][$date_page];
                } else {                                                                                                //Extract date from Asset
                    $d_comp = $item["_source"][$date_asset];
                }
                if ($min_date != "" && $max_date != "") {                                                               //Date comparisson
                    if ((string)gettype($d_comp) != "NULL") {
                        if ($this->getIfDateIsBetweenDates($min_date, $max_date, $d_comp)) {
                            $items[] = $item;
                        }
                    }
                } else {
                    if ( $type == "page" ) {
                        if ( $item["_source"]["status"] != "trash" ){
                            $items[] = $item;
                        }
                    }else{
                        $items[] = $item;
                    }
                }
            }

        } else {
            return null;
        }

        foreach ($items as $item) {                                                                      //Add items to result array. Check avoid rules. Then, change display for page content
            if ($item["_type"] == "page") {
                $item = $this->getLegacyItemFormated($item);
                if ( ( isset( $tagEval ) && $tagEval == true ) &&
                    ( !$this->in_array_asoc( $conditions->tagslug, "slug", $item["_source"]["tag"] ) )
                ) {                                                                                     //if is page tag, verify tag
                    continue;
                }
                if ( ( isset( $authorEval ) && $authorEval == true ) &&
                    ( !$this->in_array_html( $conditions->author, $item["_source"]["content"][".details-box .important"] ) )
                ) {                                                                                     //if is page tag, verify tag
                    continue;
                }
                if ( ( isset( $maincategoryslugEval ) && $maincategoryslugEval == true ) &&
                    ( $item["_source"]["category_id"]["slug"] != $conditions->maincategoryslug )
                ) {                                                                                     //if is page maincategoryslug, verify slug
                    continue;
                }
                if ( ( isset( $categoryIdEval ) && $categoryIdEval == true ) &&
                    ( $item["_source"]["category_id"]["id"] != $conditions->categoryId )
                ) {                                                                                     //if is page categoriId, verify cat id
                    continue;
                }
                if ( ( isset( $tagPageEval ) && $tagPageEval == true ) &&
                    ( !$this->in_array_asoc( $conditions->tag, "title", $item["_source"]["tag"], false ) )
                ) {                                                                                     //if is page tag, verify tag
                    continue;
                }
                /*if ( ( isset( $columnaIdEval ) && $columnaIdEval == true ) &&
                    ( $conditions->columnaId != $item["_source"]["columna"]["id"] )
                ) {                                                                                     //if is page tag, verify tag
                    continue;
                }*/
                if ( ( isset( $subcategoryEval ) && $subcategoryEval == true ) &&
                    ( !$this->in_array_asoc( $conditions->subcategory, "slug", $item["_source"]["subcategories"], false ) )
                ) {                                                                                     //if is page subcategory, verify name
                    continue;
                }
                if ( ( isset( $categorynameEval ) && $categorynameEval == true ) &&
                    ( $this->toStringComparisson( $conditions->categoryname ) != $this->toStringComparisson( $item["_source"]["category_id"]["title"] ) )
                ) {                                                                                     //if is page categoryname, verify name
                    continue;
                }
                if ( ( isset( $slugEval ) && $slugEval == true ) &&
                    ( $conditions->slug != $item["_source"]["slug"] )
                ) {                                                                                     //if is page categoryname, verify name
                    continue;
                }
                if ( ( isset( $categoriesslugEval ) && $categoriesslugEval == true ) &&
                    (  ( $item["_source"]["categoryId"]["slug"] != $conditions->categoriesslug ) &&
                        ( !$this->in_array_asoc( $conditions->categoriesslug, "slug", $item["_source"]["subcategories"], false ) )
                    )
                ) {                                                                                     //if is page maincategoryslug, verify slug
                    continue;
                }
            }
            if (count(array_intersect(array($item["_type"]), $this->avoid_types)) == 0) {               //avoid no permited types
                $result[] = $item;
            }
        }
        if (count($result) > 0) {

            $result = array_map("unserialize", array_unique(array_map("serialize", $result)));      //avoid duplicated data

            if ( $partial ){                                                                             //partial result (no pagination)
                return $result;
            }

            if (
                ( isset( $orderColumnSlug ) && $orderColumnSlug === true ) ||
                ( isset( $orderAuthorSlug ) && $orderAuthorSlug === true )
            ){
                if ( $public == "true" || $public === true ){
                    $pagination = $paginator->paginate($result, $page, $size, array('defaultSortFieldName' => '[_source][publishedAt]', 'defaultSortDirection' => 'desc') );
                }
            }else{
                $pagination = $paginator->paginate($result, $page, $size);
            }

            return array($pagination->getTotalItemCount(), $pagination);
        } else {

            return array(array(), $pagination);
        }
    }

    private function is_editorial_staff( $authors, $authorId, $content ){
        for( $i = 0; $i < count( $authors ); $i++ ){                                //find author position
            if ( $authors[$i]["id"] == $authorId ){
                break;
            }
        }
        if ( isset( $content["authorsModified"] ) ){
            if ( $content["authorsModified"][$i]["editorial"] == "true" ) {
                return true;
            }
        }

        return false;
    }

}

