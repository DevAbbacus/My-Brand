<?php
namespace Joomunited\WPMF;

defined('ABSPATH') || die('No direct script access allowed!');

/**
 * Class JUMainQueue
 */
class JUMainQueue
{
    /**
     * Check queue enable
     *
     * @var boolean
     */
    public static $use_queue = false;
    /**
     * Plugin prefix
     *
     * @var string
     */
    public static $plugin_prefix = '';
    /**
     * Plugin domain
     *
     * @var string
     */
    public static $plugin_domain = '';
    /**
     * Plugin assets URL
     *
     * @var string
     */
    public static $assets_url = '';
    /**
     * Default options
     *
     * @var array
     */
    public static $default_options = array();
    /**
     * Queue retries
     *
     * @var integer
     */
    public static $retries = 3;
    /**
     * If debug is enabled or not
     *
     * @var boolean
     */
    public static $debug_enabled = false;

    /**
     * Status template
     *
     * @var array
     */
    public static $status_templates = array();
    /**
     * JUMainQueue constructor
     *
     * @param array $args Params
     *
     * @return void
     */
    public static function init($args = array())
    {
        self::$use_queue = $args['use_queue'];
        self::$plugin_prefix = $args['plugin_prefix'];
        self::$plugin_domain = $args['plugin_domain'];
        self::$default_options = $args['queue_options'];
        self::$status_templates = $args['status_templates'];
        self::$assets_url = $args['assets_url'];
        self::runUpgrades();
        // Enable logging if needed
        $options = self::getQueueOptions();
        if (!empty($options['mode_debug'])) {
            self::$debug_enabled = true;
        }

        add_action(
            'admin_init',
            function () {
                add_action('admin_footer', function () {
                    JUMainQueue::enqueueScript();
                }, 0);

                // Add menu bar
                $options = JUMainQueue::getQueueOptions();
                if (!empty($options['status_menu_bar'])) {
                    add_action(
                        'admin_bar_menu',
                        function ($wp_admin_bar) {
                            $stop = JUMainQueue::getStopStatus();
                            // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain,WordPress.WP.I18n.NonSingularStringLiteralText
                            $stop_button = (!empty($stop)) ? '<span class="dashicons dashicons-controls-play"></span><label>'. __('Start queue', JUMainQueue::$plugin_domain) .'</label>' : '<span class="dashicons dashicons-controls-pause"></span><label>'. __('Pause queue', JUMainQueue::$plugin_domain) .'</label>';
                            $args = array(
                                'id' => JUMainQueue::$plugin_prefix . '-topbar',
                                'title' => '<a href="#" class="ju-status-wrap"><span class="'. JUMainQueue::$plugin_prefix .'"></span><span class="'. JUMainQueue::$plugin_prefix .'-queue">0</span><div class="ju_queue_status"><ul><li class="'. JUMainQueue::$plugin_prefix .'_clear_queue"><span class="dashicons dashicons-remove"></span><label>'. __('Clear queue', JUMainQueue::$plugin_domain) .'</label></li><li class="'. JUMainQueue::$plugin_prefix .'_stop_queue">'. $stop_button .'</li></ul></div></a>',// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain,WordPress.WP.I18n.NonSingularStringLiteralText
                                'meta' => array(
                                    'classname' => 'ju-queue',
                                ),
                            );
                            $wp_admin_bar->add_node($args);
                        },
                        999
                    );

                    wp_register_style(JUMainQueue::$plugin_prefix . '-dummy-handle', false);
                    wp_enqueue_style(JUMainQueue::$plugin_prefix . '-dummy-handle');
                    wp_add_inline_style(
                        JUMainQueue::$plugin_prefix . '-dummy-handle',
                        '#wp-admin-bar-'. JUMainQueue::$plugin_prefix .'-topbar a {
                            color: #FFF !important;                    
                          }
                          #wp-admin-bar-'. JUMainQueue::$plugin_prefix .'-topbar span.'. JUMainQueue::$plugin_prefix .' {
                            width: 10px;
                            height: 10px;
                            border-radius: 5px;
                            background-color: #969696;
                            display: inline-block;
                            vertical-align: baseline;
                            margin-right: 6px;
                          }
                          #wp-admin-bar-'. JUMainQueue::$plugin_prefix .'-topbar span.'. JUMainQueue::$plugin_prefix .'-querying {
                            opacity: 0.6;
                          }
                          #wp-admin-bar-'. JUMainQueue::$plugin_prefix .'-topbar span.'. JUMainQueue::$plugin_prefix .'-green {
                            background-color: #4caf50;
                          }
                          #wp-admin-bar-'. JUMainQueue::$plugin_prefix .'-topbar span.'. JUMainQueue::$plugin_prefix .'-orange {
                            background-color: #ff9800;
                          }
                          #wp-admin-bar-'. JUMainQueue::$plugin_prefix .'-topbar span.'. JUMainQueue::$plugin_prefix .'-gray {
                            background-color: #969696 !important;
                          }
                          .ju-status-wrap {
                            position: relative;
                          }
                          .ju_queue_status {
                            position: absolute !important;
                            top: 100%;
                            left: -10px;
                            background: #32373c !important;
                            display: none;
                          }
                          .ju_queue_status li {
                            color: color: rgba(240, 245, 250, 0.7) !important;
                            width: 300px !important;
                            text-overflow: ellipsis !important;
                            overflow: hidden;
                            display: inline-block;
                            padding: 2px 10px !important;
                            box-sizing: border-box !important;
                            border-bottom: #474747 1px solid;
                          }
                          .ju-status-wrap:hover > .ju_queue_status{
                            display: block;
                          }
                          .'. JUMainQueue::$plugin_prefix .'_clear_queue .dashicons, .'. JUMainQueue::$plugin_prefix .'_stop_queue .dashicons {
                            font-family: dashicons !important;
                            vertical-align: middle;
                            font-size: 16px !important;
                            line-height: 18px !important;
                            margin-right: 5px !important;
                          }
                          .'. JUMainQueue::$plugin_prefix .'_clear_queue *, .'. JUMainQueue::$plugin_prefix .'_stop_queue * {
                            vertical-align: middle;
                            display: inline-block;
                          }
                          @-webkit-keyframes rotating /* Safari and Chrome */ {
                              from {
                                -webkit-transform: rotate(0deg);
                                -o-transform: rotate(0deg);
                                transform: rotate(0deg);
                              }
                              to {
                                -webkit-transform: rotate(360deg);
                                -o-transform: rotate(360deg);
                                transform: rotate(360deg);
                              }
                            }
                            @keyframes rotating {
                              from {
                                -ms-transform: rotate(0deg);
                                -moz-transform: rotate(0deg);
                                -webkit-transform: rotate(0deg);
                                -o-transform: rotate(0deg);
                                transform: rotate(0deg);
                              }
                              to {
                                -ms-transform: rotate(360deg);
                                -moz-transform: rotate(360deg);
                                -webkit-transform: rotate(360deg);
                                -o-transform: rotate(360deg);
                                transform: rotate(360deg);
                              }
                            }
                            .'. JUMainQueue::$plugin_prefix .'_clear_queue.queue_running .dashicons-remove {
                              -webkit-animation: rotating 0.2s linear infinite;
                              -moz-animation: rotating 0.2s linear infinite;
                              -ms-animation: rotating 0.2s linear infinite;
                              -o-animation: rotating 0.2s linear infinite;
                              animation: rotating 0.2s linear infinite;
                            }
                          '
                    );
                }
            }
        );

        self::initAjax();
    }

    /**
     * Get queue options
     *
     * @return array
     */
    public static function getQueueOptions()
    {
        $options = get_option(self::$plugin_prefix . '_queue_options');
        if (isset($options) && is_array($options)) {
            return array_merge(self::$default_options, $options);
        }

        return self::$default_options;
    }

    /**
     * Get stop status
     *
     * @return integer
     */
    public static function getStopStatus()
    {
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare('SELECT option_value FROM '. $wpdb->options .' WHERE option_name = %s LIMIT 1', self::$plugin_prefix . '_stop_queue'));
        if (is_object($row)) {
            $stop = (int)$row->option_value;
        } else {
            $stop = 0;
        }

        return $stop;
    }

    /**
     * Check queue exist
     *
     * @param string $value   Value
     * @param string $compare Compare
     *
     * @return array|object|void|null
     */
    public static function checkQueueExist($value = '', $compare = '=')
    {
        global $wpdb;
        $table = $wpdb->prefix . self::$plugin_prefix . '_queue';
        // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared -- Params has prepared
        switch ($compare) {
            case '=':
                $row = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $table . ' WHERE BINARY datas = BINARY %s ORDER BY date_added DESC', array($value)));
                break;
            case 'LIKE':
                $row = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $table . ' WHERE BINARY datas LIKE BINARY %s ORDER BY date_added DESC', array('%' . $value . '%')));
                break;
            default:
                $row = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $table . ' WHERE BINARY datas = BINARY %s ORDER BY date_added DESC', array($value)));
        }
        // phpcs:enable
        return $row;
    }

    /**
     * Add to the queue
     *
     * @param array $datas     Datas details
     * @param array $responses Responses details
     *
     * @return void
     */
    public static function addToQueue($datas = array(), $responses = array())
    {
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . self::$plugin_prefix . '_queue',
            array(
                'action' => $datas['action'],
                'datas' => json_encode($datas),
                'responses' => stripslashes(json_encode($responses)),
                'date_added' => round(microtime(true) * 1000),
                'date_done' => null,
                'status' => 0
            ),
            array(
                '%s',
                '%s',
                '%s',
                '%d',
                '%d',
                '%d'
            )
        );
    }

    /**
     * Proceed queue asynchronously
     *
     * @return void
     */
    public static function proceedQueueAsync()
    {
        global $wpdb;
        $stop = self::getStopStatus();
        // run if no stop
        if (empty($stop)) {
            $row = $wpdb->get_row($wpdb->prepare('SELECT option_value FROM '. $wpdb->options .' WHERE option_name = %s LIMIT 1', self::$plugin_prefix . '_queue_running'));
            if (is_object($row)) {
                $queue_running = (int)$row->option_value;
            } else {
                $queue_running = 0;
            }

            //$queue_running = get_option(self::$plugin_prefix . '_queue_running');
            $queue_length = self::getQueueLength();
            // Check if queue is currently running for less than 30 seconds
            if ($queue_length && $queue_running + 300 < time()) {
                delete_option(self::$plugin_prefix . '_queue_id_running');
            }

            if ($queue_length && $queue_running + 40 < time()) {
                update_option(self::$plugin_prefix . '_queue_running', time());
                switch ((int)self::$default_options['tasks_speed']) {
                    case 75:
                        sleep(4);
                        break;
                    case 25:
                        sleep(10);
                        break;
                }
                $result = wp_remote_head(admin_url('admin-ajax.php').'?action='. self::$plugin_prefix .'_proceed&'. self::$plugin_prefix .'_token='.get_option(self::$plugin_prefix . '_token') . '&speed=' . self::$default_options['tasks_speed'], array('sslverify' => false));
                self::log('Info : Proceed queue asynchronously ' . (is_wp_error($result)?$result->get_error_message():'success'));
            } elseif ($queue_length) {
                self::log('Info : Queue already running (queue_running: ' . $queue_running .', time: ' . time());
            }
        }
    }

    /**
     * Update responses
     *
     * @param integer $id        Item ID
     * @param array   $responses Responses
     *
     * @return void
     */
    public static function updateResponses($id, $responses = array())
    {
        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . self::$plugin_prefix . '_queue',
            array(
                'responses' => json_encode($responses)
            ),
            array('id' => $id),
            array('%s'),
            array('%d')
        );
    }

    /**
     * Update datas
     *
     * @param integer $id    Item ID
     * @param array   $datas Datas
     *
     * @return void
     */
    public static function updateDatas($id, $datas = array())
    {
        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . self::$plugin_prefix . '_queue',
            array(
                'datas' => json_encode($datas)
            ),
            array('id' => $id),
            array('%s'),
            array('%d')
        );
    }

    /**
     * Roll back queue
     *
     * @param integer $id    Queue ID
     * @param array   $datas Queue datas
     *
     * @return void
     */
    public static function rollBackQueue($id, $datas = array())
    {
        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . self::$plugin_prefix . '_queue',
            array(
                'datas' => json_encode($datas),
                'retries' => 0,
                'status' => 0
            ),
            array('id' => $id),
            array('%s', '%d', '%d'),
            array('%d')
        );
    }

    /**
     * Delete a queue by id
     *
     * @param integer $id ID of queue
     *
     * @return void
     */
    public static function deleteQueue($id)
    {
        global $wpdb;
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Params has prepared
        $wpdb->query($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . self::$plugin_prefix . '_queue WHERE id = %d', (int)$id));
    }

    /**
     * Proceed elements in the queue
     *
     * @return integer
     */
    private static function proceedQueue()
    {
        self::log('Info : Proceed queue synchronously');
        global $wpdb;
        $done = 0;
        $max_execution_time = self::getMaximumExecutionTime();
        self::log('Info : Max execution time is ' . $max_execution_time);
        // Update last queue time value
        update_option(self::$plugin_prefix . '_queue_running', time());
        // Retrieve all elements in the queue
        do {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Params has prepared
            $elements = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . self::$plugin_prefix . '_queue WHERE status=0 ORDER BY date_added ASC LIMIT 20');
            foreach ($elements as $element) {
                // check if a queue is running
                $row = $wpdb->get_row($wpdb->prepare('SELECT option_value FROM '. $wpdb->options .' WHERE option_name = %s LIMIT 1', self::$plugin_prefix . '_queue_id_running'));
                if (is_object($row)) {
                    $queue_id_running = (int)$row->option_value;
                } else {
                    $queue_id_running = 0;
                }

                if (!empty($queue_id_running) && (int)$queue_id_running === $element->id) {
                    return $done;
                }

                update_option(self::$plugin_prefix . '_queue_id_running', $element->id);
                set_time_limit(0);
                // Actually move the file
                $datas = json_decode(stripslashes($element->datas), true);
                $retries = (int) $element->retries + 1;
                if ($retries > self::$retries) {
                    $result = true;
                    $wpdb->update(
                        $wpdb->prefix . self::$plugin_prefix . '_queue',
                        array(
                            'date_done' => round(microtime(true) * 1000),
                            'retries' => (int)$retries,
                            'status' => 2
                        ),
                        array('id' => $element->id),
                        array('%d', '%d', '%d'),
                        array('%d')
                    );
                } else {
                    $result = apply_filters($datas['action'], -1, $datas, $element->id);
                    if ($result) {
                        $wpdb->update(
                            $wpdb->prefix . self::$plugin_prefix . '_queue',
                            array(
                                'date_done' => round(microtime(true) * 1000),
                                'retries' => (int)$retries,
                                'status' => 1
                            ),
                            array('id' => $element->id),
                            array('%d', '%d', '%d'),
                            array('%d')
                        );
                    } else {
                        $wpdb->update(
                            $wpdb->prefix . self::$plugin_prefix . '_queue',
                            array(
                                'retries' => (int)$retries
                            ),
                            array('id' => $element->id),
                            array('%d'),
                            array('%d')
                        );
                    }
                }

                // Update last queue time value
                update_option(self::$plugin_prefix . '_queue_running', time());
                delete_option(self::$plugin_prefix . '_queue_id_running');
                if ($result) {
                    $done++;
                }
            }
            $current_time = microtime(true);
        } while ($elements && $current_time < $max_execution_time);

        // Remove last week elements
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Params has prepared
        $wpdb->query('DELETE FROM ' . $wpdb->prefix . self::$plugin_prefix . '_queue WHERE date_done < (UNIX_TIMESTAMP()*1000 - 24 * 60 * 60 * 1000)');

        self::log('Info : Synchronous queue finished');

        return $done;
    }

    /**
     * Retrieve microtime at which the script should stop
     *
     * @return float
     */
    private static function getMaximumExecutionTime()
    {

        $max_execution_time = (int)ini_get('max_execution_time');

        if (!$max_execution_time) {
            $max_execution_time = 30;
        } elseif ($max_execution_time > 60) {
            $max_execution_time = 60;
        }

        if (isset($_SERVER['REQUEST_TIME_FLOAT'])) {
            $time = $_SERVER['REQUEST_TIME_FLOAT'];
        } else {
            // Consider script started 3 seconds ago
            $time = microtime(true) - 10 * 1000 * 1000;
        }

        // We should stop the script 3 seconds before it reach max execution limit
        return $time + $max_execution_time * 1000 * 1000 - 10 * 1000 * 1000;
    }

    /**
     * Get number of items in the queue waiting
     *
     * @return integer
     */
    public static function getQueueLength()
    {
        global $wpdb;
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Params has prepared
        return (int)$wpdb->get_var('SELECT COUNT(*) FROM ' . $wpdb->prefix . self::$plugin_prefix . '_queue WHERE status=0');
    }

    /**
     * Get status list
     *
     * @return array|object|null
     */
    public static function getStatus()
    {
        global $wpdb;
        // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared -- Params has prepared
        $results = $wpdb->get_results('SELECT COUNT(action) as count, action FROM ' . $wpdb->prefix . self::$plugin_prefix . '_queue WHERE status=0 GROUP BY action');
        return $results;
    }

    /**
     * Enqueue background task script
     *
     * @return void
     */
    public static function enqueueScript()
    {
        global $wpdb;
        $queue_length = (int)$wpdb->get_var('SELECT COUNT(*) FROM ' . $wpdb->prefix . self::$plugin_prefix . '_queue');
        if ($queue_length > 0 || self::$use_queue) {
            wp_enqueue_script(self::$plugin_prefix . '_queue', self::$assets_url, array('jquery'), null, true);
            wp_localize_script(self::$plugin_prefix . '_queue', self::$plugin_prefix . '_object_queue', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'prefix' => self::$plugin_prefix,
                'stop_label' => esc_html__('Pause queue', self::$plugin_domain),// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain,WordPress.WP.I18n.NonSingularStringLiteralText
                'start_label' => esc_html__('Start queue', self::$plugin_domain),// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain,WordPress.WP.I18n.NonSingularStringLiteralText
            ));
        }
    }

    /**
     * Ajax request
     *
     * @return void
     */
    public static function initAjax()
    {
        add_action('wp_ajax_'. self::$plugin_prefix .'_clear_queue', function () {
            global $wpdb;
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Params has prepared
            $wpdb->query('DELETE FROM ' . $wpdb->prefix . JUMainQueue::$plugin_prefix . '_queue');
            wp_send_json(array('status' => true));
        });

        add_action('wp_ajax_'. self::$plugin_prefix .'_stop_queue', function () {
            global $wpdb;
            $row = $wpdb->get_row($wpdb->prepare('SELECT option_value FROM '. $wpdb->options .' WHERE option_name = %s LIMIT 1', JUMainQueue::$plugin_prefix . '_stop_queue'));
            if (is_object($row)) {
                $stop = ((int)$row->option_value === 0) ? 1 : 0;
            } else {
                $stop = 1;
            }

            update_option(JUMainQueue::$plugin_prefix . '_stop_queue', $stop);
        });

        add_action('wp_ajax_'. self::$plugin_prefix .'_queue', function () {
            $stop = JUMainQueue::getStopStatus();
            $queue_length = JUMainQueue::getQueueLength();
            $statuss = JUMainQueue::getStatus();
            $status_html = '<ul class="ju_queue_status_res">';
            $status_templates = JUMainQueue::$status_templates;
            foreach ($statuss as $status) {
                if (isset($status_templates[$status->action])) {
                    $status_html .= '<li>'. str_replace('%d', $status->count, $status_templates[$status->action]) .'</li>';
                }
            }
            $status_html .= '</ul>';
            echo json_encode(array(
                'queue_length' => $queue_length,
                'status_html' => $status_html,
                'stop' => $stop,
                'title' => sprintf(__('%s actions queued', JUMainQueue::$plugin_domain), $queue_length) // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain,WordPress.WP.I18n.NonSingularStringLiteralText
            ));

            JUMainQueue::proceedQueueAsync();

            exit(0);
        });

        add_action('wp_ajax_nopriv_'. self::$plugin_prefix .'_proceed', function () {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No action and a custom token is used
            if (!isset($_REQUEST[JUMainQueue::$plugin_prefix . '_token']) || $_REQUEST[JUMainQueue::$plugin_prefix . '_token'] !== get_option(JUMainQueue::$plugin_prefix . '_token')) {
                JUMainQueue::log('Info : Proceed queue ajax stopped, wrong token');
                exit(0);
            }

            JUMainQueue::log('Info : Proceed queue ajax');

            if (ob_get_length()) {
                ob_end_clean();
            }
            header('Connection: close\r\n');
            header('Content-Encoding: none\r\n');
            ignore_user_abort(true);
            header('Content-Length: 0');
            ob_end_flush();
            flush();
            if (ob_get_length()) {
                ob_end_clean();
            }

            JUMainQueue::proceedQueue();
            $options = JUMainQueue::$default_options;
            switch ((int)$options['tasks_speed']) {
                case 100:
                    if (JUMainQueue::getQueueLength()) {
                        JUMainQueue::proceedQueueAsync();
                    }
                    break;
                case 75:
                    if (JUMainQueue::getQueueLength()) {
                        JUMainQueue::proceedQueueAsync();
                    }
                    break;
            }
        });
    }

    /**
     * Check if the plugin need to run an update of db or options
     *
     * @return void
     */
    public static function runUpgrades()
    {
        $checkDb = get_option(self::$plugin_prefix . '_queue', false);
        // Up to date, nothing to do
        if ($checkDb) {
            return;
        }

        global $wpdb;
        if (!$checkDb) {
            add_option(self::$plugin_prefix . '_token', self::getRandomString());
            // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared -- Params has prepared
            $wpdb->query('CREATE TABLE `'.$wpdb->prefix. self::$plugin_prefix . '_queue` (
                      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                      `datas` LONGTEXT CHARACTER SET ' . $wpdb->charset . ' COLLATE ' . $wpdb->collate . ' NOT NULL,
                      `action` LONGTEXT CHARACTER SET ' . $wpdb->charset . ' COLLATE ' . $wpdb->collate . ' NOT NULL,
                      `responses` LONGTEXT CHARACTER SET ' . $wpdb->charset . ' COLLATE ' . $wpdb->collate . ' DEFAULT NULL,
                      `date_added` VARCHAR(14) CHARACTER SET ' . $wpdb->charset . ' COLLATE ' . $wpdb->collate . ' NOT NULL,
                      `date_done` VARCHAR(14) CHARACTER SET ' . $wpdb->charset . ' COLLATE ' . $wpdb->collate . ' DEFAULT NULL,
                      `retries` int(11) UNSIGNED NOT NULL DEFAULT 0,
                      `status` tinyint(1) UNSIGNED NOT NULL,
                      PRIMARY KEY (`id`)
                    ) CHARACTER SET '.$wpdb->charset.' COLLATE ' . $wpdb->collate . ' ENGINE=InnoDB');
            // phpcs:enable
        }

        update_option(self::$plugin_prefix . '_queue', 1);
    }

    /**
     * Generate a random string
     *
     * @param integer $length Length of the returned string
     *
     * @author https://stackoverflow.com/questions/4356289/php-random-string-generator#answer-4356295
     *
     * @return string
     */
    public static function getRandomString($length = 20)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * Log into a debug file
     *
     * @param string $msg Message
     *
     * @return void
     */
    public static function log($msg = '')
    {
        // Do nothing if not enabled
        if (!self::$debug_enabled) {
            return;
        }

        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Log if enable debug
        error_log($msg);
    }

    /**
     * Update queue meta
     *
     * @param integer $attachment_id Attachment ID
     * @param integer $queue_id      Queue ID
     *
     * @return void
     */
    public static function updateQueuePostMeta($attachment_id, $queue_id)
    {
        $queue_meta = get_post_meta($attachment_id, 'wpmf_sync_queue', true);
        if (!empty($queue_meta) && is_array($queue_meta)) {
            $queue_ids = array_merge($queue_meta, array($queue_id));
        } else {
            $queue_ids = array((int)$queue_id);
        }
        update_post_meta($attachment_id, 'wpmf_sync_queue', array_unique($queue_ids));
    }

    /**
     * Update queue meta
     *
     * @param integer $term_id  Term ID
     * @param integer $queue_id Queue ID
     *
     * @return void
     */
    public static function updateQueueTermMeta($term_id, $queue_id)
    {
        $queue_meta = get_term_meta($term_id, 'wpmf_sync_queue', true);
        if (!empty($queue_meta) && is_array($queue_meta)) {
            $queue_ids = array_merge($queue_meta, array($queue_id));
        } else {
            $queue_ids = array((int)$queue_id);
        }
        update_term_meta($term_id, 'wpmf_sync_queue', array_unique($queue_ids));
    }
}
