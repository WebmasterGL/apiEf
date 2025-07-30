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
use BackendBundle\Controller\APIController;
use Symfony\Component\HttpFoundation\Request;

use Elastica\Query;

class BorrarPortadasCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('api:delete:portadas')
            ->setDescription('Borrar portadas, 0:Imprime cuantas , 1:Imprime cuales , 2:Borra')
            ->addArgument('diasRespetar',InputArgument::REQUIRED, 'Dias sin borrar')
            ->addArgument('task', InputArgument::REQUIRED, '0:Imprime cuantas , 1:Imprime cuales , 2:Borra');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $request = Request::createFromGlobals();


        $controller = new APIController();

        $dias = $input->getArgument('diasRespetar');
        $task = $input->getArgument('task');

        $em = $this->getContainer()->get('doctrine')->getManager();
        $helpers = $this->getContainer()->get('app.helpers');
        $minDiasVigentesPortadas = $this->getContainer()->getParameter('minDiasVigentesPortadas');

        $resultado = $controller->cleanFoldsAction(2,$dias,$task, $em, $helpers, $minDiasVigentesPortadas);
    }

}
