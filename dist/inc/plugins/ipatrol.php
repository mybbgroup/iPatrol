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
} else {
    $plugins->add_hook('xmlhttp', 'ipatrol_api_call');
    $plugins->add_hook('global_start', 'ipatrol_bot_trap');
    $plugins->add_hook('global_end', 'ipatrol_ban_proxy');
    $plugins->add_hook('member_do_register_start', 'ipatrol_ban_dupereg');
}

function ipatrol_info()
{
    return array(
        'name' => 'iPatrol',
        'description' => "IP Police",
        'website' => 'https://demonate.club/thread-1665.html',
        'author' => 'effone</a> of <a href="https://github.com/mybbgroup">MyBBGroup</a>',
        'authorsite' => 'https://eff.one',
        'version' => '1.0.0',
        'compatibility' => '18*',
        'codename' => 'ipatrol',
    );
}

function ipatrol_activate()
{
    global $db;
    // Build Plugin Settings
    $ipatrol_group = array(
        "name" => "ipatrol",
        "title" => "iPatrol",
        "description" => "A Plugin for MyBB to take better control over visitors.",
        "disporder" => "9",
        "isdefault" => "0",
    );
    $db->insert_query("settinggroups", $ipatrol_group);
    $gid = $db->insert_id();

    $ipatrol[] = array(
        "name" => "ipatrol_locateuser",
        "title" => "Locate users based on their IP",
        "description" => "Allow admins and mods with permission to fetch various details of the user based on their IP.",
        "optionscode" => "onoff",
        "value" => '1',
        "disporder" => '1',
        "gid" => intval($gid),
    );

    $ipatrol[] = array(
        "name" => "ipatrol_banproxy",
        "title" => "Ban users using proxy",
        "description" => "Ban the IP address of the users who use proxy to access the site.",
        "optionscode" => "onoff",
        "value" => '1',
        "disporder" => '2',
        "gid" => intval($gid),
    );

    $ipatrol[] = array(
        "name" => "ipatrol_banregdupe",
        "title" => "Ban duplicate user registration",
        "description" => "Ban the IP address of the users who already has an account registered with same IP.",
        "optionscode" => "onoff",
        "value" => '0',
        "disporder" => '3',
        "gid" => intval($gid),
    );

    $ipatrol[] = array(
        "name" => "ipatrol_skipregdupe",
        "title" => "Exclude groups from banning",
        "description" => "Skip banning the IP address if the user belongs to the group.",
        "optionscode" => "groupselect",
        "value" => '1,3,4',
        "disporder" => '4',
        "gid" => intval($gid),
    );

    $ipatrol[] = array(
        "name" => "ipatrol_detectbot",
        "title" => "Automatically detect spiders visiting your site",
        "description" => "Detect new / unregistered spiders that MyBB is considering as guest.",
        "optionscode" => "onoff",
        "value" => '1',
        "disporder" => '5',
        "gid" => intval($gid),
    );

    $ipatrol[] = array(
        "name" => "ipatrol_autoaddbot",
        "title" => "Add the detected spider to database",
        "description" => "Update spider database so that MyBB detects it and the new spider name can show up in online list.",
        "optionscode" => "onoff",
        "value" => '1',
        "disporder" => '6',
        "gid" => intval($gid),
    );

    $ipatrol[] = array(
        "name" => "ipatrol_uashortbot",
        "title" => "Use the keyword for User Agent String",
        "description" => "Using keyword in place of whole user agent string has a greater chance to detect similar strings.",
        "optionscode" => "radio\n0=Save full user agent string\n1=Save keyword only",
        "value" => '1',
        "disporder" => '7',
        "gid" => intval($gid),
    );

    $ipatrol[] = array(
        "name" => "ipatrol_similarbot",
        "title" => "Check Similar Named Spiders",
        "description" => "Find for similar named spiders and if exists just notify for manual action. Setting this on will not add the spider in the database in case of a match.",
        "optionscode" => "onoff",
        "value" => '1',
        "disporder" => '8',
        "gid" => intval($gid),
    );

    $ipatrol[] = array(
        "name" => "ipatrol_simstrength",
        "title" => "Spider name existance match strength",
        "description" => "The % strength of the matching. A lower value has a chance of detecting more matches but with less efficiency. 100% means will catch only exact matches. If you are unsure about it leave with default value (40%).",
        "optionscode" => "numeric",
        "value" => '40',
        "disporder" => '9',
        "gid" => intval($gid),
    );

    $ipatrol[] = array(
        "name" => "ipatrol_mailalert",
        "title" => "Send mail notification",
        "description" => "Alert sending a mail to board email whenever iPatrol commits an action.",
        "optionscode" => "yesno",
        "value" => '1',
        "disporder" => '10',
        "gid" => intval($gid),
    );

    foreach ($ipatrol as $ipatrol_opt) {
        $db->insert_query("settings", $ipatrol_opt);
    }

    rebuild_settings();
}
function ipatrol_deactivate()
{
    global $db;
    $db->delete_query("settings", "name LIKE '%ipatrol%'");
    $db->delete_query("settinggroups", "name='ipatrol'");

    rebuild_settings();
}

function ipatrol_settingspeekers(&$peekers)
{
    $peekers[] = 'new Peeker($(".setting_ipatrol_banregdupe"), $("#row_setting_ipatrol_skipregdupe"),/1/,true)';
    $peekers[] = 'new Peeker($(".setting_ipatrol_detectbot"), $("#row_setting_ipatrol_autoaddbot"),/1/,true)';
    $peekers[] = 'new Peeker($(".setting_ipatrol_autoaddbot"), $("#row_setting_ipatrol_uashortbot"),/1/,true)';
    $peekers[] = 'new Peeker($(".setting_ipatrol_similarbot"), $("#row_setting_ipatrol_simstrength"),/1/,true)';
}

function ipatrol_api_call()
{
    global $mybb, $lang;
    //$lang->load('ipatrol');

    switch ($mybb->input['action']) {
        case 'get_iplocation':
            if (!verify_post_check($mybb->get_input('my_post_key'), true)) {
                xmlhttp_error($lang->invalid_post_code);
            }

            if (!$mybb->usergroup['canuseipsearch']) {
                xmlhttp_error($lang->permission_error);
            }

            $response = file_get_contents("http://ip-api.com/json/" . $mybb->get_input('ip') . "?fields=" . $mybb->get_input('fields'));
            json_decode($response);
            if (!json_last_error() == 0) {
                xmlhttp_error($lang->invalid_response);
            }
            header("Content-type: application/json; charset={$charset}");
            die($response);
            break;

        default:
            xmlhttp_error($lang->unknown_error);
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
            $stream = stream_context_create(array(
                'http' => array(
                    'timeout' => 3, // Timeout in seconds
                ),
            ));
            $response = @file_get_contents("http://ip-api.com/json/" . $ip . "?fields=proxy", 0, $stream);

            if (!empty($response) && json_decode($response, true)['proxy']) {
                // Ban this IP
                ipatrol_ip_ban($ip);
                my_mail('me@eff.one', 'Proxy IP banned @ Demonate', 'Banned IP: ' . $ip);

                // Redirect immediately to trap the user with IP ban notice
                header("Location: {$mybb->settings['bburl']}");
            }
        }
    }
}

function ipatrol_bot_trap()
{
    global $db, $mybb, $lang, $cache;
    
    if ($mybb->settings['ipatrol_detectbot'] && !$mybb->user['uid']) {

        $logged = file('skipbot.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $query = $db->simple_select("sessions", "useragent", "sid NOT LIKE'%bot%' AND UID = '0'");
        while ($skip = $db->fetch_array($query)) {
            $u_agent = trim($skip['useragent']);
            if (!empty($u_agent) && !in_array($u_agent, $logged)) { // && preg_match($match_params, $u_agent)

                // Load the detector library
                $lib_files = ['CrawlerDetect', 'AbstractProvider', 'Crawlers', 'Exclusions', 'Headers'];
                foreach ($lib_files as $i => $file) {
                    $file = $i ? 'Fixtures/' . $file : $file;
                    require_once MYBB_ROOT . '/inc/3rdparty/crawlerdetect/' . $file . '.php';
                }
                $CrawlerDetect = new Jaybizzle\CrawlerDetect\CrawlerDetect;

                if ($CrawlerDetect->isCrawler($u_agent)) {
                    $bot_name = $CrawlerDetect->getMatches();
                    $similar_spiders = array();

                    if ($mybb->settings['ipatrol_similarbot']) {
                        $registered_spiders = $cache->read('spiders');
                        $match_power = (int)$mybb->settings['ipatrol_simstrength'];
                        $match_power = (!$match_power || $match_power < 1) ? 40 : (($match_power > 100) ? 100 : $match_power);

                        foreach ($registered_spiders as $registered_spider) {
                            similar_text($bot_name, $registered_spider['name'], $match);
                            if ($match >= $match_power) {
                                $similar_spiders[] = $registered_spider['name'];
                            }
                        }
                    }

                    if (count($similar_spiders)) {
                        print_r($similar_spiders);
                        // Send mail
                        if($mybb->settings['ipatrol_mailalert']){
                            $mail_matter = "A new spider visit has been detected. New spider is '".$bot_name."' having a user agent string ".$u_agent." The detected spider's name is similat to ".count($similar_spiders)." existing spider/s.";
                            my_mail($mybb->settings['adminemail'], 'Spider detected @ '.$mybb->settings['bbname'],  $mail_matter);
                        }
                        // Log here
                    } else {
                        if($mybb->settings['ipatrol_autoaddbot']){
                            $alert_message = "The detected spider is added to the database.";
                            $insert = array(
                                'name' => $bot_name,
                                'useragent' => strtolower($bot_name), //$u_agent,
                                'lastvisit' => TIME_NOW,
                            );

                            $db->insert_query("spiders", $insert);
                            $cache->update_spiders();
                        } else {                            
                            $alert_message = "This is for your information and further necessary manual action.";
                        }
                        
                        file_put_contents('skipbot.txt', $u_agent . "\n", FILE_APPEND | LOCK_EX); // Add to CACHE INSTEAD
                        // Send mail
                        if($mybb->settings['ipatrol_mailalert']){
                            $mail_matter = "A new spider visit has been detected. New spider is '".$bot_name."' having a user agent string ".$u_agent." ".$alert_message;
                            my_mail($mybb->settings['adminemail'], 'Spider detected @ '.$mybb->settings['bbname'],  $mail_matter);
                        }
                        // Log here
                    }
                }
            }
        }
    }
}

function ipatrol_ip_ban($ip)
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

function ipatrol_ban_dupereg()
{
    global $mybb;
    // IP Ban the user havind an account already with same IP
    if ($mybb->settings['ipatrol_banregdupe']) {
    }
}