<?php
/*
* Plugin Name:          Daily Devotions Slider
* Description:          It provides an easy way that allows church to post daily devotions, and display scheduled daily devotions in a slider format
* Version:              1.0
* Author:               Joseph Justine
* Author URI:           https://josandeep.wordpress.com/
* Requires at least:    5.6
* Requires PHP:         7.2
* License:              GPL v2 or later
* License URI:          https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain:          daily-devotions
*/

if (!defined('ABSPATH')) exit;

function register_devotion_post_type() {
    $labels = array(
        'name'               => _x('Devotions', 'post type general name'),
        'singular_name'      => _x('Devotion', 'post type singular name'),
        'menu_name'          => _x('Devotions', 'admin menu'),
        'name_admin_bar'     => _x('Devotion', 'add new on admin bar'),
        'add_new'            => _x('Add New', 'devotion'),
        'add_new_item'       => __('Add New Devotion'),
        'new_item'           => __('New Devotion'),
        'edit_item'          => __('Edit Devotion'),
        'view_item'          => __('View Devotion'),
        'all_items'          => __('All Devotions'),
        'search_items'       => __('Search Devotions'),
        'parent_item_colon'  => __('Parent Devotions:'),
        'not_found'          => __('No devotions found.'),
        'not_found_in_trash' => __('No devotions found in Trash.')
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'devotions'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5, // Below Posts
        'menu_icon'          => 'dashicons-book-alt', // WordPress dashicon
        'supports'           => array('title', 'editor', 'thumbnail'),
        //'show_in_rest'       => true // Enable Gutenberg editor
    );

    register_post_type('devotion', $args);
    
    // Register category taxonomy
    register_taxonomy(
        'devotion_category',
        'devotion',
        array(
            'label' => __('Categories'),
            'rewrite' => array('slug' => 'devotion-category'),
            'hierarchical' => true,
            'show_in_rest' => true
        )
    );
}
add_action('init', 'register_devotion_post_type');

// Register shortcode
add_shortcode('devotion_slider', 'devotion_slider_shortcode');

function devotion_slider_shortcode($atts) {
    $atts = shortcode_atts(array(
        'autoplay' => false,
        'interval' => 5000,
        'show_nav' => true,
        'show_dots' => true,
        'category' => '',
        'mode' => 'all' // 'all' or 'scheduled'
    ), $atts);

    // Query arguments
    $args = array(
        'post_type' => 'devotion',
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC'
    );
    
    if (!empty($atts['category'])) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'devotion_category',
                'field' => 'slug',
                'terms' => sanitize_text_field($atts['category'])
            )
        );
    }

    $devotions = new WP_Query($args);
    
    if (!$devotions->have_posts()) return '<p>No devotions found.</p>';
    
    ob_start(); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sliders = document.querySelectorAll('.devotions-slider');
            
            sliders.forEach(slider => {
                const wrapper = slider.querySelector('.slider-wrapper');
                const slides = slider.querySelectorAll('.devotion-slide:not(.no-devotion)');
                const prevBtn = slider.querySelector('.slider-prev');
                const nextBtn = slider.querySelector('.slider-next');
                const dotsContainer = slider.querySelector('.slider-dots');
                let currentIndex = 0;
                let autoplayInterval;
                let touchStartX = 0;
                let touchEndX = 0;
                
                // Only initialize if there are slides
                if (slides.length > 0) {
                    initSlider();
                }
                
                function initSlider() {
                    // Set initial active slide
                    slides[currentIndex].classList.add('active');
                    
                    // Create dots if needed
                    if (dotsContainer) {
                        slides.forEach((_, index) => {
                            const dot = document.createElement('button');
                            dot.classList.add('slider-dot');
                            dot.setAttribute('aria-label', `Go to slide ${index + 1}`);
                            dot.addEventListener('click', () => goToSlide(index));
                            dotsContainer.appendChild(dot);
                        });
                        updateDots();
                    }
                    
                    // Handle autoplay
                    if (slider.dataset.autoplay === 'true') {
                        startAutoplay();
                        
                        // Pause on hover
                        slider.addEventListener('mouseenter', pauseAutoplay);
                        slider.addEventListener('mouseleave', startAutoplay);
                    }
                    
                    // Touch events
                    wrapper.addEventListener('touchstart', handleTouchStart, { passive: true });
                    wrapper.addEventListener('touchend', handleTouchEnd, { passive: true });
                }
                
                // [Keep all other functions from previous slider.js implementation]
                // nextSlide(), prevSlide(), goToSlide(), updateDots(), etc.
                // ...
            });
        });
    </script>
    <div class="devotions-slider" 
         data-autoplay="<?php echo $atts['autoplay'] ? 'true' : 'false'; ?>"
         data-interval="<?php echo esc_attr($atts['interval']); ?>"
         data-mode="<?php echo esc_attr($atts['mode']); ?>">
        
        <div class="slider-wrapper">
            <?php 
            $today = current_time('timestamp');
            $has_scheduled = false;
            
            while ($devotions->have_posts()) : $devotions->the_post();
                $post_id = get_the_ID();
                $schedule_type = get_post_meta($post_id, 'schedule_type', true);
                $schedule_value = get_post_meta($post_id, 'schedule_value', true);
                
                // Check if devotion should be shown based on schedule
                $show_devotion = true;
                if ($atts['mode'] === 'scheduled') {
                    $show_devotion = false;
                    switch ($schedule_type) {
                        case 'weekly':
                            if (date('N', $today) == $schedule_value) $show_devotion = true;
                            break;
                        case 'monthly':
                            if (date('j', $today) == $schedule_value) $show_devotion = true;
                            break;
                        case 'yearly':
                            list($month, $day) = explode('-', $schedule_value);
                            if (date('m-d', $today) == $month.'-'.$day) $show_devotion = true;
                            break;
                    }
                }
                
                if ($show_devotion) : 
                    $has_scheduled = true;
                ?>
                <div class="devotion-slide" data-schedule-type="<?php echo esc_attr($schedule_type); ?>">
                    <div class="schedule-badge">
                        <?php echo get_schedule_badge($schedule_type, $schedule_value); ?>
                    </div>
                    <h3><?php the_title(); ?></h3>
                    <div class="bible-verse">
                        <?php echo esc_html(get_post_meta($post_id, 'bible_verse', true)); ?>
                    </div>
                    <div class="description"><?php the_content(); ?></div>
                    <div class="author">
                        Author: <?php echo esc_html(get_post_meta($post_id, 'author', true)); ?>
                    </div>
                </div>
                <?php endif; ?>
            <?php endwhile; ?>
            
            <?php if (!$has_scheduled && $atts['mode'] === 'scheduled') : ?>
                <div class="devotion-slide no-devotion">
                    <p>No devotion scheduled for today.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($atts['show_nav'] && $has_scheduled) : ?>
            <button class="slider-prev" aria-label="Previous devotion">←</button>
            <button class="slider-next" aria-label="Next devotion">→</button>
        <?php endif; ?>
        
        <?php if ($atts['show_dots'] && $has_scheduled) : ?>
            <div class="slider-dots"></div>
        <?php endif; ?>
    </div>
    
    <?php
    wp_reset_postdata();
    
    // Enqueue assets
    wp_enqueue_style('devotion-slider-style', 
        plugins_url('css/style.css', __FILE__),
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'css/style.css')
    );
    
    wp_enqueue_script('devotion-slider-script',
        plugins_url('js/slider.js', __FILE__),
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'js/slider.js'),
        true
    );
    
    return ob_get_clean();
}

// Helper function to display schedule badge
function get_schedule_badge($type, $value) {
    if (empty($type)) return '';
    
    switch ($type) {
        case 'weekly':
            $days = array('1' => 'Monday', '2' => 'Tuesday', '3' => 'Wednesday', 
                         '4' => 'Thursday', '5' => 'Friday', '6' => 'Saturday', '7' => 'Sunday');
            return '<span class="badge weekly">Every '.$days[$value].'</span>';
        case 'monthly':
            return '<span class="badge monthly">Day '.$value.' of month</span>';
        case 'yearly':
            list($month, $day) = explode('-', $value);
            return '<span class="badge yearly">'.date('F j', strtotime("$month/$day/2000")).'</span>';
        default:
            return '<span class="badge">One-time</span>';
    }
}

// [Keep all the previous functions for post type, meta boxes, etc. from the original version]
// ... register_devotion_post_type(), add_devotion_meta_boxes(), etc. ...

// Add schedule meta box
function add_schedule_meta_box() {
    add_meta_box(
        'devotion_schedule',
        'Schedule Settings',
        'render_schedule_meta_box',
        'devotion',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_schedule_meta_box');

function render_schedule_meta_box($post) {
    wp_nonce_field('save_schedule_settings', 'schedule_nonce');
    
    $schedule_type = get_post_meta($post->ID, 'schedule_type', true);
    $schedule_value = get_post_meta($post->ID, 'schedule_value', true);
    ?>
    
    <div class="schedule-type">
        <label for="schedule_type">Schedule Type:</label>
        <select name="schedule_type" id="schedule_type">
            <option value="">One-time devotion</option>
            <option value="weekly" <?php selected($schedule_type, 'weekly'); ?>>Weekly</option>
            <option value="monthly" <?php selected($schedule_type, 'monthly'); ?>>Monthly</option>
            <option value="yearly" <?php selected($schedule_type, 'yearly'); ?>>Yearly</option>
        </select>
    </div>
    
    <div id="weekly_fields" class="schedule-fields" style="display:<?php echo ($schedule_type == 'weekly') ? 'block' : 'none'; ?>;">
        <label>Day of Week:</label>
        <select name="schedule_weekly_day">
            <option value="1" <?php selected($schedule_value, '1'); ?>>Monday</option>
            <option value="2" <?php selected($schedule_value, '2'); ?>>Tuesday</option>
            <option value="3" <?php selected($schedule_value, '3'); ?>>Wednesday</option>
            <option value="4" <?php selected($schedule_value, '4'); ?>>Thursday</option>
            <option value="5" <?php selected($schedule_value, '5'); ?>>Friday</option>
            <option value="6" <?php selected($schedule_value, '6'); ?>>Saturday</option>
            <option value="7" <?php selected($schedule_value, '7'); ?>>Sunday</option>
        </select>
    </div>
    
    <div id="monthly_fields" class="schedule-fields" style="display:<?php echo ($schedule_type == 'monthly') ? 'block' : 'none'; ?>;">
        <label>Day of Month (1-31):</label>
        <select name="schedule_monthly_day">
            <?php for ($i = 1; $i <= 31; $i++) : ?>
                <option value="<?php echo $i; ?>" <?php selected($schedule_value, $i); ?>><?php echo $i; ?></option>
            <?php endfor; ?>
        </select>
    </div>
    
    <div id="yearly_fields" class="schedule-fields" style="display:<?php echo ($schedule_type == 'yearly') ? 'block' : 'none'; ?>;">
        <label>Date:</label>
        <select name="schedule_yearly_month">
            <?php for ($m = 1; $m <= 12; $m++) : ?>
                <option value="<?php echo str_pad($m, 2, '0', STR_PAD_LEFT); ?>" 
                    <?php selected(explode('-', $schedule_value)[0], str_pad($m, 2, '0', STR_PAD_LEFT)); ?>>
                    <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                </option>
            <?php endfor; ?>
        </select>
        
        <select name="schedule_yearly_day">
            <?php for ($d = 1; $d <= 31; $d++) : ?>
                <option value="<?php echo str_pad($d, 2, '0', STR_PAD_LEFT); ?>" 
                    <?php selected(explode('-', $schedule_value)[1], str_pad($d, 2, '0', STR_PAD_LEFT)); ?>>
                    <?php echo $d; ?>
                </option>
            <?php endfor; ?>
        </select>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        $('#schedule_type').change(function() {
            $('.schedule-fields').hide();
            $('#' + $(this).val() + '_fields').show();
        });
    });
    </script>
    <?php
}

function save_schedule_meta($post_id) {
    if (!isset($_POST['schedule_nonce']) || 
        !wp_verify_nonce($_POST['schedule_nonce'], 'save_schedule_settings')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    
    // Save schedule type
    if (isset($_POST['schedule_type'])) {
        update_post_meta($post_id, 'schedule_type', sanitize_text_field($_POST['schedule_type']));
    }
    
    // Save schedule value based on type
    if (!empty($_POST['schedule_type'])) {
        $value = '';
        switch ($_POST['schedule_type']) {
            case 'weekly':
                $value = sanitize_text_field($_POST['schedule_weekly_day']);
                break;
            case 'monthly':
                $value = sanitize_text_field($_POST['schedule_monthly_day']);
                break;
            case 'yearly':
                $month = sanitize_text_field($_POST['schedule_yearly_month']);
                $day = sanitize_text_field($_POST['schedule_yearly_day']);
                $value = $month.'-'.$day;
                break;
        }
        update_post_meta($post_id, 'schedule_value', $value);
    }
}
add_action('save_post_devotion', 'save_schedule_meta');