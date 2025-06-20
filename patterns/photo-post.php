<?php
/**
 * Title: Photo Post
 * Slug: kiwi-theme/photo-post
 * Categories: gallery, media
 * Description: A photo-focused post pattern with caption support
 */
?>

<!-- wp:group {"className":"feed-item photo-post","style":{"textAlign":"center"}} -->
<div class="wp-block-group feed-item photo-post">
    
    <!-- wp:group {"className":"post-meta","style":{"spacing":{"margin":{"bottom":"var:preset|spacing|lg"}}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"center"}} -->
    <div class="wp-block-group post-meta">
        <!-- wp:html -->
        <span class="post-type-indicator post-type-photo" aria-hidden="true"></span>
        <!-- /wp:html -->
        
        <!-- wp:post-date {"className":"post-date","style":{"typography":{"fontSize":"var:preset|font-size|sm"},"spacing":{"margin":{"right":"var:preset|spacing|md"}}}} /-->
        
        <!-- wp:post-terms {"term":"category","className":"post-category","style":{"typography":{"fontSize":"var:preset|font-size|xs","textTransform":"uppercase","fontWeight":"500","letterSpacing":"0.5px"}}} /-->
    </div>
    <!-- /wp:group -->
    
    <!-- wp:post-title {"level":2,"isLink":true,"className":"post-title","style":{"typography":{"fontSize":"clamp(1.5rem, 3vw, 1.75rem)","fontWeight":"500","textAlign":"center"},"spacing":{"margin":{"bottom":"var:preset|spacing|lg"}}}} /-->
    
    <!-- wp:post-featured-image {"className":"photo-preview","style":{"border":{"radius":"12px"},"spacing":{"margin":{"bottom":"var:preset|spacing|lg"}}}} /-->
    
    <!-- wp:post-excerpt {"className":"post-excerpt","style":{"typography":{"fontSize":"var:preset|font-size|sm","lineHeight":"1.6","textAlign":"center"}}} /-->
    
</div>
<!-- /wp:group -->