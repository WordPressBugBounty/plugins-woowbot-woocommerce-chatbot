jQuery(document).ready(function($) {
    var $modal = $('#qcld_woo_chatbot-tryon-modal');
    var currentProductId = '';
    var currentProductName = '';
    var currentProductImage = '';
    var currentProductImageBase64 = '';
    var currentVariationId = '';
    var currentSecondImage = null;

    // High-performance client-side image compressor (reduces 10MB photo to ~150KB for instant processing)
    function compressImageFile(file, maxDimension, quality, callback) {
        if (!file) {
            callback('');
            return;
        }
        maxDimension = maxDimension || 1024;
        quality = quality || 0.88;

        var reader = new FileReader();
        reader.onload = function(e) {
            var img = new Image();
            img.onload = function() {
                var w = img.width;
                var h = img.height;
                if (w > maxDimension || h > maxDimension) {
                    if (w > h) {
                        h = Math.round((h * maxDimension) / w);
                        w = maxDimension;
                    } else {
                        w = Math.round((w * maxDimension) / h);
                        h = maxDimension;
                    }
                }
                var canvas = document.createElement('canvas');
                canvas.width = w;
                canvas.height = h;
                var ctx = canvas.getContext('2d');
                ctx.imageSmoothingEnabled = true;
                ctx.imageSmoothingQuality = 'high';
                ctx.drawImage(img, 0, 0, w, h);
                var compressedDataUrl = canvas.toDataURL('image/jpeg', quality);
                callback(compressedDataUrl);
            };
            img.onerror = function() {
                callback(e.target.result);
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }

    function urlToBase64(url, callback) {
        if (!url) {
            callback('');
            return;
        }
        var img = new Image();
        img.crossOrigin = 'Anonymous';
        img.onload = function() {
            try {
                var maxDimension = 1024;
                var w = img.width;
                var h = img.height;
                if (w > maxDimension || h > maxDimension) {
                    if (w > h) {
                        h = Math.round((h * maxDimension) / w);
                        w = maxDimension;
                    } else {
                        w = Math.round((w * maxDimension) / h);
                        h = maxDimension;
                    }
                }
                var canvas = document.createElement('canvas');
                canvas.width = w;
                canvas.height = h;
                var ctx = canvas.getContext('2d');
                ctx.imageSmoothingEnabled = true;
                ctx.imageSmoothingQuality = 'high';
                ctx.drawImage(img, 0, 0, w, h);
                var dataURL = canvas.toDataURL('image/jpeg', 0.88);
                callback(dataURL);
            } catch(e) {
                callback('');
            }
        };
        img.onerror = function() {
            callback('');
        };
        img.src = url;
    }

    // Open Modal interface
    $(document).on('click', '#qcld_woo_chatbot-open-modal-btn', function(e) {
        e.preventDefault();
        var $btn = $(this);
        currentProductId = $btn.data('product-id') || '';
        currentProductName = $btn.data('product-title') || '';
        currentProductImage = $btn.data('product-image') || '';
        currentProductImageBase64 = '';
        currentVariationId = '';

        // Pick up a variation the shopper already selected on the page before opening the modal.
        var $pageVariationForm = $('form.variations_form');
        if ($pageVariationForm.length) {
            var preselected = $pageVariationForm.find('input[name="variation_id"], input.variation_id').val();
            if (preselected && preselected !== '0') {
                currentVariationId = String(preselected);
                var variations = $pageVariationForm.data('product_variations') || [];
                for (var i = 0; i < variations.length; i++) {
                    if (String(variations[i].variation_id) === currentVariationId && variations[i].image && variations[i].image.src) {
                        currentProductImage = variations[i].image.src;
                        break;
                    }
                }
            }
        }

        if (currentProductImage) {
            $('#qcld_woo_chatbot-modal-product-image').attr('src', currentProductImage);
        }
        
        initModalVariations($btn);
        syncModalQuantityFromPage();
        updateGenerateButtonState();
        $modal.fadeIn(200);
    });

    // Close Actions
    $(document).on('click', '.qcld_woo_chatbot-close-modal', function(e) {
        $modal.fadeOut(200);
    });

    // Initialize and sync WooCommerce product variations in modal
    function initModalVariations($btn) {
        var $varContainer = $('#qcld_woo_chatbot-modal-variations-container');
        if ($varContainer.length === 0) return;
        $varContainer.empty().addClass('qcld_woo_chatbot-hidden');
        
        var isVariable = ($btn.data('is-variable') == '1') || ($('form.variations_form').length > 0);
        if (!isVariable) {
            return;
        }

        var $pageForm = $('form.variations_form');
        var createdAny = false;

        // 1. Check if page has native variation selects
        if ($pageForm.length > 0 && $pageForm.find('.variations select').length > 0) {
            $pageForm.find('.variations select').each(function() {
                var $pageSelect = $(this);
                var selectName = $pageSelect.attr('name');
                if (!selectName) return;

                var labelText = $pageSelect.closest('tr').find('label').text().trim() || 
                                $pageSelect.data('attribute_name') || 
                                selectName.replace('attribute_', '');
                labelText = labelText.replace(/[:*]/g, '').trim();

                var $wrapper = $('<div class="qcld_woo_chatbot-modal-var-item"></div>');
                var $modalSelect = $('<select class="qcld_woo_chatbot-modal-variation-select"></select>');
                $modalSelect.attr('data-target-name', selectName);
                $modalSelect.attr('title', labelText);

                $pageSelect.find('option').each(function() {
                    var val = $(this).val();
                    var text = $(this).text();
                    var $opt = $('<option></option>').val(val).text(text);
                    if ($(this).is(':selected')) {
                        $opt.prop('selected', true);
                    }
                    $modalSelect.append($opt);
                });

                $wrapper.append($modalSelect);
                $varContainer.append($wrapper);
                createdAny = true;
            });
        } else {
            // 2. Fallback: Parse data-attributes passed from PHP button
            var rawAttrs = $btn.data('attributes');
            if (rawAttrs) {
                var attrs = (typeof rawAttrs === 'string') ? JSON.parse(rawAttrs) : rawAttrs;
                $.each(attrs, function(attrTax, options) {
                    var labelText = attrTax.replace(/^pa_/, '').replace(/[-_]/g, ' ');
                    labelText = labelText.charAt(0).toUpperCase() + labelText.slice(1);
                    
                    var $wrapper = $('<div class="qcld_woo_chatbot-modal-var-item"></div>');
                    var $modalSelect = $('<select class="qcld_woo_chatbot-modal-variation-select"></select>');
                    $modalSelect.attr('data-target-name', 'attribute_' + attrTax);
                    var chooseText = (qcld_woo_chatbot_ajax_obj && qcld_woo_chatbot_ajax_obj.i18n && qcld_woo_chatbot_ajax_obj.i18n.choose_option) ? qcld_woo_chatbot_ajax_obj.i18n.choose_option : 'Choose an option';
                    $modalSelect.append('<option value="">' + chooseText + ' (' + labelText + ')</option>');
                    
                    if (Array.isArray(options)) {
                        $.each(options, function(i, optVal) {
                            $modalSelect.append($('<option></option>').val(optVal).text(optVal));
                        });
                    }
                    $wrapper.append($modalSelect);
                    $varContainer.append($wrapper);
                    createdAny = true;
                });
            }
        }

        if (createdAny) {
            $varContainer.removeClass('qcld_woo_chatbot-hidden');
        }
    }

    // Handle variation dropdown change in modal
    $(document).on('change', '.qcld_woo_chatbot-modal-variation-select', function() {
        var $modalSelect = $(this);
        var targetName = $modalSelect.data('target-name');
        var selectedVal = $modalSelect.val();
        $modalSelect.removeClass('qcld_woo_chatbot-select-error');

        // Sync with page variations form
        var $pageForm = $('form.variations_form');
        if ($pageForm.length > 0) {
            var $pageSelect = $pageForm.find('select[name="' + targetName + '"]');
            if ($pageSelect.length > 0) {
                $pageSelect.val(selectedVal).trigger('change');
            }
        }
    });

    // When WooCommerce finds a matching variation, update product image if available
    $(document).on('found_variation', 'form.variations_form', function(event, variation) {
        currentVariationId = (variation && variation.variation_id) ? String(variation.variation_id) : '';
        if (variation && variation.image && variation.image.src) {
            $('#qcld_woo_chatbot-modal-product-image').attr('src', variation.image.src);
            currentProductImage = variation.image.src;
        }
        syncModalQuantityFromPage();
    });

    $(document).on('reset_data', 'form.variations_form', function() {
        // Selection cleared: go back to the main product image.
        currentVariationId = '';
        var mainImage = $('#qcld_woo_chatbot-open-modal-btn').data('product-image') || '';
        if (mainImage) {
            currentProductImage = mainImage;
            $('#qcld_woo_chatbot-modal-product-image').attr('src', mainImage);
        }
        syncModalQuantityFromPage();
    });

    // Initialize and sync WooCommerce product quantity in modal
    function syncModalQuantityFromPage() {
        var $qtyWrapper = $('#qcld_woo_chatbot-modal-quantity-wrapper');
        if ($qtyWrapper.length === 0) return;

        var $btn = $('#qcld_woo_chatbot-open-modal-btn');
        var isSoldIndividually = ($btn.data('sold-individually') == '1');
        
        if (isSoldIndividually) {
            $qtyWrapper.addClass('qcld_woo_chatbot-hidden').hide();
            return;
        }

        var $pageQty = $('form.cart input[name="quantity"], form.cart input.qty, .woocommerce-variation-add-to-cart input[name="quantity"]');
        var min = 1;
        var max = 0;
        var step = 1;
        var currentVal = 1;

        if ($pageQty.length > 0) {
            currentVal = parseInt($pageQty.val(), 10) || 1;
            if ($pageQty.attr('min') !== undefined && $pageQty.attr('min') !== '') {
                min = parseInt($pageQty.attr('min'), 10);
            }
            if ($pageQty.attr('max') !== undefined && $pageQty.attr('max') !== '') {
                max = parseInt($pageQty.attr('max'), 10);
            }
            if ($pageQty.attr('step') !== undefined && $pageQty.attr('step') !== '') {
                step = parseInt($pageQty.attr('step'), 10) || 1;
            }
        } else {
            if ($btn.data('min-qty') !== undefined && $btn.data('min-qty') !== '') {
                min = parseInt($btn.data('min-qty'), 10) || 1;
            }
            if ($btn.data('max-qty') !== undefined && $btn.data('max-qty') !== '') {
                max = parseInt($btn.data('max-qty'), 10) || 0;
            }
            if ($btn.data('step-qty') !== undefined && $btn.data('step-qty') !== '') {
                step = parseInt($btn.data('step-qty'), 10) || 1;
            }
        }

        if (currentVal < min) currentVal = min;
        if (max > 0 && currentVal > max) currentVal = max;

        var $input = $('#qcld_woo_chatbot-modal-qty-input');
        $input.attr('min', min);
        if (max > 0) {
            $input.attr('max', max);
        } else {
            $input.removeAttr('max');
        }
        $input.attr('step', step);
        $input.val(currentVal);

        updateQtyButtonsState(currentVal, min, max);
        $qtyWrapper.removeClass('qcld_woo_chatbot-hidden').show();
    }

    function updateQtyButtonsState(val, min, max) {
        var $minus = $('.qcld_woo_chatbot-qty-minus');
        var $plus = $('.qcld_woo_chatbot-qty-plus');
        
        $minus.prop('disabled', val <= min);
        if (max && max > 0) {
            $plus.prop('disabled', val >= max);
        } else {
            $plus.prop('disabled', false);
        }
    }

    // Modal quantity minus button click
    $(document).on('click', '.qcld_woo_chatbot-qty-minus', function(e) {
        e.preventDefault();
        var $input = $('#qcld_woo_chatbot-modal-qty-input');
        var min = parseInt($input.attr('min'), 10) || 1;
        var max = parseInt($input.attr('max'), 10) || 0;
        var step = parseInt($input.attr('step'), 10) || 1;
        var currentVal = parseInt($input.val(), 10) || min;

        var newVal = currentVal - step;
        if (newVal < min) newVal = min;

        $input.val(newVal);
        updateQtyButtonsState(newVal, min, max);

        var $pageQty = $('form.cart input[name="quantity"], form.cart input.qty, .woocommerce-variation-add-to-cart input[name="quantity"]');
        if ($pageQty.length > 0) {
            $pageQty.val(newVal).trigger('change');
        }
    });

    // Modal quantity plus button click
    $(document).on('click', '.qcld_woo_chatbot-qty-plus', function(e) {
        e.preventDefault();
        var $input = $('#qcld_woo_chatbot-modal-qty-input');
        var min = parseInt($input.attr('min'), 10) || 1;
        var max = parseInt($input.attr('max'), 10) || 0;
        var step = parseInt($input.attr('step'), 10) || 1;
        var currentVal = parseInt($input.val(), 10) || min;

        var newVal = currentVal + step;
        if (max > 0 && newVal > max) newVal = max;

        $input.val(newVal);
        updateQtyButtonsState(newVal, min, max);

        var $pageQty = $('form.cart input[name="quantity"], form.cart input.qty, .woocommerce-variation-add-to-cart input[name="quantity"]');
        if ($pageQty.length > 0) {
            $pageQty.val(newVal).trigger('change');
        }
    });

    // Modal quantity manual input change
    $(document).on('change input', '#qcld_woo_chatbot-modal-qty-input', function() {
        var $input = $(this);
        var min = parseInt($input.attr('min'), 10) || 1;
        var max = parseInt($input.attr('max'), 10) || 0;
        var val = parseInt($input.val(), 10);

        if (isNaN(val) || val < min) {
            val = min;
        } else if (max > 0 && val > max) {
            val = max;
        }

        $input.val(val);
        updateQtyButtonsState(val, min, max);

        var $pageQty = $('form.cart input[name="quantity"], form.cart input.qty, .woocommerce-variation-add-to-cart input[name="quantity"]');
        if ($pageQty.length > 0) {
            $pageQty.val(val).trigger('change');
        }
    });

    // Image Upload Logic with Instant Client-Side Compression
    var currentSecondImage = null;
    
    function updateGenerateButtonState() {
        if (!currentSecondImage) {
            $('#qcld_woo_chatbot-submit-generation').prop('disabled', true).css('opacity', '0.5').css('cursor', 'not-allowed');
        } else {
            $('#qcld_woo_chatbot-submit-generation').prop('disabled', false).css('opacity', '1').css('cursor', 'pointer');
        }
    }
    
    $('#qcld_woo_chatbot-modal-second-image-placeholder').on('click', function() {
        $('#qcld_woo_chatbot-second-image-upload').click();
    });

    $('#qcld_woo_chatbot-second-image-upload').on('change', function(e) {
        var file = e.target.files[0];
        if (file) {
            var $placeholder = $('#qcld_woo_chatbot-modal-second-image-placeholder');
            $placeholder.html('<div class="qcld_woo_chatbot-placeholder-inner"><span class="dashicons dashicons-update-alt qcld_woo_chatbot-spin" style="font-size:24px;width:24px;height:24px;color:#8b5cf6;"></span><span class="qcld_woo_chatbot-placeholder-title">Optimizing photo...</span></div>');
            
            // Client-side downscaling & compression to 1024px / 0.88 JPEG (< 150KB)
            compressImageFile(file, 1024, 0.88, function(compressedBase64) {
                currentSecondImage = compressedBase64;
                $placeholder.html('<img src="' + currentSecondImage + '" alt="Uploaded Image" /><span class="qcld_woo_chatbot-card-tag qcld_woo_chatbot-user-tag">Your Photo</span><div class="qcld_woo_chatbot-image-hover-overlay"><span class="dashicons dashicons-camera"></span> Change</div>');
                updateGenerateButtonState();
            });
        }
    });

    // Tabs functionality
    $('.qcld_woo_chatbot-tab').on('click', function() {
        $('.qcld_woo_chatbot-tab').removeClass('active');
        $(this).addClass('active');
        
        var type = $(this).data('type');
        $('.qcld_woo_chatbot-tab-content').addClass('qcld_woo_chatbot-hidden-tab');
        $('#qcld_woo_chatbot-form-' + type).removeClass('qcld_woo_chatbot-hidden-tab');
        updateCanvasRatio();
    });
    
    // Dynamic Canvas Aspect Ratio
    function updateCanvasRatio() {
        $('.qcld_woo_chatbot-response-canvas').css({
            'aspect-ratio': '16/9',
            'min-height': 'auto',
            'height': 'auto'
        });
    }
    // Initialize on load
    updateCanvasRatio();

    // High-Performance AI Generation Handler
    var progressTimer = null;

    $('#qcld_woo_chatbot-submit-generation').on('click', function(e) {
        e.preventDefault();
        
        var promptText = $('#qcld_woo_chatbot-prompt').val();
        var genType = $('.qcld_woo_chatbot-tab.active').data('type') || 'image';
        
        var bgPref = '';
        var outputStyle = '';
        var imageSize = '16:9';
        
        if (genType === 'image') {
            bgPref = $('#qcld_woo_chatbot-bg-preference').val() || 'Studio';
            outputStyle = $('#qcld_woo_chatbot-output-style').val() || 'Model Shoot';
            if (!promptText.trim()) {
                promptText = bgPref + " background, " + outputStyle + " style.";
            } else {
                promptText = bgPref + " background, " + outputStyle + " style. " + promptText;
            }
        } else {
            bgPref = $('#qcld_woo_chatbot-video-style').val() || 'Cinematic';
            outputStyle = $('#qcld_woo_chatbot-camera-motion').val() || 'Slow Zoom';
            if (!promptText.trim()) {
                promptText = bgPref + " style, " + outputStyle + " motion.";
            } else {
                promptText = bgPref + " style, " + outputStyle + " motion. " + promptText;
            }
        }

        $('#qcld_woo_chatbot-result-placeholder').addClass('qcld_woo_chatbot-hidden');
        $('#qcld_woo_chatbot-loading-spinner').removeClass('qcld_woo_chatbot-hidden');
        $('#qcld_woo_chatbot-result-display').empty();
        
        var $spinner      = $('#qcld_woo_chatbot-loading-spinner');
        var $progressFill = $spinner.find('.qcld_woo_chatbot-progress-fill');
        var $progressPct  = $spinner.find('.qcld_woo_chatbot-progress-percent');
        var $submitBtn    = $(this);
        var originalBtnText = $submitBtn.text();
        
        $submitBtn.prop('disabled', true).text(qcld_woo_chatbot_ajax_obj.i18n.processing).css('opacity', '0.7').css('cursor', 'not-allowed');

        // Progressive Multi-Stage Micro-Timer
        var currentPct = 5;
        if ($progressFill.length) $progressFill.css('width', currentPct + '%');
        if ($progressPct.length) $progressPct.text(currentPct + '%');

        if (progressTimer) clearInterval(progressTimer);
        var startTime = Date.now();

        progressTimer = setInterval(function() {
            var elapsed = (Date.now() - startTime) / 1000;
            // Asymptotic curve tuned for real render times (~20-90s): ~50% at 20s, ~80% at 45s, never hits 100 until done.
            currentPct = Math.min(97, Math.round(5 + 92 * (1 - Math.exp(-elapsed / 28))));

            if ($progressFill.length) $progressFill.css('width', currentPct + '%');
            if ($progressPct.length) $progressPct.text(currentPct + '%');
        }, 500);

        function handleFailure(errorMsg) {
            if (progressTimer) {
                clearInterval(progressTimer);
                progressTimer = null;
            }
            $spinner.addClass('qcld_woo_chatbot-hidden');
            $submitBtn.prop('disabled', false).text(originalBtnText).css('opacity', '1').css('cursor', 'pointer');

            if (!errorMsg) {
                errorMsg = (qcld_woo_chatbot_ajax_obj && qcld_woo_chatbot_ajax_obj.i18n && qcld_woo_chatbot_ajax_obj.i18n.unexpected_error) ? qcld_woo_chatbot_ajax_obj.i18n.unexpected_error : 'Unexpected error occurred.';
            }

            var prefix = (qcld_woo_chatbot_ajax_obj && qcld_woo_chatbot_ajax_obj.i18n && qcld_woo_chatbot_ajax_obj.i18n.execution_error) ? qcld_woo_chatbot_ajax_obj.i18n.execution_error : '';
            $('#qcld_woo_chatbot-result-display').html('<p class="qcld_woo_chatbot-error">' + prefix + errorMsg + '</p>');
            $('#qcld_woo_chatbot-result-placeholder').removeClass('qcld_woo_chatbot-hidden');
        }

        function executeStep2Generation(refId, finalPrompt, productRefId) {

            $.ajax({
                url: qcld_woo_chatbot_ajax_obj.ajax_url,
                type: 'POST',
                data: {
                    action: 'qcld_woo_chatbot_generate_media',
                    security: qcld_woo_chatbot_ajax_obj.nonce,
                    product_id: currentProductId,
                    prompt: finalPrompt || promptText,
                    product_name: currentProductName,
                    variation_id: currentVariationId || '',
                    second_image: refId ? refId : (currentSecondImage || ''),
                    bg_preference: bgPref,
                    output_style: outputStyle,
                    image_size: imageSize,
                    generation_type: genType
                },
                timeout: 60000,
                success: function(response) {
                    // New async flow: server queued the render and returned a job id -> poll for the result.
                    if (response && response.success && response.data && response.data.job_id && !response.data.html) {
                        pollJobStatus(response.data.job_id, 0, 0);
                        return;
                    }
                    handleStep2Response(response);
                },
                error: handleAjaxError
            });
        }

        function pollJobStatus(jobId, attempt, networkErrors) {
            var maxWaitMs = 6 * 60 * 1000;
            if ((Date.now() - startTime) > maxWaitMs) {
                handleFailure('The render is taking longer than expected. Please try again.');
                return;
            }
            setTimeout(function() {
                $.ajax({
                    url: qcld_woo_chatbot_ajax_obj.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'qcld_woo_chatbot_job_status',
                        security: qcld_woo_chatbot_ajax_obj.nonce,
                        job_id: jobId
                    },
                    // Normally returns instantly; long timeout only matters if the server has to run the job inline.
                    timeout: 300000,
                    success: function(response) {
                        if (response && response.success && response.data && (response.data.status === 'queued' || response.data.status === 'processing')) {
                            pollJobStatus(jobId, attempt + 1, 0);
                            return;
                        }
                        handleStep2Response(response);
                    },
                    error: function(xhr, status, error) {
                        // Tolerate brief network hiccups (mobile connections etc.) while the render continues server-side.
                        if (networkErrors < 4 && (!xhr || !xhr.status || xhr.status >= 500 || status === 'timeout')) {
                            pollJobStatus(jobId, attempt + 1, networkErrors + 1);
                            return;
                        }
                        handleAjaxError(xhr, status, error);
                    }
                });
            }, attempt === 0 ? 4000 : 2500);
        }

        function handleStep2Response(response) {
                    if (progressTimer) {
                        clearInterval(progressTimer);
                        progressTimer = null;
                    }

                    if (response && response.success && response.data && response.data.html) {
                        // Smooth completion animation
                        if ($progressFill.length) $progressFill.css('width', '100%');
                        if ($progressPct.length) $progressPct.text('100%');

                        setTimeout(function() {
                            $spinner.addClass('qcld_woo_chatbot-hidden');
                            $submitBtn.prop('disabled', false).text(originalBtnText).css('opacity', '1').css('cursor', 'pointer');
                            var regenLabel = (typeof qcld_woo_chatbot_ajax_obj !== 'undefined' && qcld_woo_chatbot_ajax_obj.i18n && qcld_woo_chatbot_ajax_obj.i18n.palette_regenerate) ? qcld_woo_chatbot_ajax_obj.i18n.palette_regenerate : 'Regenerate';
                            var downloadLabel = (typeof qcld_woo_chatbot_ajax_obj !== 'undefined' && qcld_woo_chatbot_ajax_obj.i18n && qcld_woo_chatbot_ajax_obj.i18n.palette_download) ? qcld_woo_chatbot_ajax_obj.i18n.palette_download : 'Download';
                            var paletteHtml = '<div class="qcld_woo_chatbot-floating-palette">' +
                                '<button type="button" class="qcld_woo_chatbot-palette-btn qcld_woo_chatbot-regenerate-btn" title="' + regenLabel + '"><span class="dashicons dashicons-update-alt"></span><br>' + regenLabel + '</button>' +
                                '<button type="button" class="qcld_woo_chatbot-palette-btn qcld_woo_chatbot-download-btn" title="' + downloadLabel + '"><span class="dashicons dashicons-download"></span><br>' + downloadLabel + '</button>' +
                                '</div>';
                            $('#qcld_woo_chatbot-result-display').html(response.data.html + paletteHtml);
                            $('#qcld_woo_chatbot-result-placeholder').addClass('qcld_woo_chatbot-hidden');
                        }, 220);
                    } else {
                        var err = '';
                        if (response && response.data) {
                            if (typeof response.data === 'string') {
                                err = response.data;
                            } else if (response.data.message) {
                                err = response.data.message;
                            }
                        } else if (response && response.message) {
                            err = response.message;
                        } else if (typeof response === 'string' && response.length > 0) {
                            if (response === '0' || response === '-1') {
                                err = 'Security verification failed (WordPress Nonce expired). Please refresh the page and try again.';
                            } else {
                                err = response;
                            }
                        }
                        handleFailure(err);
                    }
        }

        function handleAjaxError(xhr, status, error) {
                    var serverError = '';
                    if (status === 'timeout') {
                        serverError = 'Request timed out. Please check your internet connection and try again.';
                    } else if (xhr) {
                        if (xhr.responseJSON && xhr.responseJSON.data) {
                            serverError = (typeof xhr.responseJSON.data === 'string') ? xhr.responseJSON.data : (xhr.responseJSON.data.message || '');
                        } else if (xhr.responseText) {
                            try {
                                var parsed = JSON.parse(xhr.responseText);
                                if (parsed && parsed.data) {
                                    serverError = (typeof parsed.data === 'string') ? parsed.data : (parsed.data.message || '');
                                }
                            } catch(e) {
                                if (xhr.status === 500) {
                                    serverError = 'Server Timeout or PHP Execution Error (HTTP 500). Please check your AI API key or increase FastCGI timeout.';
                                } else if (xhr.status === 504 || xhr.status === 502) {
                                    serverError = 'Gateway Timeout (HTTP ' + xhr.status + '). The AI provider took longer than expected to respond.';
                                } else if (xhr.status === 403) {
                                    serverError = 'Access Forbidden (HTTP 403). Nonce verification or permission check failed.';
                                }
                            }
                        }
                        if (!serverError && xhr.status) {
                            serverError = 'Server communication error (HTTP ' + xhr.status + (xhr.statusText ? ' ' + xhr.statusText : '') + ')';
                        }
                    }
                    if (!serverError && error) {
                        serverError = String(error);
                    }
                    handleFailure(serverError);
        }

        // Multi-Step Pipeline: Step 1 Pre-Process Visual Assets & Synthesize High-Fidelity Prompt

        $.ajax({
            url: qcld_woo_chatbot_ajax_obj.ajax_url,
            type: 'POST',
            data: {
                action: 'qcld_woo_chatbot_prepare_generation',
                security: qcld_woo_chatbot_ajax_obj.nonce,
                image_data: currentSecondImage || '',
                second_image: currentSecondImage || '',
                product_id: currentProductId,
                product_name: currentProductName,
                variation_id: currentVariationId || '',
                prompt: promptText,
                bg_preference: bgPref,
                output_style: outputStyle,
                image_size: imageSize,
                generation_type: genType
            },
            timeout: 60000,
            success: function(prepRes) {
                if (prepRes && prepRes.success && prepRes.data) {
                    var prepData = prepRes.data;
                    var finalRefId = prepData.ref_id || '';
                    var synthesizedPrompt = prepData.synthesized_prompt || promptText;

                    // Step 2: Invoke AI Provider for Ultra-HD Image Generation
                    executeStep2Generation(finalRefId, synthesizedPrompt, prepData.product_ref_id || '');
                } else {
                    var prepErr = (prepRes && prepRes.data && prepRes.data.message) ? prepRes.data.message : 'Scene synthesis and prompt preparation failed.';
                    handleFailure(prepErr);
                }
            },
            error: function(xhr, status, error) {
                var errText = 'Preparation error';
                if (xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                    errText = xhr.responseJSON.data.message;
                } else if (xhr && xhr.status === 500) {
                    errText = 'Server execution error during preparation (HTTP 500).';
                } else if (error) {
                    errText = String(error);
                }
                handleFailure(errText);
            }
        });
    });

    // Palette button actions
    $(document).on('click', '.qcld_woo_chatbot-regenerate-btn', function() {
        $('#qcld_woo_chatbot-submit-generation').click();
    });

    $(document).on('click', '.qcld_woo_chatbot-download-btn', function() {
        var mediaSrc = $('#qcld_woo_chatbot-result-display .qcld_woo_chatbot-output-media').attr('src');
        if (mediaSrc) {
            if (mediaSrc.startsWith('data:')) {
                var a = document.createElement('a');
                a.href = mediaSrc;
                a.download = 'qcld_woo_chatbot-generated-media';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            } else {
                var $btn = $(this);
                var originalHtml = $btn.html();
                $btn.html('<span class="dashicons dashicons-download"></span><br>Wait...');
                
                fetch(mediaSrc)
                    .then(response => response.blob())
                    .then(blob => {
                        var blobUrl = window.URL.createObjectURL(blob);
                        var a = document.createElement('a');
                        a.href = blobUrl;
                        var ext = mediaSrc.split('.').pop().split('?')[0];
                        if (!ext || ext.length > 4) ext = 'mp4';
                        a.download = 'qcld_woo_chatbot-generated-media.' + ext;
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        window.URL.revokeObjectURL(blobUrl);
                        $btn.html(originalHtml);
                    })
                    .catch(err => {
                        console.error('Download error:', err);
                        var a = document.createElement('a');
                        a.href = mediaSrc;
                        a.download = 'qcld_woo_chatbot-generated-media';
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        $btn.html(originalHtml);
                    });
            }
        } else {
            var textContent = $('#qcld_woo_chatbot-result-display .qcld_woo_chatbot-text-block, #qcld_woo_chatbot-result-display .qcld_woo_chatbot-video-text-block').text();
            if (textContent) {
                var blob = new Blob([textContent], { type: "text/plain;charset=utf-8" });
                var a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                a.download = 'qcld_woo_chatbot-generated-concept.txt';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            } else {
                alert(qcld_woo_chatbot_ajax_obj.i18n.no_media);
            }
        }
    });

    $(document).on('click', '.qcld_woo_chatbot-add-media-btn', function() {
        var mediaSrc = $('#qcld_woo_chatbot-result-display .qcld_woo_chatbot-output-media').attr('src');
        if (!mediaSrc) {
            alert(qcld_woo_chatbot_ajax_obj.i18n.only_media);
            return;
        }
        var $btn = $(this);
        var originalText = $btn.html();
        $btn.html('<span class="dashicons dashicons-update-alt qcld_woo_chatbot-spin"></span><br>' + qcld_woo_chatbot_ajax_obj.i18n.saving);
        $btn.prop('disabled', true);
        
        $.ajax({
            url: qcld_woo_chatbot_ajax_obj.ajax_url,
            type: 'POST',
            data: {
                action: 'qcld_woo_chatbot_save_media',
                security: qcld_woo_chatbot_ajax_obj.nonce,
                media_data: mediaSrc
            },
            success: function(response) {
                $btn.prop('disabled', false);
                if (response.success) {
                    $btn.html('<span class="dashicons dashicons-yes"></span><br>' + qcld_woo_chatbot_ajax_obj.i18n.saved);
                    setTimeout(function() {
                        $btn.html(originalText);
                    }, 2000);
                } else {
                    $btn.html('<span class="dashicons dashicons-warning"></span><br>' + qcld_woo_chatbot_ajax_obj.i18n.error);
                    var saveMsg = (response && response.data && response.data.message) ? response.data.message : (qcld_woo_chatbot_ajax_obj.i18n.error_saving || 'Failed to save media.');
                    alert(saveMsg);
                    setTimeout(function() {
                        $btn.html(originalText);
                    }, 2000);
                }
            },
            error: function() {
                $btn.prop('disabled', false);
                $btn.html('<span class="dashicons dashicons-warning"></span><br>' + qcld_woo_chatbot_ajax_obj.i18n.error);
                alert(qcld_woo_chatbot_ajax_obj.i18n.network_error);
                setTimeout(function() {
                    $btn.html(originalText);
                }, 2000);
            }
        });
    });

    // Modal Add to Cart Button Logic
    $('#qcld_woo_chatbot-modal-add-to-cart-btn').on('click', function() {
        var $btn = $(this);
        var originalText = $btn.html();
        
        // Validate if variable product has any unselected options
        var hasUnselected = false;
        var $firstUnselected = null;
        $('.qcld_woo_chatbot-modal-variation-select').each(function() {
            if (!$(this).val()) {
                $(this).addClass('qcld_woo_chatbot-select-error');
                hasUnselected = true;
                if (!$firstUnselected) {
                    $firstUnselected = $(this);
                }
            } else {
                $(this).removeClass('qcld_woo_chatbot-select-error');
            }
        });

        if (hasUnselected) {
            if ($firstUnselected) {
                $firstUnselected.focus();
            }
            alert((qcld_woo_chatbot_ajax_obj && qcld_woo_chatbot_ajax_obj.i18n && qcld_woo_chatbot_ajax_obj.i18n.select_option) ? qcld_woo_chatbot_ajax_obj.i18n.select_option : 'Please select product options first.');
            return;
        }

        // Sync modal quantity to page input before submission
        var $qtyWrapper = $('#qcld_woo_chatbot-modal-quantity-wrapper');
        if ($qtyWrapper.is(':visible')) {
            var modalQty = parseInt($('#qcld_woo_chatbot-modal-qty-input').val(), 10) || 1;
            var $pageQty = $('form.cart input[name="quantity"], form.cart input.qty, .woocommerce-variation-add-to-cart input[name="quantity"]');
            if ($pageQty.length > 0) {
                $pageQty.val(modalQty).trigger('change');
            }
        }

        $btn.html('<span class="dashicons dashicons-update-alt qcld_woo_chatbot-spin"></span> ' + qcld_woo_chatbot_ajax_obj.i18n.adding);
        $btn.prop('disabled', true);
        
        // Find the WooCommerce add to cart button on the main page
        var $wcAddToCart = $('.single_add_to_cart_button');
        
        if ($wcAddToCart.length > 0) {
            // Trigger the native WooCommerce Add to Cart button click
            $wcAddToCart.click();
            
            // Close the modal after a short delay
            setTimeout(function() {
                $modal.fadeOut(200);
                $btn.html(originalText);
                $btn.prop('disabled', false);
            }, 800);
        } else {
            // Fallback if button is not found (e.g. out of stock or custom theme)
            $btn.html('<span class="dashicons dashicons-warning"></span> ' + qcld_woo_chatbot_ajax_obj.i18n.error);
            setTimeout(function() {
                $btn.html(originalText);
                $btn.prop('disabled', false);
            }, 2000);
        }
    });
});

