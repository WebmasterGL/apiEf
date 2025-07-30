<?php
/**
 * Created by PhpStorm.
 * User: jmorquecho
 * Date: 17/07/17
 * Time: 11:35 AM
 */

namespace BackendBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Elastica\Query;

class PublicarNotasProgramadasCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('api:publish:pages:scheduled')
            ->setDescription('Publicar las notas y portadas que fueron Calendarizadas para su publicación, 0:Print-Past , 1:Print-Future , 2:Execute-Past, 3:Execute-Page-Version, 4:Print-Past-Page-Version, 5:Print-Future-Page-Version')
            ->addArgument('task', InputArgument::REQUIRED, 'La tarea a Realizar, 0:Print-Past , 1:Print-Future , 2:Execute-Past, 3:Execute-Page-Version, 4:Print-Past-Page-Version, 5:Print-Future-Page-Version');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->notas($input, $output);
        $this->folds($input, $output);
        $this->portada($input, $output);
    }

    private function portada(InputInterface $input, OutputInterface $output)
    {

        $tiempo = date('Y-m-d' ) . ' ' . date('H:i:s' );

        $em = $this->getContainer()->get('doctrine')->getManager();

        $helpers = $this->getContainer()->get("app.helpers");

        if ($input->getArgument('task') == 0 || $input->getArgument('task') == 2) {
            $query = $em->getRepository("BackendBundle:Portada")->createQueryBuilder('p')//tambien se tarda
            ->where("p.status = 'scheduled'")
                ->andWhere('p.nextPublishedAt is not NULL')
                ->andWhere('p.nextPublishedAt <= :p_tiempo') //CURRENT_TIMESTAMP()
                ->setParameter('p_tiempo', $tiempo)
                ->orderBy('p.nextPublishedAt', 'ASC')
                ->getQuery();
        } else if ($input->getArgument('task') == 1) {
            $query = $em->getRepository("BackendBundle:Portada")->createQueryBuilder('p')//tambien se tarda
            ->where("p.status = 'scheduled'")
                ->andWhere('p.nextPublishedAt is not NULL')
                ->andWhere('p.nextPublishedAt > :p_tiempo')
                ->setParameter('p_tiempo', $tiempo)
                ->orderBy('p.nextPublishedAt', 'DESC')
                ->getQuery();
        }

        if ($input->getArgument('task') <= 2) {

            $portadas = $query->getResult();
            $cuantos = count($portadas);
            $output->writeln("Portadas:" . $cuantos);

            foreach ($portadas as $portada) {
                $output->writeln($portada->getNextPublishedAt()->format('Y-m-d H:i:s') . " <=> " . $portada->getId());
            }
            $lastCoverId      = 0;
            $covers_published = array();
            if ($input->getArgument('task') == 2) {
                foreach ($portadas as $portada) {

                    $query = $em->getRepository("BackendBundle:Portada")->createQueryBuilder('p')//tambien se tarda
                    ->where("p.status = 'published'")
                        ->andWhere('p.category =' . $portada->getCategory()->getId())
                        ->getQuery();


                    $portadas_off = $query->getResult();

                    foreach ($portadas_off as $portada_off) {
                        $output->writeln($portada_off->getId());
                        $portada_off->setStatus("default");
                        $em->persist($portada_off);
                    }
                    $em->flush();


                    $output->writeln("Actualizando Portada...");
                    $portada->setNextPublishedAt(NULL);
                    $portada->setPublishedAt(new \DateTime());
                    $portada->setStatus("published");
                    $lastCoverId = $portada->getId();
                    $em->persist($portada);

                    $res = $helpers->Purga($portada->getCategory()->getSlug());
                    if ($portada->getCategory()->getSlug() == "cartones") {
                        $res = $helpers->Purga("opinion");
                    }

                    //Call Top News Helpers, only if portada is Home
                    if($portada->getCategory()->getId() == 1){
                        $data_topnews = $helpers->topNews($portada->getId());
                        $helpers->logActivity("topsnews@crontab.com", "notas recibidas: " . $data_topnews['conteo'] . "---" . "ID Portada: " . $data_topnews['idPortada']);
                    }
                    elseif ($portada->getCategory()->getId() == 81){

                        $covers_published[] = $portada->getCategory()->getSlug();

                        $ipApiPrivada = $this->getContainer()->getParameter('ip_domain');

                        $querytv = $em->getRepository("BackendBundle:Category")->createQueryBuilder('c')//tambien se tarda
                        ->where("c.parentId = 81")
                            ->getQuery();

                        $categoriesTv = $querytv->getResult();

                        foreach ($categoriesTv as $categoryTv) {

                            $resultadobusqueda=$this->getData($ipApiPrivada. "/api/search/typed/?_format=json&json={\"search\":\"*\",\"categoryId\":". $categoryTv->getId() ."}&type=page&subtype=tv&page=1&size=13&public=true");
                            $this->_cache("pagetv{'search':'*','categoryId':". $categoryTv->getId()  ."}113true0", $resultadobusqueda);

                        }



                        /*$resultadobusqueda=$this->getData($ipApiPrivada. "/api/search/typed/?_format=json&json={\"search\":\"*\",\"categoryId\":83}&type=page&subtype=tv&page=1&size=13&public=false");
                        $this->_cache("pagetv{'search':'*','categoryId':83}113false0", $resultadobusqueda);

                        $resultadobusqueda=$this->getData($ipApiPrivada. "/api/search/typed/?_format=json&json={\"search\":\"*\",\"categoryId\":84}&type=page&subtype=tv&page=1&size=13&public=false");
                        $this->_cache("pagetv{'search':'*','categoryId':84}113false0", $resultadobusqueda);

                        $resultadobusqueda=$this->getData($ipApiPrivada. "/api/search/typed/?_format=json&json={\"search\":\"*\",\"categoryId\":85}&type=page&subtype=tv&page=1&size=13&public=false");
                        $this->_cache("pagetv{'search':'*','categoryId':85}113false0", $resultadobusqueda);

                        $resultadobusqueda=$this->getData($ipApiPrivada. "/api/search/typed/?_format=json&json={\"search\":\"*\",\"categoryId\":86}&type=page&subtype=tv&page=1&size=13&public=false");
                        $this->_cache("pagetv{'search':'*','categoryId':86}113false0", $resultadobusqueda);

                        $resultadobusqueda=$this->getData($ipApiPrivada. "/api/search/typed/?_format=json&json={\"search\":\"*\",\"categoryId\":87&type=page&subtype=tv&page=1&size=13&public=false");
                        $this->_cache("pagetv{'search':'*','categoryId':87}113false0", $resultadobusqueda);

                        $resultadobusqueda=$this->getData($ipApiPrivada. "/api/search/typed/?_format=json&json={\"search\":\"*\",\"categoryId\":88}&type=page&subtype=tv&page=1&size=13&public=false");
                        $this->_cache("pagetv{'search':'*','categoryId':88}113false0", $resultadobusqueda);

                        $resultadobusqueda=$this->getData($ipApiPrivada. "/api/search/typed/?_format=json&json={\"search\":\"*\",\"categoryId\":89}&type=page&subtype=tv&page=1&size=13&public=false");
                        $this->_cache("pagetv{'search':'*','categoryId':89}113false0", $resultadobusqueda);

                        $resultadobusqueda=$this->getData($ipApiPrivada. "/api/search/typed/?_format=json&json={\"search\":\"*\",\"categoryId\":90}&type=page&subtype=tv&page=1&size=13&public=false");
                        $this->_cache("pagetv{'search':'*','categoryId':90}113false0", $resultadobusqueda);

                        $resultadobusqueda=$this->getData($ipApiPrivada. "/api/search/typed/?_format=json&json={\"search\":\"*\",\"categoryId\":91}&type=page&subtype=tv&page=1&size=13&public=false");
                        $this->_cache("pagetv{'search':'*','categoryId':91}113false0", $resultadobusqueda);

                        $resultadobusqueda=$this->getData($ipApiPrivada. "/api/search/typed/?_format=json&json={\"search\":\"*\",\"categoryId\":92}&type=page&subtype=tv&page=1&size=13&public=false");
                        $this->_cache("pagetv{'search':'*','categoryId':92}113false0", $resultadobusqueda);

                        $resultadobusqueda=$this->getData($ipApiPrivada. "/api/search/typed/?_format=json&json={\"search\":\"*\",\"categoryId\":93}&type=page&subtype=tv&page=1&size=13&public=false");
                        $this->_cache("pagetv{'search':'*','categoryId':93}113false0", $resultadobusqueda);

                        $resultadobusqueda=$this->getData($ipApiPrivada. "/api/search/typed/?_format=json&json={\"search\":\"*\",\"categoryId\":94}&type=page&subtype=tv&page=1&size=13&public=false");
                        $this->_cache("pagetv{'search':'*','categoryId':94}113false0", $resultadobusqueda);

                        $resultadobusqueda=$this->getData($ipApiPrivada. "/api/search/typed/?_format=json&json={\"search\":\"*\",\"categoryId\":95}&type=page&subtype=tv&page=1&size=13&public=false");
                        $this->_cache("pagetv{'search':'*','categoryId':95}113false0", $resultadobusqueda);

                        $resultadobusqueda=$this->getData($ipApiPrivada. "/api/search/typed/?_format=json&json={\"search\":\"*\",\"categoryId\":96}&type=page&subtype=tv&page=1&size=13&public=false");
                        $this->_cache("pagetv{'search':'*','categoryId':96}113false0", $resultadobusqueda);

                        $resultadobusqueda=$this->getData($ipApiPrivada. "/api/search/typed/?_format=json&json={\"search\":\"*\",\"categoryId\":97}&type=page&subtype=tv&page=1&size=13&public=false");
                        $this->_cache("pagetv{'search':'*','categoryId':97}113false0", $resultadobusqueda);

                        $resultadobusqueda=$this->getData($ipApiPrivada. "/api/search/typed/?_format=json&json={\"search\":\"*\",\"categoryId\":98}&type=page&subtype=tv&page=1&size=13&public=false");
                        $this->_cache("pagetv{'search':'*','categoryId':98}113false0", $resultadobusqueda);

                        $resultadobusqueda=$this->getData($ipApiPrivada. "/api/search/typed/?_format=json&json={\"search\":\"*\",\"categoryId\":99}&type=page&subtype=tv&page=1&size=13&public=false");
                        $this->_cache("pagetv{'search':'*','categoryId':99}113false0", $resultadobusqueda);

                        $resultadobusqueda=$this->getData($ipApiPrivada. "/api/search/typed/?_format=json&json={\"search\":\"*\",\"categoryId\":100}&type=page&subtype=tv&page=1&size=13&public=false");
                        $this->_cache("pagetv{'search':'*','categoryId':100}113false0", $resultadobusqueda);

                        $resultadobusqueda=$this->getData($ipApiPrivada. "/api/search/typed/?_format=json&json={\"search\":\"*\",\"categoryId\":101}&type=page&subtype=tv&page=1&size=13&public=false");
                        $this->_cache("pagetv{'search':'*','categoryId':101}113false0", $resultadobusqueda);

                        $resultadobusqueda=$this->getData($ipApiPrivada. "/api/search/typed/?_format=json&json={\"search\":\"*\",\"categoryId\":102}&type=page&subtype=tv&page=1&size=13&public=false");
                        $this->_cache("pagetv{'search':'*','categoryId':102}113false0", $resultadobusqueda);

                        $resultadobusqueda=$this->getData($ipApiPrivada. "/api/search/typed/?_format=json&json={\"search\":\"*\",\"categoryId\":103}&type=page&subtype=tv&page=1&size=13&public=false");
                        $this->_cache("pagetv{'search':'*','categoryId':103}113false0", $resultadobusqueda);

                        $resultadobusqueda=$this->getData($ipApiPrivada. "/api/search/typed/?_format=json&json={\"search\":\"*\",\"categoryId\":104}&type=page&subtype=tv&page=1&size=13&public=false");
                        $this->_cache("pagetv{'search':'*','categoryId':104}113false0", $resultadobusqueda);

                        $resultadobusqueda=$this->getData($ipApiPrivada. "/api/search/typed/?_format=json&json={\"search\":\"*\",\"categoryId\":105}&type=page&subtype=tv&page=1&size=13&public=false");
                        $this->_cache("pagetv{'search':'*','categoryId':105}113false0", $resultadobusqueda);

                        $resultadobusqueda=$this->getData($ipApiPrivada. "/api/search/typed/?_format=json&json={\"search\":\"*\",\"categoryId\":106}&type=page&subtype=tv&page=1&size=13&public=false");
                        $this->_cache("pagetv{'search':'*','categoryId':106}113false0", $resultadobusqueda);

                        $resultadobusqueda=$this->getData($ipApiPrivada. "/api/search/typed/?_format=json&json={\"search\":\"*\",\"categoryId\":107}&type=page&subtype=tv&page=1&size=13&public=false");
                        $this->_cache("pagetv{'search':'*','categoryId':107}113false0", $resultadobusqueda);

                        $resultadobusqueda=$this->getData($ipApiPrivada. "/api/search/typed/?_format=json&json={\"search\":\"*\",\"categoryId\":108}&type=page&subtype=tv&page=1&size=13&public=false");
                        $this->_cache("pagetv{'search':'*','categoryId':108}113false0", $resultadobusqueda);

                        $resultadobusqueda=$this->getData($ipApiPrivada. "/api/search/typed/?_format=json&json={\"search\":\"*\",\"categoryId\":109}&type=page&subtype=tv&page=1&size=13&public=false");
                        $this->_cache("pagetv{'search':'*','categoryId':109}113false0", $resultadobusqueda);

                        $resultadobusqueda=$this->getData($ipApiPrivada. "/api/search/typed/?_format=json&json={\"search\":\"*\",\"categoryId\":112}&type=page&subtype=tv&page=1&size=13&public=false");
                        $this->_cache("pagetv{'search':'*','categoryId':112}113false0", $resultadobusqueda);*/


                    }



                }
                $em->flush();
                foreach( $covers_published as $c ){
                    $res = $helpers->Purga( $c );
                    if ( $c == "cartones") {
                        $res = $helpers->Purga("opinion");
                    }
                }
            }
        }
    }

    private function notas(InputInterface $input, OutputInterface $output)
    {

        $tiempo = date('Y-m-d' ) . ' ' . date('H:i:s' );


        $em = $this->getContainer()->get('doctrine')->getManager();
        //Es la fecha que se mete como nula cuando se da de alta una nota, ya que el formulario de calendario no permitia nulos

        $helpers = $this->getContainer()->get("app.helpers");

        $datetime_null = \DateTime::createFromFormat("Y-m-d H:i:s", date(intval(date("Y")) - 5 . "-01-01 00:00:00"));

        if ($input->getArgument('task') == 0 || $input->getArgument('task') == 2) {
            $query = $em->getRepository("BackendBundle:Page")->createQueryBuilder('p')//tambien se tarda
            ->where("p.status = 'scheduled'")
                ->andWhere('p.portalId = 3')
                ->andWhere('p.nextPublishedAt is not NULL')
                ->andWhere('p.nextPublishedAt != :p_date')
                ->andWhere('p.nextPublishedAt <= :p_tiempo')
                ->setParameter('p_date', $datetime_null)
                ->setParameter('p_tiempo', $tiempo)
                ->orderBy('p.nextPublishedAt', 'DESC')
                ->getQuery();
        } else if ($input->getArgument('task') == 3 || $input->getArgument('task') == 4) {
            $query = $em->getRepository("BackendBundle:Page")->createQueryBuilder('p')//tambien se tarda
            ->where("p.status = 'published'")
                ->andWhere('p.portalId = 3')
                ->andWhere('p.nextPublishedAt is not NULL')
                ->andWhere('p.nextPublishedAt <= :p_tiempo')
                ->setParameter('p_tiempo', $tiempo)
                ->orderBy('p.nextPublishedAt', 'DESC')
                ->getQuery();
        } else if ($input->getArgument('task') == 1) {
            $query = $em->getRepository("BackendBundle:Page")->createQueryBuilder('p')//tambien se tarda
            ->where("p.status = 'scheduled'")
                ->andWhere('p.portalId = 3')
                ->andWhere('p.nextPublishedAt is not NULL')
                ->andWhere('p.nextPublishedAt != :p_date')
                ->andWhere('p.nextPublishedAt > :p_tiempo')
                ->setParameter('p_date', $datetime_null)
                ->setParameter('p_tiempo', $tiempo)
                ->orderBy('p.nextPublishedAt', 'ASC')
                ->getQuery();
        } else if ($input->getArgument('task') == 5) {
            $query = $em->getRepository("BackendBundle:Page")->createQueryBuilder('p')//tambien se tarda
            ->where("p.status = 'published'")
                ->andWhere('p.portalId = 3')
                ->andWhere('p.nextPublishedAt is not NULL')
                ->andWhere('p.nextPublishedAt > :p_tiempo')
                ->setParameter('p_tiempo', $tiempo)
                ->orderBy('p.nextPublishedAt', 'ASC')
                ->getQuery();
        }

        $notas = $query->getResult();
        $cuantos = count($notas);
        $output->writeln("Notas:" . $cuantos);
        if ($notas) {
            foreach ($notas as $nota) {
                $output->writeln($nota->getNextPublishedAt()->format('Y-m-d H:i:s') . " <=> " . $nota->getId() . " <=> " . $nota->getTitle());
            }
        }

        if ($input->getArgument('task') == 2) {
            foreach ($notas as $nota) {
                //Buscar la data de page_version, para actualizar page
                $idpage = $nota->getId();
                $latestPageVersion = $em->getRepository("BackendBundle:PageVersion")->latestVersion($idpage);
                $page_version = $em->getRepository("BackendBundle:PageVersion")->findOneBy(array(
                    'page' => $idpage,
                    'versionNo' => $latestPageVersion
                ));

                //Actualizar page con la data de page_version
                $output->writeln("Actualizando...");

                $helpers->logActivity("notas@crontab.com", "La nota original con el ID: " . $idpage . " Su ultima version es: " . $latestPageVersion);

                // --Update page
                // Cuando existe más de una page_version entonces...
                // normalmente se debe a que una nota_original fue creada,programada y despues desprogramada, para despues volver a ser clonada y programada
                if ($latestPageVersion > 1) {
                    $this->updatedPage($nota, $page_version, $flag = 'scheduled');
                } else {
                    $helpers->logActivity("notas@crontab.com", "Entro por aquí");

                    $nota->setNextPublishedAt(NULL);
                    $nota->setPublishedAt($page_version->getNextPublishedAtPage());
                    $nota->setUpdatedAt($page_version->getNextPublishedAtPage());
                    $nota->setPublisher($page_version->getEditingBy() != null ? $nota->getEditingBy() : null); //Aquí agarra el usuario que edito por ultima vez la nota
                    $nota->setStatus("published");
                    $em->persist($nota);

                    $res = $helpers->Purga($nota->getSlug());
                    $res = $helpers->Purga(strstr($nota->getSlug(), "/", true)); //purgando la portada de la seccion de la nota

                    //Publicar en page version
                    if ($page_version->getStatus() == "scheduled") {
                        $page_version->setStatus("published");
                        $page_version->setPublishedAtPage($page_version->getNextPublishedAtPage());
                        $page_version->setPublishedAt($page_version->getNextPublishedAtPage());
                        $page_version->setPublisher($page_version->getEditingBy() != null ? $nota->getEditingBy() : null ); //Aquí agarra el usuario que edito por ultima vez la nota
                        $em->persist($page_version);
                    }

                    if ($nota->getPageType() == 'tv') {

                        $ipApiPrivada = $this->getContainer()->getParameter('ip_domain');
                        $page_category = $nota->getCategoryId();
                        $category_id = $page_category->getId();

                        $resultadobusqueda=$this->getData($ipApiPrivada. "/api/search/typed/?_format=json&json={\"search\":\"*\",\"categoryId\":". $category_id ."}&type=page&subtype=tv&page=1&size=13&public=true");
                        $valorfile = $this->_cache("pagetv{'search':'*','categoryId':". $category_id ."}113true0", $resultadobusqueda);
                    }
                }

            }
            $em->flush();
        } elseif ($input->getArgument('task') == 3) {
            if ($notas) {
                $helpers->logActivity("crontab3@crontab.com", "encontro notas status publish: " . count($notas));
                foreach ($notas as $nota) {
                    //Buscar la data de page_version, para actualizar page
                    $idpage = $nota->getId();
                    $latestPageVersion = $em->getRepository("BackendBundle:PageVersion")->latestVersion($idpage);
                    $page_version = $em->getRepository("BackendBundle:PageVersion")->findOneBy(array(
                        'page' => $idpage,
                        'versionNo' => $latestPageVersion
                    ));

                    //Actualizar page con la data de page_version
                    $output->writeln("Actualizando...");

                    //Update page
                    $this->updatedPage($nota, $page_version, $flag = 'published');

                }
                $em->flush();
            }

        }
    }

    private function folds(InputInterface $input, OutputInterface $output)
    {
        $tiempo = date('Y-m-d' ) . ' ' . date('H:i:s' );

        $em = $this->getContainer()->get('doctrine')->getManager();

        $helpers = $this->getContainer()->get("app.helpers");

        //Es la fecha que se mete como nula cuando se da de alta una nota, ya que el formulario de calendario no permitia nulos
        $datetime_null = \DateTime::createFromFormat("Y-m-d H:i:s", date(intval(date("Y")) - 5 . "-01-01 00:00:00"));

        if ($input->getArgument('task') == 0 || $input->getArgument('task') == 2) {
            $query = $em->getRepository("BackendBundle:PortadaFolds")->createQueryBuilder('p')//tambien se tarda
            ->where("p.status = 'scheduled'")
                ->andWhere('p.nextPublishedAt is not NULL')
                ->andWhere('p.nextPublishedAt != :p_date')
                ->andWhere('p.nextPublishedAt <= :p_tiempo')
                ->setParameter('p_date', $datetime_null)
                ->setParameter('p_tiempo', $tiempo)
                ->orderBy('p.nextPublishedAt', 'DESC')
                ->getQuery();
        } else if ($input->getArgument('task') == 1) {
            $query = $em->getRepository("BackendBundle:PortadaFolds")->createQueryBuilder('p')//tambien se tarda
            ->where("p.status = 'scheduled'")
                ->andWhere('p.nextPublishedAt is not NULL')
                ->andWhere('p.nextPublishedAt != :p_date')
                ->andWhere('p.nextPublishedAt > :p_tiempo')
                ->setParameter('p_date', $datetime_null)
                ->setParameter('p_tiempo', $tiempo)
                ->orderBy('p.nextPublishedAt', 'ASC')
                ->getQuery();
        }

        if ($input->getArgument('task') <= 2) {

            $folds = $query->getResult();
            $cuantos = count($folds);
            $output->writeln("Folds:" . $cuantos);
            foreach ($folds as $fold) {
                $output->writeln($fold->getNextPublishedAt()->format('Y-m-d H:i:s') . " <=> " . $fold->getId());
            }

            if ($input->getArgument('task') == 2) {
                foreach ($folds as $fold) {

                    $cloneId = $fold->getCloneId();                                                                       //unpublish portadafolds cloned
                    $queryFold = $em->getRepository("BackendBundle:PortadaFolds")->createQueryBuilder('p')//tambien se tarda
                    ->where("p.id = :clone_id")
                        ->setParameter('clone_id', $cloneId)
                        ->getQuery();
                    $clones = $queryFold->getResult();
                    foreach ($clones as $clone) {
                        $clone->setStatus("trash");
                        $em->persist($clone);
                        $res=$helpers->Purga( "");
                    }

                    $output->writeln("Actualizando...");
                    $fold->setNextPublishedAt(NULL);
                    $fold->setPublishedAt(new \DateTime());
                    $fold->setStatus("published");
                    $em->persist($fold);


                    $res = $helpers->Purga($fold->getIdportada()->getCategory()->getSlug());


                    /*if($fold->getIdportada()->getCategory()->getSlug()==""){
                        $res = $helpers->Purga("/"); //alguna variante por si acaso faltara
                    }*/

                    //Call Top News Helpers, only if portada_category is Home
                    if($fold->getIdportada()->getCategory()->getId() == 1){
                        $data_topnews = $helpers->topNews($fold->getIdportada()->getId());
                        $helpers->logActivity("topsnews@crontab.com", "notas recibidas: " . $data_topnews['conteo'] . "---" . "ID Portada: " . $data_topnews['idPortada']);
                    }

                }
                $em->flush();
            }
        }
    }

    /**
     * Update Original Page from data to PageVersion
     * @param $nota, $page_version, $flag
     * @return true
     */
    private function updatedPage($nota, $page_version, $flag)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $helpers = $this->getContainer()->get("app.helpers");

        //Get fields many_to_many
        $many_to_many = $page_version->getFieldsManyToMany();

        //Loop and set new arrays data
        foreach ($many_to_many as $key => $indice) {
            if ($key == 'categories') {
                $categories_array = array();
                foreach ($indice as $value) {
                    array_push($categories_array, $value);
                }
            }
            if ($key == 'authors') {
                $authors_array = array();
                foreach ($indice as $value) {
                    array_push($authors_array, $value);
                }
            }
            if ($key == 'images') {
                $images_array = array();
                foreach ($indice as $value) {
                    array_push($images_array, $value);
                }
            }
            if ($key == 'tags') {
                $tags_array = array();
                foreach ($indice as $value) {
                    array_push($tags_array, $value);
                }
            }
        }

        //categories array to purga
        $categories_purga = array();
        $add_categories_purga = array();
        //tags array to purga
        $tags_purga = array();
        $add_tags_purga = array();

        //If data is new then remove and add data
        if ($categories_array != null) {
            $categories_db = $nota->getCategory();
            foreach ($categories_db as $val) {
                $nota->removeCategory($val);
                array_push($categories_purga, $val->getSlug());
            }

            foreach ($categories_array as $val) {
                $category = $em->getRepository('BackendBundle:Category')->find($val);
                $nota->addCategory($category);
                array_push($add_categories_purga, $category->getSlug());
            }
            //If data is null remove data
        } else {
            $categories_db = $nota->getCategory();
            foreach ($categories_db as $val) {
                $nota->removeCategory($val);
                array_push($categories_purga, $val->getSlug());
            }
        }
        if ($authors_array != null) {
            $authors_db = $nota->getAuthor();
            foreach ($authors_db as $val) {
                $nota->removeAuthor($val);
            }

            foreach ($authors_array as $val) {
                $author = $em->getRepository('BackendBundle:Author')->find($val);
                $nota->addAuthor($author);
            }
        } else {
            $authors_db = $nota->getAuthor();
            foreach ($authors_db as $val) {
                $nota->removeAuthor($val);
            }
        }
        if ($images_array != null) {
            $images_db = $nota->getImage();
            foreach ($images_db as $val) {
                $nota->removeImage($val);
            }

            foreach ($images_array as $val) {
                $image = $em->getRepository('BackendBundle:Image')->find($val);
                $nota->addImage($image);
            }
        } else {
            $images_db = $nota->getImage();
            foreach ($images_db as $val) {
                $nota->removeImage($val);
            }
        }
        if ($tags_array != null) {
            $tags_db = $nota->getTag();
            foreach ($tags_db as $val) {
                $nota->removeTag($val);
                array_push($tags_purga, $val->getSlug());
            }

            foreach ($tags_array as $val) {
                $tag = $em->getRepository('BackendBundle:Tag')->find($val);
                $nota->addTag($tag);
                array_push($add_tags_purga, $tag->getSlug());
            }
        } else {
            $tags_db = $nota->getTag();
            foreach ($tags_db as $val) {
                $nota->removeTag($val);
                array_push($tags_purga, $val->getSlug());
            }
        }

        $nota->setFlag($page_version->getFlag());
        $nota->setRelated($page_version->getRelated());
        $nota->setBullets($page_version->getBullets());
        $nota->setNewslatter($page_version->getNewslatter());
        $nota->setPlace($page_version->getPlace());
        $nota->setMostViewed($page_version->getMostViewed());
        $nota->setRss($page_version->getRss());
        $category = $em->getRepository('BackendBundle:Category')->find($page_version->getCategoryId());
        $nota->setCategoryId($category);
        $nota->setTitle($page_version->getTitle());
        $nota->setShortDescription($page_version->getShortDescription());
        $nota->setTemplate($page_version->getTemplate());
        $nota->setPageType($page_version->getPageType());
        $nota->setHtml($page_version->getHtml());
        $nota->setHtmlSerialize($page_version->getHtmlSerialize());
        $nota->setSettings($page_version->getSettings());
        $nota->setContent($page_version->getContent());
        $nota->setModules($page_version->getModules());
        $nota->setSeo($page_version->getSeo());
        $nota->setSocial($page_version->getSocial());
        $nota->setEditingBy($page_version->getEditingBy());
        $nota->setColumna($page_version->getColumna());
        $nota->setBlog($page_version->getBlog());
        $nota->setIsBreaking($page_version->getIsBreaking());
        $nota->setElementHtml($page_version->getElementHtml());
        $nota->setElementHtmlSerialized($page_version->getElementHtmlSerialized());
        $nota->setCode($page_version->getCode());
        $nota->setSlugRedirect($page_version->getSlugRedirect());
        $nota->setMainImage($page_version->getMainImage());
        $nota->setNextPublishedAt(NULL);
        //$nota->setPublisher($page_version->getPublisher());
        $nota->setPublisher($page_version->getEditingBy() != null ? $nota->getEditingBy() : null); //Aquí agarra el usuario que edito por ultima vez la nota

        // Si la bandera es 'published', signufica que la nota original esta publicada
        // entonces seteo el campo 'updated_at'de la nota priginal con el campo 'next_published_at' de la ultima page_version
        if($flag == 'published'){
            $nota->setUpdatedAt($page_version->getNextPublishedAt());
            // De lo contrario significa que la nota original esta como 'scheduled'
        }else{
            // Seteo la nota original en el campo 'published_at' con el valor, de la proxima publicacion de la ultima page_version
            $nota->setPublishedAt(( $page_version->getNextPublishedAtPage() != null ) ? $page_version->getNextPublishedAtPage() : new \DateTime());
            //si 'updated_at' de page_original es igual al 'updated_at_page' de page version, entonces...
            if($page_version->getUpdatedAtPage() == $nota->getUpdatedAt()){
                // Seteo en la nota_original 'updated_at', con el valor de la proxima fecha de publicacion de la ultima page_version
                $nota->setUpdatedAt($page_version->getNextPublishedAtPage());
            }
        }

        $nota->setStatus("published");
        $em->persist($nota);

        $res = $helpers->Purga($nota->getSlug());
        $res = $helpers->Purga(strstr($nota->getSlug(), "/", true)); //purgando la portada de la seccion de la nota

        //CACHE TV
        if ($nota->getPageType() == 'tv') {

            $ipApiPrivada = $this->getContainer()->getParameter('ip_domain');
            $page_category = $nota->getCategoryId();
            $category_id = $page_category->getId();

            $resultadobusqueda=$this->getData($ipApiPrivada. "/api/search/typed/?_format=json&json={\"search\":\"*\",\"categoryId\":". $category_id ."}&type=page&subtype=tv&page=1&size=13&public=true");
            $valorfile = $this->_cache("pagetv{'search':'*','categoryId':". $category_id ."}113true0", $resultadobusqueda);
        }

        //purga categories removed
        foreach ($categories_purga as $category_slug) {
            $res = $helpers->Purga($category_slug);
        }
        //purga categories added
        foreach ($add_categories_purga as $category_slug) {
            $res = $helpers->Purga($category_slug);
        }
        //purga tags removed
        foreach ($tags_purga as $tag_slug) {
            $res = $helpers->Purga("tag/" . $tag_slug);
        }
        //purga tags added
        foreach ($add_tags_purga as $tag_slug) {
            $res = $helpers->Purga("tag/" . $tag_slug);
        }

        //Publicar en page version
        if ($page_version->getStatus() == "scheduled") {
            //Mando a trash las nota(s) de page_version que esten en 'published'
            $pages_version = $em->getRepository('BackendBundle:PageVersion')->findBy(array('page' => $nota->getId()));
            foreach ($pages_version as $page_v) {
                if ($page_v->getStatus() == 'published') {
                    $page_v->setStatus('trash');
                    $em->persist($page_v);
                    $em->flush();
                }
            }
            $page_version->setStatus("published");
            $page_version->setPublishedAtPage(new \DateTime());
            $page_version->setPublishedAt(new \DateTime());
            $page_version->setPublisher($nota->getPublisher());
            $em->persist($page_version);
        }

        return true;
    }

    private function getData($url)
    {
        $jwt_auth = $this->getContainer()->get('app.jwt_auth');
        $bearer = $jwt_auth->signup("sadmin", "sadmin", true);

        $headers = ['Authorization: bearer ' . $bearer];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $content = curl_exec($ch);
        curl_close($ch);

        return $content;

    }

    private function _cache($url, $response)
    {
        $hash = md5(serialize($url));


        $rutacachetv = $this->getContainer()->getParameter('rutacachetv');

        //$response = json_encode($response);


        $file = $this->getContainer()->get('kernel')->getRootDir() . "/../" . $rutacachetv . '/' . $hash . '.cache';


        if (file_exists($file)) {
            unlink($file);
        }

        return file_put_contents($file, $response);

    }


}
