<?php

namespace Pridwen;

use function fphp\{prop, concat};

class Auth {
	private static $__permissions = [];

	private static $__retrieveCredentials = null;

	public static function setPermissions($permissions = []) {
		self::$__permissions = $permissions;
	}

	public static function retrieveCredentials($callback) {
		self::$__retrieveCredentials = $callback;
	}

	public static function findById($id) {
		$callable = self::$__retrieveCredentials;
		$userData = $callable($id);

		return new UserAuth(self::$__permissions, $userData);
	}
}

