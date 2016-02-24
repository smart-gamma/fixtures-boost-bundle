<?php

namespace spec\Gamma\FixturesBoostBundle;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FixturesBoostBundleSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Gamma\FixturesBoostBundle\FixturesBoostBundle');
    }

    function it_should_be_a_bundle()
    {
        $this->shouldHaveType('Symfony\Component\HttpKernel\Bundle\Bundle');
    }
}
