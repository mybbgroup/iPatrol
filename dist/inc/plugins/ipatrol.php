<?php
/**
 * @package iPatrol
 * @version 1.1.0
 * @category MyBB 1.8.x Plugin
 * @author effone <effone@mybb.com>
 * @license MIT
 *
 */

if (!defined('IN_MYBB')) {
    die('Direct access prohibited.');
}

if (defined('IN_ADMINCP')) {
    $plugins->add_hook('admin_settings_print_peekers', 'ipatrol_settingspeekers');

    function ipatrol_info()
    {
        global $lang;
        $lang->load('ipatrol');

        return array(
            'name' => 'iPatrol',
            'description' => $lang->ipatrol_desc,
            'website' => 'https://github.com/mybbgroup/iPatrol',
            'author' => 'effone</a> of <a href="https://mybb.group">MyBBGroup</a>',
            'authorsite' => 'https://eff.one',
            'version' => '1.0.0-alpha',
            'compatibility' => '18*',
            'codename' => 'ipatrol',
        );
    }

    function ipatrol_install()
    {
        global $db, $lang;
        $lang->load('ipatrol');

        $tpl = array();
        $tpl['iPatrol_locate_frame'] = '<div class="modal">
    <div style="overflow-y: auto; max-height: 400px;">
        <table width="100%" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" border="0" align="center" class="tborder">
            <tr>
                <td colspan="2" class="thead"><strong>{$lang->locate_title}</strong></td>
            </tr>
            <tr>
                <td class="tcat"><span class="smalltext"><strong>{$lang->locate_item}</strong></span></td>
                <td class="tcat"><span class="smalltext"><strong>{$lang->locate_detail}</strong></span></td>
            </tr>
            {$locate_details}
        </table>
    </div>
</div>';
        $tpl['iPatrol_locate_map'] = '<tr>
    <td colspan="2" class="{$altbg}"><div>{$locate_map}</div></td>
</tr>';
        $tpl['iPatrol_locate_valid_row'] = '<tr>
    <td class="{$altbg}"><strong>{$locate_item}</strong></td>
    <td class="{$altbg}">{$locate_detail}</td>
</tr>';
        $tpl['iPatrol_locate_error_row'] = '<tr>
    <td colspan="2"><div class="red_alert" style="margin: 5px;">{$error_note}</div></td>
</tr>';

        $db->insert_query('templategroups', array('prefix' => $db->escape_string('iPatrol'), 'title' => $db->escape_string('iPatrol')));
        foreach ($tpl as $name => $code) {
            $db->insert_query('templates', array('title' => $db->escape_string($name), 'template' => $db->escape_string($code), 'version' => 1, 'sid' => -2, 'dateline' => TIME_NOW));
        }

        // Create our table collation
        $collation = $db->build_create_table_collation();
        $db->write_query("CREATE TABLE " . TABLE_PREFIX . "ipatrol_actlog (
            xid int unsigned NOT NULL auto_increment,
            act_data varchar(400) NOT NULL default '',
            act_on int(10) unsigned NOT NULL default 0,
            act_done tinyint(1) NOT NULL default 0,
            act_ping tinyint(1) NOT NULL default 0,
            PRIMARY KEY (xid)
        ) ENGINE=MyISAM{$collation};");

        // Build Plugin Settings
        $ipatrol_group = array(
            "name" => "ipatrol",
            "title" => "iPatrol",
            "description" => $lang->ipatrol_desc,
            "disporder" => "9",
            "isdefault" => "0",
        );
        $db->insert_query("settinggroups", $ipatrol_group);
        $gid = $db->insert_id();
        $disporder = 0;

        $ipatrol[] = array(
            "name" => "ipatrol_locateuser",
            "optionscode" => "onoff",
            "value" => '1',
        );

        $ipatrol[] = array(
            "name" => "ipatrol_apicachelimit",
            "optionscode" => "numeric",
            "value" => '100',
        );

        $ipatrol[] = array(
            "name" => "ipatrol_banproxy",
            "optionscode" => "onoff",
            "value" => '1',
        );

        $ipatrol[] = array(
            "name" => "ipatrol_whiteip",
            "optionscode" => "textarea",
            "value" => '',
        );

        $ipatrol[] = array(
            "name" => "ipatrol_postcheck",
            "optionscode" => "onoff",
            "value" => '1',
        );

        $ipatrol[] = array(
            "name" => "ipatrol_postcheckedit",
            "optionscode" => "onoff",
            "value" => '1',
        );

        $ipatrol[] = array(
            "name" => "ipatrol_postchecknum",
            "optionscode" => "numeric",
            "value" => '5',
        );

        $ipatrol[] = array(
            "name" => "ipatrol_postcheckgids",
            "optionscode" => "text",
            "value" => '1,2,5,7',
        );

        $ipatrol[] = array(
            "name" => "ipatrol_postcheckwlist",
            "optionscode" => "text",
            "value" => '',
        );
/*
        $ipatrol[] = array(
            "name" => "ipatrol_noregdupe",
            "optionscode" => "onoff",
            "value" => '0',
        );

        $ipatrol[] = array(
            "name" => "ipatrol_skipregdupe",
            "optionscode" => "groupselect",
            "value" => '1,3,4',
        );
*/
        $ipatrol[] = array(
            "name" => "ipatrol_detectbot",
            "optionscode" => "onoff",
            "value" => '1',
        );

        $ipatrol[] = array(
            "name" => "ipatrol_autoaddbot",
            "optionscode" => "onoff",
            "value" => '1',
        );

        $ipatrol[] = array(
            "name" => "ipatrol_uashortbot",
            "optionscode" => "radio\n0=" . $lang->ipatrol_uashortbot_option_1 . "\n1=" . $lang->ipatrol_uashortbot_option_2,
            "value" => '1',
        );

        $ipatrol[] = array(
            "name" => "ipatrol_similarbot",
            "optionscode" => "onoff",
            "value" => '1',
        );

        $ipatrol[] = array(
            "name" => "ipatrol_simstrength",
            "optionscode" => "numeric",
            "value" => '70',
        );

        $ipatrol[] = array(
            "name" => "ipatrol_pmalert",
            "optionscode" => "yesno",
            "value" => '1',
        );

        $ipatrol[] = array(
            "name" => "ipatrol_mailalert",
            "optionscode" => "yesno",
            "value" => '1',
        );

        foreach ($ipatrol as $ipatrol_opt) {
            $ipatrol_opt['title'] = $lang->{$ipatrol_opt['name'] . "_title"};
            $ipatrol_opt['description'] = $lang->{$ipatrol_opt['name'] . "_desc"};
            $ipatrol_opt['disporder'] = ++$disporder;
            $ipatrol_opt['gid'] = intval($gid);

            $db->insert_query("settings", $ipatrol_opt);
        }

        rebuild_settings();
    }

    function ipatrol_is_installed()
    {
        global $db;
        return $db->table_exists('ipatrol_actlog');
    }

    function ipatrol_activate()
    {
        require MYBB_ROOT . "/inc/adminfunctions_templates.php";
        find_replace_templatesets('online_row_ip', '#{\$lookup}#', '<!-- iPatrol --><a href="#" class="iPatrol_locate" data-ip="{$user[\'ip\']}"></a><!-- /iPatrol -->{$lookup}');
    }

    function ipatrol_deactivate()
    {
        require MYBB_ROOT . "/inc/adminfunctions_templates.php";
        find_replace_templatesets('online_row_ip', '#\<!--\siPatrol\s--\>(.+)\<!--\s\/iPatrol\s--\>#is', '', 0);

    }

    function ipatrol_uninstall()
    {
        global $db, $cache;

        $db->delete_query('templategroups', "prefix='iPatrol'");
        $db->delete_query('templates', "title LIKE 'iPatrol_%'");

        // $db->drop_table('ipatrol_actlog');
        $cache->delete('ipatrol_apiresponses');
        $cache->delete('ipatrol_bottrap');
        $db->delete_query("settings", "name LIKE '%ipatrol%'");
        $db->delete_query("settinggroups", "name='ipatrol'");

        rebuild_settings();
    }

    function ipatrol_settingspeekers(&$peekers)
    {
        //$peekers[] = 'new Peeker($(".setting_ipatrol_noregdupe"), $("#row_setting_ipatrol_skipregdupe"),/1/,true)';
        $peekers[] = 'new Peeker($(".setting_ipatrol_detectbot"), $("#row_setting_ipatrol_autoaddbot"),/1/,true)';
        $peekers[] = 'new Peeker($(".setting_ipatrol_detectbot"), $("#row_setting_ipatrol_similarbot"),/1/,true)';
        $peekers[] = 'new Peeker($(".setting_ipatrol_banproxy"), $("#row_setting_ipatrol_whiteip"),/1/,true)';
        $peekers[] = 'new Peeker($(".setting_ipatrol_postcheck"), $("#row_setting_ipatrol_postcheckedit"),/1/,true)';
        $peekers[] = 'new Peeker($(".setting_ipatrol_postcheck"), $("#row_setting_ipatrol_postchecknum"),/1/,true)';
        $peekers[] = 'new Peeker($(".setting_ipatrol_postcheck"), $("#row_setting_ipatrol_postcheckgids"),/1/,true)';
        $peekers[] = 'new Peeker($(".setting_ipatrol_postcheck"), $("#row_setting_ipatrol_postcheckwlist"),/1/,true)';
        $peekers[] = 'new Peeker($(".setting_ipatrol_autoaddbot"), $("#row_setting_ipatrol_uashortbot"),/1/,true)';
        $peekers[] = 'new Peeker($(".setting_ipatrol_similarbot"), $("#row_setting_ipatrol_simstrength"),/1/,true)';
    }
} else {
    $plugins->add_hook('xmlhttp', 'ipatrol_fetch_details');
    $plugins->add_hook('online_start', 'ipatrol_js_inject');
    $plugins->add_hook('global_start', 'ipatrol_bot_trap');
    $plugins->add_hook('global_end', 'ipatrol_ban_proxy');
    $plugins->add_hook('member_do_register_start', 'ipatrol_ban_regdupe');

    // Hooks for auto-unapproval of post
    $plugins->add_hook('datahandler_post_validate_post', 'ipatrol_spamcheck');
    $plugins->add_hook('datahandler_post_validate_thread', 'ipatrol_spamcheck');
    $plugins->add_hook('datahandler_post_update_thread', 'ipatrol_spamcheck');
    $plugins->add_hook('datahandler_post_update', 'ipatrol_spamcheck');

    function ipatrol_fetch_details()
    {
        global $mybb;
        if ($mybb->settings['ipatrol_locateuser'] && $mybb->input['action'] == 'iplocate') {
            global $lang, $templates, $theme;
            $lang->load('ipatrol');
            $error_note = $locate_details = "";

            if (!verify_post_check($mybb->get_input('my_post_key'), true)) {
                $error_note = 'invalid_post_code';
            } else if (!$mybb->usergroup['canuseipsearch']) {
                $error_note = 'permission_error';
            } else {
                $alt = 2;
                $api_response = json_decode(ipatrol_ip_info($mybb->get_input('ip'), $mybb->get_input('fields')), true);
                if ($api_response['status'] == 'success') {
                    // Info to display, later drive it through setting
                    $disp = array('as', 'isp', 'reverse', 'lat', 'lon', 'zip', 'regionName', 'city', 'country', 'timezone', 'org');
                    
                    foreach ($api_response as $locate_item => $locate_detail) {
                        if (in_array($locate_item, $disp) && !empty($locate_detail)) {
                            $alt = $alt == 1 ? 2 : 1;
                            $altbg = 'trow' . $alt;
                            $locate_item = $lang->{'api_' . $locate_item};
                            $locate_details .= eval($templates->render('iPatrol_locate_valid_row'));
                        }
                    }
                } else {
                    $error_note = str_replace(' ', '_', $api_response['message']);
                }
            }

            if (empty($locate_details) && !empty($error_note)) {
                $error_note = $lang->{$error_note};
                $locate_details = eval($templates->render('iPatrol_locate_error_row'));
            }

            eval("\$locate = \"" . $templates->get("iPatrol_locate_frame") . "\";");
            die($locate);
        }
    }

    function ipatrol_ban_proxy()
    {
        global $mybb;

        // IP Ban the user using Proxy
        if ($mybb->settings['ipatrol_banproxy']) {            
            // Don't try to track real IP using get_ip(), we need to punish presented IP
            $ip = my_strtolower(trim($_SERVER['REMOTE_ADDR']));
            $whitelist = array_map('trim', preg_split('/\r\n|\r|\n/', $mybb->settings['ipatrol_whiteip']));

            if (!is_banned_ip($ip) && !in_array($ip, $whitelist)) {
                $response = ipatrol_ip_info($ip, 'proxy');
                if (!empty($response) && json_decode($response, true)['proxy']) {
                    // Ban this IP
                    $actlog['act_done'] = 8;
                    $actlog['act_data']['ip'] = $ip;
                    ipatrol_ip_ban($ip);
                    ipatrol_publish($actlog);

                    // Redirect immediately to trap the user with IP ban notice
                    header("Location: {$mybb->settings['bburl']}");
                }
            }
        }
    }

    function ipatrol_bot_trap()
    {
        global $db, $mybb, $lang, $cache;
        $lang->load('ipatrol');

        if ($mybb->settings['ipatrol_detectbot'] && !$mybb->user['uid']) {

            //$logged = file('skipbot.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $logged = $cache->read('ipatrol_bottrap');
            if (!isset($logged) || empty($logged)) {
                $logged = array();
            }

            $query = $db->simple_select("sessions", "useragent", "sid NOT LIKE'%bot%' AND UID = '0'");
            while ($skip = $db->fetch_array($query)) {
                $u_agent = trim($skip['useragent']);
                if (!empty($u_agent) && !in_array($u_agent, $logged)) {

                    // Load the detector library
                    $lib_files = ['CrawlerDetect', 'AbstractProvider', 'Crawlers', 'Exclusions', 'Headers'];
                    foreach ($lib_files as $i => $file) {
                        $file = $i ? 'Fixtures/' . $file : $file;
                        require_once MYBB_ROOT . '/inc/3rdparty/crawlerdetect/' . $file . '.php';
                    }
                    $CrawlerDetect = new Jaybizzle\CrawlerDetect\CrawlerDetect;

                    if ($CrawlerDetect->isCrawler($u_agent)) {
                        $similar_spiders = $actlog = array();
                        $bot_name = $CrawlerDetect->getMatches();

                        $actlog['act_data']['name'] = $bot_name;
                        $actlog['act_data']['ip'] = my_strtolower(trim($_SERVER['REMOTE_ADDR']));
                        $actlog['act_data']['uas'] = $u_agent;

                        if ($mybb->settings['ipatrol_similarbot']) {
                            $registered_spiders = $cache->read('spiders');
                            $match_power = (int) $mybb->settings['ipatrol_simstrength'];
                            $match_power = (!$match_power || $match_power < 1) ? 70 : (($match_power > 100) ? 100 : $match_power);

                            foreach ($registered_spiders as $registered_spider) {
                                similar_text($bot_name, $registered_spider['name'], $match);
                                if ($match >= $match_power) {
                                    $similar_spiders[] = $registered_spider['name'];
                                }
                            }
                        }

                        if (count($similar_spiders)) {
                            $actlog['act_data']['match'] = $similar_spiders;
                            $actlog['act_done'] = 1;
                        } else {
                            if ($mybb->settings['ipatrol_autoaddbot']) {
                                $insert = array(
                                    'name' => ($db->escape_string($bot_name)),
                                    'useragent' => ($db->escape_string(strtolower($mybb->settings['ipatrol_uashortbot'] ? $bot_name : $u_agent))),
                                    'lastvisit' => TIME_NOW,
                                );

                                $db->insert_query("spiders", $insert);
                                $cache->update_spiders();
                                $actlog['act_done'] = 3;
                            } else {
                                $actlog['act_done'] = 2;
                            }
                        }

                        $logged[] = $u_agent;
                        $cache->update('ipatrol_bottrap', $logged);

                        // Log and notify
                        ipatrol_publish($actlog);
                    }
                }
            }
        }
    }

    function ipatrol_ip_ban(&$ip)
    {
        global $db, $cache;
        $insert = array(
            "filter" => $db->escape_string($ip),
            "type" => 1,
            "dateline" => TIME_NOW,
        );
        $db->insert_query("banfilters", $insert);
        $cache->update_bannedips();
    }

    function ipatrol_api_call($url)
    {
        $stream = stream_context_create(array(
            'http' => array(
                'timeout' => 3, // Timeout in seconds
            ),
        ));

        $response = @file_get_contents($url, 0, $stream);
        $response = json_decode($response, true);
        if (!json_last_error() == 0) {
            // Return response failed / not in expected JSON format
            return false;
        } else {
            return $response;
        }
    }

    function ipatrol_ip_info($ip, $fields = '')
    {
        global $cache;
        $prepatrol = $cache->read('ipatrol_apiresponses');
        if (isset($prepatrol) && is_array($prepatrol)) {
            $cid = array_search($ip, array_column($prepatrol, 'query'));
        }
        if ((isset($cid) && $cid === 0) || !empty($cid)) {
            $response = $prepatrol[$cid];
        } else {
            $response = ipatrol_api_call("http://ip-api.com/json/" . $ip . "?fields=786431"); // Fetch all data for reference

            if (!$response) {
                global $lang;
                $lang->load('ipatrol');
                $response = array('error' => $lang->invalid_response);
            } else {
                if (!isset($prepatrol) || empty($prepatrol)) {
                    $prepatrol = array();
                }
                // Push the new data to the beginning so that we can trim limit overflow from end
                array_unshift($prepatrol, $response);
                $limit = (int) $mybb->settings['ipatrol_apicachelimit'];
                if (!$limit) {
                    $limit = 100;
                }

                if (count($prepatrol) > $limit) {
                    array_splice($prepatrol, $limit, count($prepatrol) - $limit);
                }

                $cache->update('ipatrol_apiresponses', $prepatrol);
            }
        }

        if (!empty($fields)) {
            if (!is_array($fields)) {
                $fields = explode(',', $fields);
            }

            foreach ($response as $field => $data) {
                if (!in_array($field, $fields)) {
                    unset($response[$field]);
                }
            }
        }
        return json_encode($response);
    }

/*
1 : Similar spider detected
2 : Spider detected
3 : Spider added
4 :
5 :
6 : Action blocked
7 : Dupereg IP Banned
8 : Proxy IP Banned
9 : Honeypot trapped IP banned
 */
// Notification: [0 => 'No Notification, Only Log', 1 => 'PM Notification', 2 => 'Email Notification'];
    function ipatrol_publish($actlog = array())
    {
        if (!empty($actlog) && $actlog['act_done']) {
            global $db, $mybb, $lang;
            $lang->load('ipatrol');
            $actlog['act_ping'] = 0;

            // Set event
            $event = $matter = "";
            $act = (int) $actlog['act_done'];
            $actlog['act_on'] = TIME_NOW;
            switch (true) {
                case $act <= 5:
                    $event = 'bottrap';
                    $matter .= $lang->sprintf($lang->{'matter_' . $event}, $actlog['act_data']['name'], $actlog['act_data']['uas']);
                    if (!isset($actlog['act_data']['match'])) {
                        $actlog['act_data']['match'] = array();
                    }
                    $matter .= $lang->sprintf($lang->{'event_' . $act}, count($actlog['act_data']['match']));
                    break;

                case $act == 6:
                    $event = 'actblock';
                    break;

                case $act > 6 && $act <= 10:
                    $event = 'ipban';
                    $matter .= $lang->sprintf($lang->{'event_' . $act}, $actlog['act_data']['ip']);
                    break;
            }
            $event = $lang->{'event_' . $event};
            $matter = $lang->sprintf($lang->matter_body, $matter);

            // Notify over PM
            if ($mybb->settings['ipatrol_pmalert']) {
                include_once MYBB_ROOT . 'inc/datahandlers/pm.php';
                $pmhandler = new PMDataHandler();
                $pmhandler->admin_override = true;
                $pm = array(
                    'subject' => $event,
                    'message' => $matter,
                    'fromid' => '1',
                    'toid' => array('1'),
                    'do' => '',
                    'pmid' => '',
                    'options' => array('signature' => '0', 'disablesmilies' => '0', 'savecopy' => '0', 'readreceipt' => '0'),
                );
                $pmhandler->set_data($pm);

                $pmsent = array();
                if ($pmhandler->validate_pm()) {
                    $pmsent = $pmhandler->insert_pm();
                }
                if (isset($pmsent['messagesent']) && $pmsent['messagesent']) {
                    $actlog['act_ping'] = 1;
                }
            }

            // Send mail
            if ($mybb->settings['ipatrol_mailalert']) {
                $actlog['act_ping'] = $actlog['act_ping'] + 2;
                $mail_to = empty($mybb->settings['returnemail']) ? $mybb->settings['adminemail'] : $mybb->settings['returnemail'];
                my_mail(trim($mail_to), $lang->sprintf($lang->event_subject, $event, $mybb->settings['bbname']), $matter);
            }

            // Log action
            $actlog['act_data'] = json_encode($actlog['act_data']);
            $db->insert_query('ipatrol_actlog', $actlog);
        }
    }

    function ipatrol_js_inject()
    {
        global $mybb;
        if ($mybb->settings['ipatrol_locateuser']) {
            global $lang, $headerinclude;
            $lang->load('ipatrol');
            $headerinclude .= "
        <script type='text/javascript'>
        var lText = '" . $lang->locate_button . "';
        $(function(){
            $('.iPatrol_locate')
            .each(function(){
                $(this).html(lText);
            })
            .on('click', function(e){
                e.preventDefault();
                MyBB.popupWindow('/xmlhttp.php?action=iplocate&my_post_key='+my_post_key+'&ip='+$(this).data('ip'));
            })
        });
        </script>";
        }
    }

    function ipatrol_spamcheck(&$post)
    {
        global $mybb;
        if (!$mybb->user['moderateposts']
            && !in_array($mybb->user['uid'], explode(',', $mybb->settings['ipatrol_postcheckwlist']))
            && $mybb->settings['ipatrol_postcheck']
            && (int) $mybb->user['postnum'] < (int) $mybb->settings['ipatrol_postchecknum']) {
            $suspectedgroups = array_filter(array_unique(explode(',', $mybb->settings['ipatrol_postcheckgids'])));
            $usergroups = array_filter(array_unique(explode(',', $mybb->user['usergroup'] . ',' . $mybb->user['additionalgroups'])));

            if (!empty($suspectedgroups)
                && !empty(array_intersect($usergroups, $suspectedgroups))
                && ipatrol_scanpost($post->data['message'])) {
                if ($post->method == "update") {
                    if ($mybb->settings['ipatrol_postcheckedit']) {
                        if ($post->first_post) {
                            $post->thread_update_data['visible'] = 0;
                        }
                        $post->post_update_data['visible'] = 0;
                    }
                } else {
                    // No hook in required place; let's fool MyBB
                    $mybb->user['moderateposts'] = 1;

                    // Temporarily modify notice string
                    global $lang;
                    $lang->load('ipatrol');
                    $lang->redirect_newreply_moderation = $lang->spampost_caught;
                    $lang->redirect_newthread_moderation = $lang->spamthread_caught;
                }
            }
        }
    }

    function ipatrol_scanpost($post)
    {
        global $mybb;

        // Check for restricted words
        $catch_str = explode(',', $mybb->settings['ipatrol_postcheckstring']);
        if (!empty($catch_str)) {
            foreach ($catch_str as $str) {
                if (stripos($post, trim($str)) !== false) {
                    return true;
                }
            }
        }

        // Check for foreign url
        $in_house = parse_url(strtolower($mybb->settings['bburl']));
        preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $post, $match);
        if (!empty($match[0])) {
            foreach ($match[0] as $url) {
                $url = parse_url(strtolower($url));
                if ($url['host'] !== $in_house['host']) {
                    return true;
                }
            }
        }
        return false;
    }
}