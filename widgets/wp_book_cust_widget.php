<?php
/**
 * This class extends from WP_Widget class defines all code necessary to run this custom widget.
 *
 * @since           1.0.0
 * @package         Wpb
 * @subpackage      Wpb/widgets
 * @author          Akshay Meher <akshay.meher@hbwl.com>
 * Description      Create custom widget to display books of selected category in the sidebar.
 */

// Register custom widget named WP Book Widget .
function wp_book_widget_init() {
	register_widget( 'WPB_Book_Widget' );
}

/**
 * Summary of WPB_Book_Widget
 */
class WPB_Book_Widget extends WP_Widget {

	/**Construct Widget Options. */
	public function __construct() {
		$widgetOps = array(
			'classname'                   => 'wp_book_widget',
			'description'                 => 'To display books of selected category.',
			'customize_selective_refresh' => true,
		);
		parent::__construct( 'wp_book_widget', 'WP Book Widget', $widgetOps );

	}


	/**
	 * The widget create form (for the backend ).
	 *
	 * @param      array $instance   Current settings.
	 *
	 * @return     void.
	 */
	public function form( $instance ) {
		// Set widget defaults.
		$defaults = array(
			'title'  => '',
			'text'   => '',
			'select' => '',
		);
		// Parse current settings with defaults.
		extract( wp_parse_args( (array) $instance, $defaults ) ); ?>

			<?php
			// Text Field.
			?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>"><?php __( 'Text:', 'wp-book' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'text' ) ); ?>" type="text" value="<?php echo esc_attr( $text ); ?>" />
		</p>

		<?php
		// Dropdown.
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'select' ); ?>"><?php __( 'Select', 'wp-book' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'select' ); ?>" id="<?php echo $this->get_field_id( 'select' ); ?>" class="widefat">
			<?php
			$categories = get_terms( array( 'taxonomy' => 'Book Category' ) );
			foreach ( $categories as $category ) {
				echo '<option value="' . esc_attr( $category->name ) . '" id="' . esc_attr( $category->name ) . '" ' . selected( $select, $category->name, false ) . '>' . $category->name . '</option>';
			}
			?>
			</select>
			</ul>
		</p>
			<?php

	}


	/**
	 * The widget update form (for the backend ).
	 *
	 * @param    array $newInstance New settings for this instance as input by the user via WP_Widget::form().
	 * @param    array $oldInstance Old settings for this instance.
	 *
	 * @return void.
	 */
	public function update( $newInstance, $oldInstance ) {
		$instance          = $oldInstance;
		$instance['title'] = '';
		if ( isset( $newInstance['title'] ) == true ) {
			$instance['title'] = wp_strip_all_tags( $newInstance['title'] );
		}

		$instance['text'] = '';
		if ( isset( $newInstance['text'] ) == true ) {
			$instance['text'] = wp_strip_all_tags( $newInstance['text'] );
		}

		$instance['select'] = '';
		if ( isset( $newInstance['select'] ) == true ) {
			$instance['select'] = wp_strip_all_tags( $newInstance['select'] );
		}

		return $instance;

	}//end update()


	/**
	 * The widget display form (for the backend ).
	 *
	 * @param    array $args     Display arguments including 'before_title', 'after_title', 'before_widget', and 'after_widget'.
	 * @param    array $instance The settings for the particular instance of the widget.
	 *
	 * @return void.
	 */
	public function widget( $args, $instance ) {
		extract( $args );

		// Check the widget options.
		$text = '';
		if ( isset( $instance['text'] ) == true ) {
			$text = $instance['text'];
		}

		$select = '';
		if ( isset( $instance['select'] ) == true ) {
			$select = $instance['select'];

		}

		// Display text field.
		if ( $text == true ) {
			echo $args['before_widget'] . $args['before_title'] . $text . $args['after_title'];
		}

		// Display select field.
		$id_count = 0;
		if ( $select == true ) {
			$args  = array(
				'post_type'   => 'book',
				'post_status' => 'publish',
				'tax_query'   => array(
					array(
						'taxonomy' => 'Book Category',
						'field'    => 'slug',
						'terms'    => $select,
					),
				),
			);
			$query = new WP_Query( $args );

			if ( $query->have_posts() == true ) {

				while ( $query->have_posts() == true ) {
					$query->the_post();
					$id_count++;
					?>
					<h5><a href="<?php the_permalink(); ?>"><?php echo $id_count . '.' . get_the_title(); ?></a></h5>
					<?php
				}
			}
			wp_reset_query();
		}
	}
}
