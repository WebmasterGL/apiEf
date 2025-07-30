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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Elastica\Query;
class CreateGoogleARTFileCommand extends Command {

    private $analytics;
    private $profile;
    private $results;

    protected function configure() {
        $this
                ->setName('app:create-file-ga')
                ->setDescription('Create a json file from google analytics data')
                ->setHelp('This command allows create json file from google analytics data');
        // ...
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        try {
            $analytics = $this->initializeAnalytics();
            //Get profile data from Google API
            //$profile = $this->getFirstProfileId($analytics);
            $profile = 169193601;
            //Get results from obtained data
            $results = $this->getResults($analytics, $profile);
            //Return processed data
            $data_ga = fopen(__DIR__.'/../Resources/config/google/analytics/pre_data-rt.json', 'w') or
                    die("Unable to open file pre_data-rt.json");
            fwrite($data_ga, json_encode($results['rows']));
            fclose($data_ga);
            $output->writeln('Pre-File created with sucess');
           

            //Obteniendo informacion de Elastik
            $this->getDatafromES();

            $output->writeln('File created with sucess, data-rt.json from ES');

        } catch (\Exception $e) {
            $output->writeln($e->getTraceAsString());
        } 
    }

    private function getDatafromES(){

        $data_from_ga = file_get_contents(__DIR__.'/../Resources/config/google/analytics/pre_data-rt.json');
        $nuevo_array = json_decode($data_from_ga);
        $i=0;
        foreach($nuevo_array  as $elemento){
        
            $article = $this->searchUrl($elemento[1]);


            if(count($article)==1){
            
                array_push($nuevo_array[$i], $article->getpageType());
                array_push($nuevo_array[$i],

                    array("page_id" => $article->getId(),
                    "title" => $article->getTitle(),
                    "slug" => $article->getSlug(),
                    "seo_image" => $article->getMainImage()->getImagePath(),
                    "main_image" => $this->getMainImage($article),
                    "bullet" => ($article->getBullets()) ? $article->getBullets()[0] : NULL,
                    "hits" => $elemento[2],
                    "kicker" => strtoupper($article->getCategoryId()->getTitle()),
                    "kickerSlug" => $article->getCategoryId()->getSlug(),
                    "publishedAt" => $article->getPublishedAt()) );
                
            }
            else{
                array_push($nuevo_array[$i], "ERR");
            }

            $i++;

        }
        $data_ga = fopen(__DIR__.'/../Resources/config/google/analytics/data-rt.json', 'w') or
                    die("Unable to open file data-rt.json");
            fwrite($data_ga, json_encode($nuevo_array));
            fclose($data_ga);

            

    }

    private function getMainImage($article) {
        $json = json_decode($article->getElementHtmlSerialized(), true);
        if ($json['type'] == 'image' && $json['layout'] != "no-display") {
            return $json['data']['imagePath'];
        } else {
            return $article->getMainImage()->getImagePath();
        }
    }

    private function searchUrl($p_url){
        $finder          = $this->getApplication()->getKernel()->getContainer()->get('fos_elastica.finder.efredisenio.page');
        $boolQuery = new Query\Bool();
        $statusQuery = new Query\Match();
        $statusQuery->setFieldQuery('status', 'published');

        $searchQuery = new Query\Match();
        $searchQuery->setFieldQuery('slug', $p_url); 

        $portalQuery = new Query\Match();
        $portalQuery->setFieldQuery('portalId', 3);

        $mostViewedQuery = new Query\Match();
        $mostViewedQuery->setFieldQuery('mostViewed', true);
    

        
        $boolQuery->addMust($statusQuery);
        $boolQuery->addMust($searchQuery);
        $boolQuery->addMust($portalQuery);
        $boolQuery->addMust($mostViewedQuery);

        $results = $finder->find($boolQuery,1);
        $article ="";
        foreach ($results as $result) {
            $article=$result;
        }

        if ($article && "/".$article->getSlug()==$p_url) {
             
            return $article;
        }
        else{
             
            return array();
        }

    }

    private function initializeAnalytics() {
        //Load autoload for dependencies
        require_once __DIR__ . '/../../../vendor/autoload.php';
        // Creates and returns the Analytics Reporting service object.
        // Use the developers console and download your service account
        // credentials in JSON format. Place them in this directory or
        // change the key file location if necessary.
        $KEY_FILE_LOCATION = __DIR__ . '/../Resources/config/google/my-project-f6ccae1ef113.json';
        // Create and configure a new client object.
        $client = new \Google_Client();
        $client->setApplicationName("Analytics Reporting");
        $client->setAuthConfig($KEY_FILE_LOCATION);
        $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
        $analytics = new \Google_Service_Analytics($client);
        return $analytics;
    }

    private function getFirstProfileId($analytics) {
        // Get the user's first view (profile) ID.
        // Get the list of accounts for the authorized user.
        $accounts = $analytics->management_accounts->listManagementAccounts();


        if (count($accounts->getItems()) > 0) {
            $items = $accounts->getItems();
            $firstAccountId = $items[0]->getId();

            // Get the list of properties for the authorized user.
            $properties = $analytics->management_webproperties
                    ->listManagementWebproperties($firstAccountId);

            if (count($properties->getItems()) > 0) {
                $items = $properties->getItems();
                $firstPropertyId = $items[0]->getId();

                // Get the list of views (profiles) for the authorized user.
                $profiles = $analytics->management_profiles
                        ->listManagementProfiles($firstAccountId, $firstPropertyId);

                if (count($profiles->getItems()) > 0) {
                    $items = $profiles->getItems();

                    // Return the first view (profile) ID.
                    return $items[0]->getId();
                } else {
                    die('No views (profiles) found for this user.');
                    throw new Exception('No views (profiles) found for this user.');
                }
            } else {
                die('No properties found for this user.');
                throw new Exception('No properties found for this user.');
            }
        } else {
            die('No accounts found for this user.');
            throw new Exception('No accounts found for this user.');
        }
    }

    private function getResults($analytics, $profileId) {
        // Calls the Core Reporting API and queries for the number of sessions pageviews uniquePageviews
        // for the last seven days.
        //el usuario comenta que se interesa como pageviews


        $optParams = array(
            'dimensions' => 'rt:pageTitle,rt:pagePath',
            'sort' => '-rt:pageviews',
            //'filters' => 'rt:pagePath%3D@.html',
            'filters' => 'ga:pagePath!=/',
            'max-results' => '50');

        return $analytics->data_realtime->get(
                        'ga:' . $profileId, 'rt:pageviews', $optParams);
    }

}
