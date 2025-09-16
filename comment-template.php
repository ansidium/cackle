<?php
if (!function_exists('cackle_comments_closed')) {
    function cackle_comments_closed() {
        global $post;

        if (!$post instanceof WP_Post) {
            return true;
        }

        return !comments_open($post);
    }
}

if (!cackle_comments_closed()) {
    do_action('comment_form_before');
}

function cackle_get_avatar_url($id) {
    $avatar_url = get_avatar_url($id, array('size' => 96));

    if (!$avatar_url) {
        $avatar = get_avatar($id);
        if ($avatar) {
            $avatar = str_replace('&#038;', '&', $avatar);
            if (preg_match('/src=(\'|")(.*?)(\'|")/i', $avatar, $matches)) {
                $avatar_url = trim($matches[2]);
            }
        }
    }

    return $avatar_url ? esc_url_raw($avatar_url) : '';
}

function cackle_auth() {
    $current_user = wp_get_current_user();
    $timestamp = time();
    $site_api_key = get_option('cackle_siteApiKey');

    if (is_user_logged_in()) {
        $user = array(
            'id' => $current_user->ID,
            'name' => $current_user->display_name,
            'email' => $current_user->user_email,
            'avatar' => cackle_get_avatar_url($current_user->ID),
        );
    } else {
        $user = new stdClass();
    }

    $user_data = base64_encode(wp_json_encode($user));
    $sign = md5($user_data . $site_api_key . $timestamp);

    return sprintf('%s %s %d', $user_data, $sign, $timestamp);
}

$api_id = get_option('cackle_apiId', '');

if (!cackle_comments_closed()) {
    require_once __DIR__ . '/cackle_api.php';
    require_once __DIR__ . '/sync.php';

    if (!function_exists('cackle_comment')) {
        function cackle_comment($comment, $args, $depth) {
            $GLOBALS['comment'] = $comment;
            ?>
            <li <?php comment_class(); ?> id="cackle-comment-<?php echo esc_attr(get_comment_ID()); ?>">
                <div id="cackle-comment-header-<?php echo esc_attr(get_comment_ID()); ?>" class="cackle-comment-header">
                    <cite id="cackle-cite-<?php echo esc_attr(get_comment_ID()); ?>">
                        <?php if (get_comment_author_url()) : ?>
                            <a id="cackle-author-user-<?php echo esc_attr(get_comment_ID()); ?>"
                               href="<?php echo esc_url(get_comment_author_url()); ?>" target="_blank"
                               rel="nofollow noopener"><?php echo esc_html(get_comment_author()); ?></a>
                        <?php else : ?>
                            <span id="cackle-author-user-<?php echo esc_attr(get_comment_ID()); ?>"><?php echo esc_html(get_comment_author()); ?></span>
                        <?php endif; ?>
                    </cite>
                </div>
                <div id="cackle-comment-body-<?php echo esc_attr(get_comment_ID()); ?>" class="cackle-comment-body">
                    <div id="cackle-comment-message-<?php echo esc_attr(get_comment_ID()); ?>"
                         class="cackle-comment-message"><?php echo wp_kses_post(get_comment_text()); ?></div>
                </div>
            </li>
            <?php
        }
    }
    ?>
    <div class="comments-area">
        <div id="mc-container">
            <div id="mc-content">
                <?php if ((int) get_option('cackle_sync') === 1) : ?>
                    <ul id="cackle-comments">
                        <?php wp_list_comments(array('callback' => 'cackle_comment')); ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
        <?php if ((int) get_option('cackle_whitelabel', 0) === 0) : ?>
            <a id="mc-link" href="https://cackle.me" rel="noopener" target="_blank">
                <?php esc_html_e('Комментарии для сайта', 'cackle'); ?>
                <b style="color:#4FA3DA">Cackl</b><b style="color:#F65077">e</b>
            </a>
        <?php endif; ?>
    </div>
    <?php
    $lang_for_cackle = null;
    if (defined('ICL_LANGUAGE_CODE')) {
        $language_map = array(
            'uk' => 'uk',
            'pt-br' => 'pt',
            'be' => 'be',
            'kk' => 'kk',
            'en' => 'en',
            'es' => 'es',
            'de' => 'de',
            'lv' => 'lv',
            'el' => 'el',
            'fr' => 'fr',
            'ro' => 'ro',
            'it' => 'it',
            'ru' => 'ru',
        );
        $language_code = strtolower(ICL_LANGUAGE_CODE);
        if (isset($language_map[$language_code])) {
            $lang_for_cackle = $language_map[$language_code];
        }
    }

    $widget_config = array(
        'widget' => 'Comment',
        'countContainer' => 'c' . get_the_ID(),
        'id' => $api_id,
        'channel' => (string) get_the_ID(),
    );

    if ((int) get_option('cackle_sso', 0) === 1) {
        $widget_config['ssoAuth'] = cackle_auth();
    }

    if ($lang_for_cackle) {
        $widget_config['lang'] = $lang_for_cackle;
    }

    $widget_config_json = wp_json_encode($widget_config);
    ?>
    <script type="text/javascript">
        (function () {
            window.cackle_widget = window.cackle_widget || [];
            var config = <?php echo $widget_config_json; ?>;
    <?php if ((int) get_option('cackle_counter', 0) === 1) : ?>
            config.callback = {
                ready: [function () {
                    var count = document.getElementById(<?php echo wp_json_encode('c' . get_the_ID()); ?>);
                    if (!count) {
                        return;
                    }
                    var value = parseInt(count.textContent || count.innerText, 10);
                    if (isNaN(value)) {
                        value = 0;
                    }
                    var lang = config.lang || 'ru';
                    if (window.Cackle && Cackle.Comment && Cackle.Comment.lang && Cackle.Comment.lang[lang]) {
                        count.textContent = Cackle.Comment.lang[lang].commentCount(value);
                    }
                }]
            };
    <?php endif; ?>
            window.cackle_widget.push(config);
            var container = document.getElementById('mc-container');
            if (container) {
                container.innerHTML = '';
            }
            var mc = document.createElement('script');
            mc.type = 'text/javascript';
            mc.async = true;
            mc.src = 'https://cackle.me/widget.js';
            var s = document.getElementsByTagName('script')[0];
            s.parentNode.insertBefore(mc, s.nextSibling);
        }());
    </script>
    <?php do_action('comment_form_after');
}

if ($api_id === '') :
    esc_html_e('API ID not specified', 'cackle');
endif;
