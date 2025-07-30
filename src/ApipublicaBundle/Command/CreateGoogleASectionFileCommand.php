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
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class CreateGoogleASectionFileCommand extends ContainerAwareCommand {

    private $analytics;
    private $profile;
    private $results;

    protected function configure() {
        $this
                ->setName('app:create-file-ga-section')
                ->setDescription('Create a json file from google analytics data per section')
                ->setHelp('This command allows create json file from google analytics data per section');
        // ...
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $analytics = $this->initializeAnalytics();
        //Get profile data from Google API
        //$profile = $this->getFirstProfileId($analytics);
        $profile = 169193601;
        //Get sections from DB
        $em = $this->getContainer()->get('doctrine')->getManager();
        $query = $em->getRepository("BackendBundle:Category")->createQueryBuilder('c')
                        ->where("c.active = 1")->getQuery();

        $sections = $query->getResult();

        /*foreach ($sections as $section) {
            //

            if( $data_ga = fopen(__DIR__ . '/../Resources/config/google/analytics/' . $section->getSlug() . '.json', 'w') )
            {
                fwrite($data_ga, $section->getSlug());
                fclose($data_ga);
                var_dump("1");
                $output->writeln('File created with success ' . $section->getSlug());
                chmod(__DIR__ . '/../Resources/config/google/analytics/' . $section->getSlug() . '.json',0646);
            }
            else{
                $output->writeln("Unable to open file: ". __DIR__ . '/../Resources/config/google/analytics/' . $section->getSlug() . '.json');
                var_dump("0");
            }
        }*/

        foreach ($sections as $section) {
            //Get results from obtained data
            $results = $this->getResults($analytics, $profile, $section->getSlug()); //getTitle

            if(count($results['rows'])>10) {

                //Return processed data
               if( $data_ga = fopen(__DIR__ . '/../Resources/config/google/analytics/' . $section->getSlug() . '.json', 'w') )
               {
                   fwrite($data_ga, json_encode($results['rows']));
                   fclose($data_ga);
                   $output->writeln('File created with success ' . $section->getSlug());
                   chmod(__DIR__ . '/../Resources/config/google/analytics/' . $section->getSlug() . '.json',0646);
               }
               else{
                   $output->writeln("Unable to open file: ". __DIR__ . '/../Resources/config/google/analytics/' . $section->getSlug() . '.json');
               }

            }else{
                $output->writeln('File ' . $section->getSlug() . ' not re-created, from GA' . print_r($results['rows']));
            }
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

    private function getResults($analytics, $profileId, $section) {
        // Calls the Core Reporting API and queries for the number of sessions pageviews uniquePageviews
        // for the last seven days.
        //el usuario comenta que se interesa como pageviews

        $optParams = array(
            'dimensions' => 'ga:pageTitle,ga:pagePath',
            'sort' => '-ga:pageviews',
            //Filter for no categories urls and urls with html ending
            //'filters' => 'ga:pagePath%3D@html;ga:pagePath%3D@/'.$section."/",
            'filters' => 'ga:pagePath%3D@/'.$section."/",
            'max-results' => '50');

        $from = date('Y-m-d', time()-1*86400);
        $to = date('Y-m-d');

        return $analytics->data_ga->get(
                        'ga:' . $profileId, $from, $to, 'ga:pageviews', $optParams);
    }

}
