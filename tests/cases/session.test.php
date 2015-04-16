<?php

class SessionTest extends PHPUnit_Framework_TestCase {

	public function testSessionPut() {
		$storage = $this->getMock('Session\Contracts\Storage');
		$storage->expects($this->once())
			->method('put')
			->with($this->equalTo('foo'), $this->equalTo('bar'));

		$s = new Session\Session($storage);
		$s->put('foo', 'bar');
	}

	public function testSessionGet() {
		$storage = $this->getMock('Session\Contracts\Storage');
		$storage->expects($this->once())
			->method('get')
			->with($this->equalTo('foo'))
			->will($this->returnValue('bar'));

		$s = new Session\Session($storage);
		$this->assertEquals('bar', $s->get('foo'));
	}

}
