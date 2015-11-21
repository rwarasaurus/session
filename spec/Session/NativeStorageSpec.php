<?php

namespace spec\Session;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class NativeStorageSpec extends ObjectBehavior {

	public function letgo() {
		$this->clear();
		$this->close();
	}

	public function it_is_initializable() {
		$this->shouldHaveType('Session\NativeStorage');
	}

	public function it_should_return_empty_id() {
		$this->id()->shouldBeString();
	}

	public function it_should_return_session_id() {
		$this->start();
		$this->id()->shouldMatch('/[a-z0-9]+/i');
	}

	public function it_should_check_the_session_has_not_started() {
		$this->started()->shouldReturn(false);
	}

	public function it_should_check_the_session_has_started() {
		$this->start();
		$this->started()->shouldReturn(true);
	}

	public function it_should_check_if_a_key_is_set() {
		$this->start();
		$this->has('foo')->shouldReturn(false);
	}

	public function it_should_store_a_key_value() {
		$this->start();
		$this->put('foo', 'bar');
		$this->has('foo')->shouldReturn(true);
	}

	public function it_should_fetch_a_value_by_key() {
		$this->start();
		$this->put('foo', 'bar');
		$this->get('foo')->shouldEqual('bar');
	}

	public function it_should_remove_a_value_by_key() {
		$this->start();
		$this->put('foo', 'bar');
		$this->remove('foo');
		$this->has('foo')->shouldReturn(false);
	}

	public function it_should_regenerate_the_session_id() {
		$this->start();
		$this->put('foo', 'bar');
		$id = $this->id();
		$this->regenerate();
		$this->id()->shouldHaveDiff($id);
		$this->has('foo')->shouldReturn(true);
	}

	public function getMatchers() {
		return [
			'haveDiff' => function($a, $b) {
				return $a !== $b;
			},
		];
	}

}
