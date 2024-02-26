<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestTemplatesAnswerForm extends TestCase {

	use Testcases\Common;

	public function testAnswerForm() {
		$question_id = $this->insert_question();
		// Set up the action hook callback.
		$callback_triggered = false;
		add_action( 'wordpress_social_login', function() use ( &$callback_triggered ) {
			$callback_triggered = true;
		} );

		// Test 1.
		$this->setRole( 'subscriber' );
		$callback_triggered = false;
		$this->assertFalse( $callback_triggered );
		$this->go_to( '/?post_type=question&p=' . $question_id );
		ob_start();
		ap_get_template_part( 'answer-form' );
		$result = ob_get_clean();
		$ajax_query = wp_json_encode(
			array(
				'ap_ajax_action' => 'load_tinymce',
				'question_id'    => get_question_id(),
			)
		);
		$this->assertStringContainsString( '<div id="answer-form-c" class="ap-minimal-editor">', $result );
		$this->assertStringContainsString( '<div class="ap-avatar ap-pull-left">', $result );
		$this->assertStringContainsString( '<div id="ap-drop-area" class="ap-cell ap-form-c clearfix">', $result );
		$this->assertStringContainsString( '<div class="ap-cell-inner">', $result );
		$this->assertStringContainsString( '<div class="ap-minimal-placeholder">', $result );
		$this->assertStringContainsString( '<div class="ap-dummy-editor"></div>', $result );
		$this->assertStringContainsString( '<div class="ap-dummy-placeholder">Write your answer.</div>', $result );
		$this->assertStringContainsString( '<div class="ap-editor-fade" ap="loadEditor" data-apquery="' . esc_js( $ajax_query ) . '"></div>', $result );
		$this->assertStringContainsString( '<div id="ap-form-main">', $result );
		$this->assertStringNotContainsString( '<div class="ap-login">', $result );
		$this->assertStringNotContainsString( '<div class="ap-login-buttons">', $result );
		$this->assertFalse( $callback_triggered );
		$this->assertFalse( did_action( 'wordpress_social_login' ) > 0 );
		$this->logout();

		// Test 2.
		$callback_triggered = false;
		$this->assertFalse( $callback_triggered );
		$this->go_to( '/?post_type=question&p=' . $question_id );
		ob_start();
		ap_get_template_part( 'answer-form' );
		$result = ob_get_clean();
		$this->assertStringNotContainsString( '<div id="answer-form-c" class="ap-minimal-editor">', $result );
		$this->assertStringContainsString( '<div class="ap-login">', $result );
		$this->assertStringContainsString( '<div class="ap-login-buttons">', $result );
		$this->assertTrue( $callback_triggered );
		$this->assertTrue( did_action( 'wordpress_social_login' ) > 0 );
	}
}
