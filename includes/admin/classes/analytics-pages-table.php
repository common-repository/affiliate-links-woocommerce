<?php

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class MXALFWPLinkAnalytics extends WP_List_Table
{

    /*
    * MXALFWPLinkAnalytics
    */
    public $linkData = NULL;

    public function __construct($args = [])
    {

        parent::__construct(
            [
                'singular' => 'mxalfwp_la_singular',
                'plural'   => 'mxalfwp_la_plural',
            ]
        );

        $this->linkData    = $args['data'];
    }

    public function prepare_items()
    {

        // pagination
        $perPage     = 20;
        $currentPage = $this->get_pagenum();

        if (1 < $currentPage) {
            $offset = $perPage * ($currentPage - 1);
        } else {
            $offset = 0;
        }

        // get data
        $linkId = isset($_GET['mxalfwp-link-id']) ? trim(sanitize_text_field($_GET['mxalfwp-link-id'])) : 0;

        $data = [];

        if ($this->linkData !== NULL) {
            $unserialize = maybe_unserialize($this->linkData);
            $data = $unserialize;
        }

        // set data
        $items = [];

        $dataPerPage = $data;

        if (count($data) > 10) {

            $dataPerPage = array_slice($data, $offset, $perPage);
        }

        foreach ($dataPerPage as $key => $value) {
            $tmp = [
                'link_id' => $linkId,
                'page'    => $key,
                'views'   => $value
            ];

            array_push($items, $tmp);
        }

        $count = count($data);

        $this->items = $items;

        // set comumn headers
        $columns  = $this->get_columns();

        $this->_column_headers = [
            $columns
        ];

        // Set the pagination.
        $this->set_pagination_args(
            [
                'total_items' => $count,
                'per_page'    => $perPage,
                'total_pages' => ceil($count / $perPage),
            ]
        );
    }

    public function get_columns()
    {

        return [
            'page'     => __('Visited Page', 'mxalfwp-domain'),
            'views'    => __('Views Number', 'mxalfwp-domain'),
            'actions'  => __('Actions', 'mxalfwp-domain'),
        ];
    }

    public function column_default($item, $columnName)
    {
        do_action("manage_mxalfwp_pages_custom_column", $columnName, $item);
    }

    public function column_page($item)
    {
        echo $item['page'];
    }

    public function column_views($item)
    {
        echo count($item['views']);
    }

    public function column_actions($item)
    {

        $url = admin_url('admin.php?page=mxalfwp-visited-page-details'); ?>

        <a href="<?php echo esc_url($url); ?>&mxalfwp-link-id=<?php echo $item['link_id']; ?>&mxalfwp-visited-page=<?php echo $item['page']; ?>">Details</a>
<?php
    }
}

if (!function_exists('mxalfwpAnalyticsPagesTableLayout')) {

    function mxalfwpAnalyticsPagesTableLayout($data)
    {

        $tableInstance = new MXALFWPLinkAnalytics($data);

        $tableInstance->prepare_items();

        echo '<form id="mxalfwp_analytics_pages_form" method="post">';
        $tableInstance->display();
        echo '</form>';
    }
}
