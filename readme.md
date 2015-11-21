# Just another session library

Quick start using native session storage

	$s = new Session\Session();
	$s->start();

	$s->put('foo', 'bar');
	echo $\_SESSION['foo']; // output "bar"

	$a = $s->get('foo');
	echo $a; // output "bar"

	$s->remove('foo');

	$b = $s->get('foo', 'baz');
	echo $b; // output "baz"

## Session storage handlers

Memcached example

	$memcached = new Memcached;
	$memcached->addServer('localhost', 11211);

	// session config
	// http://php.net/manual/en/session.configuration.php
	$options = ['name' => 'my_session'];

	$handler = new Session\Handler\Memcached($memcached);
	$storage = new Session\NativeStorage($handler, $options);
	$session = new Session\Session($storage);
