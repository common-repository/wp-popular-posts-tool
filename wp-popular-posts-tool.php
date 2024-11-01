<?php
/*
Plugin Name: WP-Popular Posts Tool
Plugin URI: http://teofiloisrael.com/plugin-popular-posts-tool/
Description: Enables you to automatically display most commented posts, either by category or tag. Optional: You can choose manually the category or tag you want to display its most commented posts. It has several configuration options, and can list your comments with color bars. It has a widget to add it easily to your sidebar. See this plugin in action in http://movilarena.com
Author: Teofilo Israel Vizcaino Rodriguez
Version: 3.0
Author URI: http://teofiloisrael.com
Demo URI: http://movilarena.com
*/ 
 
class WpPopularPostsTool extends WP_Widget {
    
    function WpPopularPostsTool() {
        //Constructor
        $widget_ops = array('classname' => 'WpPopularPostsTool', 'description' => __('Automatically display most commented posts, either by category or tag, or manually choose the category or tag to display its most commented posts'));
        $this->WP_Widget('WpPopularPostsTool', 'WP-Popular Posts Tool', $widget_ops);
    }
    
    /**
     * Prints the list of popular posts
     */
	function ti_popular_posts($num, $my_id=0, $begin='<ul>', $end='</ul>', $pre='<li>', $suf='</li>', $mode=0, $disableComments=0, $barsLocation=0){
        global $wpdb;
        if($my_id==0):
            if(is_category()): 
                $my_title = strtolower(single_cat_title('', false));  $my_id = get_cat_ID($my_title); 
            elseif(is_tag()):
                $my_title = $my_id = intval(get_query_var('tag_id'));
            elseif(is_single()):
                $my_title = get_the_category(); $my_id = $my_title[0]->cat_ID;
            endif;
        endif;
        if(!is_category() && !is_tag() && !is_single() && $my_id==0): 
            $querystr = "SELECT $wpdb->posts.post_title, $wpdb->posts.ID, $wpdb->posts.post_content, $wpdb->posts.comment_count FROM $wpdb->posts WHERE $wpdb->posts.post_status = 'publish' AND $wpdb->posts.post_type = 'post' ORDER BY $wpdb->posts.comment_count DESC LIMIT $num";
        else:
            $querystr = "SELECT $wpdb->posts.post_title, $wpdb->posts.ID, $wpdb->posts.post_content, $wpdb->posts.comment_count FROM $wpdb->posts INNER JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id) INNER JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id) WHERE term_id=$my_id AND $wpdb->posts.post_status = 'publish' AND $wpdb->posts.post_type = 'post' ORDER BY comment_count desc LIMIT $num";
        endif;	

        $myposts = $wpdb->get_results($querystr, OBJECT);
        
        if($mode==0) echo $begin;
        $postCount = 0;
        $maxPostComments = 0;
        //$colors = array('#FF3333', '#FF6666', '#FF9999', '#FFCCCC', '#FFEEEE');
        $colors = array('#CC0000', '#FF9900', '#FFCC00', '#669966', '#336699');
        foreach($myposts as $post) {
            if($mode==0) echo $pre;
            switch($mode){
                case 0://Normal mode, with comment count
                default:    
                    ?><a href="<?php echo get_permalink($post->ID); ?>"><?php echo $post->post_title ?></a><?php
                    if($disableComments==0){echo '<br />' . $post->comment_count . ' '; _e('comments');}
                    break;
                case 1://Graphical mode
                    if($postCount==0) $maxPostComments = $post->comment_count;
                    $areaSize = 100;
                    $width = $post->comment_count / $maxPostComments * $areaSize / 2;
                    if($barsLocation==1){//BARS AT THE RIGHT SIDE
                    ?><table style="width:100%">
                        <tr style="height:40px">                            
                            <td style="width:<?php echo $areaSize / 2;?>%;">
                                <a href="<?php echo get_permalink($post->ID); ?>">
                                    <?php echo $post->post_title ?>
                                </a>
                                <?php echo '<br />'; if($disableComments==0){echo '<a title="Comments" class="hot-comments-count" href="'. get_permalink($post->ID).'#comments"><img src="' .plugins_url('', __FILE__) . '/comments.png" alt="Comments" title="Comments" /><span>' . $post->comment_count .'</span></a>';}?>
                            </td>
                            <td style="width:<?php echo ($width); ?>%;
                            background-color:<?php if($postCount < 5){echo $colors[$postCount];}else{echo $colors[4];}; ?>"></td>
                            <td style="width:<?php echo ( $areaSize / 2 - $width); ?>%;"></td>
                        </tr>
                      </table>   
                        <?php
                    }else{//BARS AT THE LEFT SIDE?>
                    <table style="width:100%">
                        <tr style="height:40px">
                            <td style="width:<?php echo ($width); ?>%;
                            background-color:<?php if($postCount < 5){echo $colors[$postCount];}else{echo $colors[4];}; ?>"></td>                            
                            <td style="width:<?php echo $areaSize - $width;?>%;padding-left:2px">
                                <a href="<?php echo get_permalink($post->ID); ?>">
                                    <?php echo $post->post_title ?>
                                </a>
                                <?php echo '<br />'; if($disableComments==0){echo '<a title="Comments" class="hot-comments-count" href="'. get_permalink($post->ID).'#comments"><img src="' .plugins_url('', __FILE__) . '/comments.png" alt="Comments" title="Comments" /><span>' . $post->comment_count .'</span></a>';}?>
                            </td>
                        </tr>
                      </table>                    
                    <?php }   
                        $postCount++;
                    break;
            } 
            if($mode==0) echo $suf;
        } 
        echo $end;
    }
    
	/**
	 * Prints widget for user
	 */    
    function widget($args, $instance) {
        extract($args, EXTR_SKIP);
    
        $itemsNumber = $instance['itemsQuantity'];
        $catId = $instance['catId'];
    
        $title = apply_filters('widget_title', $instance['title']);
     
        $widget_id = $args['widget_id'];
        
        do_action( 'TB_RenderWidget',$before_widget,$after_widget,$title,$itemsNumber,$catId,$before_title,
                                    $after_title,$instance['displayMode'],$instance['disableCommentCount'],$instance['barsLocation']);
  
    }
    
    function render($before_widget,$after_widget,$title,$itemsNumber,$catId,$before_title,$after_title,$mode,$disableCommentsCount,$barsLocation){
        //echo $before_widget;
      
        if ( !empty( $title ) ) { 
            echo $before_title . $title . $after_title; 
        };

       WpPopularPostsTool::ti_popular_posts($itemsNumber, $catId, '<ul>', '</ul>', '<li>', '</li>', $mode, $disableCommentsCount, $barsLocation);
        
       //echo $after_widget;
    }
    
    /**
     * Saves the widget
     */
    function update($new_instance, $old_instance) {
    
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['itemsQuantity'] = absint(strip_tags($new_instance['itemsQuantity'])); 
        $instance['catId'] = absint(strip_tags($new_instance['catId']));
        $instance['displayMode'] = absint(strip_tags($new_instance['displayMode']));
        $instance['disableCommentCount'] = absint(strip_tags($new_instance['disableCommentCount']));
        $instance['barsLocation'] = absint(strip_tags($new_instance['barsLocation']));
        
        return $instance;
    }
    
    /**
     * Widget form for backend
     */
    function form($instance) {
        $instance = wp_parse_args( (array) $instance, array( 'title' => 'WP-Popular Posts Tool', 'itemsQuantity' => 5, 'catId' => 0 ) );
        $title = strip_tags($instance['title']);
        $itemsQuantity = absint($instance['itemsQuantity']);
        $catId = absint($instance['catId']);
        $displayMode = absint($instance['displayMode']);
        $disableCommentCount = absint($instance['disableCommentCount']);
        $barsLocation = absint($instance['barsLocation']);
    ?>
    
    <p><label for="<?php echo $this->get_field_id('title'); ?>">
        <?php echo esc_html__('Title'); ?>: 
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" 
        type="text" value="<?php echo attribute_escape($title); ?>" />
    </label></p>
    
    <p><label for="<?php echo $this->get_field_id('itemsQuantity'); ?>">
        <?php echo esc_html__('Number of items to show'); ?>: 
        <input class="widefat" id="<?php echo $this->get_field_id('itemsQuantity'); ?>" name="<?php echo $this->get_field_name('itemsQuantity'); ?>" 
        type="text" value="<?php echo attribute_escape($itemsQuantity); ?>" />
    </label></p>  
    
    <p><label for="<?php echo $this->get_field_id('catId'); ?>">
        <?php echo esc_html__('Id of the cateogory or tag (leave it blank for automatic detection)'); ?>: 
        <input class="widefat" id="<?php echo $this->get_field_id('catId'); ?>" name="<?php echo $this->get_field_name('catId'); ?>" 
        type="text" value="<?php echo attribute_escape($catId); ?>" />
    </label></p>
    
    <p><label for="<?php echo $this->get_field_id('displayMode'); ?>">
        <?php echo esc_html__('Display mode'); ?>: 
        <select class="widefat" id="<?php echo $this->get_field_id('displayMode'); ?>" name="<?php echo $this->get_field_name('displayMode'); ?>" 
        type="text">
            <option value="0" <?php if($displayMode == 0) echo 'selected'; ?>>Text Only</option>
            <option value="1" <?php if($displayMode == 1) echo 'selected'; ?>>Graphic</option>
        </select>
    </label></p>
    
    <p><label for="<?php echo $this->get_field_id('barsLocation'); ?>">
        <?php echo esc_html__('Bars Location (if mode = Graphic)'); ?>: 
        <select class="widefat" id="<?php echo $this->get_field_id('barsLocation'); ?>" name="<?php echo $this->get_field_name('barsLocation'); ?>" 
        type="text">
            <option value="0" <?php if($barsLocation == 0) echo 'selected'; ?>>Left</option>
            <option value="1" <?php if($barsLocation == 1) echo 'selected'; ?>>Right</option>
        </select>
    </label></p>    
    
    <p><label for="<?php echo $this->get_field_id('disableCommentCount'); ?>">
        <?php echo esc_html__('Disable comment count'); ?>: 
        <select class="widefat" id="<?php echo $this->get_field_id('disableCommentCount'); ?>" name="<?php echo $this->get_field_name('disableCommentCount'); ?>" 
        type="text">
            <option value="0" <?php if($disableCommentCount == 0) echo 'selected'; ?>>No</option>
            <option value="1" <?php if($disableCommentCount == 1) echo 'selected'; ?>>Yes</option>
        </select>
    </label></p>    
            
    <?php
    }
}

/**
 * Use this function if you want to use the plugin directly from the code
 */
function ti_popular_posts($num, $my_id=0, $begin='<ul>', $end='</ul>', $pre='<li>', $suf='</li>', $mode=0){
    WpPopularPostsTool::ti_popular_posts($num, $my_id, $begin, $end, $pre, $suf, $mode);
}
   
add_action( 'widgets_init', create_function('', 'return register_widget("WpPopularPostsTool");') );
add_action( 'TB_RenderWidget', array('WpPopularPostsTool', 'render'),10,12 );   

?>