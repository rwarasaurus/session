<?php

namespace spec\Session\Handler;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FilesSpec extends ObjectBehavior {

	protected $id = 'foo.sess';

	public function it_is_initializable() {
		$this->shouldHaveType('Session\Handler\Files');
	}

	public function it_should_open_a_new_file_to_store_session_data() {
		$this->open(sys_get_temp_dir(), $this->id)->shouldReturn(true);
	}

	public function it_should_write_session_data_to_a_file() {
		$this->open(sys_get_temp_dir(), $this->id);
		$this->write($this->id, 'bar');
		$this->read($this->id)->shouldEqual('bar');
	}

	public function is_should_destory_session_data() {
		$this->open(sys_get_temp_dir(), $this->id);
		$this->write($this->id, 'bar');
		$this->destory()->shouldReturn(true);
	}

	public function is_should_delete_expired_files() {
		$this->open(sys_get_temp_dir(), $this->id);
		$this->gc(1)->shouldReturn(true);
	}

}
