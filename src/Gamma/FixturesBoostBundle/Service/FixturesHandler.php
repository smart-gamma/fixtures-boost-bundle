<?php

namespace Gamma\FixturesBoostBundle\Service;

use Doctrine\Bundle\FixturesBundle\Command\LoadDataFixturesDoctrineCommand;

class FixturesHandler
{
    /**
     * @var LoadDataFixturesDoctrineCommand
     */
    private $fixturesLoadCommand;

    /**
     * @param LoadDataFixturesDoctrineCommand $fixturesLoadCommand
     */
    public function __construct(LoadDataFixturesDoctrineCommand $fixturesLoadCommand)
    {
        $this->fixturesLoadCommand = $fixturesLoadCommand;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return bool
     */
    public function loadFixtures(InputInterface $input, OutputInterface $output)
    {
        $inputArgs = new ArrayInput([
            '--env' => $input->getOption('env'),
        ]);

        $inputArgs->setInteractive(false);

        $returnCode = $this->fixturesLoadCommand->run($inputArgs, $output);

        if ($returnCode !== 0) {
            return false;
        }

        $output->writeln('Fixtures successfully loaded.');

        return true;
    }

    /**
     * @param InputInterface $input
     *
     * @return string
     */
    public function readHashFromFile(InputInterface $input)
    {
        return file_get_contents($this->createFixturesHashFileIfNotExists($input));
    }

    /**
     * @param InputInterface $input
     * @param string         $hash
     *
     * @return int
     */
    public function writeHashToFile(InputInterface $input, $hash)
    {
        return file_put_contents($this->createFixturesHashFileIfNotExists($input), $hash);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return string
     */
    public function generateFixturesHash(InputInterface $input, OutputInterface $output)
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
