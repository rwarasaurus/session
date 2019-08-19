[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/rwarasaurus/session/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/rwarasaurus/session/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/rwarasaurus/session/badges/build.png?b=master)](https://scrutinizer-ci.com/g/rwarasaurus/session/build-status/master)

# JSON Session storage

Session data is stored as a json encoded string.

- not affected by PHP serialization RCE attacks

Quick start using array session storage

    use Session\{
        Session,
        Cookies,
        Storage\ArrayStorage
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

    # Using PSR7 Response
    $session->close();
    $response = new Psr\Http\Message\Response;
    $response->withAddedHeader('Set-Cookie', $session->cookie());

## Session storage handlers

Redis example

    use Session\{
        Session,
        Cookies,
        Storage\RedisStorage
    };

    $redis = new \Redis; // or \Predis\Client
    $ttl = 3600;
    $storage = new RedisStorage(redis, $ttl);
    $session = new Session(new Cookies, $storage);

File storage example

    use Session\{
        Session,
        Cookies,
        Storage\FilesystemStorage
    };

    $ttl = 3600;
    $adapter = new \League\Flysystem\Adapter\Local('/path/to/sessions/');
    $filesystem = new \League\Flysystem\Filesystem($adapter);
    $storage = new FilesystemStorage($filesystem, $ttl);

    // remove expired sessions
    $storage->purge();

    $session = new Session(new Cookies, $storage);
