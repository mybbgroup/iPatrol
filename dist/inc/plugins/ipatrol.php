<?php
/**
 * @package iPatrol
 * @version 1.0.0
 * @category MyBB 1.8.x Plugin
 * @author effone <effone@mybb.com>
 * @license MIT
 *
 * @todo ACP Settings
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
            'version' => '1.0.0',
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
            acton_name varchar(100) NOT NULL default '',
            acton_uas varchar(300) NOT NULL default '',
            acton_id int(10) unsigned NULL,
            acton_on int(10) unsigned NOT NULL default 0,
            acton_action tinyint(1) NOT NULL default 0,
            actin_notify tinyint(1) NOT NULL default 0,
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

        $ipatrol[] = array(
            "name" => "ipatrol_locateuser",
            "title" => $lang->ipatrol_locateuser_title,
            "description" => $lang->ipatrol_locateuser_desc,
            "optionscode" => "onoff",
            "value" => '1',
            "disporder" => '1',
            "gid" => intval($gid),
        );

        $ipatrol[] = array(
            "name" => "ipatrol_apicachelimit",
            "title" => $lang->ipatrol_apicachelimit_title,
            "description" => $lang->ipatrol_apicachelimit_desc,
            "optionscode" => "numeric",
            "value" => '100',
            "disporder" => '2',
            "gid" => intval($gid),
        );

        $ipatrol[] = array(
            "name" => "ipatrol_banproxy",
            "title" => $lang->ipatrol_banproxy_title,
            "description" => $lang->ipatrol_banproxy_desc,
            "optionscode" => "onoff",
            "value" => '1',
            "disporder" => '3',
            "gid" => intval($gid),
        );

        $ipatrol[] = array(
            "name" => "ipatrol_noregdupe",
            "title" => $lang->ipatrol_noregdupe_title,
            "description" => $lang->ipatrol_noregdupe_desc,
            "optionscode" => "onoff",
            "value" => '0',
            "disporder" => '4',
            "gid" => intval($gid),
        );

        $ipatrol[] = array(
            "name" => "ipatrol_skipregdupe",
            "title" => $lang->ipatrol_skipregdupe_title,
            "description" => $lang->ipatrol_skipregdupe_desc,
            "optionscode" => "groupselect",
            "value" => '1,3,4',
            "disporder" => '5',
            "gid" => intval($gid),
        );

        $ipatrol[] = array(
            "name" => "ipatrol_detectbot",
            "title" => $lang->ipatrol_detectbot_title,
            "description" => $lang->ipatrol_detectbot_desc,
            "optionscode" => "onoff",
            "value" => '1',
            "disporder" => '6',
            "gid" => intval($gid),
        );

        $ipatrol[] = array(
            "name" => "ipatrol_autoaddbot",
            "title" => $lang->ipatrol_autoaddbot_title,
            "description" => $lang->ipatrol_autoaddbot_desc,
            "optionscode" => "onoff",
            "value" => '1',
            "disporder" => '7',
            "gid" => intval($gid),
        );

        $ipatrol[] = array(
            "name" => "ipatrol_uashortbot",
            "title" => $lang->ipatrol_uashortbot_title,
            "description" => $lang->ipatrol_uashortbot_desc,
            "optionscode" => "radio\n0=" . $lang->ipatrol_uashortbot_option_1 . "\n1=" . $lang->ipatrol_uashortbot_option_2,
            "value" => '1',
            "disporder" => '8',
            "gid" => intval($gid),
        );

        $ipatrol[] = array(
            "name" => "ipatrol_similarbot",
            "title" => $lang->ipatrol_similarbot_title,
            "description" => $lang->ipatrol_similarbot_desc,
            "optionscode" => "onoff",
            "value" => '1',
            "disporder" => '9',
            "gid" => intval($gid),
        );

        $ipatrol[] = array(
            "name" => "ipatrol_simstrength",
            "title" => $lang->ipatrol_simstrength_title,
            "description" => $lang->ipatrol_simstrength_desc,
            "optionscode" => "numeric",
            "value" => '70',
            "disporder" => '10',
            "gid" => intval($gid),
        );

        $ipatrol[] = array(
            "name" => "ipatrol_mailalert",
            "title" => $lang->ipatrol_mailalert_title,
            "description" => $lang->ipatrol_mailalert_desc,
            "optionscode" => "yesno",
            "value" => '1',
            "disporder" => '11',
            "gid" => intval($gid),
        );

        foreach ($ipatrol as $ipatrol_opt) {
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
        find_replace_templatesets('online_row_ip', '#{\$lookup}#', '<!-- iPatrol --><a href="#" class="iPatrol_locate" data-ip="{$user[\'ip\']}">[Locate]</a><!-- /iPatrol --> {$lookup}');
    }

    function ipatrol_deactivate()
    {
        require MYBB_ROOT . "/inc/adminfunctions_templates.php";
        find_replace_templatesets('online_row_ip', '#\<!--\siPatrol\s--\>(.+)\<!--\s/iPatrol\s--\>\s#is', '', 0);

    }

    function ipatrol_uninstall()
    {
        global $db, $cache;

        $db->delete_query('templategroups', "prefix='iPatrol'");
        $db->delete_query('templates', "title LIKE 'iPatrol_%'");

        $db->drop_table('ipatrol_actlog');
        $cache->delete('ipatrol_apiresponses');
        $cache->delete('ipatrol_bottrap');
        $db->delete_query("settings", "name LIKE '%ipatrol%'");
        $db->delete_query("settinggroups", "name='ipatrol'");

        rebuild_settings();
    }

    function ipatrol_settingspeekers(&$peekers)
    {
        $peekers[] = 'new Peeker($(".setting_ipatrol_noregdupe"), $("#row_setting_ipatrol_skipregdupe"),/1/,true)';
        $peekers[] = 'new Peeker($(".setting_ipatrol_detectbot"), $("#row_setting_ipatrol_autoaddbot"),/1/,true)';
        $peekers[] = 'new Peeker($(".setting_ipatrol_autoaddbot"), $("#row_setting_ipatrol_uashortbot"),/1/,true)';
        $peekers[] = 'new Peeker($(".setting_ipatrol_similarbot"), $("#row_setting_ipatrol_simstrength"),/1/,true)';
    }
} else {
    $plugins->add_hook('xmlhttp', 'ipatrol_fetchdetails');
    $plugins->add_hook('online_start', 'ipatrol_jsinject');
    $plugins->add_hook('global_start', 'ipatrol_bot_trap');
    $plugins->add_hook('global_end', 'ipatrol_ban_proxy');
    $plugins->add_hook('member_do_register_start', 'ipatrol_ban_regdupe');

    function ipatrol_fetchdetails()
    {
        global $mybb, $lang, $templates, $theme;
        $lang->load('ipatrol');
        switch ($mybb->input['action']) {
            case 'iplocate':
                if (!verify_post_check($mybb->get_input('my_post_key'), true)) {
                    xmlhttp_error($lang->invalid_post_code);
                }

                if (!$mybb->usergroup['canuseipsearch']) {
                    xmlhttp_error($lang->permission_error);
                }
                $locate_details = "";
                $alt = 2;
                $api_response = json_decode(ipatrol_apicall($mybb->get_input('ip'), $mybb->get_input('fields')), true);
                if ($api_response['status'] == 'success') {
                    unset($api_response['status'], $api_response['query']);
                    foreach ($api_response as $locate_item => $locate_detail) {
                        if (!empty($locate_detail)) {
                            $alt = $alt == 1 ? 2 : 1;
                            $altbg = 'trow' . $alt;
                            $locate_item = 'api_' . $locate_item;
                            $locate_item = $lang->$locate_item;
                            $locate_details .= eval($templates->render('iPatrol_locate_valid_row'));
                        }
                    }
                } else {
                    // API error phases : private range, reserved range, invalid query
                    $error_note = str_replace(' ', '_', $api_response['message']);
                    $error_note = $lang->$error_note;
                    $locate_details = eval($templates->render('iPatrol_locate_error_row'));
                }
                eval("\$locate = \"" . $templates->get("iPatrol_locate_frame") . "\";");
                die($locate);
                break;
        }
    }

    function ipatrol_ban_proxy()
    {
        global $mybb;
        
        // IP Ban the user using Proxy
        if ($mybb->settings['ipatrol_banproxy']) {
            // Don't try to track real IP using get_ip(), we need to punish presented IP
            $ip = my_strtolower(trim($_SERVER['REMOTE_ADDR']));

            if (!is_banned_ip($ip)) { // Also check with some whitelist for already passed IPs
                $response = ipatrol_apicall($ip, 'proxy');
                if (!empty($response) && json_decode($response, true)['proxy']) {
                    // Ban this IP
                    ipatrol_ip_ban($ip);
                    ipatrol_publish('ip_proxyban', ['ip' => $ip]);

                    // Redirect immediately to trap the user with IP ban notice
                    header("Location: {$mybb->settings['bburl']}");
                }
            }
        }
    }

    function ipatrol_bot_trap()
    {echo preg_quote("{$user['ip']}");
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
                        $similar_spiders = $db_insert = array();
                        $bot_name = $CrawlerDetect->getMatches();
                        $db_insert['acton_name'] = $bot_name;
                        $db_insert['acton_uas'] = $u_agent;
                        $db_insert['acton_on'] = TIME_NOW;

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
                        // ['notified', 'similar', 'added']
                        if (count($similar_spiders)) {
                            $db_insert['acton_notify'] = 1;
                            // Send mail
                            if ($mybb->settings['ipatrol_mailalert']) {
                                $mail_to = empty($mybb->settings['returnemail']) ? $mybb->settings['adminemail'] : $mybb->settings['returnemail'];
                                $mail_matter = $lang->newcrawler_detected;
                                $mail_matter .= " " . $lang->sprintf($lang->newcrawler_detail, $bot_name, $u_agent);
                                $mail_matter .= " " . $lang->sprintf($lang->newcrawler_similar, count($similar_spiders));
                                $mail_matter .= " " . $lang->newcrawler_notify;
                                my_mail(trim($mail_to), $lang->sprintf($lang->newcrawler_subject, $mybb->settings['bbname']), $mail_matter);
                            }
                            // Log here
                        } else {
                            if ($mybb->settings['ipatrol_autoaddbot']) {
                                $alert_message = $lang->newcrawler_autoadd;
                                $insert = array(
                                    'name' => $bot_name,
                                    'useragent' => strtolower($mybb->settings['ipatrol_uashortbot'] ? $bot_name : $u_agent),
                                    'lastvisit' => TIME_NOW,
                                );

                                $db->insert_query("spiders", $insert);
                                $cache->update_spiders();
                                $db_insert['acton_notify'] = 2;
                            } else {
                                $db_insert['acton_notify'] = 0;
                                $alert_message = $lang->newcrawler_notify;
                            }

                            //file_put_contents('skipbot.txt', $u_agent . "\n", FILE_APPEND | LOCK_EX); // Add to CACHE INSTEAD
                            // Send mail
                            if ($mybb->settings['ipatrol_mailalert']) {
                                $mail_to = empty($mybb->settings['returnemail']) ? $mybb->settings['adminemail'] : $mybb->settings['returnemail'];
                                $mail_matter = $lang->sprintf($lang->newcrawler_detected, $bot_name, $u_agent);
                                $mail_matter .= $alert_message;
                                my_mail(trim($mail_to), $lang->sprintf($lang->newcrawler_autoadd_subject, $mybb->settings['bbname']), $mail_matter);
                            }
                        }

                        $logged[] = $u_agent;
                        $cache->update('ipatrol_bottrap', $logged);
                        $db->insert_query('ipatrol_actlog', $db_insert);
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

    function ipatrol_ban_regdupe()
    {
        global $mybb;
        // Restrict registration of user havind an account already with same IP
        if ($mybb->settings['ipatrol_noregdupe']) {
        }
    }

    function ipatrol_apicall($ip, $fields = '')
    {
        global $cache;
        $prepatrol = $cache->read('ipatrol_apiresponses');
        $cid = array_search($ip, array_column($prepatrol, 'query'));
        if ((isset($cid) && $cid === 0) || !empty($cid)) {
            $response = $prepatrol[$cid];
        } else {
            $stream = stream_context_create(array(
                'http' => array(
                    'timeout' => 3, // Timeout in seconds
                ),
            ));

            $response = @file_get_contents("http://ip-api.com/json/" . $ip . "?fields=786431", 0, $stream); // Fetch all data for reference
            $response = json_decode($response, true);
            if (!json_last_error() == 0) {
                // Return response failed / not in expected JSON format
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

    // Action: [0 => 'No action, only notified', 1 => 'Added to database', 2 => 'Action from IP address blocked.', 3 => 'IP address banned.'];
    // Notification: [0 => 'No Notification, Only Log', 1 => 'PM Notification', 2 => 'Email Notification'];
    function ipatrol_publish($action, $data = array())
    {
        if (!empty($data)) {
            global $mybb, $lang;
            $lang->load('ipatrol');

            //$mailbody = $lang->sprintf($lang->newcrawler_detected, $data['botname'], $data['botua']);
            switch ($action) {
                case 'newcrawler_similar':
                    break;

                case 'newcrawler_autoadd':
                    break;

                case 'newcrawler_notify':
                    break;

                case 'ip_proxyban':
                    $mailbody = $lang->sprintf($lang->ip_proxyban_mailbody, $mybb->settings['bbname'], $data['ip']);
                    break;
            }

            // Send mail
            if ($mybb->settings['ipatrol_mailalert']) {
                $mail_to = empty($mybb->settings['returnemail']) ? $mybb->settings['adminemail'] : $mybb->settings['returnemail'];
                $subject = $action . '_subject';
                my_mail(trim($mail_to), $lang->sprintf($lang->$subject, $mybb->settings['bbname']), $mailbody);
            }

            // Notify in-site

            // Log action
        }
    }

    function ipatrol_jsinject()
    {
        global $mybb, $headerinclude;
        $headerinclude .= "
        <script type='text/javascript'>
        $(function(){
            $('.iPatrol_locate').on('click', function(e){
                e.preventDefault();
                MyBB.popupWindow('/xmlhttp.php?action=iplocate&my_post_key='+my_post_key+'&ip='+$(this).data('ip'));
            })
        });
        </script>";
    }
}
