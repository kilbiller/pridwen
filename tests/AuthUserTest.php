<?php

use Pridwen\Auth;

describe('AuthUser', function () {
	beforeEach(function () {
		Auth::setPermissions([
			'roles' => [
				'admin' => [
					'ticketing.*' => true,
					'ticketing.view' => true,
					'ticketing.create' => true,
					'ticketing.edit' => true
				]
			],
			'users' => [
				'12' => [
					'ticketing.create' => false,
					'ticketing.edit' => true
				]
			]
		]);

		Auth::retrieveCredentials(function ($id) {
			return ['user_id' => $id, 'roles' => ['admin']];
		});
	});

	describe('can', function () {
		it('should return true when permission is enabled on one of the user roles', function () {
			$user = Auth::findById(12);

			expect($user->can('ticketing.view'))->toBe(true);
		});

		it('should return true when permission is enabled on one of the user roles and on the user himself', function () {
			$user = Auth::findById(12);

			expect($user->can('ticketing.edit'))->toBe(true);
		});

		it('should return false when permission is enabled on one of the user roles but disabled on the user himself', function () {
			$user = Auth::findById(12);

			expect($user->can('ticketing.create'))->toBe(false);
		});

		it('should return true when parent permission is enabled on one of the user roles', function () {
			$user = Auth::findById(12);

			expect($user->can('ticketing.test'))->toBe(true);
		});
	});
});
