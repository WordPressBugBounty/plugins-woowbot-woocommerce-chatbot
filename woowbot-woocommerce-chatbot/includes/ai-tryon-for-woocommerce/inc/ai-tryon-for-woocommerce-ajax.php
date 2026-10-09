<?php
defined('ABSPATH') or die("No direct script access!");

add_action( 'wp_ajax_qcld_woo_chatbot_test_openai_connection', 'qcld_woo_chatbot_test_openai_connection' );
function qcld_woo_chatbot_test_openai_connection() {
    check_ajax_referer( 'qcld_woo_chatbot_admin_nonce', 'security' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => esc_html__( 'Permission denied.', 'woowbot-woocommerce-chatbot' ) ) );
    }
    
    $api_key = isset( $_POST['api_key'] ) ? sanitize_text_field( wp_unslash( $_POST['api_key'] ) ) : '';
    
    if ( empty( $api_key ) ) {
        wp_send_json_error( array( 'message' => esc_html__('API Key is empty.', 'woowbot-woocommerce-chatbot') ) );
    }

    $response = wp_remote_get( 'https://api.openai.com/v1/models', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key
        ),
        'timeout' => 30
    ) );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => $response->get_error_message() ) );
    }

    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );

    if ( isset( $data['error'] ) ) {
        wp_send_json_error( array( 'message' => $data['error']['message'] ) );
    }

    if ( isset( $data['data'] ) ) {
        $image_models = array();
        $video_models = array();
        
        foreach ( $data['data'] as $model ) {
            $model_id = $model['id'];
            $lowerM = strtolower($model_id);
            
            // Check if it qualifies as an image model
            if ( stripos($lowerM, 'dall') !== false || stripos($lowerM, 'midjourney') !== false || stripos($lowerM, 'flux') !== false || stripos($lowerM, 'sd') !== false || stripos($lowerM, 'stable') !== false || stripos($lowerM, 'imagen') !== false || stripos($lowerM, 'ideogram') !== false || stripos($lowerM, 'image') !== false || stripos($lowerM, 'vision') !== false ) {
                $image_models[] = $model_id;
            }
            
            // Check if it qualifies as a video model
            if ( stripos($lowerM, 'sora') !== false || stripos($lowerM, 'runway') !== false || stripos($lowerM, 'luma') !== false || stripos($lowerM, 'gen') !== false || stripos($lowerM, 'kling') !== false || stripos($lowerM, 'video') !== false || stripos($lowerM, 'minimax') !== false || stripos($lowerM, 'haiper') !== false || stripos($lowerM, 'veo') !== false ) {
                $video_models[] = $model_id;
            }
        }
        
        $saved_image = get_option('qcld_woo_chatbot_openai_image_model', '');
        $saved_video = get_option('qcld_woo_chatbot_openai_video_model', '');
        if (!empty($saved_image) && !in_array($saved_image, $image_models)) $image_models[] = $saved_image;
        if (!empty($saved_video) && !in_array($saved_video, $video_models)) $video_models[] = $saved_video;
        
        $image_models = array_values(array_unique($image_models));
        sort($image_models);
        $video_models = array_values(array_unique($video_models));
        sort($video_models);
        
        update_option( 'qcld_woo_chatbot_openai_available_image_models', $image_models );
        update_option( 'qcld_woo_chatbot_openai_available_video_models', $video_models );
        wp_send_json_success( array( 'message' => esc_html__('Connection successful!', 'woowbot-woocommerce-chatbot'), 'image_models' => $image_models, 'video_models' => $video_models ) );
    } else {
        if ( isset($data['error']['message']) ) {
            wp_send_json_error( array( 'message' => esc_html__('API Error: ', 'woowbot-woocommerce-chatbot') . $data['error']['message'] ) );
        } else {
            $status_code = wp_remote_retrieve_response_code( $response );
            $raw_body = wp_remote_retrieve_body( $response );
            /* translators: 1: HTTP status code, 2: Response body details */
            wp_send_json_error( array( 'message' => sprintf( esc_html__('Connection failed (HTTP %1$s). Details: %2$s', 'woowbot-woocommerce-chatbot'), $status_code, esc_html(substr($raw_body, 0, 200)) ) ) );
        }
    }
}

add_action( 'wp_ajax_qcld_woo_chatbot_test_gemini_connection', 'qcld_woo_chatbot_test_gemini_connection' );
function qcld_woo_chatbot_test_gemini_connection() {
    check_ajax_referer( 'qcld_woo_chatbot_admin_nonce', 'security' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => esc_html__( 'Permission denied.', 'woowbot-woocommerce-chatbot' ) ) );
    }
    
    $api_key = isset( $_POST['api_key'] ) ? sanitize_text_field( wp_unslash( $_POST['api_key'] ) ) : '';
    
    if ( empty( $api_key ) ) {
        wp_send_json_error( array( 'message' => esc_html__('API Key is empty.', 'woowbot-woocommerce-chatbot') ) );
    }

    // Call ModelService.ListModels
    $test_url = 'https://generativelanguage.googleapis.com/v1beta/models?key=' . $api_key;
    
    $response = wp_remote_get( $test_url, array(
        'timeout' => 30
    ) );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => $response->get_error_message() ) );
    }

    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );

    if ( isset( $data['error'] ) ) {
        $msg = isset($data['error']['message']) ? $data['error']['message'] : esc_html__('API Error', 'woowbot-woocommerce-chatbot');
        wp_send_json_error( array( 'message' => $msg ) );
    }

    $image_models = array();
    $video_models = array();

    if ( isset( $data['models'] ) && is_array( $data['models'] ) && !empty( $data['models'] ) ) {
        $all_supported = array();
        
        foreach ( $data['models'] as $model ) {
            // Add models that support generateContent or predictLongRunning (Veo)
            if ( isset($model['supportedGenerationMethods']) && (in_array('generateContent', $model['supportedGenerationMethods']) || in_array('predictLongRunning', $model['supportedGenerationMethods'])) ) {
                $model_name = str_replace('models/', '', $model['name']);
                $lowerM = strtolower($model_name);
                
                $all_supported[] = $model_name;
                
                // Check if it qualifies as an image model
                if ( stripos($lowerM, 'image') !== false ||  stripos($lowerM, 'flash') !== false || stripos($lowerM, 'pro') !== false || stripos($lowerM, 'imagen') !== false || stripos($lowerM, 'ideogram') !== false ) {
                    $image_models[] = $model_name;
                }
                
                // Check if it qualifies as a video model
                if ( stripos($lowerM, 'veo') !== false ) {
                    $video_models[] = $model_name;
                }
            }
        }
        
        // If keyword filtering yielded empty arrays, fallback to listing all available supported models
        if ( empty($image_models) ) $image_models = $all_supported;
        if ( empty($video_models) ) $video_models = $all_supported;
        
    } else {
        // Absolute fallback if API succeeds but returns empty models array (very rare)
        $base_fallback = array('gemini-1.5-flash', 'gemini-1.5-pro', 'gemini-2.0-flash-exp');
        $image_models = $base_fallback;
        $video_models = array('veo-3.1-generate-preview', 'veo-3.1-fast-generate-preview', 'veo-2.0-generate'); // Veo specific fallbacks
    }

    // Ensure previously saved models are not lost from the list
    $saved_image = get_option('qcld_woo_chatbot_gemini_image_model', '');
    $saved_video = get_option('qcld_woo_chatbot_gemini_video_model', '');
    if (!empty($saved_image) && !in_array($saved_image, $image_models)) $image_models[] = $saved_image;
    if (!empty($saved_video) && !in_array($saved_video, $video_models)) $video_models[] = $saved_video;
    
    // Clean up duplicates and sort
    $image_models = array_values(array_unique($image_models));
    sort($image_models);
    
    $video_models = array_values(array_unique($video_models));
    sort($video_models);
    
    update_option( 'qcld_woo_chatbot_gemini_available_image_models', $image_models );
    update_option( 'qcld_woo_chatbot_gemini_available_video_models', $video_models );
    
    wp_send_json_success( array( 
        'message'       => esc_html__('Connection successful!', 'woowbot-woocommerce-chatbot'), 
        'image_models'  => $image_models,
        'video_models'  => $video_models
    ) );
}

add_action( 'wp_ajax_qcld_woo_chatbot_verify_model_capability', 'qcld_woo_chatbot_verify_model_capability' );
function qcld_woo_chatbot_verify_model_capability() {
    check_ajax_referer( 'qcld_woo_chatbot_admin_nonce', 'security' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => esc_html__( 'Permission denied.', 'woowbot-woocommerce-chatbot' ) ) );
    }

    $provider           = isset($_POST['provider']) ? sanitize_text_field(wp_unslash($_POST['provider'])) : '';
    $generation_type    = isset($_POST['generation_type']) ? sanitize_text_field(wp_unslash($_POST['generation_type'])) : 'image';
    $model              = isset($_POST['model']) ? sanitize_text_field(wp_unslash($_POST['model'])) : '';
    $api_key            = isset($_POST['api_key']) ? sanitize_text_field(wp_unslash($_POST['api_key'])) : '';

    if (empty($api_key) || empty($model)) {
        wp_send_json_error(array('message' => esc_html__('Missing API key or model.', 'woowbot-woocommerce-chatbot')));
    }
    
    // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
    @set_time_limit(60);

    if ( $provider === 'openai' ) {
        $response = wp_remote_post( 'https://api.openai.com/v1/images/generations', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
            ),
            'body' => json_encode(array(
                'model'  => $model,
                'prompt' => 'A tiny green dot, minimalist.',
                'n'      => 1,
                'size'   => '1024x1024'
            )),
            'timeout'    => 45
        ));

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( array( 'message' => $response->get_error_message() ) );
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( isset( $body['data'][0]['url'] ) || isset( $body['data'][0]['b64_json'] ) ) {
            wp_send_json_success();
        } else {
            /* translators: %s: HTTP response code */
            $msg = isset($body['error']['message']) ? $body['error']['message'] : sprintf( esc_html__('Generation failed. (HTTP %s)', 'woowbot-woocommerce-chatbot'), wp_remote_retrieve_response_code($response) );
            wp_send_json_error( array( 'message' => $msg ) );
        }
    } elseif ( $provider === 'gemini' ) {
        $gemini_model = str_replace('models/', '', $model);
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $gemini_model . '?key=' . $api_key;
        
        $response = wp_remote_get( $url, array('timeout' => 15) );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( array( 'message' => $response->get_error_message() ) );
        }

        $code = wp_remote_retrieve_response_code($response);
        if ( $code == 200 ) {
            wp_send_json_success(array('message' => esc_html__('Model verified successfully.', 'woowbot-woocommerce-chatbot')));
        } else {
            $body = json_decode( wp_remote_retrieve_body( $response ), true );
            /* translators: %s: HTTP response code */
            $msg = isset($body['error']['message']) ? $body['error']['message'] : sprintf( esc_html__('Model verification failed. (HTTP %s)', 'woowbot-woocommerce-chatbot'), $code );
            wp_send_json_error( array( 'message' => $msg ) );
        }
    } else {
        wp_send_json_error(array('message' => esc_html__('Invalid provider.', 'woowbot-woocommerce-chatbot')));
    }
}


// 4. Secure AJAX Endpoints for Multi-Step AI Try-On Workflow
// Step 1: Pre-Process Visual Assets & Synthesize High-Fidelity Prompt
add_action( 'wp_ajax_qcld_woo_chatbot_prepare_generation', 'qcld_woo_chatbot_prepare_generation_backend' );
add_action( 'wp_ajax_nopriv_qcld_woo_chatbot_prepare_generation', 'qcld_woo_chatbot_prepare_generation_backend' );
add_action( 'wp_ajax_qcld_woo_chatbot_upload_reference_photo', 'qcld_woo_chatbot_prepare_generation_backend' );
add_action( 'wp_ajax_nopriv_qcld_woo_chatbot_upload_reference_photo', 'qcld_woo_chatbot_prepare_generation_backend' );

function qcld_woo_chatbot_prepare_generation_backend() {
    // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
    @ini_set( 'memory_limit', '512M' );
    @set_time_limit( 120 );

    try {
        check_ajax_referer( 'qcld_woo_chatbot_generation_nonce', 'security' );

        // 1. Enforce Logged-In Requirement if configured
        $require_login = get_option( 'qcld_woo_chatbot_logged_in_users_only', '0' );
        if ( $require_login === '1' && ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => esc_html__( 'You must be logged in to use this AI Try-On feature.', 'woowbot-woocommerce-chatbot' ) ) );
        }

        $product_id    = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
        $product_name  = isset( $_POST['product_name'] ) ? sanitize_text_field( wp_unslash( $_POST['product_name'] ) ) : '';
        $user_prompt   = isset( $_POST['prompt'] ) ? sanitize_text_field( wp_unslash( $_POST['prompt'] ) ) : '';
        $bg_preference = isset( $_POST['bg_preference'] ) ? sanitize_text_field( wp_unslash( $_POST['bg_preference'] ) ) : 'Studio';
        $output_style  = isset( $_POST['output_style'] ) ? sanitize_text_field( wp_unslash( $_POST['output_style'] ) ) : 'Model Shoot';

        // Image payloads: only unslash (sanitize_textarea_field can corrupt long base64 strings)
        $second_image  = isset( $_POST['second_image'] ) ? trim( wp_unslash( $_POST['second_image'] ) ) : ( isset( $_POST['image_data'] ) ? trim( wp_unslash( $_POST['image_data'] ) ) : '' );

        $temp_files = array();
        register_shutdown_function( function() use ( &$temp_files ) {
            foreach ( $temp_files as $tf ) {
                if ( $tf && file_exists( $tf ) ) {
                    @unlink( $tf );
                }
            }
        } );

        // Only the shopper's photo is handled here; the product image is looked up server-side in Step 2.
        $second_image_file = qcld_woo_chatbot_resolve_image_to_file( $second_image, 0, $temp_files );

        if ( ! empty( $second_image ) && empty( $second_image_file ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Could not read the uploaded reference photo. Please try a different image (JPG/PNG/WebP).', 'woowbot-woocommerce-chatbot' ) ) );
        }

        // 2. Persist the reference photo under a stable "tryon_ref_" name so Step 2 can resolve it.
        //    (Files created by the resolver are request-scoped temp files and are deleted on shutdown.)
        $ref_id = '';
        if ( ! empty( $second_image_file ) && file_exists( $second_image_file ) ) {
            if ( strpos( basename( $second_image_file ), 'tryon_ref_' ) === 0 ) {
                $ref_id = basename( $second_image_file );
            } else {
                if ( filesize( $second_image_file ) > 15 * 1024 * 1024 ) {
                    wp_send_json_error( array( 'message' => esc_html__( 'Image size is too large (maximum 15MB).', 'woowbot-woocommerce-chatbot' ) ) );
                }

                $upload_dir = wp_upload_dir();
                $temp_dir   = trailingslashit( $upload_dir['basedir'] ) . 'qcld_tryon_temp';
                if ( ! file_exists( $temp_dir ) ) {
                    wp_mkdir_p( $temp_dir );
                    @file_put_contents( $temp_dir . '/index.php', '<?php // Silence is golden' );
                }

                // Clean up staged files older than 2 hours
                $existing_files = @glob( $temp_dir . '/tryon_ref_*' );
                if ( ! empty( $existing_files ) ) {
                    $now = time();
                    foreach ( $existing_files as $f ) {
                        if ( is_file( $f ) && ( $now - @filemtime( $f ) > 7200 ) ) {
                            @unlink( $f );
                        }
                    }
                }

                $ext = strtolower( pathinfo( $second_image_file, PATHINFO_EXTENSION ) );
                if ( ! in_array( $ext, array( 'jpg', 'jpeg', 'png', 'webp' ), true ) ) {
                    $ext = 'jpg';
                }
                $target_file = $temp_dir . '/tryon_ref_' . wp_generate_password( 16, false ) . '_' . time() . '.' . $ext;
                if ( ! @copy( $second_image_file, $target_file ) ) {
                    wp_send_json_error( array( 'message' => esc_html__( 'Could not save temporary reference image on server.', 'woowbot-woocommerce-chatbot' ) ) );
                }
                $ref_id = basename( $target_file );
            }
        }

        // 3. Resolve Product Details
        if ( ! empty( $product_id ) && intval( $product_id ) > 0 && empty( $product_name ) ) {
            $product_post = get_post( intval( $product_id ) );
            if ( $product_post ) {
                $product_name = $product_post->post_title;
            }
        }

        // 4. Synthesize Prompt with Visual Aesthetics & Configured Base Prompt
        $provider        = get_option( 'qcld_woo_chatbot_api_provider', 'openai' );
        $configured_base = ( $provider === 'openai' )
            ? get_option( 'qcld_woo_chatbot_openai_system_prompt', '' )
            : get_option( 'qcld_woo_chatbot_gemini_prompt', '' );

        $replacements = array(
            '{product_name}'       => (string) ( $product_name ? $product_name : 'an apparel/product item' ),
            '{product_image_url}'  => '',
            '{product_image}'      => '',
            '{second_image_url}'   => ! empty( $ref_id ) ? '[Uploaded Reference Image]' : '',
            '{second_image}'       => ! empty( $ref_id ) ? '[Uploaded Reference Image]' : '',
            '{user_image_url}'     => ! empty( $ref_id ) ? '[Uploaded Reference Image]' : '',
            '{scenario}'           => (string) $user_prompt,
            '{user_prompt}'        => (string) $user_prompt,
            '{prompt}'             => (string) $user_prompt,
            '{bg_preference}'      => (string) ( $bg_preference ? $bg_preference : 'Studio' ),
            '{background}'         => (string) ( $bg_preference ? $bg_preference : 'Studio' ),
            '{output_style}'       => (string) ( $output_style ? $output_style : 'Model Shoot' ),
            '{style}'              => (string) ( $output_style ? $output_style : 'Model Shoot' ),
        );

        $synthesized_prompt = '';
        if ( ! empty( $configured_base ) ) {
            $synthesized_prompt = str_replace( array_keys( $replacements ), array_values( $replacements ), $configured_base );
            if ( ! empty( $user_prompt ) && strpos( $configured_base, '{scenario}' ) === false && strpos( $configured_base, '{user_prompt}' ) === false && strpos( $configured_base, '{prompt}' ) === false ) {
                $synthesized_prompt .= ' ' . $user_prompt;
            }
        } elseif ( ! empty( $ref_id ) ) {
            $synthesized_prompt = "Create a seamless and photorealistic eCommerce virtual try-on photograph. " .
                "The product is " . ( $product_name ? $product_name : 'an apparel/product item' ) . ". " .
                "Realistically fit, dress, and showcase the product onto the model/person with natural proportions, perspective, drape, lighting, shadows, and reflections. " .
                "Accurately preserve the person's face, identity, hair, and facial features without distortion. " .
                "Setting/Background: " . $bg_preference . ". Style: " . $output_style . ".";
            if ( ! empty( $user_prompt ) ) {
                $synthesized_prompt .= " Additional instructions: " . $user_prompt;
            }
        } else {
            $synthesized_prompt = "Create a professional fashion and eCommerce product presentation. " .
                "Product: " . ( $product_name ? $product_name : 'eCommerce item' ) . ". " .
                "Setting/Background: " . $bg_preference . ", Style: " . $output_style . ".";
            if ( ! empty( $user_prompt ) ) {
                $synthesized_prompt .= " Additional instructions: " . $user_prompt;
            }
        }

        wp_send_json_success( array(
            'step'               => 1,
            'ref_id'             => $ref_id,
            'product_name'       => $product_name,
            'synthesized_prompt' => $synthesized_prompt,
            'message'            => esc_html__( 'Prompt & visual assets prepared successfully. Ready for AI rendering.', 'woowbot-woocommerce-chatbot' )
        ) );
    } catch ( \Throwable $e ) {
        wp_send_json_error( array( 'message' => esc_html__( 'Preparation error: ', 'woowbot-woocommerce-chatbot' ) . $e->getMessage() ) );
    }
}

// Step 2 / Main Endpoint: Multi-Step AI Media Generation
add_action( 'wp_ajax_qcld_woo_chatbot_generate_media', 'qcld_woo_chatbot_handle_generation_backend' );
add_action( 'wp_ajax_nopriv_qcld_woo_chatbot_generate_media', 'qcld_woo_chatbot_handle_generation_backend' );

function qcld_woo_chatbot_get_appropriate_model( $provider, $generation_type ) {
    if ( $provider === 'openai' ) {
        return get_option('qcld_woo_chatbot_openai_image_model', 'gpt-image-1-mini');
    } elseif ( $provider === 'gemini' ) {
        return str_replace('models/', '', get_option('qcld_woo_chatbot_gemini_image_model', 'imagen-3.0-generate-002'));
    }
    return '';
}

function qcld_woo_chatbot_ensure_image_inline_data( $image_data ) {
    if ( empty( $image_data ) ) {
        return null;
    }

    try {
        // 1. If already Base64 data URI
        if ( stripos( $image_data, 'data:image' ) === 0 ) {
            if ( preg_match( '/data:(image\/[^;]+);base64,(.+)/', $image_data, $matches ) ) {
                return array(
                    'mimeType' => $matches[1],
                    'data'     => $matches[2],
                );
            }
        }

        // 2. If it's a server file path
        if ( is_string( $image_data ) && file_exists( $image_data ) && is_readable( $image_data ) ) {
            $mime = 'image/jpeg';
            $ext  = strtolower( pathinfo( $image_data, PATHINFO_EXTENSION ) );
            if ( $ext === 'png' ) {
                $mime = 'image/png';
            } elseif ( $ext === 'webp' ) {
                $mime = 'image/webp';
            }
            $contents = @file_get_contents( $image_data );
            if ( ! empty( $contents ) ) {
                return array(
                    'mimeType' => $mime,
                    'data'     => base64_encode( $contents ),
                );
            }
        }

    } catch ( \Throwable $e ) {
        // Fail gracefully
    }

    return null;
}

/**
 * Resolve the shopper's reference photo to a local file.
 *
 * SECURITY: only two inputs are accepted —
 *   1. a "tryon_ref_*" id created by Step 1 (looked up inside our own temp folder), or
 *   2. a base64 "data:image/..." upload, which must decode to a real image.
 * Local file paths and remote URLs from the browser are rejected. (Previously a request could pass
 * a server path such as wp-config.php and have it copied into the public uploads folder.)
 *
 * The product image is never taken from the browser — see qcld_woo_chatbot_get_product_image_file().
 */
function qcld_woo_chatbot_resolve_image_to_file( $image_data, $product_id = 0, &$temp_files = array() ) {
    try {
        if ( empty( $image_data ) || ! is_string( $image_data ) ) {
            return null;
        }

        $temp_dir = qcld_woo_chatbot_tryon_temp_dir();

        // 1. Reference id from Step 1
        if ( strpos( $image_data, 'tryon_ref_' ) === 0 ) {
            $safe_name = sanitize_file_name( $image_data );
            $temp_path = trailingslashit( $temp_dir ) . $safe_name;
            if ( $safe_name === $image_data && file_exists( $temp_path ) && @getimagesize( $temp_path ) ) {
                return $temp_path;
            }
            return null;
        }

        // 2. Base64 encoded image
        if ( preg_match( '/^data:image\/(jpe?g|png|webp);base64,(.+)$/is', $image_data, $matches ) ) {
            $ext = strtolower( $matches[1] );
            if ( $ext === 'jpeg' ) {
                $ext = 'jpg';
            }
            $decoded = base64_decode( $matches[2], true );
            if ( $decoded && strlen( $decoded ) <= 15 * 1024 * 1024 ) {
                $temp_file = trailingslashit( $temp_dir ) . 'tryon_tmp_' . wp_generate_password( 12, false ) . '.' . $ext;
                if ( @file_put_contents( $temp_file, $decoded ) !== false ) {
                    $temp_files[] = $temp_file;
                    if ( @getimagesize( $temp_file ) ) {
                        return $temp_file;
                    }
                }
            }
        }
    } catch ( \Throwable $e ) {
        // Fail gracefully
    }

    return null;
}

/**
 * Get the product image as a local file, looked up server-side from trusted IDs.
 * Uses the selected variation's image when a valid variation of this product is given,
 * otherwise the product's main image. Offloaded media (S3/CDN) is downloaded from the
 * attachment URL WordPress itself knows about, via download_url() (wp_safe_remote_get).
 */
function qcld_woo_chatbot_get_product_image_file( $product_id, $variation_id = 0, &$temp_files = array() ) {
    $product_id   = absint( $product_id );
    $variation_id = absint( $variation_id );
    $image_id     = 0;

    if ( $variation_id && function_exists( 'wc_get_product' ) ) {
        $variation = wc_get_product( $variation_id );
        if ( $variation && $variation->is_type( 'variation' ) && (int) $variation->get_parent_id() === $product_id ) {
            $image_id = (int) $variation->get_image_id(); // Falls back to the parent image if the variation has none.
        }
    }
    if ( ! $image_id && $product_id ) {
        $image_id = (int) get_post_thumbnail_id( $product_id );
    }
    if ( ! $image_id ) {
        return null;
    }

    $file = get_attached_file( $image_id );
    if ( $file && file_exists( $file ) ) {
        return $file;
    }

    $url = wp_get_attachment_url( $image_id );
    if ( ! $url ) {
        return null;
    }

    $temp_dir = qcld_woo_chatbot_tryon_temp_dir();
    $cached   = @glob( $temp_dir . '/tryon_opt_src_' . $image_id . '_*' );
    if ( ! empty( $cached ) && filesize( $cached[0] ) > 0 ) {
        return $cached[0];
    }

    if ( ! function_exists( 'download_url' ) ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }
    $tmp = download_url( $url, 30 );
    if ( is_wp_error( $tmp ) || ! file_exists( $tmp ) ) {
        return null;
    }
    $info = @getimagesize( $tmp );
    if ( ! $info ) {
        @unlink( $tmp );
        return null;
    }
    $ext  = ( $info['mime'] === 'image/png' ) ? 'png' : ( ( $info['mime'] === 'image/webp' ) ? 'webp' : 'jpg' );
    $dest = trailingslashit( $temp_dir ) . 'tryon_opt_src_' . $image_id . '_' . md5( $url ) . '.' . $ext;
    if ( @rename( $tmp, $dest ) || @copy( $tmp, $dest ) ) {
        @unlink( $tmp );
        return $dest;
    }
    $temp_files[] = $tmp;
    return $tmp;
}

function qcld_woo_chatbot_build_generation_prompt( $product_name, $product_image = '', $second_image = '', $prompt = '', $bg_preference = '', $output_style = '' ) {
    $product_image_url_val = '';
    if ( ! empty( $product_image ) ) {
        if ( stripos( $product_image, 'data:image' ) === 0 ) {
            $product_image_url_val = '[Image provided visually via Base64 attachment]';
        } else {
            $product_image_url_val = esc_url( $product_image );
        }
    }
    
    $second_image_url_val = '';
    if ( ! empty( $second_image ) ) {
        if ( stripos( $second_image, 'data:image' ) === 0 ) {
            $second_image_url_val = '[Image provided visually via Base64 attachment]';
        } else {
            $second_image_url_val = esc_url( $second_image );
        }
    }
    
    $default_structure = 'Product name: {product_name}. Product Image URL: {product_image_url}. Reference Image URL: {second_image_url}. Background: {bg_preference}. Style: {output_style}. Scenario instruction: {scenario}';
    $structure = get_option( 'qcld_woo_chatbot_build_generation_prompt', $default_structure );
    if ( empty( $structure ) ) {
        $structure = $default_structure;
    }
    
    $replacements = array(
        '{product_name}'       => (string) $product_name,
        '{product_image_url}'  => (string) $product_image_url_val,
        '{product_image}'      => (string) $product_image_url_val,
        '{second_image_url}'   => (string) $second_image_url_val,
        '{second_image}'       => (string) $second_image_url_val,
        '{user_image_url}'     => (string) $second_image_url_val,
        '{scenario}'           => (string) $prompt,
        '{user_prompt}'        => (string) $prompt,
        '{prompt}'             => (string) $prompt,
        '{bg_preference}'      => (string) ( $bg_preference ? $bg_preference : 'Studio' ),
        '{background}'         => (string) ( $bg_preference ? $bg_preference : 'Studio' ),
        '{output_style}'       => (string) ( $output_style ? $output_style : 'Model Shoot' ),
        '{style}'              => (string) ( $output_style ? $output_style : 'Model Shoot' ),
    );
    
    return str_replace( array_keys( $replacements ), array_values( $replacements ), $structure );
}

function qcld_woo_chatbot_execute_openai_image_generation( $product_name, $product_image_file, $second_image_file, $prompt, $bg_preference, $output_style, $api_key, $model, $api_size ) {
    try {
        $has_product = !empty($product_image_file) && file_exists($product_image_file) && is_readable($product_image_file);
        $has_second  = !empty($second_image_file) && file_exists($second_image_file) && is_readable($second_image_file);

        // Directly use the synthesized prompt generated in Step 1 (qcld_woo_chatbot_prepare_generation)
        $merge_prompt = ! empty( $prompt ) ? trim( $prompt ) : '';

        if ( empty( $merge_prompt ) ) {
            if ( $has_product && $has_second ) {
                $merge_prompt = "Create a seamless and photorealistic eCommerce virtual try-on photograph. " .
                    "The product is " . ($product_name ? $product_name : 'an apparel item') . ". " .
                    "Realistically fit, dress, and showcase the product on the model/person with natural proportions, perspective, drape, lighting, shadows, and reflections. " .
                    "Setting/Background: " . $bg_preference . ". Style: " . $output_style . ".";
            } elseif ( $has_product ) {
                $merge_prompt = "Create a professional, high-end commercial eCommerce presentation of this product (" . ($product_name ? $product_name : 'product item') . "). " .
                    "Setting/Background: " . $bg_preference . ", Style: " . $output_style . ".";
            } else {
                $merge_prompt = "Create a professional fashion and eCommerce photograph. " .
                    "Setting/Background: " . $bg_preference . ", Style: " . $output_style . ".";
            }
        }

        // Model resolution
        $use_model = !empty($model) ? trim($model) : 'dall-e-3';

        // Check model family
        $is_dalle3    = ( $use_model === 'dall-e-3' );
        $is_dalle2    = ( $use_model === 'dall-e-2' );
        $is_gpt_image = ( stripos($use_model, 'gpt-image') !== false || stripos($use_model, 'gpt-') !== false );

        // Standardize and normalize size across OpenAI models
        $target_size = '1024x1024';
        if ( in_array($api_size, array('1024x1792', '1024x1536', '9:16', '3:4', 'portrait'), true) ) {
            if ( $is_dalle3 ) {
                $target_size = '1024x1792';
            } elseif ( $is_gpt_image ) {
                $target_size = '1024x1536';
            } else {
                $target_size = '1024x1024';
            }
        } elseif ( in_array($api_size, array('1792x1024', '1536x1024', '16:9', '4:3', 'landscape'), true) ) {
            if ( $is_dalle3 ) {
                $target_size = '1792x1024';
            } elseif ( $is_gpt_image ) {
                $target_size = '1536x1024';
            } else {
                $target_size = '1024x1024';
            }
        } elseif ( $api_size === 'auto' ) {
            if ( $is_gpt_image ) {
                $target_size = 'auto';
            } else {
                $target_size = '1024x1024';
            }
        } elseif ( in_array($api_size, array('1024x1024', '512x512', '256x256'), true) ) {
            $target_size = $api_size;
        }

        // Speed settings for GPT-Image models. Without an explicit quality OpenAI uses "auto",
        // which usually renders at "high" — the single biggest cause of slow try-on renders.
        $quality = qcld_woo_chatbot_get_openai_quality();
        $timeout = qcld_woo_chatbot_get_api_timeout();

        // Branch 1: If we have visual images AND using edits model -> Use Edits endpoint
        if ( ( $has_product || $has_second ) && ! $is_dalle3 ) {
            $boundary = '--------------------------' . microtime( true ) . wp_rand( 1000, 9999 );
            $body     = '';

            $add_field = function( $name, $val ) use ( &$body, $boundary ) {
                $body .= "--" . $boundary . "\r\n";
                $body .= 'Content-Disposition: form-data; name="' . $name . '"' . "\r\n\r\n";
                $body .= $val . "\r\n";
            };

            $add_file = function( $name, $filename, $filepath ) use ( &$body, $boundary ) {
                $ext  = strtolower( pathinfo( $filepath, PATHINFO_EXTENSION ) );
                $mime = ( $ext === 'png' ) ? 'image/png' : ( ( $ext === 'webp' ) ? 'image/webp' : 'image/jpeg' );
                $body .= "--" . $boundary . "\r\n";
                $body .= 'Content-Disposition: form-data; name="' . $name . '"; filename="' . $filename . '"' . "\r\n";
                $body .= 'Content-Type: ' . $mime . "\r\n\r\n";
                $body .= @file_get_contents( $filepath ) . "\r\n";
            };

            $add_field( 'model', $use_model );
            $add_field( 'prompt', $merge_prompt );
            $add_field( 'n', '1' );
            $add_field( 'size', $target_size );
            if ( $is_gpt_image ) {
                $add_field( 'quality', $quality );
                $add_field( 'output_format', 'jpeg' );
                $add_field( 'output_compression', '85' );
            }

            if ( $has_product ) {
                $add_file( 'image[]', 'product_' . basename( $product_image_file ), $product_image_file );
            }
            if ( $has_second ) {
                $add_file( 'image[]', 'reference_' . basename( $second_image_file ), $second_image_file );
            }
            $body .= "--" . $boundary . "--\r\n";

            $response = wp_remote_post( 'https://api.openai.com/v1/images/edits', array(
                'headers'   => array(
                    'Authorization'  => 'Bearer ' . $api_key,
                    'Content-Type'   => 'multipart/form-data; boundary=' . $boundary,
                    'Content-Length' => strlen( $body ),
                ),
                'body'      => $body,
                'timeout'   => $timeout,
            ) );

            if ( is_wp_error( $response ) ) {
                return array( 'success' => false, 'message' => esc_html__( 'OpenAI API Request Failed: ', 'woowbot-woocommerce-chatbot' ) . $response->get_error_message() );
            }

            $data = json_decode( wp_remote_retrieve_body( $response ), true );
            if ( isset( $data['data'][0]['b64_json'] ) ) {
                return qcld_woo_chatbot_image_result( $data['data'][0]['b64_json'], $is_gpt_image ? 'image/jpeg' : 'image/png' );
            } elseif ( isset( $data['data'][0]['url'] ) ) {
                return qcld_woo_chatbot_image_result( '', '', $data['data'][0]['url'] );
            }

            $msg = isset( $data['error']['message'] ) ? $data['error']['message'] : sprintf( esc_html__( 'Image edit failure. (HTTP %s)', 'woowbot-woocommerce-chatbot' ), wp_remote_retrieve_response_code( $response ) );
            return array( 'success' => false, 'message' => esc_html__( 'OpenAI Error: ', 'woowbot-woocommerce-chatbot' ) . $msg );
        }

        // Branch 2: Standard Generations endpoint
        $gen_model = ! empty( $use_model ) ? $use_model : 'gpt-image-1';
        $gen_size  = $target_size;

        $payload = array(
            'model'  => $gen_model,
            'prompt' => $merge_prompt,
            'n'      => 1,
            'size'   => $gen_size,
        );
        if ( $is_gpt_image ) {
            $payload['quality']            = $quality;
            $payload['output_format']      = 'jpeg';
            $payload['output_compression'] = 85;
        }

        $response = wp_remote_post( 'https://api.openai.com/v1/images/generations', array(
            'headers'   => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
            ),
            'body'      => wp_json_encode( $payload ),
            'timeout'   => $timeout,
        ) );

        if ( is_wp_error( $response ) ) {
            return array( 'success' => false, 'message' => esc_html__( 'OpenAI API Request Failed: ', 'woowbot-woocommerce-chatbot' ) . $response->get_error_message() );
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        // If invalid size error, auto-retry with standard 1024x1024
        if ( empty( $data['data'] ) && isset( $data['error']['message'] ) && stripos( $data['error']['message'], 'Invalid size' ) !== false && $gen_size !== '1024x1024' ) {
            $payload['size'] = '1024x1024';
            $retry_response  = wp_remote_post( 'https://api.openai.com/v1/images/generations', array(
                'headers'   => array(
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type'  => 'application/json',
                ),
                'body'      => wp_json_encode( $payload ),
                'timeout'   => $timeout,
            ) );
            if ( ! is_wp_error( $retry_response ) ) {
                $retry_data = json_decode( wp_remote_retrieve_body( $retry_response ), true );
                if ( ! empty( $retry_data['data'] ) ) {
                    $data = $retry_data;
                }
            }
        }

        if ( isset( $data['data'][0]['b64_json'] ) ) {
            return qcld_woo_chatbot_image_result( $data['data'][0]['b64_json'], $is_gpt_image ? 'image/jpeg' : 'image/png' );
        } elseif ( isset( $data['data'][0]['url'] ) ) {
            return qcld_woo_chatbot_image_result( '', '', $data['data'][0]['url'] );
        } else {
            $msg = isset( $data['error']['message'] ) ? $data['error']['message'] : sprintf( esc_html__( 'Generation execution failure. (HTTP %s)', 'woowbot-woocommerce-chatbot' ), wp_remote_retrieve_response_code( $response ) );
            return array( 'success' => false, 'message' => esc_html__( 'OpenAI Error: ', 'woowbot-woocommerce-chatbot' ) . $msg );
        }
    } catch ( \Throwable $e ) {
        return array( 'success' => false, 'message' => esc_html__( 'OpenAI Exception: ', 'woowbot-woocommerce-chatbot' ) . $e->getMessage() );
    }
}

function qcld_woo_chatbot_execute_gemini_image_generation( $product_name, $product_image, $second_image, $prompt, $bg_preference, $output_style, $api_key, $model ) {
    try {
        $use_model = $model;
        // Note: previously 'gemini-2.5-flash-image' was silently swapped for Imagen, which cannot
        // accept input images at all — so the customer's photo was ignored. Keep the chosen model.
        if ( empty( $use_model ) ) {
            $use_model = 'gemini-2.5-flash-image';
        }
        $timeout = qcld_woo_chatbot_get_api_timeout();

        $is_imagen = ( stripos($use_model, 'imagen-') === 0 );

        // Directly use the synthesized prompt generated in Step 1 (qcld_woo_chatbot_prepare_generation)
        $gemini_prompt = ! empty( $prompt ) ? trim( $prompt ) : '';

        if ( empty( $gemini_prompt ) ) {
            if ( !empty($second_image) && !empty($product_image) ) {
                $gemini_prompt = "Create a seamless and realistic virtual try-on image merging these two images. " .
                    "Image 1 is the product (" . ($product_name ? $product_name : 'eCommerce item') . "). " .
                    "Image 2 is the reference image/person provided by the user. " .
                    "Realistically fit and blend the product onto the person/subject with natural proportions, perspective, drape, lighting, shadows, and reflections. " .
                    "Preserve all facial features, expression, and identity of the person accurately. " .
                    "Background: " . $bg_preference . ". Style: " . $output_style . ".";
            } else {
                $base_prompt = get_option('qcld_woo_chatbot_gemini_prompt', 'Create a realistic eCommerce product image. ');
                $gemini_prompt = trim($base_prompt) . " Product: " . $product_name . ". Style: " . $output_style . ". Background: " . $bg_preference . ".";
            }
        }

        if ( $is_imagen ) {
            // Google Imagen Predict Endpoint
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $use_model . ':predict?key=' . $api_key;
            $payload = array(
                'instances'  => array(
                    array( 'prompt' => $gemini_prompt )
                ),
                'parameters' => array(
                    'sampleCount' => 1,
                    'aspectRatio' => '1:1'
                )
            );

            $response = wp_remote_post( $url, array(
                'headers'   => array( 'Content-Type' => 'application/json' ),
                'body'      => wp_json_encode($payload),
                'timeout'   => $timeout,
            ));

            if ( is_wp_error( $response ) ) {
                return array( 'success' => false, 'message' => esc_html__('Gemini API Request Failed: ', 'woowbot-woocommerce-chatbot') . $response->get_error_message() );
            }

            $body = json_decode( wp_remote_retrieve_body( $response ), true );

            if ( isset($body['predictions'][0]['bytesBase64Encoded']) ) {
                $mime = isset($body['predictions'][0]['mimeType']) ? $body['predictions'][0]['mimeType'] : 'image/jpeg';
                return qcld_woo_chatbot_image_result( $body['predictions'][0]['bytesBase64Encoded'], $mime );
            } elseif ( isset($body['error']['message']) ) {
                return array( 'success' => false, 'message' => esc_html__('Gemini API Error: ', 'woowbot-woocommerce-chatbot') . $body['error']['message'] );
            }
        }

        // Standard Gemini GenerateContent Endpoint
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $use_model . ':generateContent?key=' . $api_key;

        $parts = array(
            array( 'text' => $gemini_prompt )
        );

        // Append product image as inlineData
        if ( !empty($product_image) && stripos($product_image, 'data:image') === 0 ) {
            if ( preg_match('/data:(image\/[^;]+);base64,(.+)/', $product_image, $matches) ) {
                $parts[] = array(
                    'inlineData' => array(
                        'mimeType' => $matches[1],
                        'data'     => $matches[2]
                    )
                );
            }
        } elseif ( !empty($product_image) && ( filter_var($product_image, FILTER_VALIDATE_URL) || file_exists($product_image) ) ) {
            $prod_inline = qcld_woo_chatbot_ensure_image_inline_data( $product_image );
            if ( $prod_inline ) {
                $parts[] = array( 'inlineData' => $prod_inline );
            }
        }

        // Append user uploaded second image as inlineData
        if ( !empty($second_image) && stripos($second_image, 'data:image') === 0 ) {
            if ( preg_match('/data:(image\/[^;]+);base64,(.+)/', $second_image, $matches) ) {
                $parts[] = array(
                    'inlineData' => array(
                        'mimeType' => $matches[1],
                        'data'     => $matches[2]
                    )
                );
            }
        } elseif ( !empty($second_image) && ( filter_var($second_image, FILTER_VALIDATE_URL) || file_exists($second_image) || ( is_string($second_image) && strpos($second_image, 'tryon_ref_') === 0 ) ) ) {
            $resolved_sec_path = $second_image;
            if ( is_string($second_image) && strpos($second_image, 'tryon_ref_') === 0 ) {
                $upload_dir = wp_upload_dir();
                $resolved_sec_path = trailingslashit($upload_dir['basedir']) . 'qcld_tryon_temp/' . sanitize_file_name($second_image);
            }
            $sec_inline = qcld_woo_chatbot_ensure_image_inline_data( $resolved_sec_path );
            if ( $sec_inline ) {
                $parts[] = array( 'inlineData' => $sec_inline );
            }
        }

        $payload = array(
            'contents' => array(
                array( 'parts' => $parts )
            )
        );

        $response = wp_remote_post( $url, array(
            'headers'   => array( 'Content-Type' => 'application/json' ),
            'body'      => wp_json_encode($payload),
            'timeout'   => $timeout,
        ));

        if ( is_wp_error( $response ) ) {
            return array( 'success' => false, 'message' => esc_html__('Gemini API Request Failed: ', 'woowbot-woocommerce-chatbot') . $response->get_error_message() );
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        // Gemini image models often return a short text part first and the image in a later part.
        if ( ! empty( $body['candidates'][0]['content']['parts'] ) && is_array( $body['candidates'][0]['content']['parts'] ) ) {
            foreach ( $body['candidates'][0]['content']['parts'] as $part ) {
                if ( ! empty( $part['inlineData']['data'] ) ) {
                    $mime = ! empty( $part['inlineData']['mimeType'] ) ? $part['inlineData']['mimeType'] : 'image/png';
                    return qcld_woo_chatbot_image_result( $part['inlineData']['data'], $mime );
                }
            }
        }

        if ( isset($body['candidates'][0]['content']['parts'][0]['text']) ) {
            $text_context = trim($body['candidates'][0]['content']['parts'][0]['text']);
            if ( filter_var($text_context, FILTER_VALIDATE_URL) ) {
                $html = '<img src="' . esc_url_raw($text_context) . '" alt="AI Try On Render Outcome" class="qcld_woo_chatbot-output-media" />';
            } else {
                $html = '<div class="qcld_woo_chatbot-text-block">' . wpautop(esc_html($text_context)) . '</div>';
            }
            return array( 'success' => true, 'html' => $html );
        } elseif ( isset($body['candidates'][0]['finishReason']) ) {
            return array( 'success' => false, 'message' => esc_html__('Gemini generation stopped. Reason: ', 'woowbot-woocommerce-chatbot') . $body['candidates'][0]['finishReason'] );
        } else {
            $msg = isset($body['error']['message']) ? $body['error']['message'] : esc_html__('Unknown Gemini Error.', 'woowbot-woocommerce-chatbot');
            return array( 'success' => false, 'message' => esc_html__('Gemini API Error: ', 'woowbot-woocommerce-chatbot') . $msg );
        }
    } catch ( \Throwable $e ) {
        return array( 'success' => false, 'message' => esc_html__('Gemini Exception: ', 'woowbot-woocommerce-chatbot') . $e->getMessage() );
    }
}

/* -------------------------------------------------------------------------
 * Generation helpers
 * ---------------------------------------------------------------------- */

/**
 * GPT-Image render quality. "medium" is roughly 2–4x faster than "high"/"auto"
 * and still looks good for try-on previews.
 */
function qcld_woo_chatbot_get_openai_quality() {
    $quality = get_option( 'qcld_woo_chatbot_openai_image_quality', 'medium' );
    if ( ! in_array( $quality, array( 'low', 'medium', 'high', 'auto' ), true ) ) {
        $quality = 'medium';
    }
    return apply_filters( 'qcld_woo_chatbot_openai_image_quality', $quality );
}

/**
 * HTTP timeout (seconds) for the AI provider call. This runs in the background,
 * so it can be generous without blocking the shopper's browser request.
 */
function qcld_woo_chatbot_get_api_timeout() {
    return max( 30, (int) apply_filters( 'qcld_woo_chatbot_api_timeout', 240 ) );
}

/**
 * Normalised success payload returned by the provider functions.
 */
function qcld_woo_chatbot_image_result( $b64, $mime, $url = '' ) {
    if ( ! empty( $url ) ) {
        return array(
            'success'   => true,
            'image_url' => esc_url_raw( $url ),
            'html'      => '<img src="' . esc_url( $url ) . '" alt="AI Try On Render Outcome" class="qcld_woo_chatbot-output-media" />',
        );
    }
    $mime = $mime ? $mime : 'image/png';
    return array(
        'success' => true,
        'b64'     => $b64,
        'mime'    => $mime,
        'html'    => '<img src="data:' . esc_attr( $mime ) . ';base64,' . $b64 . '" alt="AI Try On Render Outcome" class="qcld_woo_chatbot-output-media" />',
    );
}

function qcld_woo_chatbot_tryon_temp_dir() {
    $upload_dir = wp_upload_dir();
    $temp_dir   = trailingslashit( $upload_dir['basedir'] ) . 'qcld_tryon_temp';
    if ( ! file_exists( $temp_dir ) ) {
        wp_mkdir_p( $temp_dir );
        @file_put_contents( $temp_dir . '/index.php', '<?php // Silence is golden' );
    }
    return $temp_dir;
}

/**
 * Downscale an image before sending it to the AI provider.
 * Product images are usually the full-size original (often 2000–5000px, several MB);
 * uploading and processing that is slow and gives no quality benefit for a try-on.
 * Results are cached, so each product image is only resized once.
 */
function qcld_woo_chatbot_optimize_image_for_api( $file, $max_dimension = 1024 ) {
    if ( empty( $file ) || ! is_string( $file ) || ! file_exists( $file ) || ! is_readable( $file ) ) {
        return $file;
    }

    $info = @getimagesize( $file );
    if ( ! $info ) {
        return $file;
    }

    $max_dimension = (int) apply_filters( 'qcld_woo_chatbot_api_image_max_dimension', $max_dimension );
    if ( max( $info[0], $info[1] ) <= $max_dimension && filesize( $file ) <= 1.5 * 1024 * 1024 ) {
        return $file; // Already small enough.
    }

    // Keep PNG as PNG so transparent product cut-outs don't get a black background.
    $mime = isset( $info['mime'] ) ? $info['mime'] : 'image/jpeg';
    $ext  = ( $mime === 'image/png' ) ? 'png' : ( ( $mime === 'image/webp' ) ? 'webp' : 'jpg' );
    if ( $ext === 'jpg' ) {
        $mime = 'image/jpeg';
    }

    $cache_file = trailingslashit( qcld_woo_chatbot_tryon_temp_dir() ) . 'tryon_opt_' . md5( $file . '|' . @filemtime( $file ) . '|' . $max_dimension ) . '.' . $ext;
    if ( file_exists( $cache_file ) && filesize( $cache_file ) > 0 ) {
        return $cache_file;
    }

    $editor = wp_get_image_editor( $file );
    if ( is_wp_error( $editor ) ) {
        return $file;
    }
    $editor->resize( $max_dimension, $max_dimension, false );
    $editor->set_quality( 88 );
    $saved = $editor->save( $cache_file, $mime );

    if ( is_wp_error( $saved ) || empty( $saved['path'] ) || ! file_exists( $saved['path'] ) ) {
        return $file;
    }
    return $saved['path'];
}

/**
 * Persist a base64 reference photo under a stable tryon_ref_ name (used if Step 1 was skipped).
 */
function qcld_woo_chatbot_persist_reference_image( $data_uri ) {
    if ( ! preg_match( '/^data:image\/(jpe?g|png|webp);base64,(.+)$/i', $data_uri, $m ) ) {
        return '';
    }
    $decoded = base64_decode( $m[2] );
    if ( ! $decoded || strlen( $decoded ) > 15 * 1024 * 1024 ) {
        return '';
    }
    $ext  = strtolower( $m[1] ) === 'jpeg' ? 'jpg' : strtolower( $m[1] );
    $name = 'tryon_ref_' . wp_generate_password( 16, false ) . '_' . time() . '.' . $ext;
    $path = trailingslashit( qcld_woo_chatbot_tryon_temp_dir() ) . $name;
    if ( @file_put_contents( $path, $decoded ) === false ) {
        return '';
    }
    if ( ! @getimagesize( $path ) ) {
        @unlink( $path );
        return '';
    }
    return $name;
}

/**
 * Remove old generated / optimised files.
 */
function qcld_woo_chatbot_cleanup_tryon_files() {
    $temp_dir = qcld_woo_chatbot_tryon_temp_dir();
    $now      = time();
    $rules    = array(
        'tryon_out_*' => 2 * HOUR_IN_SECONDS,
        'tryon_ref_*' => 2 * HOUR_IN_SECONDS,
        'tryon_opt_*' => DAY_IN_SECONDS,
    );
    foreach ( $rules as $pattern => $max_age ) {
        $files = @glob( $temp_dir . '/' . $pattern );
        if ( empty( $files ) ) {
            continue;
        }
        foreach ( $files as $f ) {
            if ( is_file( $f ) && ( $now - @filemtime( $f ) > $max_age ) ) {
                @unlink( $f );
            }
        }
    }
}

function qcld_woo_chatbot_job_key( $job_id ) {
    return 'qcld_woo_chatbot_job_' . $job_id;
}

function qcld_woo_chatbot_sanitize_job_id( $job_id ) {
    return preg_replace( '/[^A-Za-z0-9]/', '', (string) $job_id );
}

/**
 * Send the JSON response to the browser now and keep PHP running afterwards.
 * Works on PHP-FPM (nginx / most Apache hosts) and LiteSpeed. Returns false if
 * the server does not support it, so the caller can use another strategy.
 */
function qcld_woo_chatbot_send_json_and_continue( $data ) {
    $can_fpm = function_exists( 'fastcgi_finish_request' );
    $can_ls  = function_exists( 'litespeed_finish_request' );
    if ( ! $can_fpm && ! $can_ls ) {
        return false;
    }

    @ignore_user_abort( true );
    $json = wp_json_encode( array( 'success' => true, 'data' => $data ) );

    // Drop anything buffered (stray notices etc.) so the browser gets clean JSON.
    while ( ob_get_level() > 0 ) {
        @ob_end_clean();
    }
    if ( ! headers_sent() ) {
        status_header( 200 );
        header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
        header( 'Cache-Control: no-cache, must-revalidate, max-age=0' );
    }
    echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    @flush();

    if ( $can_fpm ) {
        fastcgi_finish_request();
    } else {
        litespeed_finish_request();
    }
    return true;
}

/**
 * Fire a non-blocking request back to this site that runs the job.
 * Used on servers without fastcgi_finish_request (e.g. Apache mod_php).
 */
function qcld_woo_chatbot_spawn_job_worker( $job_id, $token ) {
    wp_remote_post( admin_url( 'admin-ajax.php' ), array(
        'timeout'   => 0.01,
        'blocking'  => false,
        'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
        'body'      => array(
            'action' => 'qcld_woo_chatbot_run_job',
            'job_id' => $job_id,
            'token'  => $token,
        ),
    ) );
}

/* -------------------------------------------------------------------------
 * Step 2: queue the generation and return immediately
 * ---------------------------------------------------------------------- */

function qcld_woo_chatbot_handle_generation_backend() {
    // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
    @ini_set( 'memory_limit', '512M' );

    try {
        check_ajax_referer( 'qcld_woo_chatbot_generation_nonce', 'security' );

        // 1. Enforce Server-Side Logged-In Restriction if configured
        $require_login = get_option( 'qcld_woo_chatbot_logged_in_users_only', '0' );
        if ( $require_login === '1' && ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => esc_html__( 'You must be logged in to use this AI Try-On feature.', 'woowbot-woocommerce-chatbot' ) ) );
        }

        // 2. Resolve Client Identifier
        $remote_addr     = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
        $user_identifier = is_user_logged_in() ? 'user_' . get_current_user_id() : 'ip_' . $remote_addr;

        // 3. Burst Rate Limit Protection (Max 2 requests per 15 seconds per IP/User)
        $burst_transient = 'qcld_woo_chatbot_burst_' . md5( $user_identifier );
        $burst_count     = (int) get_transient( $burst_transient );
        if ( $burst_count >= 2 ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Please wait a few seconds before requesting another generation.', 'woowbot-woocommerce-chatbot' ) ) );
        }

        // 4. Daily Generation Limit with Safe Fail-Closed Default (Default: 20 per 24 hours)
        $raw_limit   = get_option( 'qcld_woo_chatbot_generation_limit', '' );
        $daily_limit = ( $raw_limit === '' || ! is_numeric( $raw_limit ) ) ? 20 : absint( $raw_limit );

        if ( $daily_limit <= 0 ) {
            wp_send_json_error( array( 'message' => esc_html__( 'AI generation is currently disabled by the store administrator.', 'woowbot-woocommerce-chatbot' ) ) );
        }

        $limit_transient = 'qcld_woo_chatbot_gen_limit_' . md5( $user_identifier );
        $current_count   = (int) get_transient( $limit_transient );

        if ( $current_count >= $daily_limit ) {
            wp_send_json_error( array( 'message' => esc_html__( 'You have reached your daily limit for AI generations. Please try again tomorrow.', 'woowbot-woocommerce-chatbot' ) ) );
        }

        // 5. Global Site-Wide Circuit Breaker
        $raw_global_limit = get_option( 'qcld_woo_chatbot_global_daily_limit', '250' );
        $global_limit     = ( $raw_global_limit === '' || ! is_numeric( $raw_global_limit ) ) ? 250 : absint( $raw_global_limit );
        $global_limit     = (int) apply_filters( 'qcld_woo_chatbot_global_daily_limit', $global_limit );
        $global_count     = (int) get_transient( 'qcld_woo_chatbot_global_daily_count' );
        if ( $global_limit > 0 && $global_count >= $global_limit ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Site daily generation quota reached. Please try again tomorrow.', 'woowbot-woocommerce-chatbot' ) ) );
        }

        // 6. Payload Sanitization & Validation
        $product_id        = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
        $prompt            = isset( $_POST['prompt'] ) ? sanitize_text_field( wp_unslash( $_POST['prompt'] ) ) : '';
        $product_name      = isset( $_POST['product_name'] ) ? sanitize_text_field( wp_unslash( $_POST['product_name'] ) ) : '';
        $generation_type   = isset( $_POST['generation_type'] ) ? sanitize_text_field( wp_unslash( $_POST['generation_type'] ) ) : 'image';
        $bg_preference     = isset( $_POST['bg_preference'] ) ? sanitize_text_field( wp_unslash( $_POST['bg_preference'] ) ) : 'Studio';
        $output_style      = isset( $_POST['output_style'] ) ? sanitize_text_field( wp_unslash( $_POST['output_style'] ) ) : 'Model Shoot';
        $variation_id      = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;
        $second_image      = isset( $_POST['second_image'] ) ? trim( wp_unslash( $_POST['second_image'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- ref id or base64, validated below.

        if ( mb_strlen( $prompt ) > 1000 ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Prompt text is too long (maximum 1000 characters).', 'woowbot-woocommerce-chatbot' ) ) );
        }

        if ( ! in_array( $generation_type, array( 'image', 'video' ), true ) ) {
            $generation_type = 'image';
        }

        // Reference photo must be a Step-1 reference id (or a base64 image we persist now).
        $second_ref = '';
        if ( $second_image !== '' ) {
            if ( strpos( $second_image, 'tryon_ref_' ) === 0 ) {
                $second_ref = sanitize_file_name( $second_image );
                if ( ! file_exists( trailingslashit( qcld_woo_chatbot_tryon_temp_dir() ) . $second_ref ) ) {
                    wp_send_json_error( array( 'message' => esc_html__( 'Your uploaded photo has expired. Please upload it again.', 'woowbot-woocommerce-chatbot' ) ) );
                }
            } elseif ( stripos( $second_image, 'data:image' ) === 0 ) {
                $second_ref = qcld_woo_chatbot_persist_reference_image( $second_image );
                if ( $second_ref === '' ) {
                    wp_send_json_error( array( 'message' => esc_html__( 'Could not read the uploaded reference photo. Please try a different image (JPG/PNG/WebP).', 'woowbot-woocommerce-chatbot' ) ) );
                }
            }
        }

        $provider = get_option( 'qcld_woo_chatbot_api_provider', 'openai' );
        if ( $generation_type === 'video' ) {
            $provider = 'gemini'; // Force Gemini for video
        }
        $api_key = $provider === 'openai' ? get_option( 'qcld_woo_chatbot_openai_api_key', '' ) : get_option( 'qcld_woo_chatbot_gemini_api_key', '' );
        if ( empty( $api_key ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'API Configuration missing. Contact store administrator.', 'woowbot-woocommerce-chatbot' ) ) );
        }

        // 7. Create the job
        $job_id = wp_generate_password( 24, false );
        $token  = wp_generate_password( 32, false );
        $job    = array(
            'status'            => 'queued',
            'created'           => time(),
            'token'             => $token,
            'user_identifier'   => $user_identifier,
            'provider'          => $provider,
            'generation_type'   => $generation_type,
            'product_id'        => $product_id,
            'product_name'      => $product_name,
            'variation_id'      => $variation_id,
            'second_ref'        => $second_ref,
            'prompt'            => $prompt,
            'bg_preference'     => $bg_preference,
            'output_style'      => $output_style,
        );
        set_transient( qcld_woo_chatbot_job_key( $job_id ), $job, 2 * HOUR_IN_SECONDS );

        // Count the request towards the burst limiter now (prevents queuing many jobs at once).
        set_transient( $burst_transient, $burst_count + 1, 15 );

        $response = array(
            'step'    => 2,
            'job_id'  => $job_id,
            'status'  => 'queued',
            'message' => esc_html__( 'Generation started.', 'woowbot-woocommerce-chatbot' ),
        );

        // 8a. Best path: reply to the browser now, then render in this same PHP process.
        if ( qcld_woo_chatbot_send_json_and_continue( $response ) ) {
            qcld_woo_chatbot_run_tryon_job( $job_id );
            exit;
        }

        // 8b. Fallback: hand the job to a background loopback request.
        qcld_woo_chatbot_spawn_job_worker( $job_id, $token );
        wp_send_json_success( $response );
    } catch ( \Throwable $e ) {
        wp_send_json_error( array( 'message' => esc_html__( 'Server execution error: ', 'woowbot-woocommerce-chatbot' ) . $e->getMessage() ) );
    }
}

/* -------------------------------------------------------------------------
 * Background worker
 * ---------------------------------------------------------------------- */

add_action( 'wp_ajax_qcld_woo_chatbot_run_job', 'qcld_woo_chatbot_run_job_endpoint' );
add_action( 'wp_ajax_nopriv_qcld_woo_chatbot_run_job', 'qcld_woo_chatbot_run_job_endpoint' );
function qcld_woo_chatbot_run_job_endpoint() {
    // Authenticated by the per-job secret token (loopback requests carry no cookies/nonce).
    $job_id = isset( $_POST['job_id'] ) ? qcld_woo_chatbot_sanitize_job_id( wp_unslash( $_POST['job_id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $token  = isset( $_POST['token'] ) ? sanitize_text_field( wp_unslash( $_POST['token'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $job    = $job_id ? get_transient( qcld_woo_chatbot_job_key( $job_id ) ) : false;

    if ( ! is_array( $job ) || empty( $job['token'] ) || ! hash_equals( $job['token'], $token ) ) {
        wp_die( '', '', array( 'response' => 403 ) );
    }

    @ignore_user_abort( true );
    qcld_woo_chatbot_run_tryon_job( $job_id );
    wp_die( '', '', array( 'response' => 200 ) );
}

function qcld_woo_chatbot_run_tryon_job( $job_id ) {
    $key = qcld_woo_chatbot_job_key( $job_id );
    $job = get_transient( $key );
    if ( ! is_array( $job ) || $job['status'] !== 'queued' ) {
        return;
    }

    // Atomic lock so a job is never rendered twice (add_option fails if the row exists).
    $lock = 'qcld_woo_chatbot_joblock_' . $job_id;
    if ( ! add_option( $lock, time(), '', 'no' ) ) {
        return;
    }

    // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
    @ini_set( 'memory_limit', '512M' );
    @set_time_limit( qcld_woo_chatbot_get_api_timeout() + 60 );
    @ignore_user_abort( true );

    $job['status']  = 'processing';
    $job['started'] = time();
    set_transient( $key, $job, 2 * HOUR_IN_SECONDS );

    $temp_files = array();

    try {
        $provider = $job['provider'];
        $api_key  = $provider === 'openai' ? get_option( 'qcld_woo_chatbot_openai_api_key', '' ) : get_option( 'qcld_woo_chatbot_gemini_api_key', '' );
        $model    = qcld_woo_chatbot_get_appropriate_model( $provider, $job['generation_type'] );

        // Resolve both images to local files, then shrink them for a fast upload to the AI provider.
        $product_image_file = qcld_woo_chatbot_get_product_image_file( $job['product_id'], isset( $job['variation_id'] ) ? $job['variation_id'] : 0, $temp_files );
        $second_image_file  = $job['second_ref'] ? qcld_woo_chatbot_resolve_image_to_file( $job['second_ref'], 0, $temp_files ) : null;

        $product_image_file = qcld_woo_chatbot_optimize_image_for_api( $product_image_file );
        $second_image_file  = qcld_woo_chatbot_optimize_image_for_api( $second_image_file );

        if ( $provider === 'openai' ) {
            $frontend_size = get_option( 'qcld_woo_chatbot_image_size', '1024x1024' );
            $gen_result    = qcld_woo_chatbot_execute_openai_image_generation( $job['product_name'], $product_image_file, $second_image_file, $job['prompt'], $job['bg_preference'], $job['output_style'], $api_key, $model, $frontend_size );
        } else {
            $gen_result = qcld_woo_chatbot_execute_gemini_image_generation( $job['product_name'], (string) $product_image_file, (string) $second_image_file, $job['prompt'], $job['bg_preference'], $job['output_style'], $api_key, $model );
        }

        if ( ! empty( $gen_result['success'] ) ) {
            // Store the image on disk (not in the options table) and let the status poll pick it up.
            if ( ! empty( $gen_result['b64'] ) ) {
                $mime = $gen_result['mime'];
                $ext  = ( $mime === 'image/jpeg' ) ? 'jpg' : ( ( $mime === 'image/webp' ) ? 'webp' : 'png' );
                $file = trailingslashit( qcld_woo_chatbot_tryon_temp_dir() ) . 'tryon_out_' . $job_id . '.' . $ext;
                if ( @file_put_contents( $file, base64_decode( $gen_result['b64'] ) ) === false ) {
                    throw new \Exception( esc_html__( 'Could not store the generated image on the server.', 'woowbot-woocommerce-chatbot' ) );
                }
                $job['result_file'] = basename( $file );
                $job['result_mime'] = $mime;
            } else {
                $job['result_html'] = $gen_result['html'];
            }

            $job['status']   = 'done';
            $job['model']    = $model;
            $job['finished'] = time();

            // Increment quotas ONLY on successful generation (read fresh values).
            $limit_transient = 'qcld_woo_chatbot_gen_limit_' . md5( $job['user_identifier'] );
            set_transient( $limit_transient, (int) get_transient( $limit_transient ) + 1, DAY_IN_SECONDS );
            set_transient( 'qcld_woo_chatbot_global_daily_count', (int) get_transient( 'qcld_woo_chatbot_global_daily_count' ) + 1, DAY_IN_SECONDS );
        } else {
            $job['status']  = 'error';
            $job['message'] = ! empty( $gen_result['message'] ) ? $gen_result['message'] : esc_html__( 'Generation failed.', 'woowbot-woocommerce-chatbot' );
        }
    } catch ( \Throwable $e ) {
        $job['status']  = 'error';
        $job['message'] = esc_html__( 'Server execution error: ', 'woowbot-woocommerce-chatbot' ) . $e->getMessage();
    }

    set_transient( $key, $job, 2 * HOUR_IN_SECONDS );
    delete_option( $lock );

    foreach ( $temp_files as $tf ) {
        if ( $tf && file_exists( $tf ) ) {
            @unlink( $tf );
        }
    }
    qcld_woo_chatbot_cleanup_tryon_files();
}

/* -------------------------------------------------------------------------
 * Status polling endpoint (short, fast requests — never hits a server timeout)
 * ---------------------------------------------------------------------- */

add_action( 'wp_ajax_qcld_woo_chatbot_job_status', 'qcld_woo_chatbot_job_status' );
add_action( 'wp_ajax_nopriv_qcld_woo_chatbot_job_status', 'qcld_woo_chatbot_job_status' );
function qcld_woo_chatbot_job_status() {
    check_ajax_referer( 'qcld_woo_chatbot_generation_nonce', 'security' );

    $job_id = isset( $_POST['job_id'] ) ? qcld_woo_chatbot_sanitize_job_id( wp_unslash( $_POST['job_id'] ) ) : '';
    $job    = $job_id ? get_transient( qcld_woo_chatbot_job_key( $job_id ) ) : false;

    if ( ! is_array( $job ) ) {
        wp_send_json_error( array( 'message' => esc_html__( 'This generation has expired. Please try again.', 'woowbot-woocommerce-chatbot' ) ) );
    }

    $now = time();

    // Safety net: the background worker never started (e.g. loopback requests blocked by the host).
    // Run it inside this request instead.
    $queued_grace = (int) apply_filters( 'qcld_woo_chatbot_job_queue_grace', 15 );
    if ( $job['status'] === 'queued' && ( $now - $job['created'] ) > $queued_grace ) {
        qcld_woo_chatbot_run_tryon_job( $job_id );
        $job = get_transient( qcld_woo_chatbot_job_key( $job_id ) );
    }

    // Worker was killed by the server (hard PHP timeout, crash) without reporting back.
    if ( is_array( $job ) && $job['status'] === 'processing' && ! empty( $job['started'] ) && ( $now - $job['started'] ) > ( qcld_woo_chatbot_get_api_timeout() + 120 ) ) {
        $job['status']  = 'error';
        $job['message'] = esc_html__( 'The AI provider took too long to respond. Please try again.', 'woowbot-woocommerce-chatbot' );
        set_transient( qcld_woo_chatbot_job_key( $job_id ), $job, 2 * HOUR_IN_SECONDS );
    }

    if ( ! is_array( $job ) ) {
        wp_send_json_error( array( 'message' => esc_html__( 'This generation has expired. Please try again.', 'woowbot-woocommerce-chatbot' ) ) );
    }

    if ( $job['status'] === 'error' ) {
        wp_send_json_error( array( 'message' => $job['message'] ) );
    }

    if ( $job['status'] === 'done' ) {
        $html = '';
        if ( ! empty( $job['result_file'] ) ) {
            $path = trailingslashit( qcld_woo_chatbot_tryon_temp_dir() ) . $job['result_file'];
            $data = file_exists( $path ) ? @file_get_contents( $path ) : false;
            if ( $data === false ) {
                wp_send_json_error( array( 'message' => esc_html__( 'The generated image has expired. Please try again.', 'woowbot-woocommerce-chatbot' ) ) );
            }
            $html = '<img src="data:' . esc_attr( $job['result_mime'] ) . ';base64,' . base64_encode( $data ) . '" alt="AI Try On Render Outcome" class="qcld_woo_chatbot-output-media" />';
        } elseif ( ! empty( $job['result_html'] ) ) {
            $html = $job['result_html'];
        }

        wp_send_json_success( array(
            'step'     => 2,
            'status'   => 'done',
            'html'     => $html,
            'provider' => $job['provider'],
            'model'    => isset( $job['model'] ) ? $job['model'] : '',
            'message'  => esc_html__( 'AI Try-On render generated successfully.', 'woowbot-woocommerce-chatbot' ),
        ) );
    }

    wp_send_json_success( array(
        'status'  => $job['status'],
        'elapsed' => $now - $job['created'],
    ) );
}


// 5. Add Media Library Save Endpoint
add_action( 'wp_ajax_qcld_woo_chatbot_save_media', 'qcld_woo_chatbot_save_media_to_library' );
function qcld_woo_chatbot_save_media_to_library() {
    check_ajax_referer( 'qcld_woo_chatbot_generation_nonce', 'security' );

    if ( ! current_user_can( 'upload_files' ) ) {
        wp_send_json_error( array( 'message' => esc_html__( 'Permission denied. You do not have permission to upload files.', 'woowbot-woocommerce-chatbot' ) ) );
    }

    $media_data = isset($_POST['media_data']) ? sanitize_textarea_field( wp_unslash( $_POST['media_data'] ) ) : '';
    
    if ( empty($media_data) ) {
        wp_send_json_error( array('message' => esc_html__('No media data provided.', 'woowbot-woocommerce-chatbot')) );
    }

    require_once( ABSPATH . 'wp-admin/includes/image.php' );
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
    require_once( ABSPATH . 'wp-admin/includes/media.php' );

    if ( stripos($media_data, 'data:image') === 0 || stripos($media_data, 'data:video') === 0 ) {
        preg_match('/data:((image|video)\/[^;]+);base64,(.+)/', $media_data, $matches);
        if ( count($matches) !== 4 ) {
            wp_send_json_error( array('message' => esc_html__('Invalid base64 data.', 'woowbot-woocommerce-chatbot')) );
        }
        $mime = $matches[1];
        $type = $matches[2];
        $base64 = $matches[3];
        
        $ext = str_replace(array('image/', 'video/'), '', $mime);
        if ($ext === 'jpeg') $ext = 'jpg';
        
        $decoded = base64_decode($base64);
        if ( !$decoded ) {
            wp_send_json_error( array('message' => esc_html__('Failed to decode base64.', 'woowbot-woocommerce-chatbot')) );
        }
        
        $filename = 'qcld_woo_chatbot-generated-' . time() . '.' . $ext;
        $upload_file = wp_upload_bits( array(
            'name' => $filename,
            'type' => $mime,
            'tmp_name' => '',
            'error' => 0,
            'size' => strlen($decoded)
        ), null, $decoded );
        
        if ( ! $upload_file['error'] ) {
            $attachment = array(
                'post_mime_type' => $mime,
                'post_title'     => sanitize_file_name($filename),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );
            $attachment_id = wp_insert_attachment( $attachment, $upload_file['file'] );
            if ( ! is_wp_error( $attachment_id ) ) {
                $attach_data = wp_generate_attachment_metadata( $attachment_id, $upload_file['file'] );
                wp_update_attachment_metadata( $attachment_id, $attach_data );
                wp_send_json_success( array('message' => esc_html__('Saved to Media Library!', 'woowbot-woocommerce-chatbot')) );
            }
        }
        wp_send_json_error( array('message' => esc_html__('Failed to save base64 media.', 'woowbot-woocommerce-chatbot')) );
    } else if ( filter_var($media_data, FILTER_VALIDATE_URL) ) {
        $tmp = download_url( $media_data );
        if ( is_wp_error( $tmp ) ) {
            wp_send_json_error( array('message' => esc_html__('Failed to download media from URL.', 'woowbot-woocommerce-chatbot')) );
        }
        
        $mime = wp_check_filetype($tmp);
        $ext = isset($mime['ext']) && $mime['ext'] ? $mime['ext'] : 'jpg';
        
        $file_array = array(
            'name'     => 'qcld_woo_chatbot-generated-' . time() . '.' . $ext,
            'type'     => $mime['type'],
            'tmp_name' => $tmp,
            'error'    => 0,
            'size'     => filesize($tmp),
        );
        $attachment_id = media_handle_sideload( $file_array, 0 );
        if ( is_wp_error($attachment_id) ) {
            @wp_delete_file($file_array['tmp_name']);
            wp_send_json_error( array('message' => esc_html__('Failed to save downloaded media.', 'woowbot-woocommerce-chatbot')) );
        }
        wp_send_json_success( array('message' => esc_html__('Saved to Media Library!', 'woowbot-woocommerce-chatbot')) );
    } else {
        wp_send_json_error( array('message' => esc_html__('Invalid media format.', 'woowbot-woocommerce-chatbot')) );
    }
}
