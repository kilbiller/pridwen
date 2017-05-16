<?php

namespace Pridwen;

use fphp\{prop, concat, reduce, filter};

class UserAuth {
	public $id = null;
	public $roles = [];
	public $permissions = [];

	function __construct($permissions, $user) {
		$this->id = prop('user_id', $user);
		$this->roles = prop('roles', $user) ?? [];
		$this->permissions = $this->__computePermissions($permissions);
	}

	/**
	 * Compute user permissions
	 *
	 */
	private function __computePermissions($permissions) {
		$result = [];

		// User permissions
		$userPermissions = prop('users.' . $this->id, $permissions) ?? [];
		$stuff = [];
		foreach ($userPermissions as $userPermissionName => $userPermissionStatus) {
			$stuff[] = ['name' => $userPermissionName, 'allowed' => $userPermissionStatus, 'role' => null];
		}
		$result = concat($result, $stuff);

		// Roles permissions
		$roles = prop('roles', $permissions) ?? [];
		foreach ($roles as $role => $rolePermissions) {
			if (in_array($role, $this->roles)) {
				$stuff = [];
				foreach ($rolePermissions as $rolePermissionName => $rolePermissionStatus) {
					$stuff[] = ['name' => $rolePermissionName, 'allowed' => $rolePermissionStatus, 'role' => $role];
				}
				$result = concat($result, $stuff);
			}
		}

		return $result;
	}

	private function __findMatch($request) {
		// Try to match with user permissions first
		$permissions = filter(function ($permission) {
			return $permission['role'] === null;
		}, $this->permissions);

		foreach ($permissions as $permission) {
			if ($request === $permission['name']) {
				return $permission;
			}
		}

		// Then with roles
		$permissions = filter(function ($permission) {
			return $permission['role'] !== null;
		}, $this->permissions);

		foreach ($permissions as $permission) {
			if ($request === $permission['name']) {
				return $permission;
			}
		}

		return null;
	}

	public function can($request) {
		// Match with closest matching rule possible
		while (!empty($request)) {
			$match = $this->__findMatch($request);

			if ($match) {
				return $match['allowed'];
			}

			if (substr($request, -1) === '*') {
				$request = substr($request, 0, -2);
			}

			if (!empty($request)) {
				$request = explode('.', $request);
				array_pop($request);
				$request = implode('.', $request) . '.*';
			}
		}

		return false;
	}
}
