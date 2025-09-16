<?php if (get_option('cackle_apiId', '')==""){
    echo'<script> window.location="edit-comments.php?page=cackle_settings"; </script> ';
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
    cackle_widget.push({widget: 'CommentAdmin', id: <?php print_r(get_option('cackle_apiId', '')); ?>});
    (function () {
        var mc = document.createElement('script');
        mc.type = 'text/javascript';
        mc.async = true;
        mc.src = ('https:' == document.location.protocol ? 'https' : 'http') + '://cackle.me/widget.js';
        var s = document.getElementsByTagName('script')[0];
        s.parentNode.insertBefore(mc, s.nextSibling);
    })();
</script>



