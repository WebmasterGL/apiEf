<?php

namespace ApipublicaBundle\Command;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CreateGoogleAFile
 *
 * @author victor.nombret
 */
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use DateTime;

class CreateSitemapLastMothCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('app:create-sitemap')
                ->setDescription('Create an xml file sitemap from the specific month')
                ->setHelp('This command allows to create sitemap from the specific month'
                        . 'with one argument to send, '
                        . 'if this argument is "last" make from the last month based on current month'
                        . 'or send a month and year in format MM-YYYY')
                ->addArgument('date', InputArgument::REQUIRED, 'Put Last or put date to make a specific sitemap');

        // ...
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $fechas = [];

        $date = $input->getArgument('date');
        if ($date == "last" || $this->validateDate($date)) {
            if ($date == "last") {
                $date = date("Y-m", strtotime("last day of previous month"));
                $date_2 = date("Y-m", strtotime("last day of -2 month"));
                $date_3 = date("Y-m", strtotime("last day of -1 year"));

                array_push($fechas, $date,$date_2,$date_3);


                $this->getContainer()->get('app.helpers')->logActivity("sitemap@crontab.com", "last month: " . $date );
            }
            elseif ($this->validateDate($date)){

                array_push($fechas, $date);
            }
            try {
                foreach($fechas as $fecha){
                    $this->callingSitemap($fecha, $output);
                }


            } catch (\Exception $e) {
                $output->writeln($e->getTraceAsString());
            }
        } else {
            $output->writeln('Invalid date');
        }
    }

    private function callingSitemap($date, $output){
        $data_sitemap = fopen(__DIR__ . '/../../../web/sitemap/articles-' . $date . '.xml', 'w') or die("Unable to open file");
        $notes = $this->getNotesFromLastMonth($date);
        fwrite($data_sitemap, $notes );
        fclose($data_sitemap);
        $output->writeln('Success, Sitemap created:'.$date);
    }

    //Check argument in format MM-YYYY
    private function validateDate($date, $format = 'Y-m') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    private function getNotesFromLastMonth($date) {

        $em = $this->getContainer()->get('doctrine')->getManager();

        $d2 = new DateTime();//Fecha actual


        $month = explode("-", $date)[0];
        $year = explode("-", $date)[1];
        $query = $em->createQuery("SELECT p FROM BackendBundle:Page p "
                . "WHERE p.publishedAt >= '" . $date . "-01 00:00:00'" .
                " AND p.publishedAt <= '" . $date . "-31 23:59:59'" .
                " AND p.status='published'"
        );

        $notes_from_date = $query->getResult();
        $data_notes = array();


        foreach ($notes_from_date as $note) {
            //Add elements to item sitemap

            $d1 = $note->getPublishedAt();


            $ts1 = strtotime(date_format($d1,'Y/n/d'));
            $ts2 = strtotime(date_format($d2,'Y/n/d'));
            $year1 = date('Y', $ts1);
            $year2 = date('Y', $ts2);
            $month1 = date('m', $ts1);
            $month2 = date('m', $ts2);
            $diff = (($year2 - $year1) * 12) + ($month2 - $month1);

            $data_notes[] = array(
                'loc' => $note->getSlug(),
                'lastmod' => date_format($note->getPublishedAt(), 'c'),
                'changefreq' => $diff<=1?'Weekly':($diff>=2&&$diff<=11?'Monthly':'Yearly'),
                'priority' => $diff<=1?'0.6':($diff>=2&&$diff<=11?'0.4' :'0.3')
            );
        }
        $host_ef = $this->getContainer()->getParameter('host_uri');
        $this->getContainer()->get('app.helpers')->logActivity("sitemap@crontab.com", "Conteo: " . count( $data_notes ) );

        return $this->getContainer()->get('templating')->render('@Apipublica/Sitemap/subsitemap.xml.twig',array(
            'urls'     => $data_notes,
            'hostname' => $host_ef
            )
        );
    }

}
