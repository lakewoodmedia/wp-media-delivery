<?php

namespace Advanced_Media_Offloader\Admin\Observers;

use Advanced_Media_Offloader\Interfaces\ObserverInterface;

class MediaLibraryColumns implements ObserverInterface
{
    private static $instance = null;

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        $this->register();
    }

    /**
     * Register the observer with WordPress hooks.
     *
     * @return void
     */
    public function register(): void
    {
        // Add the storage status column
        add_filter('manage_media_columns', [$this, 'add_media_columns']);
        add_action('manage_media_custom_column', [$this, 'display_media_column_content'], 10, 2);
        add_action('admin_head', [$this, 'add_column_styles']);
        
        // Add inline actions to media list
        add_filter('media_row_actions', [$this, 'add_media_row_actions'], 10, 2);
        
        // Add bulk action
        add_filter('bulk_actions-upload', [$this, 'register_bulk_actions']);
        add_filter('handle_bulk_actions-upload', [$this, 'handle_bulk_actions'], 10, 3);
        
        // Admin notices for action results
        add_action('admin_notices', [$this, 'display_admin_notices']);
    }

    /**
     * Add the cloud storage status column to the media library.
     *
     * @param array $columns The existing columns.
     * @return array The modified columns.
     */
    public function add_media_columns(array $columns): array
    {
        $columns['advmo_storage_status'] = __('Storage Location', 'wp-media-delivery');
        return $columns;
    }

    /**
     * Display the content for the custom column.
     *
     * @param string $column_name The name of the column.
     * @param int $post_id The ID of the post.
     * @return void
     */
    public function display_media_column_content(string $column_name, int $post_id): void
    {
        if ($column_name !== 'advmo_storage_status') {
            return;
        }

        $is_offloaded = get_post_meta($post_id, 'advmo_offloaded', true);
        $provider_type = get_post_meta($post_id, 'advmo_provider_type', true);
        
        if (!$is_offloaded) {
            echo '<span class="advmo-status advmo-status-local" title="' . esc_attr__('Served from local server', 'wp-media-delivery') . '">';
            echo '<span class="dashicons dashicons-database"></span> ';
            echo esc_html__('Local Server', 'wp-media-delivery');
            echo '</span>';
            return;
        }

        $provider_label = $this->get_provider_label($provider_type);
        $provider_icon = $this->get_provider_icon($provider_type);
        
        echo '<span class="advmo-status advmo-status-cloud" title="' . esc_attr(sprintf(__('Served from %s', 'wp-media-delivery'), $provider_label)) . '">';
        echo '<span class="dashicons ' . esc_attr($provider_icon) . '"></span> ';
        echo esc_html($provider_label);
        echo '</span>';
    }
    
    /**
     * Add custom row actions to the media list.
     *
     * @param array    $actions An array of actions.
     * @param \WP_Post $post    The post object.
     * @return array The filtered actions.
     */
    public function add_media_row_actions(array $actions, \WP_Post $post): array
    {
        // Only show for attachments
        if ($post->post_type !== 'attachment') {
            return $actions;
        }
        
        $is_offloaded = get_post_meta($post->ID, 'advmo_offloaded', true);
        
        // Only add offload option if not already offloaded and we have a cloud provider
        if (!$is_offloaded && advmo_get_cloud_provider_key()) {
            $offload_url = wp_nonce_url(
                add_query_arg(
                    [
                        'action' => 'advmo_offload_media',
                        'attachment_id' => $post->ID,
                    ],
                    admin_url('admin.php')
                ),
                'advmo_offload_media_' . $post->ID
            );
            
            $actions['offload'] = sprintf(
                '<a href="%s" class="advmo-offload-link">%s</a>',
                esc_url($offload_url),
                esc_html__('Offload to Cloud', 'wp-media-delivery')
            );
        }
        
        // Add fetch back option for already offloaded media
        if ($is_offloaded) {
            $fetch_url = wp_nonce_url(
                add_query_arg(
                    [
                        'action' => 'advmo_fetch_media',
                        'attachment_id' => $post->ID,
                    ],
                    admin_url('admin.php')
                ),
                'advmo_fetch_media_' . $post->ID
            );
            
            $actions['fetch_back'] = sprintf(
                '<a href="%s" class="advmo-fetch-link">%s</a>',
                esc_url($fetch_url),
                esc_html__('Fetch Back', 'wp-media-delivery')
            );
        }
        
        return $actions;
    }
    
    /**
     * Register bulk actions for the media library.
     *
     * @param array $bulk_actions Array of bulk actions.
     * @return array The filtered bulk actions.
     */
    public function register_bulk_actions(array $bulk_actions): array
    {
        // Only add if we have a cloud provider configured
        if (advmo_get_cloud_provider_key()) {
            $bulk_actions['advmo_bulk_offload'] = __('Offload to Cloud', 'wp-media-delivery');
        }
        
        return $bulk_actions;
    }
    
    /**
     * Handle the bulk actions for the media library.
     *
     * @param string $redirect_to The redirect URL.
     * @param string $doaction    The action being taken.
     * @param array  $post_ids    The items to take the action on.
     * @return string The redirect URL.
     */
    public function handle_bulk_actions(string $redirect_to, string $doaction, array $post_ids): string
    {
        if ($doaction !== 'advmo_bulk_offload') {
            return $redirect_to;
        }
        
        // Don't redirect to bulk edit again
        $redirect_to = remove_query_arg(
            ['bulk_edit'], 
            $redirect_to
        );
        
        $offloaded = 0;
        $errors = 0;
        
        foreach ($post_ids as $post_id) {
            // Skip already offloaded files
            if (get_post_meta($post_id, 'advmo_offloaded', true)) {
                continue;
            }
            
            // Get cloud provider and attempt to offload
            global $advmo;
            if ($advmo->container->has('offloader') && $advmo->container->get('offloader') !== null) {
                $offloader = $advmo->container->get('offloader');
                $uploader = new \Advanced_Media_Offloader\Services\CloudAttachmentUploader($offloader->cloudProvider);
                
                if ($uploader->uploadAttachment((int)$post_id)) {
                    $offloaded++;
                } else {
                    $errors++;
                }
            }
        }
        
        return add_query_arg(
            [
                'advmo_bulk_offloaded' => $offloaded,
                'advmo_bulk_errors' => $errors,
            ],
            $redirect_to
        );
    }
    
    /**
     * Display admin notices for bulk operations.
     *
     * @return void
     */
    public function display_admin_notices(): void
    {
        // For bulk offload results
        if (isset($_GET['advmo_bulk_offloaded']) || isset($_GET['advmo_bulk_errors'])) {
            $offloaded = isset($_GET['advmo_bulk_offloaded']) ? (int)$_GET['advmo_bulk_offloaded'] : 0;
            $errors = isset($_GET['advmo_bulk_errors']) ? (int)$_GET['advmo_bulk_errors'] : 0;
            
            if ($offloaded > 0) {
                $message = sprintf(
                    _n(
                        '%s media file successfully offloaded to cloud storage.',
                        '%s media files successfully offloaded to cloud storage.',
                        $offloaded,
                        'wp-media-delivery'
                    ),
                    number_format_i18n($offloaded)
                );
                
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
            }
            
            if ($errors > 0) {
                $message = sprintf(
                    _n(
                        'Failed to offload %s media file. Check error logs for details.',
                        'Failed to offload %s media files. Check error logs for details.',
                        $errors,
                        'wp-media-delivery'
                    ),
                    number_format_i18n($errors)
                );
                
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($message) . '</p></div>';
            }
        }
        
        // For single offload result
        if (isset($_GET['advmo_offload_success'])) {
            echo '<div class="notice notice-success is-dismissible"><p>' . 
                esc_html__('Media file successfully offloaded to cloud storage.', 'wp-media-delivery') . 
                '</p></div>';
        }
        
        if (isset($_GET['advmo_offload_error'])) {
            echo '<div class="notice notice-error is-dismissible"><p>' . 
                esc_html__('Failed to offload media file. Check error logs for details.', 'wp-media-delivery') . 
                '</p></div>';
        }
        
        // For fetch back results
        if (isset($_GET['advmo_fetch_success'])) {
            echo '<div class="notice notice-success is-dismissible"><p>' . 
                esc_html__('Media file successfully fetched back from cloud storage.', 'wp-media-delivery') . 
                '</p></div>';
        }
        
        if (isset($_GET['advmo_fetch_error'])) {
            $error_type = isset($_GET['advmo_fetch_error']) ? $_GET['advmo_fetch_error'] : 'general';
            
            switch ($error_type) {
                case 'not_offloaded':
                    $error_message = __('The selected media file is not currently in cloud storage.', 'wp-media-delivery');
                    break;
                default:
                    $error_message = __('Failed to fetch media file. Check error logs for details.', 'wp-media-delivery');
                    break;
            }
            
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($error_message) . '</p></div>';
        }
    }

    /**
     * Get the provider label based on the provider type.
     *
     * @param string $provider_type The provider type.
     * @return string The provider label.
     */
    private function get_provider_label(string $provider_type): string
    {
        $labels = [
            's3' => 'Amazon S3',
            'r2' => 'Cloudflare R2',
            'do_spaces' => 'DigitalOcean Spaces',
            'wasabi' => 'Wasabi',
            'minio' => 'Min.io',
            'google_cloud' => 'Google Cloud Storage',
        ];

        return $labels[$provider_type] ?? ucfirst($provider_type);
    }

    /**
     * Get the provider icon based on the provider type.
     *
     * @param string $provider_type The provider type.
     * @return string The provider icon class.
     */
    private function get_provider_icon(string $provider_type): string
    {
        // Default to cloud icon
        return 'dashicons-cloud';
    }

    /**
     * Add styles for the storage status column.
     *
     * @return void
     */
    public function add_column_styles(): void
    {
        echo '<style>
            .column-advmo_storage_status {
                width: 120px;
            }
            .advmo-status {
                display: flex;
                align-items: center;
                background: #f0f0f1;
                border-radius: 4px;
                padding: 3px 8px;
                font-size: 12px;
                line-height: 1.4;
                white-space: nowrap;
                width: fit-content;
            }
            .advmo-status-cloud {
                background: #e6f6ff;
                color: #0078a7;
            }
            .advmo-status-local {
                background: #f0f0f1;
                color: #50575e;
            }
            .advmo-status .dashicons {
                font-size: 16px;
                width: 16px;
                height: 16px;
                margin-right: 5px;
            }
        </style>';
    }

    // Prevent cloning of the instance
    private function __clone() {}

    // Prevent unserializing of the instance
    public function __wakeup() {}
} 