<?php

namespace Gamma\FixturesBoostBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class FixturesBoostCommand extends ContainerAwareCommand
{
    const FIXTURES_HASH_LOG = 'fixtures_hash.log';
    const FIXTURES_DUMP_SQL = 'fixtures_dump.sql';

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
        if (!$md5hash = $this->generateFixturesHash($input, $output)) {
            return false;
        }

        if (!file_exists(self::FIXTURES_DUMP_SQL) || $this->readHashFromFile($input) !== md5($md5hash)) {
            $output->writeln('Fixtures changed!');

            if ($input->getOption('clear')) {
                switch ($input->getOption('clear')) {
                    case 'schema':
                        $this->dropSchema($input, $output);
                        $this->createSchema($input, $output);

                        break;
                    case 'database':
                        $this->dropDatabase($input, $output);
                        $this->createDatabase($input, $output);
                        $this->createSchema($input, $output);

                        break;
                }
            }

            if ($this->loadFixtures($input, $output)) {
                $this->writeHashToFile($input, md5($md5hash));

                $this->createMysqlDump(self::FIXTURES_DUMP_SQL);

                $output->writeln('Fixtures updated! Dump created (or updated)!');
            }

            return true;
        }

        $output->writeln('Fixtures up to date! Load from dump');

        $this->loadMysqlDump(self::FIXTURES_DUMP_SQL);

        return true;
    }

    /**
     * @param $path
     *
     * @return string|void
     */
    private function createMysqlDump($path)
    {
        if (!$params = $this->getContainer()->get('doctrine')->getConnection()->getParams()) {
            return;
        }

        return exec(sprintf(
            'mysqldump --user=%s --password=%s --host=%s %s > %s',
            $params['user'],
            $params['password'],
            $params['host'],
            $params['dbname'],
            $path
        ));
    }

    /**
     * @param $path
     *
     * @return string|void
     */
    private function loadMysqlDump($path)
    {
        if (!$params = $this->getContainer()->get('doctrine')->getConnection()->getParams()) {
            return;
        }

        return exec(sprintf(
            'mysql --user=%s --password=%s --host=%s %s < %s',
            $params['user'],
            $params['password'],
            $params['host'],
            $params['dbname'],
            $path
        ));
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return bool
     */
    protected function loadFixtures(InputInterface $input, OutputInterface $output)
    {
        $command = $this->getApplication()->find('doctrine:fixtures:load');

        $inputArgs = new ArrayInput([
            '--env' => $input->getOption('env'),
        ]);

        $inputArgs->setInteractive(false);

        $returnCode = $command->run($inputArgs, $output);

        if ($returnCode !== 0) {
            return false;
        }

        $output->writeln('Fixtures successfully loaded.');

        return true;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return bool
     */
    private function createSchema(InputInterface $input, OutputInterface $output)
    {
        $command = $this->getApplication()->find('doctrine:schema:create');

        $inputArgs = new ArrayInput([
            '--env' => $input->getOption('env'),
        ]);

        $inputArgs->setInteractive(false);

        $returnCode = $command->run($inputArgs, $output);

        if (0 !== $returnCode) {
            return false;
        }

        $output->writeln('Schema created.');

        return true;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return bool
     */
    private function dropSchema(InputInterface $input, OutputInterface $output)
    {
        $command = $this->getApplication()->find('doctrine:schema:drop');

        $inputArgs = new ArrayInput([
            '--env' => $input->getOption('env'),
            '--force' => true,
        ]);

        $inputArgs->setInteractive(false);

        $returnCode = $command->run($inputArgs, $output);

        if (0 !== $returnCode) {
            return false;
        }

        $output->writeln('Schema dropped.');

        return true;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return bool
     */
    private function dropDatabase(InputInterface $input, OutputInterface $output)
    {
        $command = $this->getApplication()->find('doctrine:database:drop');

        $inputArgs = new ArrayInput([
            '--env' => $input->getOption('env'),
            '--if-exists' => true,
            '--force' => true,
        ]);

        $inputArgs->setInteractive(false);

        $returnCode = $command->run($inputArgs, $output);

        if (0 !== $returnCode) {
            return false;
        }

        $output->writeln('Database dropped.');

        return true;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return bool
     */
    private function createDatabase(InputInterface $input, OutputInterface $output)
    {
        $command = $this->getApplication()->find('doctrine:database:create');

        $inputArgs = new ArrayInput([
            '--env' => $input->getOption('env'),
            '--if-not-exists' => true,
        ]);

        $inputArgs->setInteractive(false);

        $returnCode = $command->run($inputArgs, $output);

        if (0 !== $returnCode) {
            return false;
        }

        $output->writeln('Database created.');

        return true;
    }

    /**
     * @param InputInterface $input
     *
     * @return string
     */
    protected function readHashFromFile(InputInterface $input)
    {
        return file_get_contents($this->createFixturesHashFileIfNotExists($input));
    }

    /**
     * @param InputInterface $input
     * @param string         $hash
     *
     * @return int
     */
    protected function writeHashToFile(InputInterface $input, $hash)
    {
        return file_put_contents($this->createFixturesHashFileIfNotExists($input), $hash);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return string
     */
    protected function generateFixturesHash(InputInterface $input, OutputInterface $output)
    {
        $finder = new Finder();

        if (!$path = $input->getOption('fixtures-dir')) {
            $path = sprintf('%s/../src', $this->getContainer()->getParameter('kernel.root_dir'));
        }

        try {
            $finder
                ->name('*.php')
                ->in($path)
                ->filter(function (SplFileInfo $i) {
                    return (bool)preg_match('/.*(DataFixtures\/ORM).*/', $i->getPath());
                })
                ->sortByName();

            if (0 === $finder->count()) {
                $output->writeln(sprintf('Fixtures not found in path: %s', $path));

                return;
            }

            $md5hash = sprintf('%s::', $finder->count());

            foreach ($finder as $file) {
                $md5hash .= md5_file($file->getRealpath());
            }

            return $md5hash;
        } catch (\Exception $e) {
            $output->writeln(sprintf('Error: %s', $e->getMessage()));
            $output->writeln(sprintf('Fixtures not found in path: %s', $path));

            return;
        }
    }

    /**
     * @param InputInterface $input
     *
     * @return string
     */
    private function createFixturesHashFileIfNotExists(InputInterface $input)
    {
        if (file_exists($filePath = $this->resolveFixturesHashFilePath($input).self::FIXTURES_HASH_LOG)) {
            return $filePath;
        }

        fclose(fopen($filePath, 'a+'));

        return $filePath;
    }

    /**
     * @param InputInterface $input
     *
     * @return string
     */
    private function resolveFixturesHashFilePath(InputInterface $input)
    {
        return $input->getOption('log-file-dir') ?: sprintf('%s/../', $this->getContainer()->getParameter('kernel.root_dir'));
    }
}

