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

	public function can($requestedPermissionName) {
		// User permissions
		$permissions = filter(function ($permission) {
			return $permission['role'] === null;
		}, $this->permissions);

		foreach ($permissions as $permission) {
			if ($permission['allowed'] && $permission['name'] === $requestedPermissionName) {
				return true;
			} elseif (!$permission['allowed'] && $permission['name'] === $requestedPermissionName) {
				return false;
			}
		}

		// Roles permissions
		$permissions = filter(function ($permission) {
			return $permission['role'] !== null;
		}, $this->permissions);

		foreach ($permissions as $permission) {
			if ($permission['allowed'] && $permission['name'] === $requestedPermissionName) {
				return true;
			} elseif (!$permission['allowed'] && $permission['name'] === $requestedPermissionName) {
				return false;
			}
		}

		return false;
	}
}
