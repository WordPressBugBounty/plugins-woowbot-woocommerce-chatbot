jQuery(document).ready(function($) {

    // Tab Switching Logic
    $('.qcld_woo_chatbot-tab-link').on('click', function(e) {
        e.preventDefault();
        
        $('.qcld_woo_chatbot-tab-link').removeClass('active');
        $('.qcld_woo_chatbot-tab-content').removeClass('active');
        
        $(this).addClass('active');
        $('#' + $(this).data('target')).addClass('active');
        
        const href = $(this).attr('href');
        if (history.pushState) {
            history.pushState(null, null, href);
        }
        
        // Persist active tab across form submissions
        sessionStorage.setItem('qcld_woo_chatbot_active_tab', href);
    });

    // Restore active tab on page load
    let activeTabHref = window.location.hash;
    if (!activeTabHref) {
        activeTabHref = sessionStorage.getItem('qcld_woo_chatbot_active_tab');
    }
    
    if (activeTabHref) {
        const targetTab = $('.qcld_woo_chatbot-tab-link[href="' + activeTabHref + '"]');
        if (targetTab.length) {
            targetTab.click();
        }
    }

    //$('#qcld_woo_chatbot_openai_image_model, #qcld_woo_chatbot_openai_video_model, #qcld_woo_chatbot_gemini_image_model, #qcld_woo_chatbot_gemini_video_model').select2({ width: '100%' });

    function updateRows(provider) {
        if (provider === 'openai') {
            $('.qcld_woo_chatbot-openai-settings-row').show();
            // Show Gemini general/video settings, but hide Gemini Image Settings
            $('.qcld_woo_chatbot-gemini-settings-row').show();
            $('.qcld_woo_chatbot-gemini-image-row').hide();
        } else if (provider === 'gemini') {
            $('.qcld_woo_chatbot-openai-settings-row').hide();
            // Show all Gemini settings
            $('.qcld_woo_chatbot-gemini-settings-row').show();
            $('.qcld_woo_chatbot-gemini-image-row').show();
        }
    }

    $('.qcld_woo_chatbot-ai-provider-card').on('click', function() {
        $('.qcld_woo_chatbot-ai-provider-card').removeClass('active');
        $(this).addClass('active');
        $(this).find('input').prop('checked', true);
        
        updateRows($(this).find('input').val());
    });

    $('.qcld_woo_chatbot-radio-card').on('click', function() {
        $(this).closest('.qcld_woo_chatbot-radio-grid').find('.qcld_woo_chatbot-radio-card').removeClass('active');
        $(this).addClass('active');
        $(this).find('input').prop('checked', true);
    });

    $('.qcld_woo_chatbot-reset-prompt-btn').on('click', function(e) {
        e.preventDefault();
        const target = $(this).data('target');
        const defaultVal = $(this).data('default');
        $('#' + target).val(defaultVal);
        
        // Optional visual feedback
        const originalText = $(this).text();
        $(this).text(qcld_woo_chatbot_admin_ajax.i18n.reset);
        setTimeout(() => { $(this).text(originalText); }, 1500);
    });

    // trigger on load
    const checkedInput = $('.qcld_woo_chatbot-ai-provider-card input:checked');
    if (checkedInput.length) {
        updateRows(checkedInput.val());
    }

    // OpenAI Test Connection AJAX
    $('#qcld_woo_chatbot_test_openai_btn').on('click', function(e) {
        e.preventDefault();
        
        var apiKey = $('#qcld_woo_chatbot_openai_api_key').val().trim();
        var $status = $('#qcld_woo_chatbot_openai_test_status');
        var $imageModelSelect = $('#qcld_woo_chatbot_openai_image_model');
        var $videoModelSelect = $('#qcld_woo_chatbot_openai_video_model');
        
        if (!apiKey) {
            $status.css('color', '#dc3232').text(qcld_woo_chatbot_admin_ajax.i18n.enter_api_key);
            return;
        }

        $status.css('color', '#64748b').text(qcld_woo_chatbot_admin_ajax.i18n.testing_connection);
        var $btn = $(this);
        $btn.prop('disabled', true);
        
        $.ajax({
            url: qcld_woo_chatbot_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'qcld_woo_chatbot_test_openai_connection',
                security: qcld_woo_chatbot_admin_ajax.nonce,
                api_key: apiKey
            },
            success: function(response) {
                $btn.prop('disabled', false);
                
                if (response.success) {
                    $status.css('color', '#46b450').text(response.data.message);
                    
                    if ( (response.data.image_models && response.data.image_models.length > 0) || (response.data.video_models && response.data.video_models.length > 0) ) {
                        // Dynamically update selects without reloading the page
                        var imageSelect = $('#qcld_woo_chatbot_openai_image_model');
                        var videoSelect = $('#qcld_woo_chatbot_openai_video_model');
                        
                        var currentImage = imageSelect.val();
                        var currentVideo = videoSelect.val();
                        
                        imageSelect.empty();
                        videoSelect.empty();
                        
                        if (response.data.image_models) {
                            $.each(response.data.image_models, function(i, model) {
                                imageSelect.append($('<option>').val(model).text(model));
                            });
                        }
                        
                        if (response.data.video_models) {
                            $.each(response.data.video_models, function(i, model) {
                                videoSelect.append($('<option>').val(model).text(model));
                            });
                        }
                        
                        // Add the current values if they aren't in the lists
                        if (currentImage && imageSelect.find('option[value="' + currentImage + '"]').length === 0) {
                            imageSelect.prepend($('<option>').val(currentImage).text(currentImage));
                        }
                        imageSelect.val(currentImage);
                        
                        if (currentVideo && videoSelect.find('option[value="' + currentVideo + '"]').length === 0) {
                            videoSelect.prepend($('<option>').val(currentVideo).text(currentVideo));
                        }
                        videoSelect.val(currentVideo);
                    }
                } else {
                    $status.css('color', '#dc3232').text(qcld_woo_chatbot_admin_ajax.i18n.error + (response.data.message || qcld_woo_chatbot_admin_ajax.i18n.unknown_error));
                }
            },
            error: function(xhr, status, error) {
                $btn.prop('disabled', false);
                $status.css('color', '#dc3232').text(qcld_woo_chatbot_admin_ajax.i18n.request_failed + error);
            }
        });
    });

    // Gemini Test Connection AJAX
    $('#qcld_woo_chatbot_test_gemini_btn').on('click', function(e) {
        e.preventDefault();
        
        var apiKey = $('#qcld_woo_chatbot_gemini_api_key').val().trim();
        var $status = $('#qcld_woo_chatbot_gemini_test_status');
        var $imageModelSelect = $('#qcld_woo_chatbot_gemini_image_model');
        var currentModel = $('#qcld_woo_chatbot_gemini_image_model').val();
        
        if (!apiKey) {
            alert(qcld_woo_chatbot_admin_ajax.i18n.enter_api_key);
            return;
        }

        var $btn = $(this);
        
        $btn.prop('disabled', true);
        $status.css('color', '#666').text(qcld_woo_chatbot_admin_ajax.i18n.testing_connection);

        $.ajax({
            url: qcld_woo_chatbot_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'qcld_woo_chatbot_test_gemini_connection',
                security: qcld_woo_chatbot_admin_ajax.nonce,
                api_key: apiKey,
                model: currentModel
            },
            success: function(response) {
                $btn.prop('disabled', false);
                
                if (response.success) {
                    $status.css('color', '#46b450').text(response.data.message);
                    
                    if ( (response.data.image_models && response.data.image_models.length > 0) || (response.data.video_models && response.data.video_models.length > 0) ) {
                        // Dynamically update selects without reloading the page
                        var gImageSelect = $('#qcld_woo_chatbot_gemini_image_model');
                        var gVideoSelect = $('#qcld_woo_chatbot_gemini_video_model');
                        
                        var currentGImage = gImageSelect.val();
                        var currentGVideo = gVideoSelect.val();
                        
                        gImageSelect.empty();
                        gVideoSelect.empty();
                        
                        if (response.data.image_models) {
                            $.each(response.data.image_models, function(i, model) {
                                var label = model;
                                if (model.indexOf('gemini-2.0-flash') !== -1 || model.indexOf('gemini-1.5-flash') !== -1) {
                                    label += ' - ' + qcld_woo_chatbot_admin_ajax.i18n.recommended_veo;
                                }
                                gImageSelect.append($('<option>').val(model).text(label));
                            });
                        }
                        
                        var bestVideoModel = null;
                        if (response.data.video_models) {
                            $.each(response.data.video_models, function(i, model) {
                                var label = model;
                                if (model === 'veo-3.1-fast-generate-preview') {
                                    label += ' - $0.15/sec - ' + qcld_woo_chatbot_admin_ajax.i18n.recommended_veo;
                                    bestVideoModel = model;
                                } else if (model === 'veo-3.1-generate-preview') {
                                    label += ' - $0.40/sec';
                                } else if (model === 'veo-3.1-lite-generate-preview') {
                                    label += ' - $0.05/sec';
                                } else if (model.indexOf('veo-2.0-generate') !== -1) {
                                    label += ' - $0.40/sec';
                                }
                                gVideoSelect.append($('<option>').val(model).text(label));
                            });
                        }
                        
                        if (currentGImage && gImageSelect.find('option[value="' + currentGImage + '"]').length === 0) {
                            var labelI = currentGImage;
                            if (currentGImage.indexOf('gemini-2.0-flash') !== -1 || currentGImage.indexOf('gemini-1.5-flash') !== -1) {
                                labelI += ' - ' + qcld_woo_chatbot_admin_ajax.i18n.recommended_veo;
                            }
                            gImageSelect.prepend($('<option>').val(currentGImage).text(labelI));
                        }
                        gImageSelect.val(currentGImage);
                        
                        if (currentGVideo && gVideoSelect.find('option[value="' + currentGVideo + '"]').length === 0) {
                            var labelV = currentGVideo;
                            if (currentGVideo === 'veo-3.1-fast-generate-preview') {
                                labelV += ' - $0.15/sec - ' + qcld_woo_chatbot_admin_ajax.i18n.recommended_veo;
                            } else if (currentGVideo === 'veo-3.1-generate-preview') {
                                labelV += ' - $0.40/sec';
                            } else if (currentGVideo === 'veo-3.1-lite-generate-preview') {
                                labelV += ' - $0.05/sec';
                            } else if (currentGVideo.indexOf('veo-2.0-generate') !== -1) {
                                labelV += ' - $0.40/sec';
                            }
                            gVideoSelect.prepend($('<option>').val(currentGVideo).text(labelV));
                        }
                        
                        // Auto-select the best video model if it exists, otherwise fallback to current
                        if (bestVideoModel) {
                            gVideoSelect.val(bestVideoModel);
                        } else {
                            gVideoSelect.val(currentGVideo);
                        }
                    }
                } else {
                    $status.css('color', '#dc3232').text(qcld_woo_chatbot_admin_ajax.i18n.error + (response.data.message || qcld_woo_chatbot_admin_ajax.i18n.unknown_error));
                }
            },
            error: function(xhr, status, error) {
                $btn.prop('disabled', false);
                $status.css('color', '#dc3232').text(qcld_woo_chatbot_admin_ajax.i18n.request_failed + error);
            }
        });
    });

    // Handle Verify Model Button
    $('.qcld_woo_chatbot-verify-model-btn').on('click', function(e) {
        e.preventDefault();
        
        var $btn = $(this);
        var $status = $btn.parent().siblings('.qcld_woo_chatbot-verify-status');
        var provider = $btn.data('provider');
        var type = $btn.data('type');
        
        // Determine the select ID based on provider and type
        var selectId = '#qcld_woo_chatbot_' + provider + '_' + type + '_model';
        var selectedModel = $(selectId).val();
        
        if (!selectedModel) {
            $status.css('color', 'red').text(qcld_woo_chatbot_admin_ajax.i18n.select_model);
            return;
        }
        
        var apiKeyId = '#qcld_woo_chatbot_' + provider + '_api_key';
        var apiKey = $(apiKeyId).val().trim();
        
        if (!apiKey) {
            $status.css('color', 'red').text(qcld_woo_chatbot_admin_ajax.i18n.enter_api_key);
            return;
        }

        $btn.prop('disabled', true).text(qcld_woo_chatbot_admin_ajax.i18n.verifying);
        $status.css('color', '#666').text(qcld_woo_chatbot_admin_ajax.i18n.testing);

        $.ajax({
            url: qcld_woo_chatbot_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'qcld_woo_chatbot_verify_model_capability',
                security: qcld_woo_chatbot_admin_ajax.nonce,
                provider: provider,
                generation_type: type,
                model: selectedModel,
                api_key: apiKey
            },
            success: function(response) {
                $btn.prop('disabled', false).text(qcld_woo_chatbot_admin_ajax.i18n.verify);
                
                if (response.success) {
                    $status.css('color', '#46b450').text(' ' + qcld_woo_chatbot_admin_ajax.i18n.verified);
                } else {
                    $status.css('color', '#dc3232').text(' ' + response.data.message);
                }
            },
            error: function() {
                $btn.prop('disabled', false).text(qcld_woo_chatbot_admin_ajax.i18n.verify);
                $status.css('color', '#dc3232').text(' ' + qcld_woo_chatbot_admin_ajax.i18n.ajax_failed);
            }
        });
    });

});

