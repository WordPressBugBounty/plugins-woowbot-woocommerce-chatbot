<?php
/**
 * AI Setup Wizard Popup Template
 * Scoped styling and JS to avoid collision.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get all custom post types for Step 7
$custom_post_types = get_post_types( array( 'public' => true, '_builtin' => false ), 'objects' );
$show_wizard_automatically = isset( $show_wizard_automatically ) ? $show_wizard_automatically : false;
?>
<div id="wpbot-wizard-overlay" class="wpbot-wizard-overlay">
	<div class="wpbot-wizard-modal">
		<!-- Progress Bar -->
		<div class="wpbot-wizard-progress-container">
			<div id="wpbot-wizard-progress-bar" class="wpbot-wizard-progress-bar" style="width: 25%;"></div>
		</div>

		<div class="wpbot-wizard-header">
			<h2 class="wpbot-wizard-title"><?php esc_html_e('AI Setup Wizard', 'woowbot-woocommerce-chatbot'); ?></h2>
			<p class="wpbot-wizard-subtitle"><?php esc_html_e('Quickly configure your AI Chatbot in a few steps', 'woowbot-woocommerce-chatbot'); ?></p>
			<button type="button" class="wpbot-wizard-close-btn" id="wpbot-wizard-close">&times;</button>
		</div>

		<div class="wpbot-wizard-body">
			<form id="wpbot-wizard-form">
				<!-- Step 1: Select AI Service -->
				<div class="wpbot-wizard-step active" data-step="1">
					<h3><?php esc_html_e('Step 1: Choose your AI Service', 'woowbot-woocommerce-chatbot'); ?></h3>
					<p class="wpbot-step-desc"><?php esc_html_e('Select which artificial intelligence provider you want to power your chatbot responses.', 'woowbot-woocommerce-chatbot'); ?></p>
					<div class="wpbot-wizard-field">
						<label for="wizard_ai_provider"><?php esc_html_e('AI Provider', 'woowbot-woocommerce-chatbot'); ?></label>
						<select id="wizard_ai_provider" name="ai_provider" class="wpbot-wizard-select">
							<option value="openai"><?php esc_html_e('OpenAI', 'woowbot-woocommerce-chatbot'); ?></option>
							<option value="gemini"><?php esc_html_e('Gemini', 'woowbot-woocommerce-chatbot'); ?></option>
						</select>
					</div>
				</div>

				<!-- Step 2: Setup API Key -->
				<div class="wpbot-wizard-step" data-step="2">
					<h3><?php esc_html_e('Step 2: API Credentials', 'woowbot-woocommerce-chatbot'); ?></h3>
					<p class="wpbot-step-desc" id="wizard_credentials_desc"><?php esc_html_e('Configure the connection credentials for your selected AI service.', 'woowbot-woocommerce-chatbot'); ?></p>
					
					<!-- API Key Input (for standard providers) -->
					<div class="wpbot-wizard-field" id="wizard_api_key_container">
						<label for="wizard_api_key" id="wizard_api_key_label"><?php esc_html_e('API Key', 'woowbot-woocommerce-chatbot'); ?></label>
						<input type="password" id="wizard_api_key" name="api_key" class="wpbot-wizard-input" placeholder="<?php esc_attr_e('sk-...', 'woowbot-woocommerce-chatbot'); ?>" />
						<p class="wpbot-field-help" id="wizard_api_key_help"><?php esc_html_e('Requires an active key from your provider.', 'woowbot-woocommerce-chatbot'); ?></p>
						<p class="wpbot-field-help wpbot-wizard-paid-note" id="wizard_api_key_paid_note"><?php esc_html_e('Note: A Paid API key is required.', 'woowbot-woocommerce-chatbot'); ?></p>
					</div>

					<!-- API verification status elements -->
					<div id="wizard-api-loader" class="wpbot-wizard-api-loader">
						<div class="wpbot-spinner wpbot-wizard-spinner-lg"></div>
						<span class="wpbot-wizard-verifying-text"><?php esc_html_e('Verifying API Connection...', 'woowbot-woocommerce-chatbot'); ?></span>
					</div>
					<div id="wizard-api-error" class="wpbot-wizard-api-error">
					</div>
				</div>

				<!-- Step 3: Content Sources for Vector Embedding -->
				<div class="wpbot-wizard-step" data-step="3">
					<h3><?php esc_html_e('Step 3: Content Sources for Vector Embedding', 'woowbot-woocommerce-chatbot'); ?></h3>
					<p class="wpbot-step-desc"><?php esc_html_e('Select which content types should be automatically prepared for the RAG search database.', 'woowbot-woocommerce-chatbot'); ?></p>
					
					<div class="wpbot-wizard-field checkbox-inline-field">
						<label class="wpbot-checkbox-container">
							<input type="checkbox" name="rag_embed_pages" value="1" checked />
							<span class="wpbot-checkmark"></span>
							<?php esc_html_e('Pages', 'woowbot-woocommerce-chatbot'); ?>
						</label>
					</div>
					
					<div class="wpbot-wizard-field checkbox-inline-field">
						<label class="wpbot-checkbox-container">
							<input type="checkbox" name="rag_embed_posts" value="1" checked />
							<span class="wpbot-checkmark"></span>
							<?php esc_html_e('Posts', 'woowbot-woocommerce-chatbot'); ?>
						</label>
					</div>

					<?php if ( ! empty( $custom_post_types ) ) : ?>
						<div class="wpbot-wizard-field">
							<label class="wpbot-wizard-cpt-label"><?php esc_html_e('Custom Post Types', 'woowbot-woocommerce-chatbot'); ?></label>
							<div class="wpbot-wizard-cpt-list">
								<?php foreach ( $custom_post_types as $cpt ) : ?>
									<label class="wpbot-checkbox-container">
										<input type="checkbox" name="rag_embed_cpts[]" value="<?php echo esc_attr( $cpt->name ); ?>" />
										<span class="wpbot-checkmark"></span>
										<?php echo esc_html( $cpt->label ); ?>
									</label>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>

					<!-- Preloader for Embedding -->
					<div id="wizard-embed-preloader" class="wpbot-wizard-embed-preloader">
						<div class="wpbot-wizard-embed-header">
							<span class="wpbot-wizard-embed-title"><?php esc_html_e('Vectorizing Content...', 'woowbot-woocommerce-chatbot'); ?></span>
							<span id="wizard-embed-percent" class="wpbot-wizard-embed-percent">0%</span>
						</div>
						<div class="wpbot-wizard-embed-track">
							<div id="wizard-embed-progress-bar" class="wpbot-wizard-embed-bar"></div>
						</div>
						<div class="wpbot-wizard-embed-status-row">
							<div class="wpbot-spinner wpbot-wizard-spinner-sm"></div>
							<span id="wizard-embed-status" class="wpbot-wizard-embed-status-text"><?php esc_html_e('Preparing queue...', 'woowbot-woocommerce-chatbot'); ?></span>
						</div>
					</div>
				</div>

				<!-- Step 4: Completion Screen -->
				<div class="wpbot-wizard-step" data-step="4">
					<div class="wpbot-wizard-complete-wrap">
						<div class="wpbot-wizard-complete-icon">🎉</div>
						<h3 class="wpbot-wizard-complete-title"><?php esc_html_e('Congratulations!', 'woowbot-woocommerce-chatbot'); ?></h3>
						<p id="wizard-embed-success-msg" class="wpbot-wizard-complete-success">
							<?php
							/* translators: %s: Embedded count element */
							printf( esc_html__( 'We have Embedded %s post types successfully', 'woowbot-woocommerce-chatbot' ), '<span id="wizard-embed-count">0</span>' );
							?>
						</p>
						<p class="wpbot-step-desc wpbot-wizard-complete-desc">
							<?php esc_html_e('Now your Chatbot is trained with your website data. You can upload additional training documents from AI Settings -> Knowledge base.', 'woowbot-woocommerce-chatbot'); ?>
						</p>
					</div>
				</div>
			</form>
		</div>

		<div class="wpbot-wizard-footer">
			<button type="button" class="wpbot-wizard-btn wpbot-wizard-btn-skip" id="wpbot-wizard-skip"><?php esc_html_e('Skip Setup', 'woowbot-woocommerce-chatbot'); ?></button>
			<div class="wpbot-wizard-nav-btns">
				<button type="button" class="wpbot-wizard-btn wpbot-wizard-btn-back" id="wpbot-wizard-back" disabled><?php esc_html_e('Back', 'woowbot-woocommerce-chatbot'); ?></button>
				<button type="button" class="wpbot-wizard-btn wpbot-wizard-btn-next" id="wpbot-wizard-next"><?php esc_html_e('Next', 'woowbot-woocommerce-chatbot'); ?></button>
			</div>
		</div>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
    
	// Move overlay to body to prevent viewport container centering bugs in WP Admin
	$('body').append($('#wpbot-wizard-overlay'));
    if( <?php echo $show_wizard_automatically ? 'true' : 'false'; ?> ) {
        $('#wpbot-wizard-overlay').css({ display: 'flex', opacity: 1 });
    }
	var currentStep = 1;
	var totalSteps = 4;
    
	// Auto open wizard logic
	var wpbot_auto_open_wizard = <?php echo $show_wizard_automatically ? 'true' : 'false'; ?>;
	if (wpbot_auto_open_wizard && sessionStorage.getItem('wpbot_wizard_skipped') !== '1') {
		$('#wpbot-wizard-overlay').css({ display: 'flex', opacity: 1 });
	}

	// Trigger wizard from settings page button
	$(document).on('click', '#wpbot-trigger-wizard', function(e) {
		e.preventDefault();
		goToStep(1);
		$('#wpbot-wizard-overlay')
			.css({ display: 'flex', opacity: 0 })
			.animate({ opacity: 1 }, 300);
	});

	// Provider detail configuration
	var providerConfig = {
		openai: {
			label: "<?php echo esc_js(__('OpenAI API Key', 'woowbot-woocommerce-chatbot')); ?>",
			placeholder: "sk-...",
			help: "<?php echo esc_js(__('Requires an active key from platform.openai.com.', 'woowbot-woocommerce-chatbot')); ?>"
		},
		gemini: {
			label: "<?php echo esc_js(__('Gemini API Key', 'woowbot-woocommerce-chatbot')); ?>",
			placeholder: "<?php echo esc_js(__('Enter Gemini API Key', 'woowbot-woocommerce-chatbot')); ?>",
			help: "<?php echo esc_js(__('Requires an active key from Google AI Studio.', 'woowbot-woocommerce-chatbot')); ?>"
		}
	};

	// Handle Provider change to adjust step 2 credentials UI
	$('#wizard_ai_provider').on('change', function() {
		var selected = $(this).val();
		$('#wizard_credentials_desc').text("<?php echo esc_js(__('Configure the connection credentials for ', 'woowbot-woocommerce-chatbot')); ?>" + $(this).find('option:selected').text() + ".");
		
		var config = providerConfig[selected];
		if (config) {
			$('#wizard_api_key_label').text(config.label);
			$('#wizard_api_key').attr('placeholder', config.placeholder);
			$('#wizard_api_key_help').text(config.help);
		}
	}).trigger('change');

	// Dismiss wizard logic
	function dismissWizard() {
		$('#wpbot-wizard-overlay').animate({ opacity: 0 }, 300, function() {
			$(this).css('display', 'none');
		});
	}

	// Go to step helper function
	function goToStep(step) {
		$('.wpbot-wizard-step').removeClass('active');
		currentStep = step;
		$('.wpbot-wizard-step[data-step="' + currentStep + '"]').addClass('active');

		// Progress bar animation
		var pct = (currentStep / totalSteps) * 100;
		$('#wpbot-wizard-progress-bar').css('width', pct + '%');

		// Enable/disable back button
		if (currentStep === 1) {
			$('#wpbot-wizard-back').prop('disabled', true);
		} else {
			$('#wpbot-wizard-back').prop('disabled', false);
		}

		// Button texts & visibility adjustments
		if (currentStep === 3) {
			$('#wpbot-wizard-next').text('<?php echo esc_js(__('Finish', 'woowbot-woocommerce-chatbot')); ?>');
			$('#wpbot-wizard-skip').show();
			$('#wpbot-wizard-back').show();
		} else if (currentStep === 4) {
			$('#wpbot-wizard-next').text('<?php echo esc_js(__('Finish Setup', 'woowbot-woocommerce-chatbot')); ?>');
			$('#wpbot-wizard-back').hide();
			$('#wpbot-wizard-skip').hide();
			// Auto-disable Site Search when wizard completes
			$('#disable_wp_chatbot_site_search').prop('checked', true);
		} else {
			$('#wpbot-wizard-next').text('<?php echo esc_js(__('Next', 'woowbot-woocommerce-chatbot')); ?>');
			$('#wpbot-wizard-skip').show();
			$('#wpbot-wizard-back').show();
		}
	}

	// Save options via AJAX
	function saveWizardData(isSkipped) {
		var formData = {
			action: 'wpbot_wizard_save',
			nonce: '<?php echo esc_attr( wp_create_nonce("wp_chatbot") ); ?>',
			is_skipped: isSkipped ? 1 : 0
		};

		if (!isSkipped) {
			// Serialize form data
			var serialized = $('#wpbot-wizard-form').serializeArray();
			$.each(serialized, function(i, field) {
				if (field.name.endsWith('[]')) {
					var cleanName = field.name.slice(0, -2);
					if (!formData[cleanName]) {
						formData[cleanName] = [];
					}
					formData[cleanName].push(field.value);
				} else {
					formData[field.name] = field.value;
				}
			});
			
			// Handle checkbox values explicitly
			formData.rag_embed_pages = $('input[name="rag_embed_pages"]').is(':checked') ? 1 : 0;
			formData.rag_embed_posts = $('input[name="rag_embed_posts"]').is(':checked') ? 1 : 0;
		}

		// Disable all buttons in footer during save
		$('.wpbot-wizard-footer button').prop('disabled', true);
		if (!isSkipped) {
			$('#wpbot-wizard-next').text('<?php echo esc_js(__('Saving...', 'woowbot-woocommerce-chatbot')); ?>');
		}

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: formData,
			success: function(response) {
				if (response.success) {
					if (isSkipped) {
						dismissWizard();
						location.reload();
					} else {
						startWizardEmbedding();
					}
				} else {
					alert(response.data || '<?php echo esc_js(__('Failed to save settings. Please try again.', 'woowbot-woocommerce-chatbot')); ?>');
					$('.wpbot-wizard-footer button').prop('disabled', false);
					$('#wpbot-wizard-next').text('<?php echo esc_js(__('Finish', 'woowbot-woocommerce-chatbot')); ?>');
				}
			},
			error: function() {
				alert('<?php echo esc_js(__('An error occurred. Please try again.', 'woowbot-woocommerce-chatbot')); ?>');
				$('.wpbot-wizard-footer button').prop('disabled', false);
				$('#wpbot-wizard-next').text('<?php echo esc_js(__('Finish', 'woowbot-woocommerce-chatbot')); ?>');
			}
		});
	}

	function startWizardEmbedding() {
		// Show preloader
		$('#wizard-embed-preloader').slideDown();
		
		// Disable all controls
		$('.wpbot-wizard-footer button').prop('disabled', true);
		$('#wpbot-wizard-close').prop('disabled', true);
		$('#wpbot-wizard-next').text('<?php echo esc_js(__('Embedding...', 'woowbot-woocommerce-chatbot')); ?>');
		
		$('#wizard-embed-percent').text('0%');
		$('#wizard-embed-progress-bar').css('width', '0%');
		$('#wizard-embed-status').text('<?php echo esc_js(__('Preparing embedding queue...', 'woowbot-woocommerce-chatbot')); ?>');

		// Fetch the queue of items to embed
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'qcld_rag_get_embed_queue',
				nonce: '<?php echo esc_attr( wp_create_nonce("wp_chatbot") ); ?>'
			},
			success: function(response) {
				if (response.success) {
					var queue = response.data;
					if (!queue || queue.length === 0) {
						// No items to embed
						$('#wizard-embed-status').text('<?php echo esc_js(__('No items found to embed.', 'woowbot-woocommerce-chatbot')); ?>');
						setTimeout(function() {
							$('#wizard-embed-count').text('0');
							$('#wizard-embed-success-msg').show();
							goToStep(4);
							$('.wpbot-wizard-footer button').prop('disabled', false);
							$('#wpbot-wizard-close').prop('disabled', false);
						}, 1000);
						return;
					}

					$('#wizard-embed-status').text('<?php echo esc_js(__('Processing ', 'woowbot-woocommerce-chatbot')); ?>0 / ' + queue.length + '<?php echo esc_js(__(' items...', 'woowbot-woocommerce-chatbot')); ?>');
					processWizardEmbedQueue(queue, 0, 0);
				} else {
					alert('<?php echo esc_js(__('Failed to fetch the embedding queue. Moving to next step.', 'woowbot-woocommerce-chatbot')); ?>');
					$('#wizard-embed-count').text('0');
					$('#wizard-embed-success-msg').show();
					goToStep(4);
					$('.wpbot-wizard-footer button').prop('disabled', false);
					$('#wpbot-wizard-close').prop('disabled', false);
				}
			},
			error: function() {
				alert('<?php echo esc_js(__('An error occurred while fetching the embedding queue. Moving to next step.', 'woowbot-woocommerce-chatbot')); ?>');
				$('#wizard-embed-count').text('0');
				$('#wizard-embed-success-msg').show();
				goToStep(4);
				$('.wpbot-wizard-footer button').prop('disabled', false);
				$('#wpbot-wizard-close').prop('disabled', false);
			}
		});
	}

	function processWizardEmbedQueue(queue, index, successCount) {
		if (index >= queue.length) {
			$('#wizard-embed-status').text('<?php echo esc_js(__('Embedding complete! Total processed: ', 'woowbot-woocommerce-chatbot')); ?>' + successCount + ' / ' + queue.length);
			setTimeout(function() {
				$('#wizard-embed-count').text(successCount);
				$('#wizard-embed-success-msg').show();
				goToStep(4);
				$('.wpbot-wizard-footer button').prop('disabled', false);
				$('#wpbot-wizard-close').prop('disabled', false);
			}, 1000);
			return;
		}

		var item = queue[index];
		var progress = Math.round(((index + 1) / queue.length) * 100);

		$('#wizard-embed-status').text('<?php echo esc_js(__('Embedding document (', 'woowbot-woocommerce-chatbot')); ?>' + (index + 1) + ' / ' + queue.length + ')...');
		$('#wizard-embed-progress-bar').css('width', progress + '%');
		$('#wizard-embed-percent').text(progress + '%');

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'qcld_rag_process_item',
				nonce: '<?php echo esc_attr( wp_create_nonce("wp_chatbot") ); ?>',
				item_id: item.id,
				item_type: item.type
			},
			success: function(response) {
				// We consider either a successful return or skip as a processed item
				var increment = 0;
				if (response.success) {
					increment = 1;
				}
				processWizardEmbedQueue(queue, index + 1, successCount + increment);
			},
			error: function() {
				// Continue to next item on failure, so wizard doesn't hang
				processWizardEmbedQueue(queue, index + 1, successCount);
			}
		});
	}

	// Skip Button Click
	$('#wpbot-wizard-skip, #wpbot-wizard-close').on('click', function(e) {
		e.preventDefault();
	
		dismissWizard();
		
	});

	// Next Button Click
	$('#wpbot-wizard-next').on('click', function(e) {
		e.preventDefault();
		
		// If we are on Step 4 (Congratulations), clicking Next should close and reload.
		if (currentStep === 4) {
			// Ensure Site Search is disabled before closing
			$('#disable_wp_chatbot_site_search').prop('checked', true);
			dismissWizard();
			location.reload();
			return;
		}

		// Basic Validation for credentials step (Step 2) and AJAX verification
		if (currentStep === 2) {
			var provider = $('#wizard_ai_provider').val();
			var apiKey = $('#wizard_api_key').val().trim();

			if (apiKey === '') {
				alert('<?php echo esc_js(__('Please enter your API Key before continuing.', 'woowbot-woocommerce-chatbot')); ?>');
				return;
			}

			// Show loader and hide old error
			$('#wizard-api-loader').show();
			$('#wizard-api-error').hide();
			$('.wpbot-wizard-footer button').prop('disabled', true);

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wpbot_wizard_verify_key',
					nonce: '<?php echo esc_attr( wp_create_nonce("wp_chatbot") ); ?>',
					ai_provider: provider,
					api_key: apiKey
				},
				success: function(response) {
					$('#wizard-api-loader').hide();
					$('.wpbot-wizard-footer button').prop('disabled', false);
					
					if (response.success) {
						goToStep(3);
					} else {
						$('#wizard-api-error').text(response.data || '<?php echo esc_js(__('Failed to verify API key. Please check your credentials.', 'woowbot-woocommerce-chatbot')); ?>').show();
					}
				},
				error: function(xhr, status, error) {
					$('#wizard-api-loader').hide();
					$('.wpbot-wizard-footer button').prop('disabled', false);
					$('#wizard-api-error').text('<?php echo esc_js(__('An error occurred during verification: ', 'woowbot-woocommerce-chatbot')); ?>' + error).show();
				}
			});
			return;
		}

		if (currentStep === 3) {
			// Save wizard data and transition to Step 4 on success
			saveWizardData(false);
			return;
		}

		if (currentStep < totalSteps) {
			goToStep(currentStep + 1);
		}
	});

	// Back Button Click
	$('#wpbot-wizard-back').on('click', function(e) {
		e.preventDefault();
		if (currentStep > 1 && currentStep < 4) {
			goToStep(currentStep - 1);
		}
	});
});
</script>
