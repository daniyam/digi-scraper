<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://www.beyranplugins.ir/
 * @since      1.0.0
 *
 * @package    digi-scraper
 * @subpackage digi-scraper/admin/background-process
 */

/**
 * The core plugin class.
 *
 * This is used to create and execute task in wordpress background based Queuing system
 *
 *
 * @since      1.0.0
 * @package    digi-scraper
 * @subpackage digi-scraper/admin/background-process
 * @author     rahim beiranvand <rbkhoram701@gmail.com>
 */

class Digi_Background_process extends WP_Background_Process {

    /*Calling essential functions for scraping and import product to woocommerce*/
    use Digi_product_parser;

    /*Calling essential functions for import product to woocommerce*/
    use Digi_product_import;

    /**
     * @var string
     */
    protected $action = 'digi_backend_process';

    /**
     * Task
     *
     * Override this method to perform any actions required on each
     * queue item. Return the modified item for further processing
     * in the next pass through. Or, return false to remove the
     * item from the queue.
     *
     * @param mixed $item Queue item to iterate over
     *
     * @return mixed
     */
    protected function task( $item ) {

        $product_item = $this->scraping_product( $item );
        $this->really_long_running_task();
        $this->insert_product( $product_item );
        return false;
    }

    /**
     * Lock process
     *
     * Lock the process so that multiple instances can't run simultaneously.
     * Override if applicable, but the duration should be greater than that
     * defined in the time_exceeded() method.
     */
    protected function lock_process() {
        $this->start_time = time(); // Set start time of current process.

        $lock_duration = ( property_exists( $this, 'queue_lock_time' ) ) ? $this->queue_lock_time : 60; // 1 minute
        $lock_duration = apply_filters( $this->identifier . '_queue_lock_time', $lock_duration );
        $lock_duration = 90;

        set_site_transient( $this->identifier . '_process_lock', microtime(), $lock_duration );
    }

    /**
     * override handle Handle
     *
     * Pass each queue item to the task handler, while remaining
     * within server memory and time limit constraints.
     */
    protected function handle()
    {
        // Check to see if sync is supposed to be cleared
        $clear = get_option('clear_sync');

        // If we do, manually clear the options from the database
        if ($clear) {
            // Get current batch and delete it
            $batch = $this->get_batch();
            $this->delete($batch->key);

            // Clear out transient that locks the process
            $this->unlock_process();

            // Call the complete method, which will tie things up
            $this->complete();

            // Remove the "clear" flag we had manually set
            delete_option('clear_sync');

            // Ensure we don't actually handle anything
            return;
        }

        // parent::handle();

        $this->lock_process();



        do {
            $batch = $this->get_batch();
            $step_d = 0;
            foreach ( $batch->data as $key => $value ) {
                $task = $this->task( $value );
                $step_d++;
                if ( false !== $task ) {
                    $batch->data[ $key ] = $task;
                } else {
                    unset( $batch->data[ $key ] );
                }


                if ( $this->time_exceeded() || $this->memory_exceeded() ) {
                    break;
                }
            }

            // Update or delete current batch.
            if ( ! empty( $batch->data ) ) {
                $this->update( $batch->key, $batch->data );
            } else {
                $this->delete( $batch->key );
            }
        } while ( ! $this->time_exceeded() && ! $this->memory_exceeded() && ! $this->is_queue_empty() );

        if ( ! $this->is_queue_empty() ) {
            $this->clear_scheduled_event();
        }

        $this->unlock_process();

        // Start next batch or complete process.
        if ( ! $this->is_queue_empty() ) {
            $this->dispatch();
        } else {
            $this->complete();
        }

        wp_die();
    }
    /**
     * Schedule cron healthcheck
     *
     * @access public
     * @param mixed $schedules Schedules.
     * @return mixed
     */
    public function schedule_cron_healthcheck( $schedules ) {
        $interval = apply_filters( $this->identifier . '_cron_interval', 2 );

        if ( property_exists( $this, 'cron_interval' ) ) {
            $interval = apply_filters( $this->identifier . '_cron_interval', $this->cron_interval );
        }

        // Adds every 5 minutes to the existing schedules.
        $schedules[ $this->identifier . '_cron_interval' ] = array(
            'interval' => MINUTE_IN_SECONDS * $interval,
            'display'  => sprintf( __( 'Every %d minutes', 'woocommerce' ), $interval ),
        );

        return $schedules;
    }

    /**
     * Cancel
     *
     * Override if applicable, but ensure that the below actions are
     * performed, or, call parent::cancel().
     */

    public function cancel_proce() {

        // Unschedule the cron healthcheck.
        error_log('test1');

        if($this->is_queue_empty())
        {
            error_log('test empty');
            $this->log( 'صف خالی است' );
        }
        else
        {
            error_log('test cancel');
            add_option('clear_sync' , 1);
        }

    }

    /**
     * Complete
     *
     * Override if applicable, but ensure that the below actions are
     * performed, or, call parent::complete().
     */
    protected function complete() {
        parent::complete();

        $scrap_detailes = get_option('progress_status_details', true);
        $scrap_name = $scrap_detailes['scrap-name'];

        //setup our default option values for import setting
        $digi_import_options=array(
            "scrap-name" => '',
            "running" => 0,
            "number_of_products_in_the_queue" => 0,
            "number_of_products_imported" => 0,
            "rate_of_progress" => 0,
            "product_name_being_imported" => '',
        );
        update_option('progress_status_details' , $digi_import_options);

        /*current task update*/
        if(get_option($scrap_name))
        {
            $current_scrap = get_option($scrap_name , true);
            $num_of_imported = $current_scrap['number-of-product-imported'];
            $num_of_queue = $current_scrap['number-of-product-in-queue'];
            if($num_of_imported == $num_of_queue)
            {
                $current_scrap['scrap-completed'] = 1;
            }
            $current_scrap['running'] = 0;
            $current_scrap['last-execute-time'] = date("Y-m-d");
            update_option($scrap_name , $current_scrap);
        }

        // Show notice to user or perform some other arbitrary task...
    }

}