<?php

namespace WordPress\Data\User;

use WP_User;
use WordPress\Data\Factory as BaseFactory;

class Factory extends BaseFactory
{
	
	protected $defaultClass = 'WordPress\Data\User\User';
	
	public function create($user) {
		
		if (! $user instanceof WP_User) {
			if (is_numeric($user)) {
				$user = Lookup::byID($user);
			} else {
				$user = Lookup::byString($user);
			}
		}
		
		$role = reset($user->roles);
		
		return $this($user, $this->getClass($role));
	}
	
}
