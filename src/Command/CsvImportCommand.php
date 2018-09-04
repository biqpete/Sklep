<?php
/**
 * Created by PhpStorm.
 * User: Peter
 * Date: 27/08/2018
 * Time: 09:25
 */

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use League\Csv\Statement;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


class CsvImportCommand extends Command
{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function configure()
    {
        $this
            ->setName('csv:import')
            ->setDescription('Imports a mock CSV file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input,$output);
        $io->title('Attempting to import the feed...');

//        $readerOrders = Reader::createFromPath('src/Data/shop.csv', 'r');
//        $readerOrders->setHeaderOffset(0);
//        $resultsOrders = $statement->process($readerOrders);

        $readerUsers = Reader::createFromPath('src/Data/shopUsers.csv', 'r');
        $readerUsers->setHeaderOffset(0);

        $statement = (new Statement())
            ->offset(0);

        $resultsUsers = $statement->process($readerUsers);

//        foreach ($results as $row)
//        {
//            print_r($row);
//        }

        foreach ($resultsUsers as $row){
            $user = (new User())
//                ->setFirstName('peter')
//                ->setSecondName('king')
//                ->setUsername('pedro')
//                ->setPassword('heyhoo')
//                ->setEmail('pedrini@pedrini.com')
//                ->setRoles(['ROLE_USER'])
//                ->setIsActive(true);
                ->setFirstName($row['first_name'])
                ->setSecondName($row['second_name'])
                ->setUsername($row['username'])
                ->setPassword($row['password'])
                ->setEmail($row['email'])
                ->setRoles([$row['roles']])
                ->setIsActive($row['is_active']);

//            $order = (new Order())
//////                ->setName($user->getUsername())
//////                ->setCpu("i3")
//////                ->setRam(8)
//////                ->setDrive(128)
//////                ->setScreen(15)
//////                ->setPrice(1600)
//////                ->setComment("hey")
//////                ->setIsSent(false)
//////                ->setDate(new \DateTime());
//                ->setName($row['name'])
//                ->setCpu($row['cpu'])
//                ->setRam($row['ram'])
//                ->setDrive($row['drive'])
//                ->setScreen($row['screen'])
//                ->setPrice($row['price'])
//                ->setComment($row['comment'])
//                ->setIsSent($row['is_sent'])
//                ->setDate(($row['date']));
//
//            $this->em->persist($order);
//            $order->setUser($user);
            $this->em->persist($user);

        }


        $this->em->flush();

        $io->success('Everything went well!');
    }
}