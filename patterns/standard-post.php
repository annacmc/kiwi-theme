<?php
/**
 * Title: Standard Post
 * Slug: kiwi-theme/standard-post
 * Categories: text
 * Description: Standard blog post layout with meta information
 */
?>

<!-- wp:group {"className":"feed-item"} -->
<div class="wp-block-group feed-item">
    
    <!-- wp:group {"className":"post-meta","style":{"spacing":{"margin":{"bottom":"var:preset|spacing|lg"}}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"left"}} -->
    <div class="wp-block-group post-meta">
        <!-- wp:html -->
        <span class="post-type-indicator post-type-post" aria-hidden="true"></span>
        <!-- /wp:html -->
        
        <!-- wp:post-date {"className":"post-date","style":{"typography":{"fontSize":"var:preset|font-size|sm"},"spacing":{"margin":{"right":"var:preset|spacing|md"}}}} /-->
        
        <!-- wp:post-terms {"term":"category","className":"post-category","style":{"typography":{"fontSize":"var:preset|font-size|xs","textTransform":"uppercase","fontWeight":"500","letterSpacing":"0.5px"}}} /-->
        
        <!-- wp:html -->
        <span class="reading-time"><?php kiwi_theme_display_reading_time(); ?></span>
        <!-- /wp:html -->
    </div>
    <!-- /wp:group -->
    
    <!-- wp:post-title {"level":2,"isLink":true,"className":"post-title","style":{"typography":{"fontSize":"clamp(1.5rem, 3vw, 2rem)","fontWeight":"600","lineHeight":"1.3"},"spacing":{"margin":{"bottom":"var:preset|spacing|md"}}}} /-->
    
    <!-- wp:post-excerpt {"className":"post-excerpt","style":{"typography":{"fontSize":"var:preset|font-size|base","lineHeight":"1.6"},"spacing":{"margin":{"bottom":"var:preset|spacing|lg"}}}} /-->
    
    <!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"left"}} -->
    <div class="wp-block-group">
        <!-- wp:read-more {"content":"Read more →","className":"read-more","style":{"typography":{"fontSize":"var:preset|font-size|sm","fontWeight":"500"}}} /-->
    </div>
    <!-- /wp:group -->
    
</div>
<!-- /wp:group -->