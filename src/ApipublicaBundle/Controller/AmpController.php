<?php

namespace ApipublicaBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use stdClass;
use Lullabot\AMP\AMP;
use Lullabot\AMP\Validate\Scope;
use DOMDocument;

class AmpController extends Controller
{

    private $url_base;
    private $slug;

    /**
     * Para acceder a este metodo, se require autorizacion(token)
     * @ApiDoc(
     *     section = "AMP Google",
     *     description="Proporciona estado de salud de amp",
     *     requirements={
     *      {"name"="parametro", "dataType"="string", "required"=true, "description"="Parametro"}
     *    }
     * )
     */
    public function healthAction(Request $request, $param = "all")
    {

        $response = $this->forward('ApipublicaBundle\Controller\HealthController::checkAction', array(
            'request' => $request,
            'param' => $param,
        ));

        return $response;
    }

    /**
     * Para acceder a este metodo, se require autorizacion(token)
     * @ApiDoc(
     *     section = "AMP Google",
     *     description="Genera la nota en formato AMP basado en el slug",
     *     requirements={
     *      {"name"="category", "dataType"="string", "required"=true, "description"="category"},
     *       {"name"="title", "dataType"="string", "required"=true, "description"="title"},
     *    }
     * )
     */
    public function indexAction(Request $request, $category, $title)
    {
        $this->setUrlBase($request->getHttpHost());
        $this->setSlug($category . '/' . $title);
        return $this->create($this->getSlug());

    }

    /**
     * Para acceder a este metodo, se require autorizacion(token)
     * @ApiDoc(
     *     section = "AMP Google",
     *     description="Genera la nota en formato AMP basado en el slug (3 pisos)",
     *     requirements={
     *      {"name"="category", "dataType"="string", "required"=true, "description"="category"},
     *       {"name"="title", "dataType"="string", "required"=true, "description"="title"},
     *    }
     * )
     */
    public function subindexAction(Request $request, $category, $subcategory, $title)
    {
        $this->setUrlBase($request->getHttpHost());
        $this->setSlug($category . '/' . $subcategory . "/" . $title);
        return $this->create($this->getSlug(), $category);
    }


    private function create($slug, $category = NULL, $request)
    {
        $em = $this->getDoctrine()->getManager();
        $page_types_allow = array('article', 'carton', 'column', 'tv', 'sponsor', 'blogpost');
        $conditionals = array('slug' => $slug, 'status' => 'published', 'pageType' => $page_types_allow, 'portalId' => "3");
        $page = $em->getRepository('BackendBundle:Page')->findOneBy($conditionals);
        if (!$page) {

            $response_page = $this->forward('ApipublicaBundle:Page:getSlug', array('s1' => $request, 'p_slug' => $slug));
            $data_s = $this->buildAMP(json_decode($response_page->getContent(), true), $category, $legacy = true);

            return $this->render('@Apipublica/Amp/index.html.twig', get_object_vars($data_s));

        } else {
            $data = $this->buildAMP($page, $category, $legacy = false);

            return $this->render('@Apipublica/Amp/index.html.twig', get_object_vars($data));

        }
    }

    private function getUrlBase()
    {
        return $this->url_base;
    }

    private function setUrlBase($url)
    {
        $this->url_base = $url;
    }

    private function getSlug()
    {
        return $this->slug;
    }

    private function setSlug($slug)
    {
        $this->slug = $slug;
    }

    //$page= type:ObjectEntityPage
    private function buildAMP($page, $category = NULL, $legacy = false)
    {
        $data = new stdClass();

        //Add all properties
        if ($legacy == true) {
            $data->url_canonical = "https://www.elfinanciero.com.mx/" . $page['data']['slug'];
        } else {
            $data->url_canonical = "https://www.elfinanciero.com.mx/" . $page->getSlug();
        }

        if ($legacy == true) {
            $data->metadata_header = json_encode($this->makeMetadaHeader($page['data'], true));
        } else {
            $response_metadata = $this->makeMetadaHeader($page);
            $data->metadata_header =  json_encode($response_metadata['schema']);

            $data->metadata_header_array = $this->makeMetadaHeader($page);
        }

        if ($legacy == true) {
            $data->section = $page['data']['category'][0]['slug'];
        } else {
            $data->section = $page->getCategoryId()->getSlug();
        }

        if ($legacy == true) {
            $data->publish_date = date('d-m-Y', $page['data']['published']['timestamp']);
        } else {
            $data->publish_date = $page->getPublishedAt()->format('d-m-Y');
        }

        if ($legacy == true) {
            $data->category = $page['data']['category'][0]['title'];
        } else {
            $data->category = $page->getCategoryId()->getTitle();
        }

        if ($legacy == true) {
            $data->description = $page['data']['shortDescription'];
        } else {
            $data->description = $page->getShortDescription();
        }

        if ($legacy == true) {
            $data->authors = $this->getAuthorMetrics($page['data']['authors'], true);
        } else {
            $data->authors = $this->getAuthors($page->getAuthor(), $page->getContent());
        }

        if ($legacy == true) {
            $data->raw_authors = $this->getAuthorMetrics($page['data']['authors'], true);
        } else {
            $data->raw_authors = $this->getAuthorMetrics($page->getAuthor());
        }

        if ($legacy == true) {
            $data->published_at = date('d.m.Y', $page['data']['published']['timestamp']);
        } else {
            $data->published_at = $page->getPublishedAt()->format('d.m.Y');
        }

        if ($legacy == true) {
            $data->updated_timestamp = $page['data']['updated']['timestamp'];
            $data->updated_at = date('d F Y H:i', $page['data']['updated']['timestamp']);
        } else {
            $data->updated_timestamp = $page->getUpdatedAt()->getTimestamp();
            $data->updated_at = $page->getUpdatedAt()->format('d F Y H:i');
        }

        if ($legacy == true) {
            $data->tags = null;
        } else {
            $data->tags = $page->getTag()->toArray();
        }

        if ($legacy == true) {
            $data->title = $page['data']['title'];
        } else {
            $data->title = $page->getTitle();
        }

        if ($legacy == true) {
            $data->bullets = $page['data']['shortDescription'];
        } else {
            $data->bullets = $page->getBullets();
        }
        if ($legacy == true) {
            $data->slug = $page['data']['slug'];
        } else {
            $data->slug = $page->getSlug();
        }

        if ($category == "opinion") {
            if ($legacy == true) {
                $data->author_element_top = $this->makeColumnaHeader($page['data']['columna'], null, true);
            } else {
                $data->author_element_top = $this->makeColumnaHeader($page->getAuthor()[0], $page->getColumna()->getNombre());
            }

        } else {
            if ($legacy == true) {
                if ($page['data']['template'] == 'video') {
                    $first_pos_data = array_shift(array_values($page['data']['modules']));
                    $image_final = $first_pos_data['data']['collection'][0]['data']['image']['src'];
                } else {

                    if (isset($page['data']['content'][".image-holder"])) {
                        $imageholder = str_replace('files/article_main/', "", $page['data']['content'][".image-holder"][0]);
                        $src_field = strstr($imageholder[0], 'uploads');
                        $image_final = strstr($src_field, '" ', true);

                        if ($image_final == false) {
                            $image_final = strstr($src_field, '"', true);
                        }

                        //si tiene doble slash la img, se fixea
                        $wrong_img = strpos($image_final, '//');
                        if ($wrong_img !== false) {
                            $img_fixed = str_replace('//', '/', $image_final);
                            $image_final = $img_fixed;
                        }
                    }
                }
                $data->element_top = array('item' => "www.elfinanciero.com.mx/" . $image_final, 'type' => 'image');
            } else {
                $data->element_top = $this->makeCover(json_decode($page->getElementHtmlSerialized(), true));
                if ( $data->element_top["type"] == "image" ){
                    $image_final               = $data->element_top["item"];
                    $replace                   = "_standard_desktop_large";
                    $a            = explode( ".", $image_final );
                    $last_pos     = count( $a ) -1;
                    $c            = $a[$last_pos];
                    $a[$last_pos] = $replace;
                    $a[]          = $c;
                    $d            = $a;
                    $f            = count( $d );
                    $d[$f-3] = $d[$f-3] . $d[$f-2];
                    $d[$f-2] = $d[$f-1];
                    unset( $d[$f-1] );
                    $e                         = implode( ".", $d );
                    $data->element_top["item"] = $e;
                }
            }
        }

        if ($legacy == true) {
            $html_legacy = $this->getBodyPage($page['data']);
            $data->body_items = $this->makeItems($html_legacy, true);
        } else {
            $data->body_items = $this->makeItems($page->getHtml(), false);
        }

        $data->cnd_repository = $this->container->getParameter('cdn_uri');
        $data->amp_logo_ef = $this->container->getParameter('logo_amp');
        return $data;
    }

    //$data = type: ObjectEntityPage
    private function makeMetadaHeader($data, $legacy = false)
    {
        $cnd_repository = $this->container->getParameter('cdn_uri');
        $amp_logo_ef = $this->container->getParameter('logo_amp');

        if ($legacy == true) {
            $legacyAuthor = null;
            if (isset($data['content']['.details-box .important']) && $data['content']['.details-box .important'][0] != '') {
                $legacyAuthor = strip_tags($data['content']['.details-box .important'][0]);
            } else {
                $legacyAuthor = isset($data['authors'][0]) ? $data['authors'][0]['firstName'] : null;
            }

            return array(
                "@context" => "https://schema.org",
                "@type" => "NewsArticle",
                "mainEntityOfPage" => "http://cdn.ampproject.org/article-metadata.html",
                "headline" => $data['title'],
                "datePublished" => date('c', $data['updated']['timestamp']), //fix formato fecha
                "dateModified" => date('c', $data['updated']['timestamp']),
                "description" => $data['shortDescription'],
                "author" => array(
                    "@type" => "Person",
                    "name" => $legacyAuthor
                ),
                "image" => array(
                    "@type" => "ImageObject",
                    "url" => null,
                    "height" => 800,
                    "width" => 800
                ),
                "publisher" => array(
                    "@type" => "Organization",
                    "name" => "El Financiero",
                    "logo" => array("@type" => "ImageObject",
                        "url" => "https://". $cnd_repository . $amp_logo_ef,
                        "width" => 600,
                        "height" => 60
                    )
                )
            );
        } else {
             $helpers = $this->get("app.helpers");
             return $helpers->microData( $data, $from = 'amp' );
        }


    }

    private function makeColumnaHeader($author, $columna = null, $legacy = false)
    {

        if ($legacy == true) {
            $author_legacy = $author;
            return array(
                "name" => $author_legacy['authors'][0]['name'] . " " . $author_legacy['authors'][0]['aPaterno'],
                "columna" => $author_legacy['nombre'],
                "facebook" =>
                    ($author_legacy['authors'][0]['facebook'] || $author_legacy['authors'][0]['facebook'] == "OpinionElFinanciero" || $author_legacy['authors'][0]['facebook']) ?
                        $author_legacy['authors'][0]['facebook'] : "ElFinancieroMx/",
                "twitter" =>
                    ($author_legacy['authors'][0]['twitter'] || $author_legacy['authors'][0]['twitter'] == "@ElFinanciero" || $author_legacy['authors'][0]['twiitter'] == "ElFinanciero") ?
                        str_replace("@", "", $author_legacy['authors'][0]['twitter']) : "ElFinanciero",
                "linkedin" =>
                    ($author_legacy['authors'][0]['linkedin'] || $author_legacy['authors'][0]['linkedin'] == "company/90169/" || $author_legacy['authors'][0]['linkedin'] == "company/90169") ?
                        str_replace(array("in/", "/in/", "/in"), "", $author_legacy['authors'][0]['linkedin']) : "company/90169/",
                "googleplus" =>
                    ($author_legacy['authors'][0]['googlePlus'] || $author_legacy['authors'][0]['googlePlus'] == "+elfinanciero" || $author_legacy['authors'][0]['googlePlus'] == "elfinanciero") ?
                        $author_legacy['authors'][0]['googlePlus'] : "+elfinanciero",
                "email" => ($author_legacy['authors'][0]['email'] || $author_legacy['authors'][0]['email'] == "opinion@elfinanciero.com.mx") ?
                    $author_legacy['authors'][0]['email'] : "contactoweb@elfinanciero.com.mx",
                "image" => $author_legacy['authors'][0]['image']['imagePath']
            );

        } else {
            return array(
                "name" => $author->getName() . " " . $author->getAPaterno(),
                "columna" => $columna,
                "facebook" =>
                    ($author->getFacebook() || $author->getFacebook() == "OpinionElFinanciero" || $author->getFacebook()) ?
                        $author->getFacebook() : "ElFinancieroMx/",
                "twitter" =>
                    ($author->getTwitter() || $author->getTwitter() == "@ElFinanciero" || $author->getTwitter() == "ElFinanciero") ?
                        str_replace("@", "", $author->getTwitter()) : "ElFinanciero",
                "linkedin" =>
                    ($author->getLinkedin() || $author->getLinkedin() == "company/90169/" || $author->getLinkedin() == "company/90169") ?
                        str_replace(array("in/", "/in/", "/in"), "", $author->getLinkedin()) : "company/90169/",
                "googleplus" =>
                    ($author->getGooglePlus() || $author->getGooglePlus() == "+elfinanciero" || $author->getGooglePlus() == "elfinanciero") ?
                        $author->getGooglePlus() : "+elfinanciero",
                "email" => ($author->getEmail() || $author->getEmail() == "opinion@elfinanciero.com.mx") ?
                    $author->getEmail() : "contactoweb@elfinanciero.com.mx",
                "image" => $author->getImage()->getImagePath()
            );
        }

    }

    //$authors = type:Collection.  description: Get collection of authors/author from page
    //$modifiers_authors = type: json. description: Get extra of authors
    private function getAuthors($authors, $modifiers_authors)
    {
        //get data from modifiers
        $mod_authors = json_decode($modifiers_authors, true)['authorsModified'];
        $authors_fixed = array();

        foreach ($authors as $key => $auth) {
            //If editorial is true set 'Redacción'
            if ($mod_authors[$key]['editorial']) {
                $authors_fixed[] = "Redacción";
            } else if ( $mod_authors[$key]['texto'] ) {
                $authors_fixed[] = "Por " . $mod_authors[$key]['texto'];
            } else {
                //If enviado is true set 'Enviado' before author
                $em = $this->getDoctrine()->getManager();
                $author = $em->getRepository('BackendBundle:Author')->find($auth);
                if ($mod_authors[$key]['enviado']) {
                    if ($author->getCorresponsal()) {
                        $authors_fixed[] = "Enviado " . $author->getName() . " " . $auth->getAPaterno() . "/Corresponsal";
                    } else {
                        $authors_fixed[] = "Enviado " . $author->getName() . " " . $auth->getAPaterno();
                    }
                } else {
                    if ($author->getCorresponsal()) {
                        $authors_fixed[] = $author->getName() . " " . $auth->getAPaterno() . "/Corresponsal";
                    } else {
                        $authors_fixed[] = $author->getName() . " " . $auth->getAPaterno();
                    }
                }
            }
        }

        return $authors_fixed;
    }

    private function getAuthorMetrics($authors, $legacy = false)
    {
        $authors_string = '';
        foreach ($authors as $key => $author) {
            if ($legacy == false) {
                $authors_string .= $author->getName() . " " . $author->getAPaterno();
            } else {
                $authors_string .= $author['firstName'] . " " . $author['lastName'];
            }

            if ($key != (count($authors) - 1)) {
                $authors_string .= " | ";
            }
        }
        return $authors_string;
    }

    //$html = type: string description: Code html from header/cover page
    private function makeCover($element, $legacy = false)
    {
        //If data not null, element top exists
        if ($element['data']) {
            $type = $element['type'];
            //get source for element
            switch ($type) {
                case "video":
                    //get video code from explode uri
                    $element = explode('/', $element['data']['item']['uri'])[2];
                    break;
                case "image":
                    $element = $this->getUrlBase() . $element['data']['imagePath'];
                    break;
                case "html":
                    $element = $this->makeItems($element['data']);
                    break;
                default:
                    $element = NULL;
            }
            return array('item' => $element, 'type' => $type);
        } else
            return;
    }

    private function makeItems($html, $legacy = false)
    {
        if ($html) {
            $amp  = new AMP();
            libxml_use_internal_errors(true);
            if ($legacy == false) {
                $html = $this->cleanInputHtml($html);
                $amp->loadHtml($html, ['scope' => Scope::HTML_SCOPE]);
            } else {
                $amp->loadHtml($html, ['scope' => Scope::HTML_SCOPE]);
            }
            $amp_html = $amp->convertToAmpHtml();
            $doc = new DOMDocument();

            //close open tags
            $amp_html = preg_replace('/<img(.*?)\/?>/', '<img$1/>', $amp_html);
            //close open tags ends

            $doc->loadHTML($amp_html);
            libxml_use_internal_errors(false);
            $body = $doc->getElementsByTagName('body');

            if ($body && 0 < $body->length) {
                $body = $body->item(0);
                return $doc->saveHTML($body);
            }
        } else {
            return $html;
        }
    }

    private function cleanInputHtml($html)
    {
        //$allowable_tags = "<div><p><span><strong><iframe><span><video><source><blockquote><time><script><a><img>";
        //$html = strip_tags($html, $allowable_tags);
        //Clean attributes in paragraph tag
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        foreach ($dom->getElementsByTagName('p') as $element) {
            $attributes = $element->attributes;
            while ($attributes->length) {
                $element->removeAttribute($attributes->item(0)->name);
            }
        }
        $dom->loadHTML($dom->saveHTML());
        $videos = $dom->getElementsByTagName('video');
        if ($videos->length != 0) {
            foreach ($videos as $video) {
                $source = $video->childNodes[0];
                $src = $source->getAttribute('src');
                $new_vimeo = $dom->createElement('iframe');
                $new_vimeo->setAttribute('src', $src);
                $video->parentNode->replaceChild($new_vimeo, $video);
            }
        }
        $html = $dom->saveHTML();
        return $html;

    }

    public function getBodyPage($data)
    {
        if ($data['template'] == 'video') {
            $first_pos_data = array_shift(array_values($data['modules']));
            $body_page = $first_pos_data['data']['collection'][0]['data']['image']['description'];
        } else {
            if (isset($data['content']['.article-paragraphs'])) {
                if ($data['template'] == 'opinion') {
                    if (count($data['content']['.article-paragraphs']) > 1) {
                        $data_body = "";
                        foreach ($data['content']['.article-paragraphs'] as $item) {
                            $data_body .= $item;
                        }
                        $body_page = $data_body;

                    } else {
                        $body = array_shift(array_values($data['content']['.article-paragraphs']));
                        if (count($body) > 1) {
                            $body_page_s = '';
                            foreach ($body as $item){
                                $find_key = 'iframe';
                                $pos_iframe = strpos($item, $find_key);
                                //Si no tiene iframe, entonces agrega
                                if ($pos_iframe == false) {
                                    //$imageholder = preg_replace('/(.*?)\/uploads/(.*?)/', "$1https://www.elfinanciero.com.mx/uploads/$2", $item);
                                    $body_page_s .= $item;
                                }
                            }

                            if(preg_match("/\/\/files\/crop/",$body_page_s, $matches ))
                            {
                                $body_page = preg_replace("/(.*?)\/\/files\/crop\/uploads(.*?)/","$1/uploads$2", $body_page_s );
                            }
                            else
                            {
                                $body_page = preg_replace('/(.*?)\/uploads(.*?)/', "$1/uploads$2", $body_page_s);
                            }

                                //$body_page = preg_replace('/(.*?)\/uploads(.*?)/', "$1https://www.elfinanciero.com.mx/uploads$2", $body_page_s);
                        } else {
                            if (gettype($body) == 'array') {
                                $body_page = array_shift(array_values($body));
                            } else {
                                $body_page = $body;
                            }
                        }
                    }

                } else {
                    $body_full = array_shift(array_values($data['content']['.article-paragraphs']));
                    $body_page = implode(",", $body_full);
                }

            } else {
                $body_page = null;
            }
        }

        return $body_page;
    }
}
