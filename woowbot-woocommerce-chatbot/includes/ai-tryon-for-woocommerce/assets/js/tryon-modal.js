jQuery(document).ready(function($) {
    var $modal = $('#qcld_woo_chatbot-tryon-modal');
    var currentProductName = '';
    var currentProductImage = '';

    var currentProductImageBase64 = '';

    function urlToBase64(url, callback) {
        var img = new Image();
        img.crossOrigin = 'Anonymous';
        img.onload = function() {
            var canvas = document.createElement('canvas');
            canvas.width = img.width;
            canvas.height = img.height;
            var ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0);
            var dataURL = canvas.toDataURL('image/png');
            callback(dataURL);
        };
        img.onerror = function() {
            callback('');
        };
        img.src = url;
    }

    // Open Modal interface
    $('#qcld_woo_chatbot-open-modal-btn').on('click', function() {
        currentProductName = $(this).data('product-title');
        currentProductImage = $(this).data('product-image');
        currentProductImageBase64 = '';
        
        if (currentProductImage) {
            $('#qcld_woo_chatbot-modal-product-image').attr('src', currentProductImage).show();
            urlToBase64(currentProductImage, function(base64) {
                currentProductImageBase64 = base64;
            });
        } else {
            $('#qcld_woo_chatbot-modal-product-image').hide();
        }
        
        updateGenerateButtonState();
        $modal.fadeIn(200);
    });

    // Close Actions
    $(document).on('click', '.qcld_woo_chatbot-close-modal', function(e) {
        $modal.fadeOut(200);
    });

    // Image Upload Logic
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
            var reader = new FileReader();
            reader.onload = function(e) {
                currentSecondImage = e.target.result;
                $('#qcld_woo_chatbot-modal-second-image-placeholder').html('<img src="' + currentSecondImage + '" alt="Uploaded Image" />');
                updateGenerateButtonState();
            }
            reader.readAsDataURL(file);
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

    // Handle Processing Loop Form Post Actions
    $('#qcld_woo_chatbot-submit-generation').on('click', function(e) {
        e.preventDefault();
        
        var promptText = $('#qcld_woo_chatbot-prompt').val();
        var genType = $('.qcld_woo_chatbot-tab.active').data('type'); // image or video
        
        var bgPref = '';
        var outputStyle = '';
        var imageSize = '16:9'; // Default size since option removed
        
        if (genType === 'image') {
            bgPref = $('#qcld_woo_chatbot-bg-preference').val();
            outputStyle = $('#qcld_woo_chatbot-output-style').val();
            if (!promptText.trim()) {
                promptText = bgPref + " background, " + outputStyle + " style.";
            } else {
                promptText = bgPref + " background, " + outputStyle + " style. " + promptText;
            }
        } else {
            bgPref = $('#qcld_woo_chatbot-video-style').val();
            outputStyle = $('#qcld_woo_chatbot-camera-motion').val();
            if (!promptText.trim()) {
                promptText = bgPref + " style, " + outputStyle + " motion.";
            } else {
                promptText = bgPref + " style, " + outputStyle + " motion. " + promptText;
            }
        }

        $('#qcld_woo_chatbot-result-placeholder').addClass('qcld_woo_chatbot-hidden');
        $('#qcld_woo_chatbot-loading-spinner').removeClass('qcld_woo_chatbot-hidden');
        $('#qcld_woo_chatbot-result-display').empty();
        
        var $submitBtn = $(this);
        var originalBtnText = $submitBtn.text();
        $submitBtn.prop('disabled', true).text(qcld_woo_chatbot_ajax_obj.i18n.processing).css('opacity', '0.7').css('cursor', 'not-allowed');

        $.ajax({
            url: qcld_woo_chatbot_ajax_obj.ajax_url,
            type: 'POST',
            data: {
                action: 'qcld_woo_chatbot_generate_media',
                security: qcld_woo_chatbot_ajax_obj.nonce,
                prompt: promptText,
                product_name: currentProductName,
                product_image: currentProductImageBase64,
                second_image: currentSecondImage,
                // Extra fields for backend logic when implemented:
                bg_preference: bgPref,
                output_style: outputStyle,
                image_size: imageSize,
                generation_type: genType
            },
            success: function(response) {
                if (response.success) {
                    if (response.data.html) {
                        $('#qcld_woo_chatbot-loading-spinner').addClass('qcld_woo_chatbot-hidden');
                        $submitBtn.prop('disabled', false).text(originalBtnText).css('opacity', '1').css('cursor', 'pointer');
                        var paletteHtml = '<div class="qcld_woo_chatbot-floating-palette">' +
                            '<button type="button" class="qcld_woo_chatbot-palette-btn qcld_woo_chatbot-regenerate-btn" title="Regenerate"><span class="dashicons dashicons-update-alt"></span><br>Regenerate</button>' +
                            '<button type="button" class="qcld_woo_chatbot-palette-btn qcld_woo_chatbot-download-btn" title="Download"><span class="dashicons dashicons-download"></span><br>Download</button>' +
                            '</div>';
                        $('#qcld_woo_chatbot-result-display').html(response.data.html + paletteHtml);
                        $('#qcld_woo_chatbot-result-placeholder').addClass('qcld_woo_chatbot-hidden');
                    }
                } else {
                    $('#qcld_woo_chatbot-loading-spinner').addClass('qcld_woo_chatbot-hidden');
                    $submitBtn.prop('disabled', false).text(originalBtnText).css('opacity', '1').css('cursor', 'pointer');
                    $('#qcld_woo_chatbot-result-display').html('<p class="qcld_woo_chatbot-error">' + qcld_woo_chatbot_ajax_obj.i18n.execution_error + response.data.message + '</p>');
                    $('#qcld_woo_chatbot-result-placeholder').removeClass('qcld_woo_chatbot-hidden');
                }
            },
            error: function() {
                $('#qcld_woo_chatbot-loading-spinner').addClass('qcld_woo_chatbot-hidden');
                $submitBtn.prop('disabled', false).text(originalBtnText).css('opacity', '1').css('cursor', 'pointer');
                $('#qcld_woo_chatbot-result-display').html('<p class="qcld_woo_chatbot-error">' + qcld_woo_chatbot_ajax_obj.i18n.unexpected_error + '</p>');
                $('#qcld_woo_chatbot-result-placeholder').removeClass('qcld_woo_chatbot-hidden');
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
                    alert(response.data.message || qcld_woo_chatbot_ajax_obj.i18n.error_saving);
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
            }, 600);
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

