<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="card-body p-sm-0">
    
    <ul class="nav nav-tabs">
        <li><a  class="active" data-toggle="tab" href="#wp-chatbot-gemini-settings"><i class="dashicons dashicons-admin-home"></i><?php echo esc_html__('Gemini settings', 'woowbot-woocommerce-chatbot'); ?></a></li>
        <li><a data-toggle="tab" href="#wp-chatbot-gemini-rag"><i class="dashicons dashicons-admin-generic"></i><?php echo esc_html__('RAG', 'woowbot-woocommerce-chatbot'); ?></a></li>
        <li><a data-toggle="tab" href="#wp-chatbot-gemini-help"><i class="dashicons dashicons-editor-help"></i><?php echo esc_html__('Gemini Help', 'woowbot-woocommerce-chatbot'); ?></a></li>
    </ul>
    <div class="qcld-tab-content-main">
    <div class="tab-content">
        <div id="wp-chatbot-gemini-settings" class="tab-pane in active">

            <div class="row gx-0">
                <div class="mb-3">
                    <div class="form-check form-switch my-4">
                        <input class="form-check-input" type="checkbox" <?php echo (get_option('qcld_gemini_enabled') == 1) ? 'checked' : ''; ?>  role="switch" value="" id="qcld_gemini_enabled">
                        <label class="form-check-label" for="qcld_gemini_enabled">
                        <?php esc_html_e('Enable Gemini AI','woowbot-woocommerce-chatbot'); ?><span class="qcld-red-text"> <?php esc_html_e('(if you want results from Gemini only, disable Site Search from Settings->Start Menu)','woowbot-woocommerce-chatbot'); ?></span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="row gx-0">
                <div class="form-check form-switch my-4">
                    <input class="form-check-input" type="checkbox" <?php echo (get_option('gemeni_context_awareness_enabled') == '1') ? 'checked' : ''; ?>  role="switch" value="" id="gemini_is_context_awareness_enabled">
                    <label class="form-check-label" for="gemini_is_context_awareness_enabled">
                    <?php  esc_html_e( 'Context awareness','woowbot-woocommerce-chatbot'); ?>
                    </label>
                    
                </div>
            </div>
            <div class="row gx-0">
                <div class="mb-3">
                    <div class="form-check form-switch my-4">
                        <input class="form-check-input" type="checkbox" <?php echo (get_option('qcld_gemini_page_suggestion_enabled') == '1') ? 'checked' : ''; ?>  role="switch" value="" id="qcld_gemini_page_suggestion_enabled">
                        <label class="form-check-label" for="qcld_gemini_page_suggestion_enabled">
                        <?php  esc_html_e( 'Enable page suggestions with Gemini Result','woowbot-woocommerce-chatbot'); ?>
                        </label>
                    </div>
            
                <!-- POST TYPE -->
                <div class="form-check form-switch my-4">
                <label><?php esc_html_e( 'Select POST TYPE(s) to include with search results', 'woowbot-woocommerce-chatbot' ); ?></label>
                    <div id="wp-chatbot-post-converter">
                        <ul class="checkbox-list">
                            <?php
                                $get_cpt_args = array(
                                    'public'   => true,
                                );
                                $post_types = get_post_types($get_cpt_args, 'object');
                                foreach ($post_types as $post_type) {
                                    if ($post_type->name != 'attachment') {
                                        $is_pro = !in_array($post_type->name, ['post', 'page','product']);
                                        ?>
                                        <div class="form-check form-check-inline">
                                            <input
                                                id="site_gemini_search_posttypes_<?php echo esc_attr( $post_type->name ); ?>"
                                                type="checkbox"
                                                name="site_gemini_search_posttypes[]"
                                                value="<?php echo esc_attr( $post_type->name ); ?>"
                                                <?php echo esc_attr(($is_pro) ? 'disabled' : ''); ?>
                                                
                                                <?php echo ((get_option('qcld_openai_relevant_post') != '') && in_array($post_type->name, get_option('qcld_openai_relevant_post'))) ? 'checked' : ''; ?>>
                                            <label class="form-check-label <?php echo esc_attr($is_pro ? 'pro-locked' : ''); ?>" for="site_gemini_search_posttypes_<?php echo esc_attr( $post_type->name ); ?>">
                                                <?php echo esc_html( $post_type->name ); ?>
                                            
                                            </label>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>
                        </ul>
                    </div>
                </div>
            <!-- /POST TYPE -->
        
                </div>  
            </div>
            <div class="row gx-0">
                <div class="mb-3 form-check ">
                    <label for="qcld_gemini_api_key" class="form-label"><?php esc_html_e('Gemini API Key','woowbot-woocommerce-chatbot');?></label>
                    <input type="password" class="form-control" id="qcld_gemini_api_key" name="qcld_gemini_api_key" placeholder="Enter your Gemini API Key" value="<?php echo esc_attr(get_option('qcld_gemini_api_key')); ?>">
                    <small class="form-text text-muted"><?php esc_html_e('Get your API key from https://aistudio.google.com/app/apikey. ','woowbot-woocommerce-chatbot'); ?></br><span class="qcld-red-text"><?php esc_html_e('It requires a paid Gemini API plan', 'woowbot-woocommerce-chatbot'); ?> </span></small>
                </div>
            </div>
            <div class="row gx-0">
                <div class="mb-3 form-check ">
                    <label for="qcld_gemini_model" class="form-label"><?php esc_html_e('Gemini Model','woowbot-woocommerce-chatbot');?></label>
                    <div class="input-group">
                        <select class="form-control" id="qcld_gemini_model" name="qcld_gemini_model">
                            <?php
                                $selected_model = get_option('qcld_gemini_model');
                                if($selected_model){
                                    echo '<option value="'.esc_attr($selected_model).'" selected>'.esc_html($selected_model).'</option>';
                                } else {
                                    echo '<option value="gemini-2.5-flash" selected>gemini-2.5-flash</option>';
                                }
                            ?>
                        </select>
                        <button type="button" class="btn btn-primary" id="qcld_gemini_fetch_models"><?php esc_html_e('Fetch Models', 'woowbot-woocommerce-chatbot'); ?></button>
                    </div>
                    <small class="form-text text-muted"><?php esc_html_e('Select your Gemini model. Click "Fetch Models" to update the list if you just added your API key.','woowbot-woocommerce-chatbot'); ?><br><span class="qcld-red-text"><?php esc_html_e('Please select a your paid model all model on the list are might not be available for free plans', 'woowbot-woocommerce-chatbot'); ?> </span></small>
                </div>
            </div>
            <div class="row g-0"> 
                
                <div class="row gx-0">
                    <div class="mb-3 form-check ">
                        <label for="qcld_gemini_prepend_content" class="form-label"><?php esc_html_e('Your Prompt to be Added before the User Query for Customized Results (Optional)','woowbot-woocommerce-chatbot');?></label>

                        <textarea type="text" class="form-control" id="qcld_gemini_prepend_content" name="qcld_gemini_prepend_content" placeholder="<?php echo esc_attr__('Content for the response','woowbot-woocommerce-chatbot'); ?>"><?php echo esc_attr( get_option('qcld_gemini_prepend_content') ); ?></textarea>
                        
                    </div>

                </div>

                <div class="row gx-0">
                    <div class="mb-3 form-check ">
                        <label for="qcld_gemini_append_content" class="form-label"><?php esc_html_e('Your Prompt to be Appended at the End of the User Query for Customized Results (Optional)','woowbot-woocommerce-chatbot');?></label>

                        <textarea type="text" class="form-control" id="qcld_gemini_append_content" name="qcld_gemini_append_content" placeholder="<?php echo esc_attr__('Content for the response','woowbot-woocommerce-chatbot'); ?>"><?php echo esc_attr( get_option('qcld_gemini_append_content') ); ?></textarea>
                        
                    </div>
                </div>
                </div>
                <div class="mb-3">
                    <a class="btn btn-success" id="qcld_save_gemini_setting"><?php esc_html_e('Save settings','woowbot-woocommerce-chatbot');?></a>
                </div>
            
        </div>
        <div id="wp-chatbot-gemini-rag" class="tab-pane">
            <div class="col-sm-12">
                <div class="wrap">
                    <h3><?php esc_html_e('Gemini RAG Settings','woowbot-woocommerce-chatbot');?></h3>
                    <p><?php esc_html_e('If you enable RAG, you must configure the','woowbot-woocommerce-chatbot');?> <a id="ai-knowledge-base-tab-gemini" href="<?php echo esc_url( admin_url('admin.php?page=chatbot_ai_setting#ai-knowledge-base-tab') ); ?>"><?php esc_html_e('Knowledgebase','woowbot-woocommerce-chatbot');?></a> <?php esc_html_e('for Post types and other data to embed.','woowbot-woocommerce-chatbot');?> </p><span class="qcld-red-text"><?php esc_html_e('It requires a paid Gemini API plan', 'woowbot-woocommerce-chatbot'); ?> </span>
                    <div class="form-check form-switch my-4">
                        <input class="form-check-input"
                            type="checkbox"
                            id="is_page_rag_enabled_gemini"
                            <?php echo (get_option('is_page_rag_enabled') == '1') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_page_rag_enabled_gemini">
                            <?php esc_html_e('Enable RAG','woowbot-woocommerce-chatbot');?>
                        </label>
                    </div>
                    <div class="mb-3">
                        <a class="btn btn-success" id="qcld_save_gemini_setting"><?php esc_html_e( 'Save settings','woowbot-woocommerce-chatbot');?></a>
                    </div>
                </div>
            <div class="qcld-row">
            <div class="alert alert-info mt-20" role="alert">
                        <p><b><?php echo esc_html__( 'Fine Tuning VS GPT Assistants VS RAG:', 'woowbot-woocommerce-chatbot' ); ?></b></p>
                        <p>
                        <?php echo esc_html__( 'We suggest using GPT Assistants or RAG instead of Fine Tuning as Fine Tuning requires a lot of properly formatted data and GPT Assistants are easier to set up. You can still use your website data to train the bot.', 'woowbot-woocommerce-chatbot' ); ?>
                        </p></br>
                        <p>
                        <b><?php echo esc_html__( 'How to Use RAG in This Plugin:', 'woowbot-woocommerce-chatbot' ); ?></b>
                        <ol>
                            <li><?php echo esc_html__( 'Enable RAG from the settings panel', 'woowbot-woocommerce-chatbot' ); ?></li>
                            <li><?php echo esc_html__( 'Click “Embed All Selected Sources” button, after selecting the sources from the', 'woowbot-woocommerce-chatbot' ); ?> <a href="<?php echo esc_url( admin_url('admin.php?page=chatbot_ai_setting#ai-knowledge-base-tab') ); ?>" target="_blank">knowledgebase tab</a></li>
                            <li><?php echo esc_html__( '(Optional) Upload PDFs or CSV files for embedding', 'woowbot-woocommerce-chatbot' ); ?></li>
                            <li><?php echo esc_html__( 'The system automatically stores embeddings in the database', 'woowbot-woocommerce-chatbot' ); ?></li>
                            <li><?php echo esc_html__( 'User questions will now be answered using your site’s knowledge base', 'woowbot-woocommerce-chatbot' ); ?></li> 
                            <li><?php echo esc_html__( 'You need to configure the OpenAI API key, AI Model and System Command under the main OpenAI Settings', 'woowbot-woocommerce-chatbot' ); ?></li> 
                        </ol>
                        <strong><?php echo esc_html__( 'You can update or re-embed content at any time without retraining.', 'woowbot-woocommerce-chatbot' ); ?></strong>
                        </p>
                        
                    </div>

            </div>	
            </div>
        </div>
        <div id="wp-chatbot-gemini-help" class="tab-pane">
            <div class="accordion" id="qcldopenaiaccordion">
                <div class="wp-chatbot-gemini-help">
                    <div id="panelsStayOpen-headingZero-gemini">
                        <h2 class="mb-0">
                            <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#panelsStayOpen-collapseZero-gemini" aria-expanded="false" aria-controls="panelsStayOpen-collapseZero-gemini">
                                <?php esc_html_e( 'Getting Started with gemini','woowbot-woocommerce-chatbot');?>
                            </button>
                        </h2>
                    </div>
                    <div id="panelsStayOpen-collapseZero-gemini" class="collapse" aria-labelledby="panelsStayOpen-headingZero-gemini" data-parent="#qcldopenaiaccordion">
                        <div class="card-body-gemini">
                            <p><?php esc_html_e( 'Gemini is an unified Interface or Aggregator for LLMs. You can choose from hundreds of different AI models from OpenAI to Deepseek or Claude to get AI responses.','woowbot-woocommerce-chatbot');?></p>
                            <p><?php esc_html_e( 'All you have to do is add the Gemini API Key and select an Gemini Model.','woowbot-woocommerce-chatbot');?></p>
                            <p><?php esc_html_e( 'Grab your API key from','woowbot-woocommerce-chatbot');?> <a href="https://gemini.ai/settings/keys">HERE</a></p>
                            <p><?php esc_html_e( 'Please make sure that DialogFlow, OpenAI are Disabled if you want Gemini to work.','woowbot-woocommerce-chatbot');?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="qcld-tab-content-right-into">

        <div class="qcld-tab-content-right-into-box">

        <div class="wp-chatbot-admingradient-color">
            <img class="wp-chatbot-admin-banner" src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/template-sample.png' ); ?>" alt="">
                <h3 class="wp-chatbot-admincart-title"><?php esc_html_e( 'Upgrade To', 'woowbot-woocommerce-chatbot' ); ?> <span><?php esc_html_e( 'Pro', 'woowbot-woocommerce-chatbot' ); ?></span></h3>
                <ul class="feature-list">
                        
                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt=""><?php esc_html_e( 'Core ChatBot Pro', 'woowbot-woocommerce-chatbot' ); ?></li>
                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt=""><?php esc_html_e( 'WooCommerce module', 'woowbot-woocommerce-chatbot' ); ?></li>
                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt=""><?php esc_html_e( 'Chat Sessions and Histories', 'woowbot-woocommerce-chatbot' ); ?></li>
                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt=""><?php esc_html_e( 'Extended Search Module', 'woowbot-woocommerce-chatbot' ); ?></li>
                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt=""><?php esc_html_e( 'Simple text Responses Pro Module', 'woowbot-woocommerce-chatbot' ); ?></li>
                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt=""><?php esc_html_e( 'Conversational Forms Pro Module', 'woowbot-woocommerce-chatbot' ); ?></li>
                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt=""><?php esc_html_e( 'OpenAI Pro Adv.(Training, Fine Tuning, Assistant)', 'woowbot-woocommerce-chatbot' ); ?></li>
                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt=""><?php esc_html_e( 'Live (Human) Chat Module', 'woowbot-woocommerce-chatbot' ); ?></li>
                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt=""><?php esc_html_e( 'Tavily Search API module', 'woowbot-woocommerce-chatbot' ); ?></li>
                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt=""><?php esc_html_e( 'Extended UI Module', 'woowbot-woocommerce-chatbot' ); ?></li>
                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt=""><?php esc_html_e( 'WebHook & Mailing List Module', 'woowbot-woocommerce-chatbot' ); ?></li>
                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt=""><?php esc_html_e( 'White Label Module', 'woowbot-woocommerce-chatbot' ); ?></li>
                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt=""><?php esc_html_e( 'FaceBook Messenger Module', 'woowbot-woocommerce-chatbot' ); ?></li>
                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt=""><?php esc_html_e( 'WhatsApp through Twilio Module', 'woowbot-woocommerce-chatbot' ); ?></li>
                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt=""><?php esc_html_e( 'Multi Language Module', 'woowbot-woocommerce-chatbot' ); ?></li>
                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt=""><?php esc_html_e( 'Voice Message Module', 'woowbot-woocommerce-chatbot' ); ?></li>
                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt=""><?php esc_html_e( 'Telegram Module', 'woowbot-woocommerce-chatbot' ); ?></li>
                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt=""><?php esc_html_e( 'Priority Technical Support', 'woowbot-woocommerce-chatbot' ); ?></li>

                </ul>

            <a class="wp-chatbot-admin-pro-upgrade-button" target="_blank" href="<?php echo esc_url( 'https://woowbot.pro/pricing/');?>"><?php esc_html_e( 'Upgrade to Pro', 'woowbot-woocommerce-chatbot' ); ?> <img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/external-white.svg' ); ?>" alt=""></a>
        </div>

        </div>

    </div>

</div>
</div>