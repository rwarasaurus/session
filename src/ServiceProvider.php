<?php

namespace Session;

use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
	public function register(Container $pimple) {
		if( ! isset($pimple['session.cookies'])) {
			$pimple['session.cookies'] = new Cookies;
		}

		if( ! isset($pimple['session.storage'])) {
			$path = __DIR__ . '/../sessions';
			if( ! is_dir($path)) {
				mkdir($path, 0700, true);
			}
			$pimple['session.storage'] = new FileStorage($path);
		}

		if( ! isset($pimple['session.options'])) {
			$pimple['session.options'] = [];
		}

		$pimple['session'] = new Session($pimple['session.cookies'], $pimple['session.storage'], $pimple['session.options']);
	}
}
