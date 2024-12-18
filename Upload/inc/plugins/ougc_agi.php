<?php

/***************************************************************************
 *
 *   ougc Additional Usergroup Images plugin (/inc/plugins/ougc_agi.php)
 *   Author: Omar Gonzalez
 *   Copyright: © 2012-2020 Omar Gonzalez
 *
 *   Website: https://ougc.network
 *
 *   Show additional user group images in profiles and posts.
 *
 ***************************************************************************
 ****************************************************************************
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 ****************************************************************************/

declare(strict_types=1);

// Die if IN_MYBB is not defined, for security reasons.
defined('IN_MYBB') || die('This file cannot be accessed directly.');

global $plugins;

if (defined('IN_ADMINCP')) {
    $plugins->add_hook('admin_config_settings_start', 'ougc_agi_lang_load');
    $plugins->add_hook('admin_style_templates_set', 'ougc_agi_lang_load');
    $plugins->add_hook('admin_config_settings_change', 'ougc_agi_settings_change');
} elseif (defined('THIS_SCRIPT')) {
    switch (THIS_SCRIPT) {
        case 'showthread.php':
        case 'private.php':
        case 'newthread.php':
        case 'newreply.php':
        case 'editpost.php':
        case 'announcements.php':
        case 'member.php':
            global $cache, $templatelist;

            $plugins->add_hook('postbit', 'ougc_agi_run');
            $plugins->add_hook('postbit_pm', 'ougc_agi_run');
            $plugins->add_hook('postbit_prev', 'ougc_agi_run');
            $plugins->add_hook('postbit_announcement', 'ougc_agi_run');
            $plugins->add_hook('member_profile_end', 'ougc_agi_profile');

            if (!isset($templatelist)) {
                $templatelist = '';
            } else {
                $templatelist .= ',';
            }

            $templatelist .= 'ougcagi';

            foreach ((array)$cache->read('usergroups') as $group) {
                $templatelist .= ',ougcagi_' . $group['gid'];
            }
    }
}

// PLUGINLIBRARY
defined('PLUGINLIBRARY') || define('PLUGINLIBRARY', MYBB_ROOT . 'inc/plugins/pluginlibrary.php');

function ougc_agi_info(): array
{
    global $lang;
    ougc_agi_lang_load();

    return [
        'name' => 'ougc Additional Usergroup Images',
        'description' => $lang->setting_group_ougc_agi_desc,
        'website' => 'https://ougc.network',
        'author' => 'Omar G.',
        'authorsite' => 'https://ougc.network',
        'version' => '1.8.38',
        'versioncode' => 1838,
        'compatibility' => '18*',
        'codename' => 'ougc_agi',
        'pl' => [
            'version' => 13,
            'url' => 'https://community.mybb.com/mods.php?action=view&pid=573'
        ]
    ];
}

function ougc_agi_activate(): bool
{
    global $PL, $lang, $cache;

    ougc_agi_lang_load();

    $PL->settings('ougc_agi', $lang->setting_group_ougc_agi, $lang->setting_group_ougc_agi_desc, [
        'groups' => [
            'title' => $lang->setting_ougc_agi_groups,
            'description' => $lang->setting_ougc_agi_groups_desc,
            'optionscode' => 'groupselect',
            'value' => '1,2,5,7',
        ]
    ]);

    $PL->templates('ougcagi', 'ougc Additional Usergroup Images', [
        '' => '{$br_postbit}<img src="{$image}" alt="{$usertitle}" title="{$usertitle}" />{$br_profile}'
    ]);

    // Insert/update version into cache
    $plugins = $cache->read('ougc_plugins');

    if (!$plugins) {
        $plugins = [];
    }

    $info = ougc_agi_info();

    if (!isset($plugins['agi'])) {
        $plugins['agi'] = $info['versioncode'];
    }

    /*~*~* RUN UPDATES START *~*~*/

    /*~*~* RUN UPDATES END *~*~*/

    $plugins['agi'] = $info['versioncode'];

    $cache->update('ougc_plugins', $plugins);

    return true;
}

function ougc_agi_is_installed(): bool
{
    global $cache;

    $plugins = (array)$cache->read('ougc_plugins');

    return !empty($plugins['agi']);
}

function ougc_agi_uninstall(): bool
{
    global $PL, $cache;
    ougc_agi_pl_check();

    /*$PL->settings_delete('ougc_agi');*/
    $PL->templates_delete('ougcagi');

    // Delete version from cache
    $plugins = (array)$cache->read('ougc_plugins');

    if (isset($plugins['agi'])) {
        unset($plugins['agi']);
    }

    if (!empty($plugins)) {
        $cache->update('ougc_plugins', $plugins);
    } else {
        $PL->cache_delete('ougc_plugins');
    }

    return true;
}

function ougc_agi_lang_load(): bool
{
    global $lang;

    isset($lang->setting_group_ougc_agi) || $lang->load('ougc_agi');

    return true;
}

function ougc_agi_pl_check(): bool
{
    global $lang;

    ougc_agi_lang_load();

    $info = ougc_agi_info();

    if (!file_exists(PLUGINLIBRARY)) {
        flash_message($lang->sprintf($lang->ougc_agi_pl_required, $info['pl']['url'], $info['pl']['version']), 'error');

        admin_redirect('index.php?module=config-plugins');

        exit;
    }

    global $PL;

    $PL || require_once PLUGINLIBRARY;

    if ($PL->version < $info['pl']['version']) {
        flash_message(
            $lang->sprintf($lang->ougc_agi_pl_old, $info['pl']['url'], $info['pl']['version'], $PL->version),
            'error'
        );

        admin_redirect('index.php?module=config-plugins');

        exit;
    }

    return true;
}

function ougc_agi_settings_change(): bool
{
    return ougc_agi_lang_load();
}

function ougc_agi_run(array &$post): array
{
    global $mybb, $templates;

    $br_postbit = '';

    $br_profile = '<br />';

    $postbit_tmpl = 'member_profile';

    if (THIS_SCRIPT != 'member.php') {
        if ($mybb->settings['postlayout'] != 'classic') {
            $br_postbit = '<br />';

            $br_profile = '';
        }

        $postbit_tmpl = $mybb->settings['postlayout'] == 'classic' ? 'postbit_classic' : 'postbit';
    }

    $post['ougc_agi'] = '';

    if (empty($post) || $mybb->settings['ougc_agi_groups'] == -1) {
        return $post;
    }

    static $uidscache = [];

    if (!isset($uidscache[$post['uid']])) {
        if (!empty($post['displaygroup'])) {
            $post['usergroup'] = $post['displaygroup'];
        }

        $uidscache[$post['uid']] = [];

        foreach (array_map('intval', explode(',', $post['additionalgroups'])) as $additional_group_id) {
            if ($additional_group_id !== (int)$post['usergroup']) {
                $uidscache[$post['uid']][] = $additional_group_id;
            }
        }
    }

    $usergroups = $uidscache[$post['uid']];

    $usergroups_cache = $mybb->cache->read('usergroups');

    global $theme;

    foreach ($usergroups as $group) {
        if (is_member($mybb->settings['ougc_agi_groups'], ['usergroup' => $group, 'additionalgroups' => ''])) {
            continue;
        }

        $post['ougc_agi_' . $group] = '';

        $displaygroup = $usergroups_cache[$group] ?? [];

        if (!empty($displaygroup['image'])) {
            $language = $mybb->settings['bblanguage'];

            if (!empty($mybb->user['language'])) {
                $language = $mybb->user['language'];
            }

            $usertitle = htmlspecialchars_uni(
                ($displaygroup['usertitle'] ? $displaygroup['usertitle'] : $displaygroup['title'])
            );

            $image = str_replace(
                ['{lang}', '{theme}'],
                [$language, $theme['imgdir']],
                htmlspecialchars_uni($displaygroup['image'])
            );

            $tmpl = isset($templates->cache['ougcagi_' . $group]) ? 'ougcagi_' . $group : 'ougcagi';

            if (
                my_strpos($templates->cache[$postbit_tmpl], "{\$post['ougc_agi_{$group}']}") !== false ||
                my_strpos($templates->cache[$postbit_tmpl], "{\$memprofile['ougc_agi_{$group}']}") !== false
            ) {
                $post["ougc_agi_{$group}"] = eval($templates->render($tmpl));

                continue;
            }

            $post['ougc_agi'] .= eval($templates->render($tmpl));
        }
    }

    return $post;
}

function ougc_agi_profile(): bool
{
    global $memprofile;

    ougc_agi_run($memprofile);

    return true;
}