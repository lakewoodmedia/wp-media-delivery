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
        add_filter('manage_media_columns', [$this, 'add_media_columns']);
        add_action('manage_media_custom_column', [$this, 'display_media_column_content'], 10, 2);
        add_action('admin_head', [$this, 'add_column_styles']);
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