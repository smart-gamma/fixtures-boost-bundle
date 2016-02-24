<?php

namespace spec\Gamma\FixturesBoostBundle\Command;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FixturesBoostCommandSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Gamma\FixturesBoostBundle\Command\FixturesBoostCommand');
    }
}
