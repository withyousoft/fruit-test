<?php
namespace App\Command;

use App\Entity\Fruit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// the name of the command is what users type after "php bin/console"
#[AsCommand(
    name: 'fruits:fetch',
    description: 'Fetch fruits from API server.',
    hidden: false,
    // aliases: ['fruits:fetch']
)]
class FetchFruitsCommand extends Command
{

    public function __construct(
        private EntityManagerInterface $entityManager,
    ){
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            // the command help shown when running the command with the "--help" option
            ->setHelp('This command allows you to fetch fruits from API server and store the response data into DB...')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $url = $_ENV['FRUIT_API_URL'];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
    
        $fruits = json_decode($response, true);

        foreach ($fruits as $fruit) {
            $fruitEntity = new Fruit();
            $fruitEntity->setName($fruit['name']);
            $fruitEntity->setGenus($fruit['genus']);
            $fruitEntity->setFamily($fruit['family']);
            $fruitEntity->setForder(($fruit['order']));
            $fruitEntity->setCalories(($fruit['nutritions']['calories']));
            $fruitEntity->setCarbohydrates(($fruit['nutritions']['carbohydrates']));
            $fruitEntity->setFat(($fruit['nutritions']['fat']));
            $fruitEntity->setProtein(($fruit['nutritions']['protein']));
            $fruitEntity->setSugar(($fruit['nutritions']['sugar']));
    
            $this->entityManager->persist($fruitEntity);
        }
    
        $this->entityManager->flush();
    
        $output->writeln('All fruits saved into local DB');


        return Command::SUCCESS;
    }
}