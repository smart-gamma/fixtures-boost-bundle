<?php

namespace spec\Gamma\FixturesBoostBundle\Service;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\Bundle\FixturesBundle\Command\LoadDataFixturesDoctrineCommand;

class FixturesHandlerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Gamma\FixturesBoostBundle\Service\FixturesHandler');
    }

    function let(LoadDataFixturesDoctrineCommand $dataFixturesDoctrineCommand)
    {
        $dataFixturesDoctrineCommand->run(Argument::cetera())->willReturn(1);
        $this->beConstructedWith($dataFixturesDoctrineCommand);
    }
}
