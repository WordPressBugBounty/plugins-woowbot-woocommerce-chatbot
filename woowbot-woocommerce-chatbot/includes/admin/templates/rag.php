<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
/**
 * AI Assistant Template - RAG Feature Extended
 * @package Botmaster
 */

$wpchatbot_license_valid            = get_option('wpchatbot_license_valid');
// $wpchatbot_license_valid            = 'starter';

?>

    <div class="qcl-openai">



        <h2 class="nav-tab-wrapper">
            <a href="#qcld-rag-settings-tab" class="nav-tab nav-tab-active"><?php esc_html_e('Setting options', 'woowbot-woocommerce-chatbot'); ?></a>
            <a href="#rag-sync" class="nav-tab"><?php esc_html_e('Sync and upload options', 'woowbot-woocommerce-chatbot'); ?></a>
            <a href="#rag-database" class="nav-tab"><?php esc_html_e('KnowledgeBase Database', 'woowbot-woocommerce-chatbot'); ?></a>
        </h2>

        <div id="qcld-rag-settings-tab" class="qcld-tab-content active">
            <!-- ===========================
         EMBEDDING SOURCE OPTIONS
    ============================ -->
        <div class="wrap">
                <h3><?php esc_html_e('Choose Data Sources to Embed', 'woowbot-woocommerce-chatbot'); ?></h3>

                <div class="mb-none">
                    <input type="checkbox" id="rag_embed_pages" <?php checked(get_option('rag_embed_pages'), '1'); ?>>
                    <label for="rag_embed_pages"><?php esc_html_e('Pages', 'woowbot-woocommerce-chatbot'); ?></label>
                </div>

                <div class="mb-none">
                    <input type="checkbox" id="rag_embed_posts" <?php checked(get_option('rag_embed_posts'), '1'); ?>>
                    <label for="rag_embed_posts"><?php esc_html_e('Posts', 'woowbot-woocommerce-chatbot'); ?></label>
                </div>
                <div class="mb-none">
                    <?php
                    $custom_post_types = get_post_types(['public' => true, '_builtin' => false], 'objects');
                    $selected_cpts = get_option('rag_embed_cpts', []);
                    ?>
                    <label><strong><?php esc_html_e('Custom Post Types:', 'woowbot-woocommerce-chatbot'); ?></strong></label><br>
                    <div class="rag_embed_cpts_wrapper">
                        <?php
                         foreach ($custom_post_types as $cpt): 
                            $is_pro = !in_array($cpt->name, ['product']);
                         ?>
                            <div class="rag_cpt_checkbox">
                                <input type="checkbox" class="rag_embed_cpts_checkbox" 
                                    id="rag_cpt_<?php echo esc_attr($cpt->name); ?>" 
                                    value="<?php echo esc_attr($cpt->name); ?>"
                                    <?php echo in_array($cpt->name, $selected_cpts) ? 'checked' : ''; ?>
                                     <?php echo (($is_pro) ? 'disabled' : ''); ?>>
                                <label for="rag_cpt_<?php echo esc_attr($cpt->name); ?>"><?php echo esc_html($cpt->label); ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <p class="description"><?php esc_html_e('Select multiple custom post types to embed.', 'woowbot-woocommerce-chatbot'); ?></p>
                </div>
        </div>
                <!-- ===========================
            EXECUTION BUTTON
        ============================ -->
            <div class="wrap my-4">
                <p class="qcld-red-text"> <b><?php esc_html_e('Please connect to an AI service like OpenAI or Gemini before embedding. ', 'woowbot-woocommerce-chatbot'); ?></b><b><a target="_blank" href="<?php echo esc_url('https://woowbot.pro/docs/knowledgebase/how-to-use-an-embedded-vector-database-and-rag-to-get-customized-responses-from-ai/'); ?>"><?php esc_html_e('Check this Tutorial for more details.', 'woowbot-woocommerce-chatbot'); ?></a></b></p>
                <form method="post" id="rag_embed_form">
                    <input type="hidden" name="embed_all_sources" value="1">
                    <button type="button" id="rag_embed_btn" class="button button-primary"><?php esc_html_e('Embed All Selected Sources', 'woowbot-woocommerce-chatbot'); ?></button>
                </form>

                <?php 
                    if (isset($_POST['embed_all_sources'])):
                        if( ( get_option( 'qcld_openai_enabled') == 1  && get_option('qcld_open_ai_api_key') ) || ( get_option('qcld_gemini_enabled') == 1 && get_option('qcld_gemini_api_key') ) || ( get_option('qcld_openrouter_enabled') == 1 && get_option('qcld_openrouter_api_key') ) ){
                ?>
                        <h3><?php esc_html_e('Embedding started...', 'woowbot-woocommerce-chatbot'); ?></h3>
                        <?php  Qcld_Bot_Rag::instance()->wp_rag_embed_all_sources(); ?>
                 <?php    }else{ ?>
                    <script>
                    swal.fire('', '<?php echo esc_js( __('Please connect to an AI service like OpenAI or Gemini with API key before embedding.', 'woowbot-woocommerce-chatbot') ); ?>', 'warning');
                    </script>
                <?php 
                    }
                endif;
                ?>
            </div>
                <!-- ===========================
            SAVE SETTINGS BUTTON
        ============================ -->
            <div class="wrap">
                <button class="qcld-btn-primary" id="save_rag_setting"><?php esc_html_e('Save Settings', 'woowbot-woocommerce-chatbot'); ?></button>
            </div>
        </div>


        <div id="rag-sync" class="qcld-tab-content">
                        <?php if ( $wpchatbot_license_valid != 'master' && $wpchatbot_license_valid != 'professional'): ?>
                            <div class="wrap">
                                <div class="rag-warning-notice">
                                    <p class="rag-warning-title">
                                        <?php
                                        /* translators: 1: Link to Professional pricing, 2: Link to Master pricing */
                                        printf(
                                            esc_html__( 'These options are available with the WoowbotPro %1$sProfessional%2$s and %3$sMaster%4$s Licenses', 'woowbot-woocommerce-chatbot' ),
                                            '<a href="https://www.woowbot.pro/pricing/" target="_blank" class="rag-danger-link">',
                                            '</a>',
                                            '<a href="https://www.woowbot.pro/pricing/" target="_blank" class="rag-danger-link">',
                                            '</a>'
                                        );
                                        ?>
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="<?php if ( $wpchatbot_license_valid != 'master' && $wpchatbot_license_valid != 'professional'){ echo 'rag-restricted-disabled'; } ?>">
                               <!-- ===========================
                            SYNC SETTINGS
                        ============================ -->
                        <div class="wrap">
                            <h3><?php esc_html_e('Sync Settings', 'woowbot-woocommerce-chatbot'); ?></h3>

                            <div class="mb-3">
                                <input type="checkbox" id="rag_auto_sync_enabled" <?php checked(get_option('rag_auto_sync_enabled'), '1'); ?>>
                                <label for="rag_auto_sync_enabled"><strong><?php esc_html_e('Enable Auto Sync (on Save)', 'woowbot-woocommerce-chatbot'); ?></strong></label>
                                <p class="description"><?php esc_html_e('Automatically update embeddings when a post or product is saved/updated.', 'woowbot-woocommerce-chatbot'); ?></p>
                            </div>

                        </div>


                            <!-- ===========================
                            PDF UPLOAD (AJAX)
                        ============================ -->
                        <div class="wrap">
                            <h3><?php esc_html_e('Upload PDF for RAG', 'woowbot-woocommerce-chatbot'); ?></h3>

                            <form id="rag-pdf-form">
                                <input type="file" id="rag-pdf-files" name="rag_pdf[]" multiple accept="application/pdf" />
                                <br>
                                <button type="submit" class="button button-primary" id="rag-pdf-submit"><?php esc_html_e('Upload & Embed PDF', 'woowbot-woocommerce-chatbot'); ?></button>
                                <span id="rag-pdf-status" class="rag-status-text"></span>
                            </form>

                            <div id="rag-pdf-output" class="rag-output-text"></div>
                        </div>

                        <!-- ===========================
                            CSV UPLOAD (AJAX)
                        ============================ -->
                        <div class="wrap">
                            <h3><?php esc_html_e('Upload CSV Data for RAG', 'woowbot-woocommerce-chatbot'); ?></h3>
                            <p><?php esc_html_e('Upload CSV files with data to be embedded. Each row will be processed as a separate document.', 'woowbot-woocommerce-chatbot'); ?> <a href="<?php echo esc_url( plugin_dir_url(__FILE__) . 'download/rag_test_data.csv' ); ?>"><?php esc_html_e('Download Test Data', 'woowbot-woocommerce-chatbot'); ?></a></p>

                            <form id="rag-csv-form">
                                <input type="file" id="rag-csv-files" name="rag_csv[]" multiple accept=".csv,text/csv" />
                                <br>
                                <button type="submit" class="button button-primary" id="rag-csv-submit"><?php esc_html_e('Upload & Embed CSV', 'woowbot-woocommerce-chatbot'); ?></button>
                                <span id="rag-csv-status" class="rag-status-text"></span>
                            </form>

                            <div id="rag-csv-output" class="rag-output-text"></div>
                        </div>

                        <!-- ===========================
                            XAML UPLOAD (AJAX)
                        ============================ -->
                        <div class="wrap">
                            <h3><?php esc_html_e('Upload XML Data for RAG', 'woowbot-woocommerce-chatbot'); ?></h3>
                            <p><?php esc_html_e('Upload XML files with data to be embedded.', 'woowbot-woocommerce-chatbot'); ?></p>

                            <form id="rag-xaml-form">
                                <input type="file" id="rag-xaml-files" name="rag_xaml[]" multiple accept=".xaml,text/xml,application/xml" />
                                <br>
                                <button type="submit" class="button button-primary" id="rag-xaml-submit"><?php esc_html_e('Upload & Embed XML', 'woowbot-woocommerce-chatbot'); ?></button>
                                <span id="rag-xaml-status" class="rag-status-text"></span>
                            </form>

                            <div id="rag-xaml-output" class="rag-output-text"></div>
                        </div>

                            <!-- ===========================
                            SITEMAP SUBMISSION
                        ============================ -->
                        <div class="wrap">
                            <h3><?php esc_html_e('Submit Sitemap for RAG', 'woowbot-woocommerce-chatbot'); ?></h3>
                            <p><?php esc_html_e('Enter your XML Sitemap URL to crawl and embed all pages.', 'woowbot-woocommerce-chatbot'); ?></p>
                            <input type="url" id="botmaster_sitemap_url" class="regular-text rag-sitemap-input" placeholder="<?php esc_attr_e('https://example.com/sitemap.xml', 'woowbot-woocommerce-chatbot'); ?>">
                            <button type="button" id="botmaster_submit_sitemap_btn" class="button button-primary"><?php esc_html_e('Process Sitemap', 'woowbot-woocommerce-chatbot'); ?></button>
                            <div id="botmaster_sitemap_status" class="qcld-mt-10"></div>
                        </div>
                        </div>
                        
                        <div class="wrap">
                            <button class="qcld-btn-primary" id="save_rag_setting"><?php esc_html_e('Save Settings', 'woowbot-woocommerce-chatbot'); ?></button>
                        </div>
        </div>

    <script>
    jQuery(document).ready(function($) {
        $('#botmaster_submit_sitemap_btn').on('click', function() {
            var sitemapUrl = $('#botmaster_sitemap_url').val();
            var statusDiv = $('#botmaster_sitemap_status');
            
            if (!sitemapUrl) {
                alert('<?php echo esc_js(__('Please enter a Sitemap URL', 'woowbot-woocommerce-chatbot')); ?>');
                return;
            }
            
            statusDiv.html('<?php echo esc_js(__('Processing... please wait.', 'woowbot-woocommerce-chatbot')); ?>');
            $(this).prop('disabled', true);
            
            $.post(ajaxurl, {
                action: 'botmaster_submit_sitemap',
                sitemap_url: sitemapUrl,
                nonce: '<?php echo esc_attr( wp_create_nonce("botmaster_kb_nonce") ); ?>'
            }, function(response) {
                $('#botmaster_submit_sitemap_btn').prop('disabled', false);
                if (response.success) {
                    statusDiv.html('<div class="notice notice-success inline"><p>' + response.data.message + '</p></div>');
                } else {
                    statusDiv.html('<div class="notice notice-error inline"><p><?php echo esc_js(__('Error: ', 'woowbot-woocommerce-chatbot')); ?>' + response.data + '</p></div>');
                }
            });
        });
    });
    </script>





        <div id="rag-database" class="qcld-tab-content">
                    <!-- ===========================
         KNOWLEDGE BASE MANAGEMENT
    ============================ -->
    <div class="wrap">
        <h3><?php esc_html_e('Knowledge Base', 'woowbot-woocommerce-chatbot'); ?></h3>
        
        <div class="tablenav top">
            <div class="alignleft actions bulkactions">
                <select id="rag-bulk-action-selector">
                    <option value="-1"><?php esc_html_e('Bulk Actions', 'woowbot-woocommerce-chatbot'); ?></option>
                    <option value="delete"><?php esc_html_e('Delete', 'woowbot-woocommerce-chatbot'); ?></option>
                </select>
                <button type="button" id="rag-apply-bulk-action" class="button action"><?php esc_html_e('Apply', 'woowbot-woocommerce-chatbot'); ?></button>
            </div>
            <div class="alignleft actions">
                <button type="button" id="rag-delete-all" class="button button-link-delete qcld-ml-10"><?php esc_html_e('Delete All', 'woowbot-woocommerce-chatbot'); ?></button>
           
            
            <div class="alignright actions qcld-ml-10">
                <form method="get" class="qcld-d-inline-block" action="?page=chatbot_ai_setting#ai-knowledge-base-tab#rag-database">
                    <input type="hidden" name="page" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_GET['page'] ?? '' ) ) ); ?>">
                    <?php if (isset($_GET['post_type'])): ?>
                        <input type="hidden" name="post_type" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) ); ?>">
                    <?php endif; ?>
                    <p class="search-box qcld-m-0">
                        <input type="search" id="rag-search-input" name="s" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_GET['s'] ?? '' ) ) ); ?>" placeholder="<?php esc_attr_e('Search documents...', 'woowbot-woocommerce-chatbot'); ?>">
                        <input type="submit" id="search-submit" class="button" value="<?php esc_attr_e('Search', 'woowbot-woocommerce-chatbot'); ?>">
                    </p>
                </form>
                </div>
            </div>
            
            <br class="clear">
        </div>
                    <?php
            global $wpdb;
            $table_rag_documents = $wpdb->prefix . 'rag_documents';

            // Handle Table Creation
            if (isset($_POST['rag_create_table']) && check_admin_referer('rag_create_table_nonce')) {
                $charset = $wpdb->get_charset_collate();
                $sql_rag_documents = "CREATE TABLE $table_rag_documents (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    doc_id VARCHAR(100) DEFAULT NULL,
                    title VARCHAR(255) NOT NULL,
                    content LONGTEXT NOT NULL,
                    embedding LONGTEXT NOT NULL,
                    source_type VARCHAR(20) DEFAULT 'post', 
                    source_url VARCHAR(255) DEFAULT NULL,
                    file_url TEXT DEFAULT NULL,
                    metadata LONGTEXT DEFAULT NULL,
                    status VARCHAR(50) DEFAULT 'complete',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                ) $charset;";
                require_once ABSPATH . 'wp-admin/includes/upgrade.php';
                dbDelta($sql_rag_documents);
                echo '<div class="notice notice-success inline"><p>' . esc_html__('Database table created successfully.', 'woowbot-woocommerce-chatbot') . '</p></div>';
            }

            // Check if table exists
            $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $wpdb->esc_like($table_rag_documents))) === $table_rag_documents; // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            
            if (!$table_exists) {
                ?>
                <div class="wrap">
                    <h3><?php esc_html_e('Knowledge Base Database', 'woowbot-woocommerce-chatbot'); ?></h3>
                    <div class="notice notice-warning inline">
                        <p>
                            <?php
                            /* translators: %s: Database table name */
                            printf(
                                esc_html__( 'The %s table does not exist. Please create it to start using the Knowledge Base.', 'woowbot-woocommerce-chatbot' ),
                                '<strong>' . esc_html( $table_rag_documents ) . '</strong>'
                            );
                            ?>
                        </p>
                    </div>
                    <form method="post">
                        <?php wp_nonce_field('rag_create_table_nonce'); ?>
                        <input type="hidden" name="rag_create_table" value="1">
                        <button type="submit" class="button button-primary"><?php esc_html_e('Create Database Table', 'woowbot-woocommerce-chatbot'); ?></button>
                    </form>
                </div>
                <?php
            } else {
            ?>
                    <?php
            $table_name = $table_rag_documents;
            
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $search_query = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
            
            if ($search_query) {
                $like = '%' . $wpdb->esc_like($search_query) . '%';
                $safe_table = esc_sql( $table_name );
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                $total_items = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM {$safe_table} WHERE title LIKE %s OR content LIKE %s", $like, $like));
            } else {
                $safe_table = esc_sql( $table_name );
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                $total_items = $wpdb->get_var("SELECT COUNT(id) FROM {$safe_table}");
            }
            $items_per_page = 50;
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $page = isset($_GET['paged']) ? absint(wp_unslash($_GET['paged'])) : 1;
            $offset = ($page - 1) * $items_per_page;
            $total_pages = ceil($total_items / $items_per_page);

            $pagination_args = array(
                'base' => add_query_arg(array('paged' => '%#%', 's' => $search_query), '?page=chatbot_ai_setting') . '#ai-knowledge-base-tab#rag-database',
                'format' => '',
                'prev_text' => __('&laquo;', 'woowbot-woocommerce-chatbot'),
                'next_text' => __('&raquo;', 'woowbot-woocommerce-chatbot'),
                'total' => $total_pages,
                'current' => $page,
                'type' => 'plain',
            );
           
            if ($total_pages > 1) {
                /* translators: %s: Number of items. */
                $displaying_num_text = sprintf( _n( '%s item', '%s items', $total_items, 'woowbot-woocommerce-chatbot' ), number_format_i18n( $total_items ) );
                echo wp_kses_post( '<div class="tablenav-pages"><span class="displaying-num">' . esc_html( $displaying_num_text ) . '</span>' );
                echo wp_kses_post( paginate_links( $pagination_args ) );
                echo wp_kses_post( '</div>' );
            }
            ?>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <td id="cb" class="manage-column column-cb check-column">
                        <input type="checkbox" id="rag-select-all">
                    </td>
                    <th><?php esc_html_e('Title', 'woowbot-woocommerce-chatbot'); ?></th>
                    <th><?php esc_html_e('Content', 'woowbot-woocommerce-chatbot'); ?></th>
                    <th><?php esc_html_e('Source Type', 'woowbot-woocommerce-chatbot'); ?></th>
                    <th><?php esc_html_e('URL/File', 'woowbot-woocommerce-chatbot'); ?></th>
                    <th><?php esc_html_e('Status', 'woowbot-woocommerce-chatbot'); ?></th>
                    <th><?php esc_html_e('Actions', 'woowbot-woocommerce-chatbot'); ?></th>
                </tr>
            </thead>
            <tbody id="rag-knowledge-base-list">
                <?php
                if ($search_query) {
                    $like = '%' . $wpdb->esc_like($search_query) . '%';
                    $safe_table = esc_sql( $table_name );
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                    $documents = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$safe_table} WHERE title LIKE %s OR content LIKE %s ORDER BY created_at DESC LIMIT %d OFFSET %d", $like, $like, $items_per_page, $offset));
                } else {
                    $safe_table = esc_sql( $table_name );
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                    $documents = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$safe_table} ORDER BY created_at DESC LIMIT %d OFFSET %d", $items_per_page, $offset));
                }

                if ($documents) {
                    foreach ($documents as $doc) {
                        ?>
                        <tr id="rag-doc-<?php echo esc_attr( $doc->id ); ?>">
                            <th scope="qcld-row" class="check-column">
                                <input type="checkbox" class="rag-doc-checkbox" value="<?php echo esc_attr( $doc->id ); ?>">
                            </th>
                            <td><?php echo esc_html($doc->title); ?></td>
                            <td>
                                <div class="rag-doc-preview-text">
                                    <?php echo wp_kses_post($doc->content); ?>
                                </div>
                            </td>
                            <td><?php echo esc_html($doc->source_type); ?></td>
                            <td><?php echo esc_html($doc->source_url ?: $doc->file_url); ?></td>
                            <td><?php echo esc_html($doc->status); ?></td>
                            <td>
                                <?php if (!in_array($doc->source_type, ['csv', 'xml', 'xaml','sitemap'])): ?>
                                    <button class="button button-small rag-sync-doc" data-id="<?php echo esc_attr( $doc->id ); ?>" title="<?php esc_attr_e('Re-sync data from source', 'woowbot-woocommerce-chatbot'); ?>"><?php esc_html_e('Sync', 'woowbot-woocommerce-chatbot'); ?></button>
                                <?php endif; ?>
                                <button class="button button-small rag-edit-doc" data-id="<?php echo esc_attr( $doc->id ); ?>"><?php esc_html_e('Edit', 'woowbot-woocommerce-chatbot'); ?></button>
                                <button class="button button-small button-link-delete rag-delete-doc" data-id="<?php echo esc_attr( $doc->id ); ?>"><?php esc_html_e('Delete', 'woowbot-woocommerce-chatbot'); ?></button>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    echo '<tr><td colspan="7">' . esc_html__('No documents found in knowledge base.', 'woowbot-woocommerce-chatbot') . '</td></tr>';
                }
                ?>
            </tbody>
        </table>
        
        <div class="tablenav bottom">
            <?php
            if ($total_pages > 1) {
                /* translators: %s: Number of items. */
                $displaying_num_text = sprintf( _n( '%s item', '%s items', $total_items, 'woowbot-woocommerce-chatbot' ), number_format_i18n( $total_items ) );
                echo wp_kses_post( '<div class="tablenav-pages"><span class="displaying-num">' . esc_html( $displaying_num_text ) . '</span>' );
                echo wp_kses_post( paginate_links( $pagination_args ) );
                echo wp_kses_post( '</div>' );
            }
            ?>
            <br class="clear">
        </div>
        <?php } ?>
    </div>
        </div>

    <!-- Edit Document Modal -->
    <div id="rag-edit-modal">
        <div id="rag-edit-modal-content">
            <h4><?php esc_html_e('Edit Knowledge Base Document', 'woowbot-woocommerce-chatbot'); ?></h4>
            <input type="hidden" id="edit-doc-id">
            <div class="mb-3">
                <label><?php esc_html_e('Title', 'woowbot-woocommerce-chatbot'); ?></label><br>
                <input type="text" id="edit-doc-title" class="regular-text rag-full-width">
            </div>
            <div class="mb-3">
                <label><?php esc_html_e('Content', 'woowbot-woocommerce-chatbot'); ?></label><br>
                <textarea id="edit-doc-content" rows="10" class="rag-full-width"></textarea>
            </div>
            <p class="description"><?php esc_html_e('Note: Updating content will re-generate embeddings.', 'woowbot-woocommerce-chatbot'); ?></p>
            <div class="mt-3">
                <button id="save-edit-doc" class="button button-primary"><?php esc_html_e('Save Changes', 'woowbot-woocommerce-chatbot'); ?></button>
                <button id="close-edit-modal" class="button"><?php esc_html_e('Cancel', 'woowbot-woocommerce-chatbot'); ?></button>
            </div>
        </div>
    </div>
    </div>



<script>
jQuery(document).ready(function($) {
	// PDF Upload Handler
	$('#rag-pdf-form').on('submit', function(e) {
		e.preventDefault();
		
		var fileInput = $('#rag-pdf-files')[0];
		if (!fileInput.files.length) {
			alert('<?php echo esc_js(__('Please select PDF files to upload', 'woowbot-woocommerce-chatbot')); ?>');
			return;
		}
		
		var formData = new FormData();
		for (var i = 0; i < fileInput.files.length; i++) {
			formData.append('rag_pdf[]', fileInput.files[i]);
		}
		formData.append('action', 'rag_upload_pdf');
		formData.append('nonce', '<?php echo esc_attr( wp_create_nonce('rag_upload_nonce') ); ?>');
		
		$('#rag-pdf-submit').prop('disabled', true);
		$('#rag-pdf-status').html('<span class="spinner is-active qcld-spinner-no-float"></span> <?php echo esc_js(__('Uploading and processing...', 'woowbot-woocommerce-chatbot')); ?>');
		$('#rag-pdf-output').html('');
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function(response) {
				$('#rag-pdf-submit').prop('disabled', false);
				if (response.success) {
					$('#rag-pdf-status').html('<span class="rag-status-success">✓ <?php echo esc_js(__('Complete', 'woowbot-woocommerce-chatbot')); ?></span>');
					$('#rag-pdf-output').html(response.data.output);
					$('#rag-pdf-files').val('');
				} else {
					$('#rag-pdf-status').html('<span class="rag-status-error">✗ <?php echo esc_js(__('Error', 'woowbot-woocommerce-chatbot')); ?></span>');
					$('#rag-pdf-output').html('<p class="qcld-red-text">' + response.data.message + '</p>');
				}
			},
			error: function(xhr) {
				$('#rag-pdf-submit').prop('disabled', false);
				$('#rag-pdf-status').html('<span class="rag-status-error">✗ <?php echo esc_js(__('Error', 'woowbot-woocommerce-chatbot')); ?></span>');
				$('#rag-pdf-output').html('<p class="qcld-red-text"><?php echo esc_js(__('Upload failed. Please try again.', 'woowbot-woocommerce-chatbot')); ?></p>');
			}
		});
	});
	
	// CSV Upload Handler
	$('#rag-csv-form').on('submit', function(e) {
		e.preventDefault();
		
		var fileInput = $('#rag-csv-files')[0];
		if (!fileInput.files.length) {
			alert('<?php echo esc_js(__('Please select CSV files to upload', 'woowbot-woocommerce-chatbot')); ?>');
			return;
		}
		
		var formData = new FormData();
		for (var i = 0; i < fileInput.files.length; i++) {
			formData.append('rag_csv[]', fileInput.files[i]);
		}
		formData.append('action', 'rag_upload_csv');
		formData.append('nonce', '<?php echo esc_attr( wp_create_nonce('rag_upload_nonce') ); ?>');
		
		$('#rag-csv-submit').prop('disabled', true);
		$('#rag-csv-status').html('<span class="spinner is-active qcld-spinner-no-float"></span> <?php echo esc_js(__('Uploading and processing...', 'woowbot-woocommerce-chatbot')); ?>');
		$('#rag-csv-output').html('');
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function(response) {
				$('#rag-csv-submit').prop('disabled', false);
				if (response.success) {
					$('#rag-csv-status').html('<span class="rag-status-success">✓ <?php echo esc_js(__('Complete', 'woowbot-woocommerce-chatbot')); ?></span>');
					$('#rag-csv-output').html(response.data.output);
					$('#rag-csv-files').val('');
				} else {
					$('#rag-csv-status').html('<span class="rag-status-error">✗ <?php echo esc_js(__('Error', 'woowbot-woocommerce-chatbot')); ?></span>');
					$('#rag-csv-output').html('<p class="qcld-red-text">' + response.data.message + '</p>');
				}
			},
			error: function(xhr) {
				$('#rag-csv-submit').prop('disabled', false);
				$('#rag-csv-status').html('<span class="rag-status-error">✗ <?php echo esc_js(__('Error', 'woowbot-woocommerce-chatbot')); ?></span>');
				$('#rag-csv-output').html('<p class="qcld-red-text"><?php echo esc_js(__('Upload failed. Please try again.', 'woowbot-woocommerce-chatbot')); ?></p>');
			}
		});
	});

	// XAML Upload Handler
	$('#rag-xaml-form').on('submit', function(e) {
		e.preventDefault();
		
		var fileInput = $('#rag-xaml-files')[0];
		if (!fileInput.files.length) {
			alert('<?php echo esc_js(__('Please select XML files to upload', 'woowbot-woocommerce-chatbot')); ?>');
			return;
		}
		
		var formData = new FormData();
		for (var i = 0; i < fileInput.files.length; i++) {
			formData.append('rag_xaml[]', fileInput.files[i]);
		}
		formData.append('action', 'rag_upload_xaml');
		formData.append('nonce', '<?php echo esc_attr( wp_create_nonce('rag_upload_nonce') ); ?>');
		
		$('#rag-xaml-submit').prop('disabled', true);
		$('#rag-xaml-status').html('<span class="spinner is-active qcld-spinner-no-float"></span> <?php echo esc_js(__('Uploading and processing...', 'woowbot-woocommerce-chatbot')); ?>');
		$('#rag-xaml-output').html('');
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function(response) {
				$('#rag-xaml-submit').prop('disabled', false);
				if (response.success) {
					$('#rag-xaml-status').html('<span class="rag-status-success">✓ <?php echo esc_js(__('Complete', 'woowbot-woocommerce-chatbot')); ?></span>');
					$('#rag-xaml-output').html(response.data.output);
					$('#rag-xaml-files').val('');
				} else {
					$('#rag-xaml-status').html('<span class="rag-status-error">✗ <?php echo esc_js(__('Error', 'woowbot-woocommerce-chatbot')); ?></span>');
					$('#rag-xaml-output').html('<p class="qcld-red-text">' + response.data.message + '</p>');
				}
			},
			error: function(xhr) {
				$('#rag-xaml-submit').prop('disabled', false);
				$('#rag-xaml-status').html('<span class="rag-status-error">✗ <?php echo esc_js(__('Error', 'woowbot-woocommerce-chatbot')); ?></span>');
				$('#rag-xaml-output').html('<p class="qcld-red-text"><?php echo esc_js(__('Upload failed. Please try again.', 'woowbot-woocommerce-chatbot')); ?></p>');
			}
		});
	});
});
</script>
<script>
jQuery(document).ready(function($) {
    // Tab switching logic
    $('.nav-tab-wrapper').on('click', '.nav-tab', function(e) {
        var $tab = $(this);
        var targetId = $tab.attr('href');
        var $content = $(targetId);

        if ($tab.data('disabled')) {
            e.preventDefault();
            return false;
        }

        if ($content.length) {
            e.preventDefault();
            // Update tabs
            $('.nav-tab').removeClass('nav-tab-active');
            $tab.addClass('nav-tab-active');
            
            // Update content area
            $('.qcld-tab-content').removeClass('active');
            $content.addClass('active');

            // Update URL hash without jumping
            var compositeHash = '#ai-knowledge-base-tab' + targetId;
            if (history.pushState) {
                history.pushState(null, null, compositeHash);
            } else {
                window.location.hash = compositeHash;
            }
        }
    });

    // Handle hash on page load
    var hash = window.location.hash;
    if (hash && hash.includes('#rag-')) {
        var subTabHash = '#' + hash.split('#').pop();
        var $targetTab = $('.nav-tab-wrapper a[href="' + subTabHash + '"]');
        if ($targetTab.length) {
            $targetTab.trigger('click');
        }
    } else if (hash === '#ai-knowledge-base-tab') {
        // Default to first tab if no sub-tab specified
        $('.nav-tab-wrapper a').first().trigger('click');
    }
});
</script>
