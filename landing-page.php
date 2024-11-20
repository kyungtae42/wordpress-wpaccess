<?php
function grid_latest_posts_shortcode() {
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => 4,
        'paged' => 1    ,
        'category_name' => 'image-post',
        'meta_key' => 'post_views_count',
        'orderby' => 'meta_value_num'
    );
    $query = new WP_Query($args);
    $output = '<div class="grid-posts-container" style="display: grid; grid-template-columns: repeat(4, minmax(200px, 1fr)); gap: 20px;">';
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $output .= '<div class="grid-post-item" style="border: 1px solid #ddd; padding: 10px; text-align: center;">';
            $output .= '<a href="' . get_permalink() . '" style="text-decoration: none; color: inherit;">';
            // 썸네일 추가
            if (has_post_thumbnail()) {
                $output .= get_the_post_thumbnail(get_the_ID(), 'medium', array('style' => 'width: 100%; height: auto;'));
            }
            $output .= '</a></div>';
        }
    } else {
        $output .= '<p>Page Not Found</p>';
    }
    $output .= '</div>';
    wp_reset_postdata(); // 쿼리 리셋
    return $output;
}
add_shortcode('latest_grid_posts', 'grid_latest_posts_shortcode');
?>