<?php

namespace Pridwen;

class UserAuth {
	public $id = null;
	public $roles = [];
	private $__permissions = [];

	function __construct($permissions, $user) {
		$this->id = $user['user_id'];
		$this->roles = $user['roles'];
		$this->__permissions = $permissions;
	}

	public function can($permission) {
		foreach ($this->__permissions['roles'] as $role => $rolePermissions) {
			if (in_array($role, $this->roles)) {
				if ($rolePermissions[$permission]) {
					return true;
				}
			}
		}

		return false;
	}
}

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

Auth::setPermissions([
	'roles' => [
		'admin' => [
			'ticketing.edit' => true
		]
	]
]);
Auth::retrieveCredentials(function ($id) {
	return ['user_id' => $id, 'roles' => ['admin']];
});

$test = Auth::findById(12);

print_r($test->can('ticketing.edit'));

