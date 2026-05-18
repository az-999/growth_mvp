<?php

namespace App\Command;

use App\DataFixtures\AppFixtures;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed',
    description: 'Загрузить первичные данные (магазины, пользователи, тестовые заказы)',
)]
class SeedInitialDataCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AppFixtures $fixtures,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_NONE,
            'Удалить существующие данные и загрузить заново',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $force = (bool) $input->getOption('force');

        $shopCount = (int) $this->em->createQuery('SELECT COUNT(s.id) FROM App\Entity\Shop s')->getSingleScalarResult();

        if ($shopCount > 0 && !$force) {
            $io->warning('В БД уже есть данные. Для полной перезагрузки: php bin/console app:seed --force');

            return Command::SUCCESS;
        }

        $executor = new ORMExecutor($this->em);
        if ($force) {
            $io->note('Очистка таблиц и загрузка первичных данных…');
            $executor->setPurger(new ORMPurger($this->em));
        }

        $executor->execute([$this->fixtures], true);

        $io->success('Первичные данные загружены.');
        $io->listing([
            'dram1008@yandex.ru / password — магазин «Акация»',
            'owner@shik-blask.ru / password — магазин «Шик блеск красота»',
        ]);

        return Command::SUCCESS;
    }
}
