<?php

/***************************************************************************
 *
 *   OUGC Additional Usergroup Images plugin
 *	 Author: Omar Gonzalez
 *   Copyright: © 2012 Omar Gonzalez
 *   
 *   Website: http://omarg.me
 *
 *   This plugin will allow you to show additional usergroup images in profile and postbit.
 *
 ***************************************************************************
 
****************************************************************************
	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
****************************************************************************/

// Die if IN_MYBB is not defined, for security reasons.
defined('IN_MYBB') or die('This file cannot be accessed directly.');

// Run the ACP hooks.
if(!defined('IN_ADMINCP') && defined('THIS_SCRIPT'))
{
	$tmplcache = false;
	switch(THIS_SCRIPT)
	{
		case 'showthread.php':
		case 'private.php':
		case 'newthread.php':
		case 'newreply.php':
		case 'editpost.php':
		case 'announcements.php':
			$tmplcache = true;
			$plugins->add_hook('postbit', 'ougc_agi_postbit');
			$plugins->add_hook('postbit_pm', 'ougc_agi_postbit');
			$plugins->add_hook('postbit_prev', 'ougc_agi_postbit');
			$plugins->add_hook('postbit_announcement', 'ougc_agi_postbit');
			break;
		case 'member.php':
			global $mybb;

			if($mybb->input['action'] == 'profile')
			{
				$tmplcache = true;
				$plugins->add_hook('member_profile_end', 'ougc_agi_profile');
			}
			break;
	}
	if($tmplcache)
	{
		global $templatelist;
		if(isset($mybb->cache) && is_object($mybb->cache))
		{
			$cache = &$mybb->cache;
		}
		else
		{
			global $cache;
		}

		$usergroups = $cache->read('usergroups');
		foreach((array)$usergroups as $group)
		{
			$templatelist .= ", postbit_groupimage_{$group['gid']}";
		}
	}
}

// Array of information about the plugin.
function ougc_agi_info()
{
	global $lang;
	isset($lang->ougc_plugin_title) or $lang->load('ougc_agi');

	return array(
		'name'			=> 'OUGC Additional Usergroup Images',
		'description'	=> $lang->ougc_plugin_desc,
		'website'		=> 'http://udezain.com.ar/',
		'author'		=> 'Omar G.',
		'authorsite'	=> 'http://udezain.com.ar/',
		'version'		=> '1.0',
		'guid' 			=> '',
		'compatibility' => '16*'
	);
}

// _activate() routine
function ougc_agi_activate()
{
	global $db, $lang;
	isset($lang->ougc_plugin_title) or $lang->load('ougc_agi');
	ougc_agi_deactivate();

	$gid = $db->insert_query('settinggroups',array(
		'name'			=> 'ougc_agi',
		'title'			=> $db->escape_string($lang->ougc_agi_settints),
		'description'	=> $db->escape_string($lang->ougc_agi_settints_desc),
		'disporder'		=> 999,
		'isdefault'		=> 'no'
	));
	$db->insert_query('settings',array(
		'name'			=> 'ougc_agi_power',
		'title'			=> $db->escape_string($lang->ougc_agi_power),
		'description'	=> $db->escape_string($lang->ougc_agi_power_desc),
		'optionscode'	=> 'onoff',
		'value'			=> '0',
		'disporder'		=> 1,
		'gid'			=> intval($gid)
	));
	$db->insert_query('settings',array(
		'name'			=> 'ougc_agi_groups',
		'title'			=> $db->escape_string($lang->ougc_agi_groups),
		'description'	=> $db->escape_string($lang->ougc_agi_groups_desc),
		'optionscode'	=> 'text',
		'value'			=> '1,2,5,7',
		'disporder'		=> 2,
		'gid'			=> intval($gid)
	));
	rebuild_settings();
	require_once MYBB_ROOT.'/inc/adminfunctions_templates.php';
	find_replace_templatesets('postbit', '#'.preg_quote('{$post[\'groupimage\']}').'#', '{$post[\'groupimage\']}{$post[\'ougc_agis\']}');
	find_replace_templatesets('postbit_classic', '#'.preg_quote('{$post[\'groupimage\']}').'#', '{$post[\'groupimage\']}{$post[\'ougc_agis\']}');
	find_replace_templatesets('member_profile', '#'.preg_quote('{$groupimage}').'#', '{$groupimage}{$memprofile[\'ougc_agis\']}');
}

// _deactivate() routine
function ougc_agi_deactivate()
{
	global $db;
	$gid = $db->fetch_field($db->simple_select('settinggroups', 'gid', 'name="ougc_agi"'), 'gid');
	if($gid)
	{
		$db->delete_query("settings", "gid='{$gid}'");
		$db->delete_query("settinggroups", "gid='{$gid}'");
		rebuild_settings();
	}
	require_once MYBB_ROOT.'/inc/adminfunctions_templates.php';
	find_replace_templatesets('postbit', '#'.preg_quote('{$post[\'ougc_agis\']}').'#', '', 0);
	find_replace_templatesets('postbit_classic', '#'.preg_quote('{$post[\'ougc_agis\']}').'#', '', 0);
	find_replace_templatesets('member_profile', '#'.preg_quote('{$memprofile[\'ougc_agis\']}').'#', '', 0);
}

// Show group iamges in posts.
function ougc_agi_profile()
{
	global $mybb;

	if($mybb->settings['ougc_agi_power'] == 1)
	{
		global $memprofile, $templates;
		$memprofile['ougc_agis'] = '';

		// Figure out the member display group.
		$usergroups = ougc_agi_getgroups($memprofile['usergroup'], $memprofile['displaygroup'], $memprofile['additionalgroups']);
	
		// Lets do it...
		$usergroups_cache = $GLOBALS['cache']->read('usergroups');
		$excludedgroups = explode(',', $mybb->settings['ougc_agi_groups']);
		foreach($usergroups as $group)
		{
			$displaygroup = $usergroups_cache[$group];
			if(!empty($displaygroup['image']))
			{
				$usertitle = htmlspecialchars_uni(($displaygroup['usertitle'] ? $displaygroup['usertitle'] : $displaygroup['title']));
				$displaygroup['image'] = htmlspecialchars_uni($displaygroup['image']);
				if(!in_array($group, $excludedgroups))
				{
					eval('$memprofile[\'ougc_agis\'] .= "'.$templates->get('member_profile_groupimage').'";');
				}
				else
				{
					$memprofile['ougc_agis'.$group] = '';
					if($templates->cache['member_profile_groupimage_'.$group])
					{
						eval("\$memprofile['ougc_agis{$group}'] = \"".$templates->get('member_profile_groupimage_'.$group)."\";");
					}
				}
			}
		}
	}
}

// Show group images in posts.
function ougc_agi_postbit(&$post)
{
	global $mybb;

	if($mybb->settings['ougc_agi_power'] == 1)
	{
		global $templates;
		$post['ougc_agis'] = '';

		// Figure out the member display group.
		$usergroups = ougc_agi_getgroups($post['usergroup'], $post['displaygroup'], $post['additionalgroups']);
	
		// Lets do it...
		$usergroups_cache = $GLOBALS['cache']->read('usergroups');
		$excludedgroups = explode(',', $mybb->settings['ougc_agi_groups']);
		foreach($usergroups as $group)
		{
			$usergroup = $usergroups_cache[$group];
			if(!empty($usergroup['image']))
			{
				$usertitle = htmlspecialchars_uni(($usergroup['usertitle'] ? $usergroup['usertitle'] : $usergroup['title']));
				$usergroup['image'] = htmlspecialchars_uni($usergroup['image']);
	
				if(!in_array($group, $excludedgroups))
				{
					eval('$post[\'ougc_agis\'] .= "'.$templates->get('postbit_groupimage').'";');
				}
				else
				{
					$post['ougc_agis'.$group] = '';
					if($templates->cache['postbit_groupimage_'.$group])
					{
						eval("\$post['ougc_agis{$group}'] = \"".$templates->get('postbit_groupimage_'.$group)."\";");
					}
				}
			}
		}
	}
}

// Get a array of gids.
function ougc_agi_getgroups($usergroup, $displaygroup, $additionalgroups)
{
	$additionalgroups = explode(',', $additionalgroups);

	// Remove display group from list.
	if($displaygroup)
	{
		$usergroup = $displaygroup;
	}
	unset($additionalgroups[$usergroup]);

	return (is_array($additionalgroups) ? $additionalgroups : array());
}