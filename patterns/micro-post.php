<?php
/**
 * Title: Micro Post
 * Slug: kiwi-theme/micro-post
 * Categories: text
 * Description: A short-form post pattern for Twitter-like content
 */
?>

<!-- wp:group {"className":"feed-item micro-post","style":{"spacing":{"padding":"1.25rem"},"border":{"radius":"12px","left":{"color":"var:preset|color|kiwi-secondary","width":"3px"}},"color":{"background":"var:preset|color|kiwi-neutral-100"}}} -->
<div class="wp-block-group feed-item micro-post">
    
    <!-- wp:group {"className":"post-meta","style":{"spacing":{"margin":{"bottom":"var:preset|spacing|md"}}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"left"}} -->
    <div class="wp-block-group post-meta">
        <!-- wp:html -->
        <span class="post-type-indicator post-type-micro" aria-hidden="true"></span>
        <!-- /wp:html -->
        
        <!-- wp:post-date {"className":"post-date","style":{"typography":{"fontSize":"var:preset|font-size|sm"}}} /-->
        
        <!-- wp:html -->
        <span class="post-category">
            <a href="#" class="category-micros"><?php esc_html_e('Micros', 'kiwi-theme'); ?></a>
        </span>
        <!-- /wp:html -->
    </div>
    <!-- /wp:group -->
    
    <!-- wp:post-content {"className":"micro-content","style":{"typography":{"fontSize":"var:preset|font-size|base","lineHeight":"1.6"}}} /-->
    
</div>
<!-- /wp:group -->