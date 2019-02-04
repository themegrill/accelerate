<?php
/**
 * Extend WP_Customize_Control for text area control option.
 *
 * Class Accelerate_Text_Area_Control
 *
 * @since 1.3.9
 */

// Adding Text Area Control For Use In Customizer.
class Accelerate_Text_Area_Control extends WP_Customize_Control {

	public $type = 'text_area';

	public function render_content() {
		?>
		<label>
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<textarea rows="5" style="width:100%;" <?php $this->link(); ?>><?php echo esc_textarea( $this->value() ); ?></textarea>
		</label>
		<?php
	}

}
