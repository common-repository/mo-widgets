<?php
/*
Plugin Name: MO Widgets
Plugin URI: http://amangs.com/wordpress/MO-Widgets.html
Description: Useing it can easier to control your Widgets.Adds checkboxes to choose your Widget to show or hide on different pages;and add a Widget with support for including PHP.Thanks for <a href="http://blog.strategy11.com/display-widgets/">Display widgets</a> and <a href="http://samsarin.com/samsarin-php-widget/">Samsarin PHP Widget</a>.|灵动边栏插件,使用它将使你更容易的控制你的边栏显示,安装激活以后会在小工具页面显示控制各个小工具在博客中任何页面是否显示,以实现不同页面不同侧边栏的效果;同时在小工具页面产生一个支持php语言的文本小工具,让你更容易的添加属于自己的边栏小工具.此插件在<a href="http://blog.strategy11.com/display-widgets/">Display widgets</a>和<a href="http://samsarin.com/samsarin-php-widget/">Samsarin PHP Widget</a>基础上修改,在此感谢原作者.
Author: 阿邙
Author URI: http://amangs.com/
Version: 1.02
*/

function d_p_d_widget($instance){
    if (is_home())
        $show = isset($instance['page-home']) ? ($instance['page-home']) : false;
    else if (is_front_page())
        $show = isset($instance['page-front']) ? ($instance['page-front']) : false;
    else if (is_category())
        $show = $instance['cat-'.get_query_var('cat')];
    else if (is_archive())
        $show = $instance['page-archive'];
    else if (is_single()){
        $show = $instance['page-single'];
        if (!$show){
            foreach(get_the_category() as $cat){ 
                if ($show) continue;
                if (isset($instance['cat-'.$cat->cat_ID]))
                    $show = $instance['cat-'.$cat->cat_ID];
            } 
        }
    }else if (is_404()) 
        $show = $instance['page-404'];
    else if (is_search())
        $show = $instance['page-search'];
    else{
        global $wp_query;
        $post_id = $wp_query->get_queried_object_id();
        $show = $instance['page-'.$post_id]; 
    }
    if (isset($instance['include']) && (($instance['include'] and $show == false) or ($instance['include'] == 0 and $show)))
        return false;
    else
        return $instance;
}

function dw_show_hide_widget_options($widget, $return, $instance){
    $last_saved = get_option('dw_check_new_pages'); //Check to see when pages and categories were last saved

    //if more than 1 minute ago, we can check again
    if(!$last_saved or ((time() - $last_saved) >= 60)){
        $pages = get_posts( array('post_type' => 'page', 'post_status' => 'published', 'numberposts' => 99, 'order_by' => 'post_title', 'order' => 'ASC'));
        $cats = get_categories();
        update_option('dw_saved_page_list', serialize($pages));
        update_option('dw_saved_cat_list', serialize($cats));
        update_option('dw_check_new_pages', time());
    }else{
        $pages = unserialize(get_option('dw_saved_page_list'));
        $cats = unserialize(get_option('dw_saved_cat_list'));
    }
       
    $wp_page_types = array('front' => 'Front', 'home' => 'Blog','archive' => 'Archives','single' => 'Single Post','404' => '404', 'search' => 'Search');
    
    $instance['include'] = isset($instance['include']) ? $instance['include'] : 0;
?>   
     <p>
    	<label for="<?php echo $widget->get_field_id('include'); ?>"><b>状态</b><br /><small>(当选中下列项目时隐藏或显示该侧边栏项)</small></label>
    	<select name="<?php echo $widget->get_field_name('include'); ?>" id="<?php echo $widget->get_field_id('include'); ?>" class="widefat">
            <option value="0" <?php echo selected( $instance['include'], 0 ) ?>>选中时在该类页面显示</option> 
            <option value="1" <?php echo selected( $instance['include'], 1 ) ?>>选中时在该类页面隐藏</option>
        </select>
    </p>    

<div style="height:150px; overflow:auto; border:1px solid #dfdfdf;">
    <p><b>页面类</b></p>
    <?php foreach ($pages as $page){ 
        $instance['page-'.$page->ID] = isset($instance['page-'.$page->ID]) ? $instance['page-'.$page->ID] : false;   
    ?>
        <p><input class="checkbox" type="checkbox" <?php checked($instance['page-'.$page->ID], true) ?> id="<?php echo $widget->get_field_id('page-'.$page->ID); ?>" name="<?php echo $widget->get_field_name('page-'.$page->ID); ?>" />
        <label for="<?php echo $widget->get_field_id('page-'.$page->ID); ?>"><?php _e($page->post_title) ?></label></p>
    <?php	}  ?>
    <p><b>文章分类</b></p>
    <?php foreach ($cats as $cat){ 
        $instance['cat-'.$cat->cat_ID] = isset($instance['cat-'.$cat->cat_ID]) ? $instance['cat-'.$cat->cat_ID] : false;   
    ?>
        <p><input class="checkbox" type="checkbox" <?php checked($instance['cat-'.$cat->cat_ID], true) ?> id="<?php echo $widget->get_field_id('cat-'.$cat->cat_ID); ?>" name="<?php echo $widget->get_field_name('cat-'.$cat->cat_ID); ?>" />
        <label for="<?php echo $widget->get_field_id('cat-'.$cat->cat_ID); ?>"><?php _e($cat->cat_name) ?></label></p>
    <?php } ?>
    
    <p><b>其他项目</b></p>
    <?php foreach ($wp_page_types as $key => $label){ 
        $instance['page-'. $key] = isset($instance['page-'. $key]) ? $instance['page-'. $key] : false;
    ?>
        <p><input class="checkbox" type="checkbox" <?php checked($instance['page-'. $key], true) ?> id="<?php echo $widget->get_field_id('page-'. $key); ?>" name="<?php echo $widget->get_field_name('page-'. $key); ?>" />
        <label for="<?php echo $widget->get_field_id('page-'. $key); ?>"><?php _e($label .' Page') ?></label></p>
    <?php } ?>
    </div>
<?php        
}

function dw_update_widget_options($instance, $new_instance, $old_instance){
    $pages = get_posts( array('post_type' => 'page', 'post_status' => 'published', 'numberposts' => 99, 'order_by' => 'post_title', 'order' => 'ASC'));
    foreach ($pages as $page)
        $instance['page-'.$page->ID] = isset($new_instance['page-'.$page->ID]) ? 1 : 0;
    foreach (get_categories() as $cat)
        $instance['cat-'.$cat->cat_ID] = isset($new_instance['cat-'.$cat->cat_ID]) ? 1 : 0;
    $instance['include'] = $new_instance['include'] ? 1 : 0;
    $instance['page-front'] = isset($new_instance['page-front']) ? 1 : 0;
    $instance['page-home'] = isset($new_instance['page-home']) ? 1 : 0;
    $instance['page-archive'] = isset($new_instance['page-archive']) ? 1 : 0;
    $instance['page-single'] = isset($new_instance['page-single']) ? 1 : 0;
    $instance['page-404'] = isset($new_instance['page-404']) ? 1 : 0;
    $instance['page-search'] = isset($new_instance['page-search']) ? 1 : 0;
    return $instance;
}


add_filter('widget_display_callback', 'd_p_d_widget');
add_action('in_widget_form', 'dw_show_hide_widget_options', 10, 3);
add_filter('widget_update_callback', 'dw_update_widget_options', 10, 3);
?>
<?php
class PhpWidget extends WP_Widget {
    function PhpWidget() {
        $widget_ops = array('classname' => 'php_widget', 'description' => '支持自定义php语言输入,定制自己的侧边栏.');
        $control_ops = array('width' => 200, 'height' => 120);
        $this->WP_Widget('php_widget', '自定边栏小工具', $widget_ops, $control_ops);
    }
    function widget($args, $instance) {     
        extract($args);
        $title = $instance['title'];
        $body = apply_filters('widget_text', $instance['body']);
        if (empty($body)) {
            $body = '&nbsp;';
        }
        print $before_widget;
        if (!empty($title)) {
            print $before_title;
            eval(" ?> $title <?php ");
            print $after_title;
        }
        eval(" ?> $body <?php ");
        print $after_widget;
    }
    function update($new_instance, $old_instance) {             
        return $new_instance;
    }
    function form($instance) {              
        $title = $instance['title'];
        $body = $instance['body'];
        $title_id = $this->get_field_id('title');
        $title_name = $this->get_field_name('title');
        $body_id = $this->get_field_id('body');
        $body_name = $this->get_field_name('body');
?>
        <p>
            <label for="<?php echo $title_id; ?>">标题:</label>
            <input class="widefat" id="<?php echo $title_id; ?>" name="<?php echo $title_name; ?>"
                   type="text" value="<?php echo esc_attr($title); ?>"/>
        </p>
        <p><label>代码区:</label>
            <textarea class="widefat" id="<?php echo $body_id; ?>" name="<?php echo $body_name; ?>"
                      rows="16" cols="20"><?php echo htmlspecialchars($body); ?></textarea>
        </p>
<?php 
    }

}

add_action('widgets_init', create_function('', 'return register_widget("PhpWidget");'));