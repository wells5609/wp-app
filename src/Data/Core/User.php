<?php

namespace WordPress\Data\Core;

use WordPress\Common\Util;
use WordPress\Data\Meta\MetadataTrait;

class User extends AbstractModel
{

	use MetadataTrait;
	
	/**
	 * The user's ID.
	 *
	 * @var int
	 */
	public $ID = 0;
	
	public $user_login;
	public $user_pass;
	public $user_nicename;
	public $user_email;
	public $user_url;
	public $user_registered;
	public $user_activation_key;
	public $user_status;
	public $display_name;
	public $status;
	public $deleted;
	#public $user_description;
	#public $user_firstname;
	#public $user_lastname;
	protected $nickname;
	protected $description;
	protected $first_name;
	protected $last_name;
	
	/**
	 * The individual capabilities the user has been given.
	 *
	 * @var array
	 */
	public $caps = array();
	
	/**
	 * User metadata option name.
	 *
	 * @var string
	 */
	public $cap_key;
	
	/**
	 * The roles the user is part of.
	 *
	 * @var array
	 */
	public $roles = array();
	
	/**
	 * All capabilities the user has, including individual and role based.
	 *
	 * @var array
	 */
	public $allcaps = array();
	
	public static $lazy_properties = array('nickname', 'description', 'first_name', 'last_name');

	public function getWordPressObjectType() {
		return 'user';
	}

	public function getMetaType() {
		return 'user';
	}
	
	/**
	 * Hydrates the model with the given data.
	 *
	 * @param mixed $data
	 */
	public function hydrate($data) {
		foreach(Util::iterate($data) as $key => $value) {
			if ('data' === $key) {
				foreach((array)$value as $k => $v) {
					$this->$k = $v;
				}
			} else {
				$this->$key = $value;
			}
		}
		$this->onHydrate();
	}

	/**
	 * Returns the value of a property.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	protected function readProperty($key) {
		if (isset($this->$key)) {
			return $this->$key;
		}
		if (in_array($key, static::$lazy_properties, true)) {
			return $this->lazyLoadProperty($key);
		}
	}
	
	/**
	 * Sets the value of a property.
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	protected function writeProperty($key, $value) {
		$this->$key = $value;
	}
	
	protected function lazyLoadProperty($key) {
		$this->getMetadata();
		if (property_exists($this, $key)) {
			return $this->$key;
		}
		return $this->getMeta($key);
	}
	
	protected function fetchAllMetadata() {
		$this->metadata = get_metadata($this->getMetaType(), $this->ID);
		foreach($this->metadata as $key => $value) {
			if (property_exists($this, $key)) {
				$this->$key = reset($value);
			}
		}
	}
	
}
