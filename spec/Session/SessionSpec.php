<?php

namespace spec\Session;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Session\StorageInterface;

class SessionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Session\Session');
    }

	function it_should_stack_a_value_in_the_same_key(StorageInterface $handler)
	{
		$key = 'foo';
		$value = 'bar';
		$handler->get($key, [])->shouldBeCalled();
		$handler->put($key, [$value])->shouldBeCalled();
		$this->beConstructedWith($handler);
		$this->push($key, $value);
	}

	function it_should_flash_a_key_value_into_the_in_index(StorageInterface $handler)
	{
		$key = 'foo';
		$value = 'bar';
		$handler->get('_in', [])->shouldBeCalled();
		$handler->put('_in', [$key])->shouldBeCalled();
		$handler->put('_flash.' . $key, $value)->shouldBeCalled();
		$this->beConstructedWith($handler);
		$this->putFlash($key, $value);
	}
}
