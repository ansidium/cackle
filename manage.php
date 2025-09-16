<?php
$api_id = get_option('cackle_apiId', '');
if ('' === $api_id) {
    wp_safe_redirect(admin_url('edit-comments.php?page=cackle_settings'));
    exit;
}
?>
<style>
    #wpcontent {

        padding: 10px;
    }
    #wpbody-content > div.error{
        display: none;
    }

    #wpwrap {
        background-color: #FFFFFF;

    }
</style>

<div id="mc-comment-admin"></div>
<script type="text/javascript">
    cackle_widget = window.cackle_widget || [];
    cackle_widget.push({widget: 'CommentAdmin', id: <?php echo wp_json_encode($api_id); ?>});
    (function () {
        var mc = document.createElement('script');
        mc.type = 'text/javascript';
        mc.async = true;
        mc.src = 'https://cackle.me/widget.js';
        var s = document.getElementsByTagName('script')[0];
        s.parentNode.insertBefore(mc, s.nextSibling);
    })();
</script>



