<?php

namespace WordPress\Data\User;

use RuntimeException;

class Lookup
{
	
	public static function byID($user_id) {
		if (! $user = get_user_by('id', $user_id)) {
			throw new RuntimeException("Invalid user ID: '$user_id'");
		}
		return $user;
	}
	
	public static function byString($argument) {
		
		if (! $user = get_user_by('slug', $argument)) {
			if (! $user = get_user_by('email', $argument)) {
				if (! $user = get_user_by('login', $argument)) {
					throw new RuntimeException("Invalid user slug/login/email string: '$argument'");
				}
			}
		}
		
		return $user;
	}
	
}
