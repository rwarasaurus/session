<?php

namespace spec\Session;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Session\Cookies;
use Session\ArrayStorage;

class SessionSpec extends ObjectBehavior
{

    public function it_should_return_a_id(Cookies $cookies, ArrayStorage $storage)
    {
        $this->beConstructedWith($cookies, $storage);
        $cookies->has('PHPSESSID')->willReturn(true);
        $cookies->get('PHPSESSID')->willReturn('1234');
        $storage->read('1234')->shouldBeCalled();
        $this->start();
        $this->id()->shouldEqual('1234');
    }

    public function it_should_return_a_name(Cookies $cookies, ArrayStorage $storage)
    {
        $this->beConstructedWith($cookies, $storage, ['name' => 'foo']);
        $this->name()->shouldEqual('foo');
    }

    public function it_should_migrate_id(Cookies $cookies, ArrayStorage $storage)
    {
        $this->beConstructedWith($cookies, $storage);
        $cookies->has('PHPSESSID')->willReturn(true);
        $cookies->get('PHPSESSID')->willReturn('1234');
        $storage->read('1234')->shouldBeCalled();
        $this->start();
        $this->id()->shouldEqual('1234');
        $this->migrate();
        $this->id()->shouldNotEqual('1234');
    }

    public function it_should_destroy_session(Cookies $cookies, ArrayStorage $storage)
    {
        $this->beConstructedWith($cookies, $storage);
        $cookies->has('PHPSESSID')->willReturn(true);
        $cookies->get('PHPSESSID')->willReturn('1234');
        $storage->read('1234')->shouldBeCalled();
        $this->start();
        $this->id()->shouldEqual('1234');
        $storage->destroy('1234')->shouldBeCalled();
        $this->destroy();
        $this->id()->shouldNotEqual('1234');
    }

    public function it_should_check_if_the_session_has_started(Cookies $cookies, ArrayStorage $storage)
    {
        $this->beConstructedWith($cookies, $storage);
        $this->started()->shouldEqual(false);
        $cookies->has('PHPSESSID')->willReturn(true);
        $cookies->get('PHPSESSID')->willReturn('1234');
        $storage->read('1234')->shouldBeCalled();
        $this->start();
        $this->started()->shouldEqual(true);
    }
}
