<?php

namespace spec\Gamma\FixturesBoostBundle\Command;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Gamma\FixturesBoostBundle\Service\FixturesBoostService;
use Symfony\Component\Console\Application;

class FixturesBoostCommandSpec extends ObjectBehavior
{
    function let(InputInterface $input)
    {
        $input->bind(Argument::cetera())->willReturn();
        $input->isInteractive()->willReturn(false);
        $input->validate()->willReturn();
        $input->getOption('clear')->willReturn(FixturesBoostService::CLEAR_MODE_SCHEMA);
        $input->getOption('fixtures-dir')->willReturn('./fake');
        $input->getOption('log-file-dir')->willReturn('./');
        $input->hasArgument('command')->willReturn(false);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Gamma\FixturesBoostBundle\Command\FixturesBoostCommand');
    }

    function it_is_a_container_aware_command()
    {
        $this->shouldHaveType('Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('gamma:fixtures:boostload');
    }

    function it_runs(InputInterface $input, OutputInterface $output)
    {
        $this->run($input, $output);
    }

    function it_should_notify_if_fixtures_is_not_found(InputInterface $input, OutputInterface $output)
    {
        $input->getOption('fixtures-dir')->willReturn('./Resources');
        $output->writeln('Fixtures not found in path: ./Resources')->shouldBeCalled();
        $this->run($input, $output);
    }

    /*function it_should_find_fixtures(InputInterface $input, OutputInterface $output, Application $application)
    {
        $input->getOption('fixtures-dir')->willReturn('./TestData');
        $output->writeln('Fixtures changed!')->shouldBeCalled();
        //$this->getApplication()->willReturn($application);
        $this->run($input, $output);
    }*/
}
