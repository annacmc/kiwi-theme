<?php
/**
 * Title: No Results Found
 * Slug: kiwi-theme/hidden-no-results
 * Inserter: no
 * Description: Hidden pattern for no search results
 */
?>

<!-- wp:group {"className":"no-results","style":{"spacing":{"padding":{"top":"var:preset|spacing|4xl","bottom":"var:preset|spacing|4xl"}},"textAlign":"center"}} -->
<div class="wp-block-group no-results">
    
    <!-- wp:heading {"level":2,"className":"no-results-title","style":{"typography":{"fontSize":"clamp(1.5rem, 3vw, 2rem)","fontWeight":"500"},"spacing":{"margin":{"bottom":"var:preset|spacing|lg"}}}} -->
    <h2 class="wp-block-heading no-results-title"><?php esc_html_e('No posts found', 'kiwi-theme'); ?></h2>
    <!-- /wp:heading -->
    
    <!-- wp:paragraph {"className":"no-results-description","style":{"typography":{"fontSize":"var:preset|font-size|lg","lineHeight":"1.6"},"spacing":{"margin":{"bottom":"var:preset|spacing|xl"}}}} -->
    <p class="no-results-description"><?php esc_html_e('Sorry, but nothing matched your search terms. Please try again with different keywords.', 'kiwi-theme'); ?></p>
    <!-- /wp:paragraph -->
    
    <!-- wp:search {"label":"Search","showLabel":false,"placeholder":"Try a different search...","className":"no-results-search","style":{"spacing":{"margin":{"bottom":"var:preset|spacing|lg"}}}} /-->
    
    <!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"center"}} -->
    <div class="wp-block-group">
        <!-- wp:button {"className":"wp-element-button"} -->
        <div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Back to Homepage', 'kiwi-theme'); ?></a></div>
        <!-- /wp:button -->
    </div>
    <!-- /wp:group -->
    
</div>
<!-- /wp:group -->