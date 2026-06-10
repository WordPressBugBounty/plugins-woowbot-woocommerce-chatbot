jQuery(function ($) {
$(document).ready(function () {
    var sbdstoredNoticeId = localStorage.getItem('qcld_wpbot_Notice_set');
    var qcld_chatbot_Notice_time_set = localStorage.getItem('qcld_wpbot_Notice_time_set');
    var notice_current_time = Math.round(new Date().getTime() / 1000);
    if ('promotion-wpchatbot' == sbdstoredNoticeId && qcld_chatbot_Notice_time_set > notice_current_time  ) {
        $('#promotion-wpchatbot').css({'display': 'none'});
    }
    $(document).on('click', '#promotion-wpchatbot .notice-dismiss', function(e){
        var currentDom = $(this);
        var currentWrap = currentDom.closest('.notice');
        currentWrap.css({'display': 'none'});
        localStorage.setItem('qcld_wpbot_Notice_set', 'promotion-wpchatbot');
        var ts = Math.round(new Date().getTime() / 1000);
        var tsYesterday = ts + (24 * 3600);
        localStorage.setItem('qcld_wpbot_Notice_time_set', tsYesterday);
    });
    [].slice.call(document.querySelectorAll('.woo-chatbot-tabs')).forEach(function (el) {
        new CBPFWTabs(el);
    });
        $(document).on('change','#qcld_woo_chatbot_change_bg',function (e) {

            if($(this).is(':checked')){

                $('.qcld-woo-chatbot-board-bg-container').show();

            }else{

                $('.qcld-woo-chatbot-board-bg-container').hide();

            }

        });
        //Custom Backgroud image

        $('.qcld_woo_chatbot_board_bg_button').click(function(e) {

            e.preventDefault();

            var image = wp.media({

                title: 'Custom Agent',

                // mutiple: true if you want to upload multiple files at once

                multiple: false

            })

                .open()

                .on('select', function(e){

                    // This will return the selected image from the Media Uploader, the result is an object

                    var uploaded_image = image.state().get('selection').first();

                    var image_url = uploaded_image.toJSON().url;

                    // Let's assign the url value to the hidden field value and img src.

                    $('#qcld_woo_chatbot_board_bg_image').attr('src',image_url).css({ display: "block" });
                    //$('#qcld_woo_chatbot_board_bg_image').css({ display: "block" });

                    $('#qcld_woo_chatbot_board_bg_path').val(image_url);

                });

        });

        $('.wp_chatbot_custom_agent_button').click(function(e) {

            e.preventDefault();
    
            var image = wp.media({
                title: 'Custom Agent',
                multiple: false
            })
                .open()
                .on('select', function(e){
                    // This will return the selected image from the Media Uploader, the result is an object
                    var uploaded_image = image.state().get('selection').first();
                    var image_url = uploaded_image.toJSON().url;
                    // Let's assign the url value to the hidden field value and img src.
                    $('#wp_chatbot_custom_agent_src').attr('src',image_url);
                    $('#wp_chatbot_custom_agent_path').val(image_url);
                    $('#wp_chatbot_agent_image_custom').val(image_url);
                });
    
        });
        $('.wp_chatbot_custom_icon_button').click(function(e) {

            e.preventDefault();
    
            var image = wp.media({
                title: 'Custom chatbot icon',
                multiple: false
            })
                .open()
                .on('select', function(e){
                    // This will return the selected image from the Media Uploader, the result is an object
                    var uploaded_image = image.state().get('selection').first();
                    var image_url = uploaded_image.toJSON().url;
                    console.log(image_url)
                    // Let's assign the url value to the hidden field value and img src.
                    $('#woo_chatbot_custom_icon').attr('src',image_url);
                    $('#wp_chatbot_custom_icon_path').val(image_url);
                  //  $('#wp_chatbot_agent_image_custom').val(image_url);
                });
    
        });
        jQuery(document).ready(function($){
            jQuery('#qcld-quick-flyout').on('click', function() {
            jQuery(this).toggleClass('is-open');
            });
        });
        jQuery(document).ready(function($){
            $('#qcld-quick-flyout').on('click', function(){
                $('body').toggleClass('woowbot-flyout');
            });
        });

});
        $('.qcl-openai').on('click', '.rag-delete-doc', function () {
            var id = $(this).data('id');
            
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: ajax_object.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'qcld_rag_delete_document',
                            nonce: ajax_object.ajax_nonce,
                            id: id
                        },
                        success: function (res) {
                            if (res.success) {
                                $('#rag-doc-' + id).remove();
                                Swal.fire('Deleted!', res.data, 'success');
                            } else {
                                Swal.fire('Error', res.data, 'error');
                            }
                        }
                    });
                }
            })
        });

        $('.qcl-openai').on('click', '#qcld_check_connection', function(){
            jQuery('#rotationloader').css('display','inline-block'); 
            $.ajax({
                url: ajax_object.ajax_url,
                type:'POST',
                data:    ({action  : 'openai_troubleshooting',nonce:ajax_object.ajax_nonce}),
                success: function(data){
                    var datas = JSON.parse(data);
                    $('#result').html(datas);
                    Swal.fire({
                        title: datas.title,
                        html: datas.msg,
                        width: 450,
                        icon: datas.icon,
                        confirmButtonText: 'Got it',
                        customClass: 'connection-modal',
                    })
                    jQuery('#rotationloader').css('display','none'); 
                //   location.reload();
                }
            });
        })
        $('#ai-provider-selector').on('change', function () {
            var selected = $(this).val();
            $('.ai-settings-provider').hide();
            $('#' + selected + '-settings').show();
            $('#rag-settings').hide(); // Ensure RAG is hidden when switching providers
        });

        if (window.location.hash.indexOf('#ai-knowledge-base-tab') === 0) {
            $('.ai-settings-provider').hide();
            $('#rag-settings').show();
        }

        $('#ai-knowledge-base-tab').on('click', function (e) {
            e.preventDefault();
            $('.ai-settings-provider').hide();
            $('#rag-settings').show();
            window.location.hash = 'ai-knowledge-base-tab';
            // Optional: Reset selector or add visual indication
        });
        // common Ai settings tab
        $('#qcld-common-ai-settings').on('click', function (e) {
            e.preventDefault();
            $('.ai-settings-provider').hide();
            $('#common-ai-settings').show();
            window.location.hash = 'common-ai-settings';
            // Optional: Reset selector or add visual indication
        });
        $('#ai-knowledge-base-tab-openai').on('click', function (e) {
            e.preventDefault();
            $('.ai-settings-provider').hide();
            $('#rag-settings').show();
            window.location.hash = 'ai-knowledge-base-tab';
            // Optional: Reset selector or add visual indication
        });
        // Delete Document
        $('#ai-provider-selector').on('change', function () {
            var selected = $(this).val();
            $('.ai-settings-provider').hide();
            $('#' + selected + '-settings').show();
            console.log(('#' + selected + '-settings'))
            $('#rag-settings').hide(); // Ensure RAG is hidden when switching providers
        });
        $(document).on('click','#qcld_article_keyword_suggestion',function(e){
            var qcld_keyword_suggestion         = $('#qcld_article_keyword_suggestion_mf').val();
            var qcld_keyword_number             = $('#qcld_keyword_number').val();
            var qcld_article_language           = $('#qcld_article_language').val();
            var qcld_article_number_of_heading  = $('#qcld_article_number_of_heading').val();
            var qcld_article_heading_tag        = $('#qcld_article_heading_tag').val();
            var qcld_article_heading_style      = $('#qcld_article_heading_style').val();
            var qcld_article_heading_tone       = $('#qcld_article_heading_tone').val();
            var qcld_article_heading_img        = $('#qcld_article_heading_img').val();
            var qcld_article_heading_img        = $("input[name=qcld_article_heading_img]:checked").val();
            var qcld_article_heading_tagline    = $("input[name=qcld_article_heading_tagline]:checked").val();
            var qcld_article_heading_intro      = $("input[name=qcld_article_heading_intro]:checked").val();
            var qcld_article_heading_conclusion = $("input[name=qcld_article_heading_conclusion]:checked").val();
            var qcld_article_label_anchor_text  = $('#qcld_article_label_anchor_text').val();
            var qcld_article_target_url         = $('#qcld_article_target_url').val();
            var qcld_article_target_label_cta   = $('#qcld_article_target_label_cta').val();
            var qcld_article_cta_pos            = $('#qcld_article_cta_pos').val();
            var qcld_article_label_keywords     = $('#qcld_article_label_keywords').val();
            var qcld_article_label_word_to_avoid= $('#qcld_article_label_word_to_avoid').val();
            var qcld_article_label_keywords_bold= $("input[name=qcld_article_label_keywords_bold]:checked").val();
            var qcld_article_heading_faq        = $("input[name=qcld_article_heading_faq]:checked").val();
            var qcld_article_img_size           = $('#qcld_article_img_size').val();
        
            $('#qcld_article_keyword_suggestion').addClass('spinning');
            $('#qcld_article_keyword_suggestion').prop("disabled",true);
            $('#linkbait_article_keyword_data').html('');
            $.ajax({
              url: ajax_object.ajax_url,
              method: 'POST',
              data: {
                  'action': 'openai_keyword_suggestion_content',
                  'keyword'                         : qcld_keyword_suggestion,
                  'keyword_number'                  : qcld_keyword_number,
                  'qcld_article_language'           : qcld_article_language,
                  'qcld_article_number_of_heading'  : qcld_article_number_of_heading,
                  'qcld_article_heading_tag'        : qcld_article_heading_tag,
                  'qcld_article_heading_style'      : qcld_article_heading_style,
                  'qcld_article_heading_tone'       : qcld_article_heading_tone,
                  'qcld_article_heading_img'        : qcld_article_heading_img,
                  'qcld_article_heading_tagline'    : qcld_article_heading_tagline,
                  'qcld_article_heading_intro'      : qcld_article_heading_intro,
                  'qcld_article_heading_conclusion' : qcld_article_heading_conclusion,
                  'qcld_article_label_anchor_text'  : qcld_article_label_anchor_text,
                  'qcld_article_target_url'         : qcld_article_target_url,
                  'qcld_article_target_label_cta'   : qcld_article_target_label_cta,
                  'qcld_article_cta_pos'            : qcld_article_cta_pos,
                  'qcld_article_label_keywords'     : qcld_article_label_keywords,
                  'qcld_article_label_word_to_avoid': qcld_article_label_word_to_avoid,
                  'qcld_article_label_keywords_bold': qcld_article_label_keywords_bold,
                  'qcld_article_heading_faq'        : qcld_article_heading_faq,
                  'qcld_article_img_size'           : qcld_article_img_size,
                  //'selectedlanguage': selectedlanguage,
              },
              dataType: 'json',
              success: function(response) {
                //$('#linkbait_keyword_data').append(response.keywords);
                $('#qcld_article_keyword_suggestion').prop("disabled",false);
                $('#qcld_article_keyword_suggestion').removeClass('spinning');
                $('#linkbait_article_keyword_data').html('<div class="qcld_copied-content-wrap"><div class="qcld_copied-content_text btn d-none link-success">Copied</div><a class="btn btn-sm btn-secondary qcld-copied-content_text"><span class="dashicons dashicons-admin-page"></span></a></div><textarea id="qcld_content_result_msg" style="height: 20rem">' + response.keywords +'</textarea>');
                $('#linkbait_article_keyword_data').append('<div class="qcld_content_result_wrap"><div class="qcld_rewrite_result_count">' + response.keywords.split(" ").length +'</div></div>');
                        
                $('#qcld_content_result_msg').focus();
                $('#qcld_content_result_msg').focusout();
              }
            });
        
        
        
        });
        
        $('#ai-provider-selector').on('change', function() {
            var selected = $(this).val();
            $('.ai-settings-provider').hide();
            $('#' + selected + '-settings').show();
        });
        
        $('#is_rate_limiting_enabled').on('change', function () {
            if ($(this).is(":checked")) {
                $('#rate_limit_settings').show();
                $('.rate-limit-input').prop('disabled', false);
                $('.rate-limit-timeframe').prop('disabled', false);
            } else {
                $('#rate_limit_settings').hide();
                $('.rate-limit-input').prop('disabled', true);
                $('.rate-limit-timeframe').prop('disabled', true);
            }
        });
        $('.qcl-openai').on('click', '#qcld_openai_rate_limit_save_setting', function () {
            console.log('Saving rate limit settings...');
            if ($('#is_rate_limiting_enabled').is(":checked")) {
                var is_rate_limiting_enabled = 1;
            } else {
                var is_rate_limiting_enabled = 0;
            }
            var rate_limits = {};
            var rate_limit_timeframes = {};
            // Collect rate limits for each role
            $('.rate-limit-input').each(function () {
                var role = $(this).attr('id').replace('rate_limit_', '');
                var limit = $(this).val();
                rate_limits[role] = limit;
                rate_limit_timeframes[role] = $('#rate_limit_timeframe_' + role).val();
            });
            $.ajax({
                url: ajax_object.ajax_url,
                type: 'POST',
                data: ({
                    action: 'rate_limit_settings_option',
                    nonce: ajax_object.ajax_nonce,
                    is_rate_limiting_enabled: is_rate_limiting_enabled,
                    rate_limits: rate_limits,
                    rate_limit_timeframes: rate_limit_timeframes
                }),
                success: function (data) {
                    $('#result').html(data);
                    Swal.fire({
                        title: 'Your settings are saved.',
                        html: '<p style=font-size:14px>Please clear your browser <b>cache</b> and <b>cookies</b> both and reload the front end before testing. Alternatively, you can launch a new browser window in <b>Incognito</b>/Private mode (Ctrl+Shift+N in chrome) to test.</p>',
                        width: 450,
                        icon: 'success',
                        confirmButtonText: 'Got it',
                        confirmButtonWidth: 100,
                        confirmButtonClass: 'btn btn-lg'
                    })
                    // location.reload();
                }
            });
        });
        $('.qcl-openai').on('click', '#save_setting', function(){
                if ($('#is_ai_enabled').is(":checked")){
                    var is_ai_enabled = 1;
                }else{
                    var is_ai_enabled = 0;
                }
                if($('#is_stream_enabled').is(":checked")){
                    var is_stream_enabled = 1;
                }else{
                    var is_stream_enabled = 0;
                }
                if($('#is_ai_only_mode').is(":checked")){
                    var is_ai_only_mode = 1; 
                }else{
                    var is_ai_only_mode = 0;
                }
                if($('#conversation_continuity').is(":checked")){
                    var conversation_continuity = 1; 
                }else{
                    var conversation_continuity = 0;
                }
                if($('#is_relevant_enabled').is(":checked")){
                    var is_relevant_enabled = 1; 
                }else{
                    var is_relevant_enabled = 0;
                }
                if($('#is_page_suggestion_enabled').is(":checked")){
                    var is_page_suggestion_enabled = 1; 
                }else{
                    var is_page_suggestion_enabled = 0;
                }
                if($('#is_context_awareness_enabled').is(":checked")){
                    var is_context_awareness_enabled = 1; 
                }else{
                    var is_context_awareness_enabled = 0;
                }
                if($('#is_product_card_enabled').is(":checked")){
                    var is_product_card_enabled = 1; 
                }else{
                    var is_product_card_enabled = 0;
                }
                if($('#is_page_rag_enabled').is(":checked")){
                    var is_page_rag_enabled = 1; 
                }else{
                    var is_page_rag_enabled = 0;
                }
                var post_types = $.map($('input[name="site_search_posttypes[]"]:checked'), function(c){return c.value; });
                var api_key = $( "input[name='api_key']" ).val();
                var openai_engines = $("[id*='openai_engines'] :selected").val();
                var qcld_openai_prompt = $("[id*='qcld_openai_prompt'] :selected").val();
                var max_tokens = $("input[name='max_tokens']" ).val();
                var file_id = $("input[name='file_id']" ).val();
                var temperature = $("input[name='temperature']" ).val();
                var presence_penalty = $("input[name='presence_penalty']" ).val();
                var openai_exclude_keyword = jQuery('#qcld_openai_exclude_keyword').val();
                var openai_include_keyword = jQuery('#qcld_openai_include_keyword').val();
                var qcld_openai_system_content = jQuery('#qcld_openai_system_content').val();
                var qcld_openai_append_content = jQuery('#qcld_openai_append_content').val();
                if(qcld_openai_prompt == "custom_prompt"){
                    var qcld_openai_prompt_custom =  $("#qcld_openai_prompt_custom").val();
                }
                var frequency_penalty = $("input[name='frequency_penalty']" ).val();
                var openai_post_type = $("input[name='site_search_posttypes[]']:checked").map(function () {
                    return this.value;
                }).get();

                $.ajax({
                    url:  ajax_object.ajax_url,
                    type:'POST',
                    data:    ({action  : 'openai_settings_option',nonce: ajax_object.ajax_nonce,api_key: api_key,openai_engines:openai_engines,qcld_openai_prompt: qcld_openai_prompt,max_tokens:max_tokens,file_id:file_id,temperature:temperature,presence_penalty:presence_penalty,frequency_penalty:frequency_penalty,qcld_openai_prompt_custom: qcld_openai_prompt_custom,openai_exclude_keyword:openai_exclude_keyword,is_relevant_enabled:is_relevant_enabled,openai_include_keyword:openai_include_keyword,qcld_openai_enabled:is_ai_enabled,is_page_suggestion_enabled:is_page_suggestion_enabled,is_context_awareness_enabled:is_context_awareness_enabled,is_product_card_enabled:is_product_card_enabled,
                    qcld_openai_system_content:qcld_openai_system_content,qcld_openai_append_content:qcld_openai_append_content,ai_only_mode: is_ai_only_mode,conversation_continuity:conversation_continuity,openai_post_type:openai_post_type,is_stream_enabled:is_stream_enabled,is_page_rag_enabled:is_page_rag_enabled}),
                    
                    success: function(data){
                        $('#result').html(data);
                        if(data.icon == "success"){
                            var color = "color: green";
                        }else if(data.icon == "error"){
                            var color = "color: red";
                        }
                        Swal.fire({
                            title: 'Your settings are saved.',
                                html: '<p style=font-size:14px>Please clear your browser <b>cache</b> and <b>cookies</b> both and reload the front end before testing. Alternatively, you can launch a new browser window in <b>Incognito</b>/Private mode (Ctrl+Shift+N in chrome) to test.</p></b><p><span style="'+ color +'">'+ data.msg+'</span></p>',
                                width: 450,
                                icon: 'success',
                                confirmButtonText: 'Got it',
                                confirmButtonWidth: 100,
                                confirmButtonClass: 'btn btn-lg'     
                            }).then((result) => {
                            });
                    }
				});
    
            })
                    var settingsRag = document.getElementById("save_rag_setting");
        if (settingsRag) {
            $('.qcl-openai').on('click', '#save_rag_setting', function () {

                var rag_embed_pages = $('#rag_embed_pages').is(':checked') ? 1 : 0;
                var rag_embed_posts = $('#rag_embed_posts').is(':checked') ? 1 : 0;
                var rag_auto_sync_enabled = $('#rag_auto_sync_enabled').is(':checked') ? 1 : 0;
                var rag_embed_str = $('#rag_embed_str').is(':checked') ? 1 : 0;
                
                var rag_embed_cpts = [];
                $('.rag_embed_cpts_checkbox:checked').each(function() {
                    rag_embed_cpts.push($(this).val());
                });

                $.ajax({
                    url: qcld_gemini_admin_data.ajax_url,
                    type: 'POST',
                    data: ({ 
                        action: 'qcld_rag_settings_option', 
                        nonce: qcld_gemini_admin_data.ajax_nonce, 
                        rag_embed_pages: rag_embed_pages,
                        rag_embed_posts: rag_embed_posts,
                        rag_auto_sync_enabled: rag_auto_sync_enabled,
                        rag_embed_str: rag_embed_str,
                        rag_embed_cpts: rag_embed_cpts
                    }),
                    success: function (data) {
                        $('#result').html(data);
                        Swal.fire({
                            title: 'Your settings are saved.',
                            html: '<p style=font-size:14px>Please clear your browser <b>cache</b> and <b>cookies</b> both and reload the front end before testing. Alternatively, you can launch a new browser window in <b>Incognito</b>/Private mode (Ctrl+Shift+N in chrome) to test.</p>',
                            width: 450,
                            icon: 'success',
                            confirmButtonText: 'Got it',
                            confirmButtonWidth: 100,
                            confirmButtonClass: 'btn btn-lg'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $.ajax({
                                    url: qcld_gemini_admin_data.ajax_url,
                                    type: 'POST',
                                    data: {
                                        action: '',
                                        nonce: qcld_gemini_admin_data.ajax_nonce,
                                        disable_ss: 1
                                    },

                                });
                            }
                        });

                        location.reload();
                    }
                });
            })
            
            $('.qcl-openai').on('click', '#rag_embed_btn', function (e) {
                e.preventDefault();
                var btn = $(this);
                var originalText = btn.text();
                btn.text('Saving settings...');

                var rag_embed_pages = $('#rag_embed_pages').is(':checked') ? 1 : 0;
                var rag_embed_posts = $('#rag_embed_posts').is(':checked') ? 1 : 0;
                var rag_auto_sync_enabled = $('#rag_auto_sync_enabled').is(':checked') ? 1 : 0;
                var rag_embed_str = $('#rag_embed_str').is(':checked') ? 1 : 0;
                
                var rag_embed_cpts = [];
                $('.rag_embed_cpts_checkbox:checked').each(function() {
                    rag_embed_cpts.push($(this).val());
                });

                $.ajax({
                    url: qcld_gemini_admin_data.ajax_url,
                    type: 'POST',
                    data: ({
                        action: 'qcld_rag_settings_option', 
                        nonce: qcld_gemini_admin_data.ajax_nonce, 
                        rag_embed_pages: rag_embed_pages,
                        rag_embed_posts: rag_embed_posts,
                        rag_auto_sync_enabled: rag_auto_sync_enabled,
                        rag_embed_str: rag_embed_str,
                        rag_embed_cpts: rag_embed_cpts
                    }),
                    success: function (data) {
                        console.log(data);
                        btn.text('Embedding...');
                        $('#rag_embed_form').submit();
                    }
                });
            })

            // Delete Document
            $('.qcl-openai').on('click', '.rag-delete-doc', function () {
                var id = $(this).data('id');
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: qcld_gemini_admin_data.ajax_url,
                            type: 'POST',
                            data: {
                                action: 'qcld_rag_delete_document',
                                nonce: qcld_gemini_admin_data.ajax_nonce,
                                id: id
                            },
                            success: function (res) {
                                if (res.success) {
                                    $('#rag-doc-' + id).remove();
                                    Swal.fire('Deleted!', res.data, 'success');
                                } else {
                                    Swal.fire('Error', res.data, 'error');
                                }
                            }
                        });
                    }
                })
            });

            // Select All Checkbox
            $('.qcl-openai').on('change', '#rag-select-all', function() {
                $('.rag-doc-checkbox').prop('checked', $(this).prop('checked'));
            });

            // Bulk Actions (Apply)
            $('.qcl-openai').on('click', '#rag-apply-bulk-action', function() {
                var action = $('#rag-bulk-action-selector').val();
                if (action !== 'delete') {
                    Swal.fire('Info', 'Please select a valid bulk action.', 'info');
                    return;
                }

                var selectedIds = [];
                $('.rag-doc-checkbox:checked').each(function() {
                    selectedIds.push($(this).val());
                });

                if (selectedIds.length === 0) {
                    Swal.fire('Warning', 'Please select at least one document.', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'Are you sure?',
                    text: "Delete " + selectedIds.length + " selected documents? This cannot be undone!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete selected!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: qcld_gemini_admin_data.ajax_url,
                            type: 'POST',
                            data: {
                                action: 'qcld_rag_bulk_delete_documents',
                                nonce: qcld_gemini_admin_data.ajax_nonce,
                                ids: selectedIds
                            },
                            success: function(res) {
                                if (res.success) {
                                    selectedIds.forEach(function(id) {
                                        $('#rag-doc-' + id).remove();
                                    });
                                    $('#rag-select-all').prop('checked', false);
                                    $('#rag-bulk-action-selector').val('-1');
                                    Swal.fire('Deleted!', res.data, 'success');
                                } else {
                                    Swal.fire('Error', res.data, 'error');
                                }
                            }
                        });
                    }
                });
            });

            // Delete All Documents
            $('.qcl-openai').on('click', '#rag-delete-all', function() {
                Swal.fire({
                    title: 'CRITICAL WARNING!',
                    text: "This will delete ALL documents in your Knowledge Base. This action is IRREVERSIBLE!",
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'YES, DELETE EVERYTHING!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: qcld_gemini_admin_data.ajax_url,
                            type: 'POST',
                            data: {
                                action: 'qcld_rag_delete_all_documents',
                                nonce: qcld_gemini_admin_data.ajax_nonce
                            },
                            success: function(res) {
                                if (res.success) {
                                    $('#rag-knowledge-base-list').html('<tr><td colspan="6">No documents found in knowledge base.</td></tr>');
                                    $('#rag-select-all').prop('checked', false);
                                    Swal.fire('Cleared!', res.data, 'success');
                                } else {
                                    Swal.fire('Error', res.data, 'error');
                                }
                            }
                        });
                    }
                });
            });

            // Edit Document - Open Modal
            $('.qcl-openai').on('click', '.rag-edit-doc', function () {
                var id = $(this).data('id');
                $.ajax({
                    url: qcld_gemini_admin_data.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'qcld_rag_get_document',
                        nonce: qcld_gemini_admin_data.ajax_nonce,
                        id: id
                    },
                    success: function (res) {
                        if (res.success) {
                            $('#edit-doc-id').val(res.data.id);
                            $('#edit-doc-title').val(res.data.title);
                            $('#edit-doc-content').val(res.data.content);
                            $('#rag-edit-modal').show();
                        } else {
                            Swal.fire('Error', res.data, 'error');
                        }
                    }
                });
            });

            // Close Edit Modal
            $(document).on('click', '#close-edit-modal', function () {
                $('#rag-edit-modal').hide();
            });

            // Manual Sync Document
        $(document).on('click', '.rag-sync-doc', function(e) {
            e.preventDefault();
            var btn = $(this);
            var docId = btn.data('id');
            
            if (!confirm('Are you sure you want to re-sync this document from its source?')) {
                return;
            }
            
            btn.prop('disabled', true).text('Syncing...');
            
            $.post(qcld_gemini_admin_data.ajax_url, {
                action: 'qcld_rag_manual_sync',
                nonce: qcld_gemini_admin_data.ajax_nonce,
                id: docId
            }, function(response) {
                btn.prop('disabled', false).text('Sync');
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert('Error: ' + response.data.message);
                }
            });
        });

            // Save Edit Document
            $(document).on('click', '#save-edit-doc', function () {
                var id = $('#edit-doc-id').val();
                var title = $('#edit-doc-title').val();
                var content = $('#edit-doc-content').val();
                var btn = $(this);
                
                btn.prop('disabled', true).text('Saving...');
                
                $.ajax({
                    url: qcld_gemini_admin_data.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'qcld_rag_update_document',
                        nonce: qcld_gemini_admin_data.ajax_nonce,
                        id: id,
                        title: title,
                        content: content
                    },
                    success: function (res) {
                        btn.prop('disabled', false).text('Save Changes');
                        if (res.success) {
                            $('#rag-edit-modal').hide();
                            Swal.fire('Updated!', res.data, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', res.data, 'error');
                        }
                    }
                });
            });
        }
                    // Delete Document
            $('.qcl-openai').on('click', '.rag-delete-doc', function () {
                var id = $(this).data('id');
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: qcld_gemini_admin_data.ajax_url,
                            type: 'POST',
                            data: {
                                action: 'qcld_rag_delete_document',
                                nonce: qcld_gemini_admin_data.ajax_nonce,
                                id: id
                            },
                            success: function (res) {
                                if (res.success) {
                                    $('#rag-doc-' + id).remove();
                                    Swal.fire('Deleted!', res.data, 'success');
                                } else {
                                    Swal.fire('Error', res.data, 'error');
                                }
                            }
                        });
                    }
                })
            });

        // $('.qcl-openai').on('click', '#qcld_check_connection', function(){
        //     jQuery('#rotationloader').css('display','inline-block'); 
        //     $.ajax({
        //         url: qcld_gemini_admin_data.ajax_url,
        //         type:'POST',
        //         data:    ({action  : 'openai_troubleshooting',nonce:qcld_gemini_admin_data.ajax_nonce}),
        //         success: function(data){
                    
        //             $('#result').html(data);
        //             Swal.fire({
        //                 title: data.title,
        //                 html: data.msg,
        //                 width: 450,
        //                 icon: data.icon,
        //                 confirmButtonText: 'Got it',
        //                 customClass: 'connection-modal',
        //             })
        //             jQuery('#rotationloader').css('display','none'); 
        //          //   location.reload();
        //         }
        //     });
        // })
});
       
 


