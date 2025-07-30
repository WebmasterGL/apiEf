<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApipublicaBundle\Controller;

/**
 * Description of RssController
 *
 * @author victor.nombret
 */
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Facebook\InstantArticles\Elements\InstantArticle;
use Facebook\InstantArticles\Elements\Header;
use Facebook\InstantArticles\Elements\Time;
use Facebook\InstantArticles\Elements\Author;
use Facebook\InstantArticles\Elements\Image;
use Facebook\InstantArticles\Elements\Caption;
use Facebook\InstantArticles\Elements\Ad;
use Facebook\InstantArticles\Elements\Analytics;
use Facebook\InstantArticles\Transformer\Transformer;
use Facebook\InstantArticles\Elements\Footer;

class InstantController extends Controller
{
    private $host_ef;

    private function setHostEf($uri)
    {
        $this->host_ef = $uri;
    }

    private function getHostEf()
    {
        return $this->host_ef;
    }

    /**
     * Para acceder a este metodo no se require autorizacion
     * @ApiDoc(
     *     section = " Facebook Instant Articles",
     *     description="Genera el Feed para Instant Articles"
     * )
     */
    public function indexAction(Request $request)
    {
        $this->setHostEf($this->container->getParameter('host_uri'));
        //Always get last records from 30 minutes.
        $real_time_datetime = date('Y-m-d H:i:s', strtotime('today midnight'));
        //Type and status from pages
        $status = 'published';
        $type1 = 'article';
        $type2 = 'column';
        $em = $this->getDoctrine()->getManager();
        //Build query to get items for feed
        $query = $em->createQuery("SELECT p FROM BackendBundle:Page p WHERE p.status = '" . $status . "'"
            . " AND (p.pageType = '" . $type1 . "'OR p.pageType = '" . $type2 . "') "
            . " AND p.publishedAt >= '" . $real_time_datetime . "'"
            . " AND p.portalId = '3'"
            . ' ORDER BY p.publishedAt DESC ');
        $articles = $query->getResult();
        // Generate items
        $items = [];
        foreach ($articles as $article) {
            $item = [
                'title' => $article->getTitle(),
                'description' => $article->getShortDescription(),
                'pubdate' => $article->getPublishedAt()->format('r'),
                'link' => $this->getHostEf() . $article->getSlug(),
                'guid' => $this->getHostEf() . $article->getSlug(),
                'category' => $article->getCategoryId()->getTitle(),
                'categorySlug' => $article->getCategoryId()->getSlug(),
                'articleSlug' => $article->getSlug(),
                'author' => $this->getAuthorMetrics($article->getAuthor()),
                //Convert html code remains
                'content' => html_entity_decode($this->createIA($article))
            ];
            $items[] = $item;
        }
        //Send data to feed
        return $this->render('@Apipublica/FacebookIA/index.xml.twig', [
            'pubdate' => date('D, d M Y H:i:s T'),
            'items' => $items,
            'uri' => $this->getHostEf()
        ]);
    }

    public function disableLog()
    {
        //\Logger::configure(['rootLogger' => ['appenders' => ['facebook-instantarticles-transformer']], 'appenders' => ['facebook-instantarticles-transformer' => ['class' => 'LoggerAppenderConsole', 'threshold' => 'INFO', 'layout' => ['class' => 'LoggerLayoutSimple']]]]);
    }

    //Build InstantArticle
    private function createIA($data)
    {
        //$this->disableLog();
        $ia = InstantArticle::create();
        $ia = $this->makeMetadataIA($ia, $data);
        $ia = $this->makeHeaderIA($ia, $data);
        $ia = $this->makeBodyIA($ia, $data);
        $ia = $this->makeFooterIA($ia);
        return $ia->render();
    }

    //Add general metadata
    private function makeMetadataIA($instant_article, $data)
    {
        $instant_article->withCharset('utf-8')
            ->withCanonicalUrl($this->getHostEf() . $data->getSlug())
            ->withStyle('financiero')
            ->addMetaProperty('fb:use_automatic_ad_placement', 'enable=true ad_density=default')
            ->addMetaProperty('og:image', 'https://www.elfinanciero.com.mx' . $data->getMainImage()->getImagePath());
        //Check metadata for FB or set metadata SEO default
        $instant_article = $this->addSocialMetadata($instant_article, $data->getSocial()['facebook'], $data->getSeo());
        return $instant_article;
    }

    //Add specific metadata
    private function addSocialMetadata($instant_article, $metadata_social, $metadata_seo)
    {
        if (!empty($metadata_social['title']) || !empty($metadata_social['description'])) {
            $instant_article->addMetaProperty('og:title', $metadata_social['title'])
                ->addMetaProperty('og:description', $metadata_social['description']);
        } elseif (!empty($metadata_seo['title']) || !empty($metadata_seo['description'])) {
            $instant_article->addMetaProperty('og:title', $metadata_seo['title'])
                ->addMetaProperty('og:description', $metadata_seo['description']);
        }
        return $instant_article;
    }

    //Add header
    private function makeHeaderIA($instant_article, $data)
    {
        $instant_article->withHeader(
            $this->makeHeaderItems($instant_article, $data)
        );
        return $instant_article;
    }

    private function makeHeaderItems($instant_article, $data)
    {
        $header = Header::create();
        $header->withTitle($data->getTitle());
        //Get subtitle from content
        if (!empty($data->getBullets())) {
            $header->withSubTitle($data->getBullets()[0]);
        }
        $header->withPublishTime(Time::create(Time::PUBLISHED)->withDatetime($data->getPublishedAt()));
        $header->withModifyTime(Time::create(Time::MODIFIED)->withDatetime($data->getUpdatedAt()));
        //Sets authors
        $header->withAuthors($this->setAuthorsIA($data->getAuthor(), $data->getContent()));
        //Set cover
        $header->withCover($this->selectCoverIA($data));
        //Set first Category as a kicker
        $header->withKicker($data->getCategoryId()->getTitle());
 
        //ID Audince Netwotk Facebook
        $id_adnw = '1063568587023463_1472451426135175';
        //Add space for ads  
        $ad = Ad::create()
            ->withHeight(300)
            ->withWidth(250)
            ->withSource('https://www.facebook.com/adnw_request?placement=' . $id_adnw . '&adtype=banner300x250');
        $header->addAd($ad); 
        
       return $header;
    }

    //
    private function setAuthorsIA($data, $extra_data)
    {
        //Get data from modifiers authors
        $mod_authors = json_decode($extra_data, true)['authorsModified'];
        $authors = [];
        foreach ($data as $key => $auth) {
            //If editorial is true set 'Redacción'
            if ($mod_authors[$key]['editorial']) {
                $authors[] = Author::create()->withName('Redacción');
            } else {
                //If enviado is true set 'Enviado' before name author
                $em = $this->getDoctrine()->getManager();
                $author = $em->getRepository('BackendBundle:Author')->find($auth);
                if ($mod_authors[$key]['enviado']) {
                    if ($author->getCorresponsal()) {
                        $authors[] = Author::create()->withName('Enviado ' . $author->getName() . ' ' . $auth->getAPaterno() . '/Corresponsal');
                    } else {
                        $authors[] = Author::create()->withName('Enviado ' . $author->getName() . ' ' . $auth->getAPaterno());
                    }
                } else {
                    if ($author->getCorresponsal()) {
                        $authors[] = Author::create()->withName($author->getName() . ' ' . $auth->getAPaterno() . '/Corresponsal');
                    } else {
                        $authors[] = Author::create()->withName($author->getName() . ' ' . $auth->getAPaterno());
                    }
                }
            }
        }
        return $authors;
    }

    private function selectCoverIA($data)
    {
        //Data replace in imgs
        $trans = ['.' => '_smartphone_retina.', '/uploads/' => 'uploads/'];

        //If note is column
        if ($data->getPageType() == 'column') {
            $author = $data->getAuthor()[0];
            $result = strtr(($author->getImage() != null) ? $author->getImage()->getImagePath() : null, $trans);
            return Image::create()->withURL($this->getHostEf() . $result);
        } else {
            //Get info from main element
            $json = json_decode($data->getElementHtmlSerialized(), true);

            //If main element is defined
            if ($json['data']) {
                //if main element is an image and is not no display layout, show it
                if ($json['type'] == 'image') {
                    $result = strtr(($json['data']['imagePath'] != null) ? $json['data']['imagePath'] : null, $trans);
                    return Image::create()
                        ->withURL($this->getHostEf() . $result)
                        ->withCaption(
                            Caption::create()
                                ->appendText($this->getHostEf() . $json['description'])
                        );
                } else {
                    $result = strtr(($data->getMainImage() != null) ? $data->getMainImage()->getImagePath() : null, $trans);
                    return Image::create()
                        ->withURL($this->getHostEf() . $result)
                        ->withCaption(
                            Caption::create()
                                ->appendText($this->getHostEf() . $data->getMainImage()->getTitle())
                        );
                }
            } else {
                $result = strtr(($data->getMainImage() != null) ? $data->getMainImage()->getImagePath() : null, $trans);
                return Image::create()
                    ->withURL($this->getHostEf() . $result)
                    ->withCaption(
                        Caption::create()
                            ->appendText($this->getHostEf() . $data->getMainImage()->getTitle())
                    );
            }
        }
    }

    private function makeBodyIA($instant_article, $data)
    {
        //Add elements from content of article (html field)
        //Load rules to transformer
        $rules_file_content = file_get_contents(__DIR__ . '/../Resources/config/facebook/content.json', true);
        $transformer = new Transformer();
        $transformer->loadRules($rules_file_content);
        $ia = InstantArticle::create();
        //Start transformation html string to obj DomDocument
        libxml_use_internal_errors(true);
        //Tags allow to mapping
        $tags_allowed = '<span><p><img><div><h3><em><strong><a><iframe><script><blockquote>';
        //Call exceptions on html rules work
        $html = $data->getHtml();
        $html = $this->makeExceptionHtml($html);
        $document = new \DOMDocument();
        $document->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_use_internal_errors(false);
        //Transformation
        $transformer->transform($ia, $document);
        libxml_use_internal_errors(true);
        foreach ($ia->getChildren() as $item) {
            $instant_article->addChild($item);
        }
        $instant_article = $this->addScriptsBodyIA($instant_article, $data);
        return $instant_article;
    }

    private function addScriptsBodyIA($instant_article, $data)
    {
        
        //Add scripts trackers
        //Facebook Pixel and Content Insight
        $instant_article->addChild(
            $this->getFacebookTracker($data)
        );

        //Facebook App Tracked
        $instant_article->addChild(
            $this->getFacebookTrackerApp()
        );
        
        //Removed
        /* $instant_article->addChild(
          $this->getContentITracker($data->getSlug())
          ); */
        //Google Analytics
        $instant_article->addChild(
            $this->getGoogleATracker($data->getSlug(), $data->getTitle(), $this->getAuthorMetrics($data->getAuthor()), $data->getCategoryId()->getTitle())
        );

        // Analytics Comscore
        $instant_article->addChild(
            $this->getComscoreTracker(str_replace('/', '.', $data->getSlug()), $data->getPublishedAt()->format('d-m-Y'), $this->getAuthorMetrics($data->getAuthor()), $data->getCategoryId()->getTitle())
        );

         //- Taboola Feed
         $instant_article->addChild(
            $this->getTaboola()
        );
        
        return $instant_article;
    }

    private function makeExceptionHtml($html)
    {
        //Exception to add PullQuote
        $html = str_replace('<div class="quote__open">“</div><div>', '<div class="quote_text">', $html);
        /* $html = str_replace('<div class="quote__credit"><span class="name">',
          '<div class="quote__name">', $html); */
        return $html;
    }

    private function getFacebookTracker($data)
    {
        return Analytics::create()->withHTML("<script>
        (function (f, b, e, v, n, t, s) {
            if (f.fbq) return; n = f.fbq = function () {
                n.callMethod ?
                    n.callMethod.apply(n, arguments) : n.queue.push(arguments)
            }; if (!f._fbq) f._fbq = n;
            n.push = n; n.loaded = !0; n.version = '2.0'; n.queue = []; t = b.createElement(e); t.async = !0;
            t.src = v; s = b.getElementsByTagName(e)[0]; s.parentNode.insertBefore(t, s)
        }(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js'));
    
        fbq('init', '141578583108418');
        fbq('track', 'PageView');
        fbq('track', 'ViewContent', {
           id: '{$data->getSlug()}',
           title: ia_document.title,
           author: '{$this->getAuthorMetrics($data->getAuthor())}',
           published_at: '{$data->getPublishedAt()->format('Y-m-d H:i')}',
           categories: ['{$data->getCategoryId()->getTitle()}', '{$data->getCategoryId()->getSlug()}'],
           platform: 'InstantArticles',
        });   
        </script>");
    }

    private function getFacebookTrackerApp(){
        return Analytics::create()->withHTML("<script>
        window.fbAsyncInit = function() {
            FB.init({
              appId      : '1063568587023463',
              xfbml      : true,
              version    : 'v2.10'
            });
            FB.AppEvents.logPageView();
          };
          (function(d, s, id){
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) {return;}
            js = d.createElement(s); js.id = id;
            js.src = 'https://connect.facebook.net/en_US/sdk.js';
            fjs.parentNode.insertBefore(js, fjs);
          }(document, 'script', 'facebook-jssdk'));
        </script>");
    }

    private function getContentITracker($slug)
    {
        return Analytics::create()
            ->withHTML(
                '<script type="text/javascript">' .
                ' /* CONFIGURATION START */' .
                'var _ain = {' .
                'referrer: "http://ia.facebook.com", ' .
                '// this must be exactly like this on all request id: "1819", ' .
                '// same domain ID as on tracking code on desktop version url: "https://www.elfinanciero.com.mx/' . $slug . '.html",' .
                '// URL of the article postid: "https://www.elfinanciero.com.mx/' . $slug . '.html", ' .
                '// must match the post id from the desktop version of the article }; ' .
                '/* CONFIGURATION END */ ' .
                "(function (d, s) { var sf = d.createElement(s); sf.type = 'text/javascript'; sf.async = true; sf.src = (('https:' == d.location.protocol) ? 'https://d7d3cf2e81d293050033-3dfc0615b0fd7b49143049256703bfce.ssl.cf1.rackcdn.com' : 'http://t.contentinsights.com') + '/stf.js'; var t = d.getElementsByTagName(s)[0]; t.parentNode.insertBefore(sf, t); })(document, 'script');"
            );
    }

    private function getGoogleATracker($slug, $title, $raw_authors, $section)
    {
        //Se quita para poner por TagManager return Analytics::create()->withHTML("<script> (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){ (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o), m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m) })(window,document,'script','https://www.google-analytics.com/analytics.js','ga'); ga('create', 'UA-112838768-1', 'auto'); ga('send', 'pageview',{ 'page' : '" . $slug . ".html?utm_source=Facebook_ia&utm_medium=Social', 'title' : (function() { return '" . $title . "'.replace(/&#(\d+);/g, function(match, dec) { return String.fromCharCode(dec); }); })() }, 'seccion': '" . $section . "', 'author' : '" . $raw_authors . "', 'tipo_social':'facebook_ia'); </script>");
        return Analytics::create()->withHTML("<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','GTM-PQSN8R3');</script>");
    }

    private function getComscoreTracker($slug, $date, $raw_authors, $section)
    {
        return Analytics::create()
            /*->withHTML('<script type="text/javascript">// function udm_(e) { var t = "comScore=", n = document, r = n.cookie, i = "", s = "indexOf", o = "substring", u = "length", a = 2048, f, l = "&ns_", c = "&", h, p, d, v, m = window, g = m.encodeURIComponent || escape; if (r[s](t) + 1) for (d = 0, p = r.split(";"), v = p[u]; d < v; d++) h = p[d][s](t), h + 1 && (i = c + unescape(p[d][o](h + t[u]))); e += l + "_t=" + +(new Date) + l + "c=" + (n.characterSet || n.defaultCharset || "") + "&c8=" + g(n.title) + i + "&c7=" + g(n.URL) + "&c9=" + g(n.referrer), e[u] > a && e[s](c) > 0 && (f = e[o](0, a - 8).lastIndexOf(c), e = (e[o](0, f) + l + "cut=" + g(e[o](f + 1)))[o](0, a)), n.images ? (h = new Image, m.ns_p || (ns_p = h), h.src = e) : n.write("<", "p", "><"' .
                    "," . " 'img src=" . '"' . "', e, '" . '" height="1" width="1" alt="*"' . "'" . ', "><", "/p", ">") };' .
                    "udm_('http://b.scorecardresearch.com/b?c1=2&c2=10181342&ns_site=elfinanciero&comscorekw=fbia&tipo_social=facebook_ia&autor=" . $raw_authors . "&name=" . $slug . "&fecha_pub=" . $date . "&seccion=" . $section . "');" .
                    ' </script> <noscript><p><img src="http://b.scorecardresearch.com/p?c1=2&amp;c2=10181342&amp;ns_site=elfinanciero&amp;comscorekw=fbia&amp;tipo_social=facebook_ia&amp;autor=' . $raw_authors . '&amp;name=' . $slug . '&amp;fecha_pub=' . $date . '$section=' . $section . '" height="1" width="1" alt="*"></p></noscript> <script type="text/javascript" src="http://b.scorecardresearch.com/c2/10181342/ct.js"></script>'
            );*/
            ->withHTML('<script>
                var _comscore = _comscore || []; 
                _comscore.push({ c1: "2", c2: "10181342", ns_site: "elfinanciero", name: "' . $slug . '", tipo_social: "facebook_ia", autor: "' . $raw_authors . '", fecha_pub: "' . $date . '", seccion: "' . $section . '", options: { url_append: "comscorekw=fbia" } }); 
                (function() {
                  var s = document.createElement("script"), el = document.getElementsByTagName("script")[0]; s.async = true;
                  s.src = (document.location.protocol == "https:" ? "https://sb" : "http://b") + ".scorecardresearch.com/beacon.js";
                  el.parentNode.insertBefore(s, el);
                })();
                </script>
                <noscript>
                    <img src="http://b.scorecardresearch.com/p?c1=2&amp;c2=10181342&amp;ns_site=elfinanciero&amp;comscorekw=fbia&amp;tipo_social=facebook_ia&amp;autor=' . $raw_authors . '&amp;name=' . $slug . '&amp;fecha_pub=' . $date . '&section=' . $section . '" />
                </noscript>');
    }

    private function getTaboola()
    {
        return Analytics::create()->withHTML("<script type='text/javascript'>
            window._taboola = window._taboola || [];
            _taboola.push({
                article: 'auto',
                ref_url: 'http://instantarticles.fb.com'
            });
            !function (e, f, u, i) {
                if (!document.getElementById(i)) {
                    e.async = 1;
                    e.src = u;
                    e.id = i;
                    f.parentNode.insertBefore(e, f);
                }
            }(document.createElement('script'),
                document.getElementsByTagName('script')[0],
                'https://cdn.taboola.com/libtrc/elfinanciero/trk.js',
                'tb_loader_script');
            if (window.performance && typeof window.performance.mark == 'function') {
                window.performance.mark('tbl_ic');
            }
        </script>");
    }

    private function getAuthorMetrics($authors)
    {
        $authors_string = '';
        foreach ($authors as $key => $author) {
            $authors_string .= $author->getName() . ' ' . $author->getAPaterno();
            if ($key != (count($authors) - 1)) {
                $authors_string .= ' | ';
            }
        }
        return $authors_string;
    }

    private function makeFooterIA($instant_article)
    {
        return $instant_article->withFooter(Footer::create()
            ->withCredits('El Financiero Bloomberg ' . date('Y') . ' (c)'));
    }
}
