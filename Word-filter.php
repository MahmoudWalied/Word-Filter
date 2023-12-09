<?php

/*
Plugin Name: Word Filter
Description: A plugin to filter the words
Author: Mahmoud Walied
Version: 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} //Exit if access directly

class WordFilter {
	public function __construct() {
		add_action(
			'admin_menu',
			array( $this, 'ourMenu' ) );
		add_action(
			'admin_init',
			array( $this, 'replacementOptions' )
		);
		if ( get_option( 'plugin_words_to_filter' ) ) {
			add_filter( 'the_content',
				array( $this, 'replacementText' ) );
		}
	}

	public function ourMenu() {
		$loadMainMenu = add_menu_page(
			'Words to Filter',
			'Word Filter',
			'manage_options',
			'wordFilter',
			array( $this, 'wordFilterPage' ),
			'dashicons-insert',
			1,
		);

		add_submenu_page(
			'wordFilter',
			'Words to Filter',
			'Word Filter',
			'manage_options',
			'wordFilter',
			array( $this, 'wordFilterPage' ),
		);

		add_submenu_page(
			'wordFilter',
			'Replacement Text',
			'Replacement Text',
			'manage_options',
			'wordFilterReplacement',
			array( $this, 'replacementHtml' ),
		);

		add_action(
			"load-{$loadMainMenu}",
			array( $this, 'mainPageAssets' ) );

	}

	public function replacementOptions() {

		add_settings_section(
			'replacement-text-section',
			null,
			null,
			'word-filter-replacement',
		);
		register_setting(
			'replacement_fields',
			'replacementText'
		);

		add_settings_field(
			'replacement_texts',
			'Text to filter',
			array( $this, 'inputHtml' ),
			'word-filter-replacement',
			'replacement-text-section',
		);
	}

	public function inputHtml() {
		?>
			<input type="text" name="replacementText" value="<?php echo get_option( 'replacementText', '***' ); ?>">
			<p>Leave blank not to echo anything instead</p>
		<?php
	}

	public function replacementHtml() {
		?>
			<div class="wrap">
				<form action="options.php" method="post">
					<h1>Replace</h1>
			<?php
			settings_errors();
			settings_fields( 'replacement_fields' );
			do_settings_sections( 'word-filter-replacement' );
			submit_button();
			?>
				</form>
			</div>
		<?php
	}

	public function mainPageAssets() {
		wp_enqueue_style(
			'filterAdminCss',
			plugin_dir_url( __FILE__ ) . 'style.css'
		);
	}

	public function wordFilterPage() {
		?>

			<div class="wrap">
				<h1>Word Filter</h1>
		  <?php
		  if ( isset( $_POST['justsubmitted'] ) ) {
			  if ( $_POST['justsubmitted'] == "true" ) {
				  $this->handleForm();
			  }
		  }
		  ?>

				<form action="" method="POST">
					<input type="hidden" name="justsubmitted" value="true">
			<?php wp_nonce_field( 'filterVerify', 'filterVerification' ); ?>
					<label for="plugin_words_to_filter">Enter comma seperated words here</label>
					<div class="textarea-flex-container">
						<textarea id="plugin_words_to_filter" class="flex-object" name="plugin_words_to_filter"
											placeholder="bad, good, player"><?php echo trim( esc_textarea( get_option( 'plugin_words_to_filter' ) ) ); ?></textarea>
					</div>
					<input type="submit" name="submit" class="button button-primary" value="Save Changes">
				</form>
			</div>
		<?php
	}

	public function handleForm() {
		if ( wp_verify_nonce( $_POST['filterVerification'], 'filterVerify' ) and
		     current_user_can( 'manage_options' ) ) {
			update_option( 'plugin_words_to_filter', esc_html( $_POST['plugin_words_to_filter'] ) );
			?>
					<div class="updated">Your options have been updated</div>
		<?php } else { ?>
					<div class="errors">Your options are not valid</div>
		<?php }

	}

	public function replacementText( $content ) {
		$badWords     = explode( ',', get_option( 'plugin_words_to_filter' ) );
		$trimmedWords = array_map( 'trim', $badWords );

		return str_ireplace( $trimmedWords, esc_html( get_option( 'replacementText' ) ), $content );
	}

}

$wordFilter = new WordFilter();