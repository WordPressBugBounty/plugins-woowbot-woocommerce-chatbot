<?php
defined('ABSPATH') or die("No direct script access!");

add_action( 'wp_ajax_qcld_woo_chatbot_test_openai_connection', 'qcld_woo_chatbot_test_openai_connection' );
function qcld_woo_chatbot_test_openai_connection() {
    check_ajax_referer( 'qcld_woo_chatbot_admin_nonce', 'security' );
    
    $api_key = isset( $_POST['api_key'] ) ? sanitize_text_field( wp_unslash( $_POST['api_key'] ) ) : '';
    
    if ( empty( $api_key ) ) {
        wp_send_json_error( array( 'message' => esc_html( 'API Key is empty.', 'ai-tryon-for-woocommerce' ) ) );
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
        wp_send_json_success( array( 'message' => esc_html( 'Connection successful!', 'ai-tryon-for-woocommerce' ), 'image_models' => $image_models, 'video_models' => $video_models ) );
    } else {
        if ( isset($data['error']['message']) ) {
            wp_send_json_error( array( 'message' => esc_html( 'API Error: ', 'ai-tryon-for-woocommerce' ) . $data['error']['message'] ) );
        } else {
            $status_code = wp_remote_retrieve_response_code( $response );
            $raw_body = wp_remote_retrieve_body( $response );
            wp_send_json_error( array( 'message' => sprintf( esc_html( 'Connection failed (HTTP %s). Details: %s', 'ai-tryon-for-woocommerce' ), $status_code, esc_html(substr($raw_body, 0, 200)) ) ) );
        }
    }
}

add_action( 'wp_ajax_qcld_woo_chatbot_test_gemini_connection', 'qcld_woo_chatbot_test_gemini_connection' );
function qcld_woo_chatbot_test_gemini_connection() {
    check_ajax_referer( 'qcld_woo_chatbot_admin_nonce', 'security' );
    
    $api_key = isset( $_POST['api_key'] ) ? sanitize_text_field( wp_unslash( $_POST['api_key'] ) ) : '';
    
    if ( empty( $api_key ) ) {
        wp_send_json_error( array( 'message' => esc_html( 'API Key is empty.', 'ai-tryon-for-woocommerce' ) ) );
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
        $msg = isset($data['error']['message']) ? $data['error']['message'] : esc_html( 'API Error', 'ai-tryon-for-woocommerce' );
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
        'message'       => esc_html( 'Connection successful!', 'ai-tryon-for-woocommerce' ), 
        'image_models'  => $image_models,
        'video_models'  => $video_models
    ) );
}

add_action( 'wp_ajax_qcld_woo_chatbot_verify_model_capability', 'qcld_woo_chatbot_verify_model_capability' );
function qcld_woo_chatbot_verify_model_capability() {
    check_ajax_referer( 'qcld_woo_chatbot_admin_nonce', 'security' );

    $provider           = isset($_POST['provider']) ? sanitize_text_field(wp_unslash($_POST['provider'])) : '';
    $generation_type    = isset($_POST['generation_type']) ? sanitize_text_field(wp_unslash($_POST['generation_type'])) : 'image';
    $model              = isset($_POST['model']) ? sanitize_text_field(wp_unslash($_POST['model'])) : '';
    $api_key            = isset($_POST['api_key']) ? sanitize_text_field(wp_unslash($_POST['api_key'])) : '';

    if (empty($api_key) || empty($model)) {
        wp_send_json_error(array('message' => esc_html( 'Missing API key or model.', 'ai-tryon-for-woocommerce' )));
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
            $msg = isset($body['error']['message']) ? $body['error']['message'] : sprintf( esc_html( 'Generation failed. (HTTP %s)', 'ai-tryon-for-woocommerce' ), wp_remote_retrieve_response_code($response) );
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
            wp_send_json_success(array('message' => esc_html( 'Model verified successfully.', 'ai-tryon-for-woocommerce' )));
        } else {
            $body = json_decode( wp_remote_retrieve_body( $response ), true );
            $msg = isset($body['error']['message']) ? $body['error']['message'] : sprintf( esc_html( 'Model verification failed. (HTTP %s)', 'ai-tryon-for-woocommerce' ), $code );
            wp_send_json_error( array( 'message' => $msg ) );
        }
    } else {
        wp_send_json_error(array('message' => esc_html( 'Invalid provider.', 'ai-tryon-for-woocommerce' )));
    }
}


// 4. Secure AJAX Endpoint to Route AI Requests Backend
add_action( 'wp_ajax_qcld_woo_chatbot_generate_media', 'qcld_woo_chatbot_handle_generation_backend' );
add_action( 'wp_ajax_nopriv_qcld_woo_chatbot_generate_media', 'qcld_woo_chatbot_handle_generation_backend' );

function qcld_woo_chatbot_get_appropriate_model( $provider, $generation_type ) {
    
    if ( $provider === 'openai' ) {
        return get_option('qcld_woo_chatbot_openai_image_model', 'dall-e-3');
    } elseif ( $provider === 'gemini' ) {
        return str_replace('models/', '', get_option('qcld_woo_chatbot_gemini_image_model', 'gemini-2.5-flash-image'));
    }
    return '';
}

function qcld_woo_chatbot_build_generation_prompt( $product_name, $product_image, $second_image, $prompt ) {
    $product_image_url_val = '';
    if ( !empty($product_image) ) {
        if ( stripos($product_image, 'data:image') === 0 ) {
            $product_image_url_val = "[Image provided visually via Base64 attachment]";
        } else {
            $product_image_url_val = esc_url($product_image);
        }
    }
    
    $second_image_url_val = '';
    if ( !empty($second_image) ) {
        if ( stripos($second_image, 'data:image') === 0 ) {
            $second_image_url_val = "[Image provided visually via Base64 attachment]";
        } else {
            $second_image_url_val = esc_url($second_image);
        }
    }
    
    $structure = get_option('qcld_woo_chatbot_build_generation_prompt', 'Product name: {product_name}. Product Image URL: {product_image_url}. Reference Image URL: {second_image_url}. Scenario instruction: {scenario}');
    
    $final = str_replace(
        array('{product_name}', '{product_image_url}', '{second_image_url}', '{scenario}'),
        array($product_name, $product_image_url_val, $second_image_url_val, $prompt),
        $structure
    );
    
    return $final;
}

function qcld_woo_chatbot_execute_openai_image_generation( $final_prompt, $api_key, $model, $api_size ) {
    $base_prompt = get_option('qcld_woo_chatbot_openai_system_prompt', 'Create a realistic virtual try-on image. The first image is the product to wear/use. The second image is the model/user/customer photo. Put the product on the person naturally with correct proportions, lighting, and perspective. Keep a neutral background suitable for eCommerce. Do not change the model/user/customer face appearance. Additional context: ');
    $openai_system_prompt = trim($base_prompt) . " " . $final_prompt;

    $payload = array(
        'model'  => $model,
        'prompt' => $openai_system_prompt,
        'n'      => 1,
        'size'   => $api_size
    );

    $response = wp_remote_post( 'https://api.openai.com/v1/images/generations', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type'  => 'application/json',
        ),
        'body'    => wp_json_encode($payload),
        'timeout' => 60
    ));

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => esc_html( 'OpenAI API Request Failed: ', 'ai-tryon-for-woocommerce' ) . $response->get_error_message() ) );
    }

    $body = json_decode( wp_remote_retrieve_body( $response ), true );
    
    if ( isset( $body['data'][0]['url'] ) ) {
        $url = $body['data'][0]['url'];
        $html = '<img src="' . esc_url_raw($url) . '" alt="AI Try On Render Outcome" class="qcld_woo_chatbot-output-media" />';
        wp_send_json_success( array( 'html' => $html ) );
    } elseif ( isset( $body['data'][0]['b64_json'] ) ) {
        $b64 = $body['data'][0]['b64_json'];
        $data_uri = 'data:image/png;base64,' . $b64;
        $html = '<img src="' . $data_uri . '" alt="AI Try On Render Outcome" class="qcld_woo_chatbot-output-media" />';
        wp_send_json_success( array( 'html' => $html ) );
    } else {
        $msg = isset($body['error']['message']) ? $body['error']['message'] : sprintf( esc_html( 'Generation execution failure. %s', 'ai-tryon-for-woocommerce' ), wp_remote_retrieve_response_code($response) );
        wp_send_json_error( array( 'message' => esc_html( 'OpenAI Error: ', 'ai-tryon-for-woocommerce' ) . $msg ) );
    }
}

function qcld_woo_chatbot_execute_gemini_image_generation( $final_prompt, $api_key, $model, $product_image, $second_image ) {
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . $api_key;
    
    $base_prompt = get_option('qcld_woo_chatbot_gemini_prompt', 'Create a realistic virtual try-on image. The first image is the product to wear/use. The second image is the model/user/customer photo. Put the product on the person naturally with correct proportions, lighting, and perspective. Keep a neutral background suitable for eCommerce. Do not change the model/user/customer face appearance. Additional context: ');
    $gemini_prompt = trim($base_prompt) . " " . $final_prompt;
    
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
    }

    $payload = array(
        'contents' => array(
            array( 'parts' => $parts )
        ),
        'generationConfig' => array(
            'responseModalities' => array( 'IMAGE' )
        )
    );

    $response = wp_remote_post( $url, array(
        'headers' => array( 'Content-Type' => 'application/json' ),
        'body'    => wp_json_encode($payload),
        'timeout' => 60
    ));

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => esc_html( 'Gemini API Request Failed: ', 'ai-tryon-for-woocommerce' ) . $response->get_error_message() ) );
    }

    $body = json_decode( wp_remote_retrieve_body( $response ), true );

    if ( isset($body['candidates'][0]['content']['parts'][0]['inlineData']) ) {
        $inline_data = $body['candidates'][0]['content']['parts'][0]['inlineData'];
        $data_uri = 'data:' . $inline_data['mimeType'] . ';base64,' . $inline_data['data'];
        $html = '<img src="' . $data_uri . '" alt="AI Try On Render Outcome" class="qcld_woo_chatbot-output-media" />';
        wp_send_json_success( array( 'html' => $html ) );
    } elseif ( isset($body['candidates'][0]['content']['parts'][0]['text']) ) {
        $text_context = trim($body['candidates'][0]['content']['parts'][0]['text']);
        if ( filter_var($text_context, FILTER_VALIDATE_URL) ) {
            $html = '<img src="' . esc_url_raw($text_context) . '" alt="AI Try On Render Outcome" class="qcld_woo_chatbot-output-media" />';
        } else {
            $html = '<div class="qcld_woo_chatbot-text-block">' . wpautop(esc_html($text_context)) . '</div>';
        }
        wp_send_json_success( array( 'html' => $html ) );
    } elseif ( isset($body['candidates'][0]['finishReason']) ) {
        wp_send_json_error( array( 'message' => esc_html( 'Gemini generation stopped. Reason: ', 'ai-tryon-for-woocommerce' ) . $body['candidates'][0]['finishReason'] ) );
    } else {
        $msg = isset($body['error']['message']) ? $body['error']['message'] : esc_html( 'Unknown Gemini Error.', 'ai-tryon-for-woocommerce' );
        wp_send_json_error( array( 'message' => esc_html( 'Gemini API Error: ', 'ai-tryon-for-woocommerce' ) . $msg ) );
    }
}



function qcld_woo_chatbot_handle_generation_backend() {
    // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
    @set_time_limit( 300 );
    check_ajax_referer( 'qcld_woo_chatbot_generation_nonce', 'security' );

    // Enforce Rate Limit
    $limit = get_option('qcld_woo_chatbot_generation_limit', '');
    if ( $limit !== '' && intval($limit) > 0 ) {
        $limit_val = intval($limit);
        $remote_addr = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
        $user_identifier = is_user_logged_in() ? 'user_' . get_current_user_id() : 'ip_' . $remote_addr;
        $transient_name = 'qcld_woo_chatbot_gen_limit_' . md5($user_identifier);
        
        $current_count = get_transient($transient_name);
        if ( $current_count === false ) {
            $current_count = 0;
        }
        
        if ( $current_count >= $limit_val ) {
            wp_send_json_error( array( 'message' => esc_html( 'You have reached your daily limit for AI generations. Please try again tomorrow.', 'ai-tryon-for-woocommerce' ) ) );
        }
        
        set_transient($transient_name, $current_count + 1, DAY_IN_SECONDS);
    }

    $prompt          = isset($_POST['prompt']) ? sanitize_text_field( wp_unslash( $_POST['prompt'] ) ) : '';
    $product_name    = isset($_POST['product_name']) ? sanitize_text_field( wp_unslash( $_POST['product_name'] ) ) : '';
    $generation_type = isset($_POST['generation_type']) ? sanitize_text_field( wp_unslash( $_POST['generation_type'] ) ) : 'image';
    $provider        = get_option( 'qcld_woo_chatbot_api_provider', 'openai' );
    
    if ( $generation_type === 'video' ) {
        $provider = 'gemini'; // Force Gemini for video
    }
    
    $api_key = $provider === 'openai' ? get_option( 'qcld_woo_chatbot_openai_api_key', '' ) : get_option( 'qcld_woo_chatbot_gemini_api_key', '' );
    $model   = qcld_woo_chatbot_get_appropriate_model( $provider, $generation_type );

    if ( empty( $api_key ) ) {
        wp_send_json_error( array( 'message' => esc_html( 'API Configuration missing. Contact store administrator.', 'ai-tryon-for-woocommerce' ) ) );
    }

    $product_image = isset($_POST['product_image']) ? sanitize_textarea_field( wp_unslash( $_POST['product_image'] ) ) : '';
    $second_image  = isset($_POST['second_image']) ? sanitize_textarea_field( wp_unslash( $_POST['second_image'] ) ) : '';
    
    $final_prompt = qcld_woo_chatbot_build_generation_prompt( $product_name, $product_image, $second_image, $prompt );
    
    $frontend_size = get_option('qcld_woo_chatbot_image_size', '1024x1792');
    
    if ( $provider === 'openai' ) {
        $api_size = $frontend_size;
        if ( $model === 'dall-e-3' && !in_array($api_size, ['1024x1024', '1024x1792', '1792x1024']) ) {
            $api_size = '1024x1024'; // Fallback to square if 256/512 selected but using DALL-E 3
        } elseif ( $model !== 'dall-e-3' && !in_array($api_size, ['256x256', '512x512', '1024x1024']) ) {
            $api_size = '1024x1024'; // Fallback to square if portrait/landscape selected but using DALL-E 2
        }
        qcld_woo_chatbot_execute_openai_image_generation( $final_prompt, $api_key, $model, $api_size );
    } else {
        $final_prompt .= " Generate the image in a " . $frontend_size . " format.";
        qcld_woo_chatbot_execute_gemini_image_generation( $final_prompt, $api_key, $model, $product_image, $second_image );
    }
    
}


// 5. Add Media Library Save Endpoint
add_action( 'wp_ajax_qcld_woo_chatbot_save_media', 'qcld_woo_chatbot_save_media_to_library' );
function qcld_woo_chatbot_save_media_to_library() {
    check_ajax_referer( 'qcld_woo_chatbot_generation_nonce', 'security' );
    
    $media_data = isset($_POST['media_data']) ? sanitize_textarea_field( wp_unslash( $_POST['media_data'] ) ) : '';
    
    if ( empty($media_data) ) {
        wp_send_json_error( array('message' => esc_html( 'No media data provided.', 'ai-tryon-for-woocommerce' )) );
    }

    require_once( ABSPATH . 'wp-admin/includes/image.php' );
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
    require_once( ABSPATH . 'wp-admin/includes/media.php' );

    if ( stripos($media_data, 'data:image') === 0 || stripos($media_data, 'data:video') === 0 ) {
        preg_match('/data:((image|video)\/[^;]+);base64,(.+)/', $media_data, $matches);
        if ( count($matches) !== 4 ) {
            wp_send_json_error( array('message' => esc_html( 'Invalid base64 data.', 'ai-tryon-for-woocommerce' )) );
        }
        $mime = $matches[1];
        $type = $matches[2];
        $base64 = $matches[3];
        
        $ext = str_replace(array('image/', 'video/'), '', $mime);
        if ($ext === 'jpeg') $ext = 'jpg';
        
        $decoded = base64_decode($base64);
        if ( !$decoded ) {
            wp_send_json_error( array('message' => esc_html( 'Failed to decode base64.', 'ai-tryon-for-woocommerce' )) );
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
                wp_send_json_success( array('message' => esc_html( 'Saved to Media Library!', 'ai-tryon-for-woocommerce' )) );
            }
        }
        wp_send_json_error( array('message' => esc_html( 'Failed to save base64 media.', 'ai-tryon-for-woocommerce' )) );
    } else if ( filter_var($media_data, FILTER_VALIDATE_URL) ) {
        $tmp = download_url( $media_data );
        if ( is_wp_error( $tmp ) ) {
            wp_send_json_error( array('message' => esc_html( 'Failed to download media from URL.', 'ai-tryon-for-woocommerce' )) );
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
            wp_send_json_error( array('message' => esc_html( 'Failed to save downloaded media.', 'ai-tryon-for-woocommerce' )) );
        }
        wp_send_json_success( array('message' => esc_html( 'Saved to Media Library!', 'ai-tryon-for-woocommerce' )) );
    } else {
        wp_send_json_error( array('message' => esc_html( 'Invalid media format.', 'ai-tryon-for-woocommerce' )) );
    }
}
