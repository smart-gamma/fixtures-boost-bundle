<?php

namespace Gamma\FixturesBoostBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Gamma\FixturesBoostBundle\Service\FixturesHandler;
use Gamma\FixturesBoostBundle\Service\DatabaseHandler;

class FixturesBoostCommand extends ContainerAwareCommand
{
    const FIXTURES_HASH_LOG = 'fixtures_hash.log';
    const FIXTURES_DUMP_SQL = 'fixtures_dump.sql';

    /**
     * @var DatabaseHandler
     */
    private $databaseHandler;

    /**
     * @var FixturesHandler
     */
    private $fixturesHandler;

    /**
     * @param FixturesHandler $fixturesHandler
     * @param DatabaseHandler $databaseHandler
     */
    public function __construct(
        FixturesHandler $fixturesHandler,
        DatabaseHandler $databaseHandler
    )
    {
        $this->fixturesHandler = $fixturesHandler;
        $this->databaseHandler = $databaseHandler;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('gamma:fixtures:boostload')
            ->setDescription('Check and load fixtures if changed.')
            ->addOption('clear', 'c', InputOption::VALUE_REQUIRED, 'Clear\Recreate database or schema. Available: schema, database')
            ->addOption('fixtures-dir', 'f', InputOption::VALUE_REQUIRED, 'Path to fixtures directory.')
            ->addOption('log-file-dir', 'l', InputOption::VALUE_REQUIRED, 'Path to log file directory.')
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return bool
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$md5hash = $this->fixturesHandler->generateFixturesHash($input, $output)) {
            return false;
        }

        if (!file_exists(self::FIXTURES_DUMP_SQL) || $this->fixturesHandler->readHashFromFile($input) !== md5($md5hash)) {
            $output->writeln('Fixtures changed!');

            if ($input->getOption('clear')) {
                switch ($input->getOption('clear')) {
                    case 'schema':
                        $this->databaseHandler->dropSchema($input, $output);
                        $this->databaseHandler->createSchema($input, $output);

                        break;
                    case 'database':
                        $this->databaseHandler->dropDatabase($input, $output);
                        $this->databaseHandler->createDatabase($input, $output);
                        $this->databaseHandler->createSchema($input, $output);

                        break;
                }
            }

            if ($this->fixturesHandler->loadFixtures($input, $output)) {
                $this->fixturesHandler->writeHashToFile($input, md5($md5hash));

                $this->databaseHandler->createMysqlDump(self::FIXTURES_DUMP_SQL);

                $output->writeln('Fixtures updated! Dump created (or updated)!');
            }

            return true;
        }

        $output->writeln('Fixtures up to date! Load from dump');

        $this->loadMysqlDump(self::FIXTURES_DUMP_SQL);

        return true;
    }

}

