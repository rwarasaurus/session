<?php

namespace spec\Session;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Session\StorageInterface;

class SessionSpec extends ObjectBehavior {

	public function it_is_initializable() {
		$this->shouldHaveType('Session\Session');
	}

	public function it_should_stack_a_value_in_the_same_key(StorageInterface $handler) {
		$key = 'foo';
		$value = 'bar';
		$handler->get($key, [])->shouldBeCalled();
		$handler->put($key, [$value])->shouldBeCalled();
		$this->beConstructedWith($handler);
		$this->push($key, $value);
	}

	public function it_should_flash_a_key_value_onto_the_in_array(StorageInterface $handler) {
		$key = 'foo';
		$value = 'bar';
		$prefix = 'myprefix';
		$handler->get($prefix.'.in', [])->shouldBeCalled();
		$handler->put($prefix.'.in', [$key])->shouldBeCalled();
		$handler->put($prefix.'.' . $key, $value)->shouldBeCalled();
		$this->beConstructedWith($handler, $prefix);
		$this->putFlash($key, $value);
	}

	public function it_should_return_flashed_value_by_key(StorageInterface $handler) {
		$key = 'foo';
		$value = 'bar';
		$prefix = 'myprefix';
		$handler->get($prefix . '.' . $key, null)->shouldBeCalled();
		$this->beConstructedWith($handler, $prefix);
		$this->getFlash($key);
	}

}
