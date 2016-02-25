<?php

namespace spec\Gamma\FixturesBoostBundle\Service;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DatabaseHandlerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Gamma\FixturesBoostBundle\Service\DatabaseHandler');
    }
}
