<?php

namespace WordPress\Admin\Settings;

class Controller
{

	protected $sections = array();

	public function setSections(array $sections) {
		$this->sections = $sections;
		return $this;
	}

	public function addSection(Section $section) {
		$this->sections[$section->id] = $section;
		return $this;
	}

	public function getSection($id) {
		return isset($this->sections[$id]) ? $this->sections[$id] : null;
	}

	public function getSections() {
		return $this->sections;
	}

	/**
	 * Add actions for the container.
	 */
	public function register() {
		add_action('admin_init', array($this, '_init'));
		add_action('admin_enqueue_scripts', array($this, '_enqueueScripts'));
	}
	
	/**
	 * Outputs the settings sections and tabbed navigation.
	 */
	public function render() {
		echo '<div class="wrap">';
		$this->renderNavigation();
		$this->renderSections();
		echo '</div>';
	}

	/**
	 * Sanitize callback for Settings API
	 */
	public function sanitizeOptions($options) {
		foreach($options as $option_slug => $option_value) {
			$callback = $this->getSanitizeCallback($option_slug);
			if ($callback) {
				$options[$option_slug] = $callback($option_value);
				continue;
			}
		}
		return $options;
	}

	/**
	 * Get sanitization callback for given option slug
	 *
	 * @param string $slug option slug
	 *
	 * @return mixed string or bool false
	 */
	public function getSanitizeCallback($slug = '') {

		if (empty($slug)) {
			return false;
		}

		// Iterate over registered fields and see if we can find proper callback
		foreach($this->sections as $section_id => $section) {
			foreach($section->getFields() as $field_name => $field) {
				if ($field->name === $slug) {
					if ($field->sanitize_callback) {
						return $field->sanitize_callback;
					}
					return false;
				}
			}
		}

		return false;
	}

	/**
	 * Get the value of a settings field
	 *
	 * @param string  $option  settings field name
	 * @param string  $section the section name this field belongs to
	 * @param string  $default default text if it's not found
	 *
	 * @return string
	 */
	public function getOption($option, $section, $default = '') {
		$options = get_option($section);
		return isset($options[$option]) ? $options[$option] : $default;
	}

	/**
	 * Enqueue scripts and styles
	 */
	public function _enqueueScripts() {
		wp_enqueue_style('wp-color-picker');
		wp_enqueue_media();
		wp_enqueue_script('wp-color-picker');
		wp_enqueue_script('jquery');
	}

	/**
	 * Registers the settings sections and fields.
	 */
	public function _init() {

		foreach ($this->sections as $section) {

			if (false == get_option($section->id)) {
				add_option($section->id);
			}

			if (! empty($section->desc) ) {
				$section->desc = '<div class="inside">'.$section->desc.'</div>';
				$callback = __NAMESPACE__.'\\_echo_section_description';
			} else if (! empty($section->callback)) {
				$callback = $section->callback;
			} else {
				$callback = null;
			}

			add_settings_section($section->id, $section->title, $callback, $section->id);

			$this->registerSectionFields($section);

			register_setting($section->id, $section->id, array($this, 'sanitizeOptions'));
		}
	}
	
	protected function registerSectionFields(Section $section) {

		foreach ($section->getFields() as $field) {
				
			$args = array(
				'id'                => $field->name,
				'label_for'         => "{$section->id}[{$field->name}]",
				'desc'              => $field->desc,
				'name'              => $field->label,
				'section'           => $section->id,
				'size'              => $field->size,
				'options'           => $field->options ?: '',
				'std'               => $field->default,
				'sanitize_callback' => $field->sanitize_callback,
				'type'              => $field->type,
			);
				
			add_settings_field(
				$section->id.'['.$field->name.']',
				$field->label,
				array($this, 'render'.ucfirst($field->type).'Field'),
				$section->id,
				$section->id,
				$args
			);
		}
	}

	/**
	 * Get field description for display
	 *
	 * @param array   $args settings field args
	 *
	 * @return string
	 */
	public function getFieldDescription($args) {
		if (! empty($args['desc'])) {
			$desc = sprintf('<p class="description">%s</p>', $args['desc']);
		} else {
			$desc = '';
		}
		return $desc;
	}

	/**
	 * Displays a text field for a settings field
	 *
	 * @param array   $args settings field args
	 */
	public function renderTextField($args) {
		$value = esc_attr($this->getOption($args['id'], $args['section'], $args['std']));
		$size = isset($args['size']) && ! is_null($args['size']) ? $args['size'] : 'regular';
		$type = isset($args['type']) ? $args['type'] : 'text';
		$html = sprintf(
			'<input type="%1$s" class="%2$s-text" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s"/>',
			$type, $size, $args['section'], $args['id'], $value
		);
		$html .= $this->getFieldDescription($args);
		echo $html;
	}

	/**
	 * Displays a url field for a settings field
	 *
	 * @param array   $args settings field args
	 */
	public function renderUrlField($args) {
		$this->renderTextField($args);
	}

	/**
	 * Displays a number field for a settings field
	 *
	 * @param array   $args settings field args
	 */
	public function renderNumberField($args) {
		$this->renderTextField($args);
	}

	/**
	 * Displays a checkbox for a settings field
	 *
	 * @param array   $args settings field args
	 */
	public function renderCheckboxField($args) {
		$value = esc_attr($this->getOption($args['id'], $args['section'], $args['std']));
		$html = '<fieldset>';
		$html .= sprintf('<label for="wpuf-%1$s[%2$s]">', $args['section'], $args['id']);
		$html .= sprintf('<input type="hidden" name="%1$s[%2$s]" value="off" />', $args['section'], $args['id']);
		$html .= sprintf(
			'<input type="checkbox" class="checkbox" id="wpuf-%1$s[%2$s]" name="%1$s[%2$s]" value="on" %3$s />',
			$args['section'], $args['id'], checked($value, 'on', false)
		);
		$html .= sprintf('%1$s</label>', $args['desc']);
		$html .= '</fieldset>';
		echo $html;
	}

	/**
	 * Displays a multicheckbox a settings field
	 *
	 * @param array   $args settings field args
	 */
	public function renderMulticheckField($args) {
		$value = $this->getOption($args['id'], $args['section'], $args['std']);
		$html = '<fieldset>';
		foreach ($args['options'] as $key => $label) {
			$checked = isset($value[$key]) ? $value[$key] : '0';
			$html .= sprintf('<label for="wpuf-%1$s[%2$s][%3$s]">', $args['section'], $args['id'], $key);
			$html .= sprintf(
				'<input type="checkbox" class="checkbox" id="wpuf-%1$s[%2$s][%3$s]" name="%1$s[%2$s][%3$s]" value="%3$s" %4$s />',
				$args['section'], $args['id'], $key, checked($checked, $key, false)
			);
			$html .= sprintf('%1$s</label><br>', $label);
		}
		$html .= $this->getFieldDescription($args);
		$html .= '</fieldset>';
		echo $html;
	}

	/**
	 * Displays a multicheckbox a settings field
	 *
	 * @param array   $args settings field args
	 */
	public function renderRadioField($args) {
		$value = $this->getOption($args['id'], $args['section'], $args['std']);
		$html = '<fieldset>';
		foreach ($args['options'] as $key => $label) {
			$html .= sprintf('<label for="wpuf-%1$s[%2$s][%3$s]">', $args['section'], $args['id'], $key);
			$html .= sprintf(
				'<input type="radio" class="radio" id="wpuf-%1$s[%2$s][%3$s]" name="%1$s[%2$s]" value="%3$s" %4$s />',
				$args['section'], $args['id'], $key, checked($value, $key, false)
			);
			$html .= sprintf('%1$s</label><br>', $label);
		}
		$html .= $this->getFieldDescription($args);
		$html .= '</fieldset>';
		echo $html;
	}

	/**
	 * Displays a selectbox for a settings field
	 *
	 * @param array   $args settings field args
	 */
	public function renderSelectField($args) {
		$value = esc_attr($this->getOption($args['id'], $args['section'], $args['std']));
		$size = isset($args['size'] ) && ! is_null($args['size']) ? $args['size'] : 'regular';
		$html = sprintf(
			'<select class="%1$s" name="%2$s[%3$s]" id="%2$s[%3$s]">',
			$size, $args['section'], $args['id']
		);
		foreach ($args['options'] as $key => $label) {
			$html .= sprintf('<option value="%s"%s>%s</option>', $key, selected($value, $key, false), $label);
		}
		$html .= '</select>';
		$html .= $this->getFieldDescription($args);
		echo $html;
	}

	/**
	 * Displays a textarea for a settings field
	 *
	 * @param array   $args settings field args
	 */
	public function renderTextareaField($args) {
		$value = esc_textarea($this->getOption($args['id'], $args['section'], $args['std']));
		$size = empty($args['size']) ? 'regular' : $args['size'];
		$html = sprintf(
			'<textarea rows="5" cols="55" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]">%4$s</textarea>',
			$size, $args['section'], $args['id'], $value
		);
		$html .= $this->getFieldDescription($args);
		echo $html;
	}

	/**
	 * Displays a textarea for a settings field
	 *
	 * @param array   $args settings field args
	 * @return string
	 */
	public function renderHtmlField($args) {
		echo $this->getFieldDescription($args);
	}

	/**
	 * Displays a rich text textarea for a settings field
	 *
	 * @param array   $args settings field args
	 */
	public function renderWysiwygField($args) {
		$value = $this->getOption($args['id'], $args['section'], $args['std']);
		$size = empty($args['size']) ? '500px' : $args['size'];
		echo '<div style="max-width:'.$size.';">';
		$editor_settings = array(
			'teeny'         => true,
			'textarea_name' => $args['section'].'['.$args['id'].']',
			'textarea_rows' => 10
		);
		if (isset($args['options']) && is_array($args['options'])) {
			$editor_settings = array_merge($editor_settings, $args['options']);
		}
		wp_editor($value, $args['section'].'-'.$args['id'], $editor_settings);
		echo '</div>';
		echo $this->getFieldDescription($args);
	}

	/**
	 * Displays a file upload field for a settings field
	 *
	 * @param array   $args settings field args
	 */
	public function renderFileField($args) {
		$value = esc_attr( $this->getOption($args['id'], $args['section'], $args['std']));
		$size = empty($args['size']) ? 'regular' : $args['size'];
		$id = $args['section'].'['.$args['id'].']';
		$label = isset($args['options']['button_label']) ? $args['options']['button_label'] : __('Choose File');
		$html = sprintf(
			'<input type="text" class="%1$s-text wpsa-url" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>',
			$size, $args['section'], $args['id'], $value
		);
		$html .= '<input type="button" class="button wpsa-browse" value="'.$label.'" />';
		$html .= $this->getFieldDescription($args);
		echo $html;
	}

	/**
	 * Displays a password field for a settings field
	 *
	 * @param array   $args settings field args
	 */
	public function renderPasswordField($args) {
		$value = esc_attr($this->getOption($args['id'], $args['section'], $args['std']));
		$size = empty($args['size']) ? 'regular' : $args['size'];
		$html = sprintf(
			'<input type="password" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>',
			$size, $args['section'], $args['id'], $value
		);
		$html .= $this->getFieldDescription($args);
		echo $html;
	}

	/**
	 * Displays a color picker field for a settings field
	 *
	 * @param array   $args settings field args
	 */
	public function renderColorField($args) {
		$value = esc_attr($this->getOption($args['id'], $args['section'], $args['std']));
		$size = empty($args['size']) ? 'regular' : $args['size'];
		$html = sprintf(
			'<input type="text" class="%1$s-text wp-color-picker-field" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s" data-default-color="%5$s" />',
			$size, $args['section'], $args['id'], $value, $args['std']
		);
		$html .= $this->getFieldDescription($args);
		echo $html;
	}

	/**
	 * Show navigations as tab
	 *
	 * Shows all the settings section labels as tab
	 */
	public function renderNavigation() {
		$html = '<h2 class="nav-tab-wrapper">';
		foreach ($this->sections as $tab) {
			$html .= sprintf('<a href="#%1$s" class="nav-tab" id="%1$s-tab">%2$s</a>', $tab->id, $tab->title);
		}
		$html .= '</h2>';
		echo $html;
	}

	/**
	 * Show the section settings forms
	 *
	 * This function displays every sections in a different form
	 */
	public function renderSections() {
		?>
<div class="metabox-holder">
<?php foreach ($this->sections as $form) : ?>
	<div id="<?=$form->id?>" class="group" style="display:none;">
		<form method="post" action="options.php">
			<?php
			do_action('wsa_form_top_'.$form->id, $form);
			settings_fields($form->id);
			do_settings_sections($form->id);
			do_action('wsa_form_bottom_'.$form->id, $form);
			?>
			<div style="padding-left:10px"><?php submit_button(); ?></div>
		</form>
	</div>
<?php endforeach; ?>
</div>
<?php
	$this->script();
	}

	/**
	 * Tabbable JavaScript codes & Initiate Color Picker
	 *
	 * This code uses localstorage for displaying active tabs
	 */
	public function script() {
	?>
		<script>
			jQuery(document).ready(function($) {
                //Initiate Color Picker
                $('.wp-color-picker-field').wpColorPicker();
                // Switches option sections
                $('.group').hide();
                var activetab = '';
                if (typeof(localStorage) != 'undefined' ) {
                    activetab = localStorage.getItem("activetab");
                }
                if (activetab != '' && $(activetab).length ) {
                    $(activetab).fadeIn();
                } else {
                    $('.group:first').fadeIn();
                }
                $('.group .collapsed').each(function(){
                    $(this).find('input:checked').parent().parent().parent().nextAll().each(
                    function(){
                        if ($(this).hasClass('last')) {
                            $(this).removeClass('hidden');
                            return false;
                        }
                        $(this).filter('.hidden').removeClass('hidden');
                    });
                });
                if (activetab != '' && $(activetab + '-tab').length ) {
                    $(activetab + '-tab').addClass('nav-tab-active');
                }
                else {
                    $('.nav-tab-wrapper a:first').addClass('nav-tab-active');
                }
                $('.nav-tab-wrapper a').click(function(evt) {
                    $('.nav-tab-wrapper a').removeClass('nav-tab-active');
                    $(this).addClass('nav-tab-active').blur();
                    var clicked_group = $(this).attr('href');
                    if (typeof(localStorage) != 'undefined' ) {
                        localStorage.setItem("activetab", $(this).attr('href'));
                    }
                    $('.group').hide();
                    $(clicked_group).fadeIn();
                    evt.preventDefault();
                });
                $('.wpsa-browse').on('click', function (event) {
                    event.preventDefault();
                    var self = $(this);
                    // Create the media frame.
                    var file_frame = wp.media.frames.file_frame = wp.media({
                        title: self.data('uploader_title'),
                        button: {
                            text: self.data('uploader_button_text'),
                        },
                        multiple: false
                    });
                    file_frame.on('select', function () {
                        attachment = file_frame.state().get('selection').first().toJSON();
                        self.prev('.wpsa-url').val(attachment.url);
                    });
                    // Finally, open the modal
                    file_frame.open();
                });
            });
        </script>
        <style type="text/css">
            /** WordPress 3.8 Fix **/
            .form-table th { padding: 20px 10px; }
            #wpbody-content .metabox-holder { padding-top: 5px; }
        </style>
<?php
    }
    
}

function _echo_section_description($section) {
	echo (array)$section['desc'];
}
