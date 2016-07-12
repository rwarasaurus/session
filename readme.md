# PSR7 JSON Session storage

Sessions are persisted using the session `close` method, optiuonally you can pass a psr7
response object to set the cookie, session data is stored as a json encoded string.

Quick start using array session storage

	use Session\{
		Session,
		Cookies,
		ArrayStorage
	};

	$session = new Session(new Cookies, new ArrayStorage);
	$session->start();

	$session->put('foo', 'bar');
	echo $session->get('foo'); // output "bar"

	$session->remove('foo');

	$b = $session->get('foo', 'baz');
	echo $b; // output "baz"

Closing the session and setting the cookie

	$session->close();
	header('Set-Cookie', $session->cookie());

Using PSR7 Response

	$response = new Psr\Http\Message\Response;
	$session->close($response);

## Session storage handlers

Redis example

	use Redis;
	use Session\{
		Session,
		Cookies,
		RedisStorage
	};

	$redis = new Redis;
	$ttl = 3600;
	$storage = new RedisStorage(redis, $ttl);
	$session = new Session(new Cookies, $storage);

File storage example

	use Session\{
		Session,
		Cookies,
		FileStorage
	};

	// you will have to implement your own garbage collection for now
	$storage = new FileStorage('/path/to/sessions');

	$session = new Session(new Cookies, $storage);
