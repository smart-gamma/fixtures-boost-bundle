<?php

namespace Gamma\FixturesBoostBundle\Service;

use Doctrine\Bundle\DoctrineBundle\Command\Proxy\DropSchemaDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\Proxy\CreateSchemaDoctrineCommand;

class DatabaseHandler
{
    /**
     * @var DropSchemaDoctrineCommand
     */
    private $schemaDropCommand;

    /**
     * @var CreateSchemaDoctrineCommand
     */
    private $schemaCreateCommand;

    /**
     * @param DropSchemaDoctrineCommand $schemaDropCommand
     * @param CreateSchemaDoctrineCommand $schemaCreateCommand
     */
    public function __construct(
        DropSchemaDoctrineCommand $schemaDropCommand,
        CreateSchemaDoctrineCommand $schemaCreateCommand
    )
    {
        $this->schemaDropCommand = $schemaDropCommand;
        $this->schemaCreateCommand = $schemaCreateCommand;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return bool
     */
    public function createSchema(InputInterface $input, OutputInterface $output)
    {
        // $command = $this->getApplication()->find('doctrine:schema:create');

        $inputArgs = new ArrayInput([
            '--env' => $input->getOption('env'),
        ]);

        $inputArgs->setInteractive(false);

        $returnCode = $this->schemaCreateCommand->run($inputArgs, $output);

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
    public function dropSchema(InputInterface $input, OutputInterface $output)
    {
        // $command = $this->getApplication()->find('doctrine:schema:drop');
        // $command = $this->getContainer()->get('doctrine.schema.drop.command');
        $inputArgs = new ArrayInput([
            '--env' => $input->getOption('env'),
            '--force' => true,
        ]);

        $inputArgs->setInteractive(false);

        $returnCode = $this->schemaDropCommand->run($inputArgs, $output);

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
    public function dropDatabase(InputInterface $input, OutputInterface $output)
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
    public function createDatabase(InputInterface $input, OutputInterface $output)
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
     * @param $path
     *
     * @return string|void
     */
    public function createMysqlDump($path)
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
    public function loadMysqlDump($path)
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
}
