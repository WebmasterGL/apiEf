<?php

namespace ApipublicaBundle\Controller;

use DateTime;
use DoctrineExtensions\Query\Mysql\Date;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SitemapController extends Controller {

    /**
     * Para acceder a este metodo no se require autorizacion
     * @ApiDoc(
     *     section = " Sitemap",
     *     description="Genera el sitemap-index"
     * )
     */
    public function indexAction(){
        $articles = array();
        $em = $this->getDoctrine()->getManager();
        //Query to get articles from today with status 'published' and published date not null 
        $query = $em->createQuery(
                "SELECT DISTINCT(EXTRACT(YEAR_MONTH FROM p.publishedAt)) FROM BackendBundle:Page p "
                . "WHERE p.publishedAt IS NOT NULL AND p.status='published'");
        //Return array of month-year in format(yyyymm)
        $months = $query->getResult();
        foreach ($months as $month) {
            $month_var = substr($month[1], -2);
            $year_var  = substr($month[1], 0, 4);
            $last_mod  = $em->createQuery("SELECT MAX(p.publishedAt) FROM BackendBundle:Page p "
                    . "WHERE MONTH(p.publishedAt) = '" . $month_var . "' AND p.publishedAt IS NOT NULL");
            $last_mod = $last_mod->getSingleResult()[1];
            $dir      = $_SERVER['DOCUMENT_ROOT'] . "/";
            $file     = 'sitemap/articles-' . $year_var . '-' . $month_var . '.xml';
            $articles[] = array(
                'loc'     => $file,
                'lastmod' => date_format(new DateTime($last_mod),
                    'c'));
            if ( !file_exists( $dir . $file ) ){
                $host_ef = $this->container->getParameter('host_uri');
                $render  = $this->render('@Apipublica/Sitemap/subsitemap-article.xml.twig', array(
                    'urls'     => $this->getSubsitemap( $month_var, $year_var ),
                    'hostname' => $host_ef
                ));
                file_put_contents( $dir . $file, $render->getContent() );
            }
        }
        $host_ef = $this->container->getParameter('host_uri');

        return $this->render('@Apipublica/Sitemap/index.xml.twig', array(
                    'urls' => $articles,
                    'hostname' => $host_ef
        ));
    }
    /**
     * Para acceder a este metodo no se require autorizacion
     * @ApiDoc(
     *     section = " Sitemap",
     *     description="Genera el sitemap-current"
     * )
     */

    public function currentAction(Request $request) {
        $month = date('n'); 
        $year = date('Y');
        $articles = array();
        $em = $this->getDoctrine()->getManager();
        //Query to get articles from dates 
        $query = $em->createQuery("SELECT p FROM BackendBundle:Page p "
                . "WHERE MONTH(p.publishedAt) = '" . $month . "'"
                . " AND YEAR(p.publishedAt) = '" . $year . "'"
                . " AND p.status='published'"
        );
        $urls = $query->getResult();
        foreach ($urls as $article) {
            //Add elements to item sitemap
            $articles[] = array(
                'loc' => $article->getSlug(),
                'lastmod' => date_format($article->getPublishedAt(), 'c'),
                'changefreq' => date("Y/n/d")== date_format($article->getPublishedAt(), "Y/n/d") ?'hourly':'daily',
                'priority' => date("Y/n/d")== date_format($article->getPublishedAt(), "Y/n/d") ? '0.9':'0.8'
            );
        }
        //Set url for location
        $host_ef = $this->container->getParameter('host_uri');
        return $this->render('@Apipublica/Sitemap/subsitemap.xml.twig', array(
                    'urls' => $articles,
                    'hostname' => $host_ef
        ));
    }

    /**
     * Para acceder a este metodo no se require autorizacion
     * @ApiDoc(
     *     section = " Sitemap",
     *     description="Genera el sitemap de articulos del mes actual
     * )
     */

    /**
     * Para acceder a este metodo no se require autorizacion
     * @ApiDoc(
     *     section = " Sitemap",
     *     description="Genera el sitemap de articulos por mes",
     *     requirements={
     *      {"name"="formato", "dataType"="string", "required"=true, "description"="formato", "default"="xml"}
     *     }
     * )
     */
    public function subsitemapAction($month, $year, $formato) {

        /*var_dump($month);
        var_dump($year);


        var_dump($formato);*/



        $articles = $this->getSubsitemap( $month, $year );
        $host_ef  = $this->container->getParameter('host_uri');

        if($formato=='json'){

            $helpers = $this->get("app.helpers");
            $response = $helpers->responseHeaders(200, $articles);

            return $response;

        }else{
            return $this->render('@Apipublica/Sitemap/subsitemap.xml.twig', array(
                    'urls'     => $articles,
                    'hostname' => $host_ef
                )
            );
        }


    }

    /**
     * Para acceder a este metodo no se require autorizacion
     * @ApiDoc(
     *     section = " Sitemap",
     *     description="Genera el sitemap de articulos por año y mes",
     *     requirements={
     *      {"name"="formato", "dataType"="string", "required"=true, "description"="formato", "default"="xml"}
     *     }
     * )
     */
    public function subsitemapArticlesAction($month, $year, $formato) {

        $helpers = $this->get("app.helpers");

        $dir      = $_SERVER['DOCUMENT_ROOT'] . "/";
        $file     = 'sitemap/articles-' . $year . '-' . $month . '.xml';

        if ( file_exists( $dir . $file ) ) {
            //Open sitemap
            $fileSitemap = file_get_contents($dir . $file);

            $articles = array();
            //create object xml
            $xml = simplexml_load_string($fileSitemap);



            foreach ($xml->url as $item){
                $slug = $item->loc;
                //replace .html string
                $search = array('.html', 'https://www.elfinanciero.com.mx/');
                $replace = array('','');
                $slug_clean = str_replace($search, $replace, $slug);
                //Add elements to item sitemap
                $articles[] = array(
                    'loc'        => $slug_clean,
                    'lastmod'    => (string) $item->lastmod,
                    'changefreq' => 'daily',
                    'priority'   => '0.8'
                );
            }

            $host_ef  = $this->container->getParameter('host_uri');

            if($formato=="xml"){
                return $this->render('@Apipublica/Sitemap/subsitemap-article.xml.twig', array(
                        'urls'     => $articles,
                        'hostname' => $host_ef
                    )
                );

            }elseif($formato=="json"){


                foreach ($xml->url as $item){
                    $item->loc = trim($item->loc);
                }



                $helpers = $this->get("app.helpers");
                $response = $helpers->responseHeaders(200, $xml );


                return $response;

            }



        }
        else{

            $msg = 'Sitemap Not Found';
            $data = $helpers->responseData($code = 404, $msg);
            $response = $helpers->responseHeaders($code = 404, $data);

            return $response;
        }


    }

    /**
     * Para acceder a este metodo no se require autorizacion
     * @ApiDoc(
     *     section = " Sitemap",
     *     description="Genera el google-narrative-news",
     *     requirements={
     *      {"name"="section", "dataType"="string", "required"=true, "description"="section", "default"="1"}
     *     }
     * )
     */
    public function googleNarrativeNewsAction(Request $request) {
        $articles = array();
        $em       = $this->getDoctrine()->getManager();
        $section  = $request->get('section',"1");

        $date_ll = new \DateTime();
        $date_ll->modify('-2 day');
        $date_ul     = new \DateTime();
        $section_sql = "";

        $pre_query = "SELECT p
                      FROM BackendBundle:Page p
                      WHERE 
                          p.publishedAt is not NULL
                          AND p.publishedAt BETWEEN '" . $date_ll->format("Y-m-d h:i:s") . "' AND '" . $date_ul->format("Y-m-d h:i:s") . "'
                          AND p.pageType='article'
                          AND p.status='published'
                          ";
        if ( $section != "1" ){
            $section_sql = " AND p.categoryId=" . $section;
            $pre_query   = $pre_query . $section_sql;
        }
        $query = $em->createQuery($pre_query);
        $urls  = $query->getResult();

        foreach ($urls as $article) {
            $tags = array();
            foreach( $article->getTag() as $tag ){
                $tags[] = $tag->getTitle();
            }
            $tags2 = implode(",",$tags);
            $articles[] = array(
                'loc'             => $article->getSlug(),
                'title'           => $article->getTitle(),
                'description'     => $article->getShortDescription(),
                'guid'            => $article->getSlug(),
                'enc_len'         => "11",
                'enc_type'        => "audio/mp3",
                'enc_url'         => "http://www.jingle.org/britdeepvo.mp3",
                'tag'             => $tags2,
                'pubdate'         => date_format($article->getPublishedAt(), 'c'),
                'itunes_duration' => "11",
            );
        }
        //Set url for location
        $host_ef = $this->container->getParameter('host_uri');

        return $this->render('@Apipublica/Sitemap/sitemapgooglenarrativenews.xml.twig', array(
            'urls'     => $articles,
            'hostname' => $host_ef
        ));
    }

    private function getSubsitemap( $month, $year ){
        $articles = array();
        $em       = $this->getDoctrine()->getManager();

        $d2 = new DateTime(date()); //Fecha actual

        //Query to get articles from dates
        $query = $em->createQuery("SELECT p FROM BackendBundle:Page p "
            . "WHERE MONTH(p.publishedAt) = '" . $month . "'"
            . " AND YEAR(p.publishedAt) = '" . $year . "'"
            . " AND p.status='published'"
        );
        $urls = $query->getResult();


        foreach ($urls as $article) {
            //Add elements to item sitemap

            $d1 = $article->getPublishedAt();


            $ts1 =strtotime(date_format($d1,'Y/n/d'));
            $ts2 = strtotime(date_format($d2,'Y/n/d'));


            $year1 = date('Y', $ts1);
            $year2 = date('Y', $ts2);
            $month1 = date('m', $ts1);
            $month2 = date('m', $ts2);

            $diff = (($year2 - $year1) * 12) + ($month2 - $month1);


            $articles[] = array(
                'loc'        => $article->getSlug(),
                'lastmod'    => date_format($article->getPublishedAt(), 'c'),
                'changefreq' => $diff<=1?'Weekly':'Monthly',
                'priority'   => $diff<=1?'0.6':'0.4'
            );
        }

        return $articles;
    }

    /**
     * Para acceder a este metodo no se require autorizacion
     * @ApiDoc(
     *     section = " Sitemap",
     *     description="Genera el celltick",
     *     requirements={
     *      {"name"="csvURL", "dataType"="string", "required"=true, "description"="csv content", "default"="cell.csv"}
     *     }
     * )
     */
    public function cellAction(Request $request) {
        //Reading google spreadsheet testing
        $spreadsheet_url = $request->get('csvURL',"");

        if(!ini_set('default_socket_timeout', 15)) echo "<!-- unable to change socket timeout -->";

        if (($handle = fopen($spreadsheet_url, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $spreadsheet_data[] = $data;
            }
            fclose($handle);
        }else
            die("Problem reading csv");

        for ( $i=1 ; $i < count( $spreadsheet_data ); $i++ ) {
            $articles[] = array(
                'title'                => $spreadsheet_data[$i][0],
                'category-description' => $spreadsheet_data[$i][1],
                'link'                 => $spreadsheet_data[$i][2],
                'slug'                 => $spreadsheet_data[$i][3],
                'publishedAt'          => $spreadsheet_data[$i][4],
                'description'          => $spreadsheet_data[$i][5]
            );
        }

        //Set url for location
        $host_ef = $this->container->getParameter('host_uri');

        $response = $this->render('@Apipublica/Sitemap/sitemapcelltick.xml.twig', array(
            'urls'     => $articles,
            'hostname' => $host_ef
        ));
        $response->headers->set('Content-Type', 'xml');
        $response->setCharset('utf-8');

        return $response;
    }

    /**
     * Para acceder a este metodo no se require autorizacion
     * @ApiDoc(
     *     section = " Sitemap",
     *     description="Genera el celltick media",
     *     requirements={
     *      {"name"="csvURL", "dataType"="string", "required"=true, "description"="csv content", "default"="gnn.csv"}
     *     }
     * )
     */
    public function cellMediaAction(Request $request) {
        //Reading google spreadsheet testing
        $spreadsheet_url = $request->get('csvURL',"gnn.csv");

        if(!ini_set('default_socket_timeout', 35)) echo "<!-- unable to change socket timeout -->";

        if (($handle = fopen($spreadsheet_url, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $spreadsheet_data[] = $data;
            }
            fclose($handle);
        }else
            die("Problem reading csv");

        for ( $i=1 ; $i < count( $spreadsheet_data ); $i++ ) {
            $articles[] = array(
                'title'           => $spreadsheet_data[$i][0],
                'description'     => $spreadsheet_data[$i][1],
                'guid'            => $spreadsheet_data[$i][2],
                'enc_url'         => $spreadsheet_data[$i][3],
                'enc_len'         => $spreadsheet_data[$i][4],
                'enc_type'        => $spreadsheet_data[$i][5],
                'pubdate'         => $spreadsheet_data[$i][6],
                'itunes_duration' => $spreadsheet_data[$i][7],
            );
        }
        $host_ef = $this->container->getParameter('host_uri');

        $response = $this->render('@Apipublica/Sitemap/sitemapcelltickmedia.xml.twig',
            array(
                'urls'     => $articles,
                'hostname' => $host_ef
            )
        );
        $response->headers->set('Content-Type', 'xml');
        $response->setCharset('utf-8');

        return $response;
    }


    /**
     * Para acceder a este metodo no se require autorizacion
     * @ApiDoc(
     *     section = " Sitemap",
     *     description="Genera el json para Alexa",
     *     requirements={
     *      {"name"="csvURL", "dataType"="string", "required"=true, "description"="csv content", "default"="alexa.csv"}
     *     }
     * )
     */
    public function alexaAction(Request $request) {
        //Reading google spreadsheet testing
        $spreadsheet_url = $request->get('csvURL',"gnn.csv");

        $helpers = $this->get("app.helpers");

        if(!ini_set('default_socket_timeout', 35)) echo "<!-- unable to change socket timeout -->";

        if (($handle = fopen($spreadsheet_url, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 5000, ",")) !== FALSE) {
                $spreadsheet_data[] = $data;
            }
            fclose($handle);
        }else
            die("Problem reading csv");

        for ( $i=0 ; $i < count( $spreadsheet_data ); $i++ ) {
            $articles[] = array(
                'uid'           => $spreadsheet_data[$i][0],
                'updateDate'    => $spreadsheet_data[$i][1],
                'titleText'     => $spreadsheet_data[$i][2],
                'mainText'      => $spreadsheet_data[$i][3],
                
               /*'streamUrl'     => $spreadsheet_data[$i][4],
                
                
                'videoUrl'      => $spreadsheet_data[$i][5]*/
            )+((strlen($spreadsheet_data[$i][4])>0) ? array('streamUrl' => $spreadsheet_data[$i][4]) : array()) + ((strlen($spreadsheet_data[$i][5])>0) ? array('videoUrl' => $spreadsheet_data[$i][5]) : array());

        }
        $host_ef = $this->container->getParameter('host_uri');

        /* $response = $this->render('@Apipublica/Sitemap/sitemapcelltickmedia.xml.twig',
            array(
                'urls'     => $articles,
                'hostname' => $host_ef
            )
        );
        $response->headers->set('Content-Type', 'json');
        $response->setCharset('utf-8');

        return $response; */

        return $helpers->json($articles, true);
    }


    /**
     * Para acceder a este metodo no se require autorizacion
     * @ApiDoc(
     *     section = " Sitemap",
     *     description="Genera sitemaps de Xalok",
     *     requirements={
     *      {"name"="year", "dataType"="string", "required"=true, "description"="year", "default"="2013"},
     *      {"name"="month", "dataType"="string", "required"=true, "description"="month", "default"="01"}
     *     }
     * )
     */
    public function xalokSitemapsGenerationAction(Request $request) {
        $host_ef   = $this->container->getParameter('host_uri');
        $year_var  = $request->get('year',"");
        $month_var = $request->get('month',"");
        $dir       = $_SERVER['DOCUMENT_ROOT'] . "/";

        if ( $year_var != "" && $month_var != "" ){
            $file   = 'sitemap/articles-' . $year_var . '-' . $month_var . '.xml';
            if ( file_exists($dir . $file ) ) {
                system("rm " . $dir . $file );
            }
            $render = $this->render('@Apipublica/Sitemap/subsitemap-article.xml.twig',
                array(
                    'urls'     => $this->getXalokSubsitemap( $month_var, $year_var ),
                    'hostname' => $host_ef
                )
            );
            if ( file_put_contents( $dir . $file, $render->getContent() ) ){
                $render->headers->set('Content-Type', 'xml');
                $render->setCharset('utf-8');

                return $render;
            }else{
                die( "Unwritten file" );
            }
        }else{
            die();
        }

    }

    private function getXalokSubsitemap( $month, $year ){
        $helpers  = $this->get("app.helpers");
        $articles = array();
        $em       = $this->getDoctrine()->getManager('efOld');

        //Query to get articles from dates
        $query = $em->getRepository("XalokBundle:Page")->createQueryBuilder('p')
            //->where('p.template = :template AND p.status= :pstatus and p.pageType= :pageType')
            ->where('p.template = :template AND p.status= :pstatus and p.pageType= :pageType AND MONTH(p.publishedAt)= :month AND YEAR(p.publishedAt)= :year')
            ->setParameter('template', 'default')
            ->setParameter('pageType', 'article')
            ->setParameter('pstatus', 'published')
            ->setParameter('month', $month)
            ->setParameter('year', $year)
            ->orderBy('p.publishedAt', 'DESC')->getQuery();

        $urls = $query->getResult();
        foreach ($urls as $article) {
            //Add elements to item sitemap
            $articles[] = array(
                'loc'        => $article->getSlug(),
                'lastmod'    => date_format($article->getPublishedAt(), 'c'),
                'changefreq' => 'daily',
                'priority'   => '0.8'
            );
        }

        return $articles;
    }

    /**
     * Para acceder a este metodo no se require autorizacion
     * @ApiDoc(
     *     section = " Sitemap",
     *     description="Genera el google-news"
     * )
     */
    public function googleNewsAction(Request $request) {
        $articles = array();
        $em       = $this->getDoctrine()->getManager();

        $date_ll = new \DateTime();
        $date_ll->modify('-2 day');
        $date_ul = new \DateTime();

        $query = $em->createQuery("SELECT p
                                   FROM BackendBundle:Page p
                                   WHERE 
                                      p.publishedAt is not NULL
                                      AND p.publishedAt BETWEEN '" . $date_ll->format("Y-m-d h:i:s") . "' AND '" . $date_ul->format("Y-m-d h:i:s") . "'
                                      AND p.pageType='article'
                                      AND p.status='published'
                                      ");
        $urls  = $query->getResult();

        foreach ($urls as $article) {
            $tags = array();
            foreach( $article->getTag() as $tag ){
                $tags[] = $tag->getTitle();
            }
            $tags2 = implode(",",$tags);
            $articles[] = array(
                'loc'         => $article->getSlug(),
                'title'       => $article->getTitle(),
                'tag'         => $tags2,
                'publishedAt' => date_format($article->getPublishedAt(), 'c')
            );
        }
        //Set url for location
        $host_ef = $this->container->getParameter('host_uri');
        return $this->render('@Apipublica/Sitemap/sitemapgooglenews.xml.twig', array(
            'urls'     => $articles,
            'hostname' => $host_ef
        ));
    }
}
