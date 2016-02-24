<?php

namespace spec\Gamma\FixturesBoostBundle\Command;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Gamma\FixturesBoostBundle\Service\FixturesBoostService;

class FixturesBoostCommandSpec extends ObjectBehavior
{
    function let(InputInterface $input)
    {
        $input->bind(Argument::cetera())->willReturn();
        $input->isInteractive()->willReturn(false);
        $input->validate()->willReturn();
        $input->getOption('clear')->willReturn(FixturesBoostService::CLEAR_MODE_SCHEMA);
        $input->getOption('fixtures-dir')->willReturn('./');
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
}
