<?php

namespace WordPress\Model\Option;

use WordPress\Model\AbstractModel;

class Option extends AbstractModel
{
	
	/**
	 * @var int
	 */
	public $option_id;
	
	/**
	 * @var string
	 */
	public $option_name;
	
	/**
	 * @var mixed
	 */
	public $option_value;
	
	/**
	 * @var string
	 */
	public $autoload = 'yes';
	
	/**
	 * Returns the term_id.
	 * 
	 * @return int
	 */
	public function getPrimaryKeyValue() {
		return $this->option_id;
	}
	
	/**
	 * Returns an array of data for an option.
	 * 
	 * @param string $option
	 * @param mixed $default [Optional] Default = false
	 * @return array|null
	 */
	public static function getOptionData($option, $default = false) {
		
		if (empty($option)) {
			return null;
		}
		
		$option_id = 0;
		$value = null;
		$autoload = false;
		
	    // prevent non-existent options from triggering multiple queries
        $notoptions = wp_cache_get('notoptions', 'options');
        if (isset($notoptions[$option])) {
            /**
             * Filter the default value for an option.
             *
             * The dynamic portion of the hook name, `$option`, refers to the option name.
             *
             * @since 3.4.0
             * @since 4.4.0 The `$option` parameter was added.
             *
             * @param mixed  $default The default value to return if the option does not exist
             *                        in the database.
             * @param string $option  Option name.
             */
            $value = apply_filters("default_option_{$option}", $default, $option);
        } else {
	        $alloptions = wp_load_alloptions();
			if (isset($alloptions[$option])) {
	            $value = $alloptions[$option];
	        	$autoload = true;
			} else {
	            $value = wp_cache_get($option, 'options');
				if (false !== $value) {
					$option_id = wp_cache_get("{$option}:id", 'options');
				} else {
	                global $wpdb;
					$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->options} WHERE option_name = %s LIMIT 1", $option));
					// Has to be get_row instead of get_var because of funkiness with 0, false, null values
	                if (is_object($row)) {
	                    $value = $row->option_value;
						$option_id = $row->option_id;
						$autoload = $row->autoload;
	                    wp_cache_add($option, $value, 'options');
						wp_cache_add("{$option}:id", $option_id, 'options');
	                } else { // option does not exist, so we must cache its non-existence
	                    is_array($notoptions) or $notoptions = array();
	                    $notoptions[$option] = true;
	                    wp_cache_set('notoptions', $notoptions, 'options');
	                    /** This filter is documented in wp-includes/option.php */
	                    $value = apply_filters("default_option_{$option}", $default, $option);
	                }
	            }
	        }
		}

	    // If home is not set use siteurl.
	    if ('home' === $option && '' == $value) {
	        return static::getOptionData('siteurl', null);
		}
		
	    if (in_array($option, array('siteurl', 'home', 'category_base', 'tag_base'))) {
	        $value = untrailingslashit($value);
		}
	 
	    /**
	     * Filter the value of an existing option.
	     *
	     * The dynamic portion of the hook name, `$option`, refers to the option name.
	     *
	     * @since 1.5.0 As 'option_' . $setting
	     * @since 3.0.0
	     * @since 4.4.0 The `$option` parameter was added.
	     *
	     * @param mixed  $value  Value of the option. If stored serialized, it will be
	     *                       unserialized prior to being returned.
	     * @param string $option Option name.
	     */
	    $value = apply_filters("option_{$option}", maybe_unserialize($value), $option);
		
		return array(
			'option_id' => $option_id,
			'option_name' => $option,
			'option_value' => $value,
			'autoload' => $autoload ? 'yes' : 'no',
		);
	}
	
	/**
	 * Overwrite forgeObject() implementation.
	 * 
	 * @param mixed $data
	 * @return \WordPress\Model\Option\Option
	 */
	public static function forgeObject($data) {
		if (empty($data)) {
			return null;
		}
		if (! is_array($data)) {
			$data = is_string($data) ? static::getOptionData($data) : (array)$data;
		}
		return new static($data);
	}
	
	/**
	 * Saves the option.
	 * 
	 * @param array $data [Optional]
	 * @return mixed
	 */
	public function save(array $data = null) {
		if ($this->option_id) {
			$result = $this->update($data);
		} else {
			$result = $this->insert($data);
		}
		return $result;
	}
	
	/**
	 * Insert the option.
	 * 
	 * @uses add_option()
	 * 
	 * @param array $data [Optional]
	 * @return boolean
	 */
	public function insert(array $data = null) {
		if ($this->option_id) {
			throw new RuntimeException("Cannot insert: option already exists.");
		}
		$this->beforeInsert();
		if (isset($data)) {
			$this->import($data);
		}
		if (empty($this->option_name)) {
			return null;
		}
		$result = (bool) add_option($this->option_name, $this->option_value, '', $this->autoload);
		$this->afterInsert($result);
		return $result;
	}
	
	/**
	 * Update the option.
	 * 
	 * @uses update_option()
	 * 
	 * @param array $data [Optional]
	 * @return boolean
	 */
	public function update(array $data = null) {
		$this->beforeUpdate();
		if (isset($data)) {
			$this->import($data);
		}
		$result = (bool) update_option($this->option_name, $this->option_value, $this->autoload);
		$this->afterUpdate($result);
		return $result;
	}
	
	/**
	 * Delete the option.
	 * 
	 * @uses delete_option()
	 * 
	 * @return boolean
	 */
	public function delete() {
		$this->beforeDelete();
		if (empty($this->option_name)) {
			return null;
		}
		$result = (bool) delete_option($this->option_name);
		$this->afterDelete($result);
		return $result;
	}
	
	
}
