<?php

namespace WordPress\Model\User;

use WordPress\Model\Definition as BaseDefinition;

class Definition extends BaseDefinition
{

	final public function getName() {
		return 'user';
	}

	final public function getTableName() {
		return 'users';
	}

	final public function getPrimaryKey() {
		return 'ID';
	}

	public function getClassName() {
		return 'WordPress\\Model\\User';
	}

	final public function getColumnMap() {
		return array(
			'ID' => 'ID',
			'user_login' => 'user_login',
			'user_pass' => 'user_pass',
			'user_nicename' => 'user_nicename',
			'user_email' => 'user_email',
			'user_url' => 'user_url',
			'user_registered' => 'user_registered',
			'user_activation_key' => 'user_activation_key',
			'user_status' => 'user_status',
			'display_name' => 'display_name',
		);
	}

}
