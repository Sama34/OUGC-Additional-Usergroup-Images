<?php

/***************************************************************************
 *
 *   OUGC Additional Usergroup Images plugin (/inc/plugins/ougc_agi.php)
 *	 Author: Omar Gonzalez
 *   Copyright: © 2012-2020 Omar Gonzalez
 *   
 *   Website: https://ougc.network
 *
 *   Show additional usergroup images in profile and postbit.
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

// Run/Add Hooks
if(defined('IN_ADMINCP'))
{
	$plugins->add_hook('admin_config_settings_start', 'ougc_agi_lang_load');
	$plugins->add_hook('admin_style_templates_set', 'ougc_agi_lang_load');
	$plugins->add_hook('admin_config_settings_change', 'ougc_agi_settings_change');
}
else
{
	if (defined('THIS_SCRIPT'))
	{
		switch(THIS_SCRIPT)
		{
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
				$plugins->add_hook('member_profile_end', 'ougc_agi_run');

				if(!isset($templatelist))
				{
					$templatelist = '';
				}
				else
				{
					$templatelist .= ',';
				}

				$templatelist .= 'ougcagi';

				$usergroups = $cache->read('usergroups');
				foreach((array)$usergroups as $group)
				{
					$templatelist .= ',ougcagi_'.$group['gid'];
				}
				break;
		}
	}
}

// PLUGINLIBRARY
defined('PLUGINLIBRARY') or define('PLUGINLIBRARY', MYBB_ROOT.'inc/plugins/pluginlibrary.php');

// Plugin API
function ougc_agi_info()
{
	global $lang;
	ougc_agi_lang_load();

	return array(
		'name'			=> 'OUGC Additional Usergroup Images',
		'description'	=> $lang->setting_group_ougc_agi_desc,
		'website'		=> 'https://ougc.network',
		'author'		=> 'Omar G.',
		'authorsite'	=> 'https://ougc.network',
		'version'		=> '1.8.21',
		'versioncode'	=> 1821,
		'compatibility'	=> '18*',
		'pl'			=> array(
			'version'	=> 13,
			'url'		=> 'https://community.mybb.com/mods.php?action=view&pid=573'
		)
	);
}

// _activate() routine
function ougc_agi_activate()
{
	global $PL/*, $lang*/, $cache;
	ougc_agi_lang_load();
	ougc_agi_deactivate();

	// Add settings group
	$PL->settings('ougc_agi', $lang->setting_group_ougc_agi, $lang->setting_group_ougc_agi_desc, array(
		'groups'	=> array(
		   'title'			=> $lang->setting_ougc_agi_groups,
		   'description'	=> $lang->setting_ougc_agi_groups_desc,
		   'optionscode'	=> 'groupselect',
			'value'			=>	'1,2,5,7',
		)
	));

	// Add template group
	$PL->templates('ougcagi', '<lang:setting_group_ougc_agi>', array(
		''	=> '{$br_postbit}<img src="{$image}" alt="{$usertitle}" title="{$usertitle}" />{$br_profile}'
	));

	// Modify templates
	require_once MYBB_ROOT.'inc/adminfunctions_templates.php';
	find_replace_templatesets('postbit', '#'.preg_quote('{$post[\'groupimage\']}').'#', '{$post[\'groupimage\']}{$post[\'ougc_agi\']}');
	find_replace_templatesets('postbit_classic', '#'.preg_quote('{$post[\'groupimage\']}').'#', '{$post[\'groupimage\']}{$post[\'ougc_agi\']}');
	find_replace_templatesets('member_profile', '#'.preg_quote('{$groupimage}').'#', '{$groupimage}{$memprofile[\'ougc_agi\']}');

	// Insert/update version into cache
	$plugins = $cache->read('ougc_plugins');
	if(!$plugins)
	{
		$plugins = array();
	}

	$info = ougc_agi_info();

	if(!isset($plugins['agi']))
	{
		$plugins['agi'] = $info['versioncode'];
	}

	/*~*~* RUN UPDATES START *~*~*/

	/*~*~* RUN UPDATES END *~*~*/

	$plugins['agi'] = $info['versioncode'];
	$cache->update('ougc_plugins', $plugins);
}

// _deactivate() routine
function ougc_agi_deactivate()
{
	ougc_agi_pl_check();

	// Revert template edits
	require_once MYBB_ROOT.'inc/adminfunctions_templates.php';
	find_replace_templatesets('postbit', '#'.preg_quote('{$post[\'ougc_agi\']}').'#', '', 0);
	find_replace_templatesets('postbit_classic', '#'.preg_quote('{$post[\'ougc_agi\']}').'#', '', 0);
	find_replace_templatesets('member_profile', '#'.preg_quote('{$memprofile[\'ougc_agi\']}').'#', '', 0);
}

// _is_installed() routine
function ougc_agi_is_installed()
{
	global $cache;

	$plugins = (array)$cache->read('ougc_plugins');

	return !empty($plugins['agi']);
}

// _uninstall() routine
function ougc_agi_uninstall()
{
	global $PL, $cache;
	ougc_agi_pl_check();

	/*$PL->settings_delete('ougc_agi');*/
	$PL->templates_delete('ougcagi');

	// Delete version from cache
	$plugins = (array)$cache->read('ougc_plugins');

	if(isset($plugins['agi']))
	{
		unset($plugins['agi']);
	}

	if(!empty($plugins))
	{
		$cache->update('ougc_plugins', $plugins);
	}
	else
	{
		$PL->cache_delete('ougc_plugins');
	}
}

// Loads language strings
function ougc_agi_lang_load()
{
	global $lang;

	isset($lang->setting_group_ougc_agi) or $lang->load('ougc_agi');
}

// PluginLibrary dependency check & load
function ougc_agi_pl_check()
{
	global $lang;
	ougc_agi_lang_load();
	$info = ougc_agi_info();

	if(!file_exists(PLUGINLIBRARY))
	{
		flash_message($lang->sprintf($lang->ougc_agi_pl_required, $info['pl']['url'], $info['pl']['version']), 'error');
		admin_redirect('index.php?module=config-plugins');
		exit;
	}

	global $PL;

	$PL or require_once PLUGINLIBRARY;

	if($PL->version < $info['pl']['version'])
	{
		flash_message($lang->sprintf($lang->ougc_agi_pl_old, $info['pl']['url'], $info['pl']['version'], $PL->version), 'error');
		admin_redirect('index.php?module=config-plugins');
		exit;
	}
}

// Language support for settings
function ougc_agi_settings_change()
{
	global $db, $mybb;

	$query = $db->simple_select('settinggroups', 'name', 'gid=\''.(int)$mybb->input['gid'].'\'');
	$groupname = $db->fetch_field($query, 'name');
	if($groupname == 'ougc_agi')
	{
		global $plugins;
		ougc_agi_lang_load();
	}
}

// Show additional group images routine
function ougc_agi_run(&$post)
{
	global $mybb, $memprofile, $templates;

	$br_postbit = '';
	$br_profile = '<br />';
	$var = 'memprofile';
	$postbit_tmpl = 'member_profile';
	if(!empty($post))
	{
		if($mybb->settings['postlayout'] != 'classic')
		{
			$br_postbit = '<br />';
			$br_profile = '';
		}
		$var = 'post';

		$postbit_tmpl = $mybb->settings['postlayout'] == 'classic' ? 'postbit_classic' : 'postbit';
	}

	if(empty(${$var}) || $mybb->settings['ougc_agi_groups'] == -1)
	{
		return;
	}

	${$var}['ougc_agi'] = '';

	static $uidscache = array();
	if(!isset($uidscache[${$var}['uid']]))
	{
		${$var}['additionalgroups'] = explode(',', ${$var}['additionalgroups']);

		if(!empty(${$var}['displaygroup']))
		{
			${$var}['usergroup'] = ${$var}['displaygroup'];
		}

		foreach(${$var}['additionalgroups'] as $key => $val)
		{
			if($val == ${$var}['usergroup'])
			{
				unset(${$var}['additionalgroups'][$key]);
			}
		}

		$uidscache[${$var}['uid']] = (array)${$var}['additionalgroups'];
	}
	$usergroups = $uidscache[${$var}['uid']];

	$usergroups_cache = $mybb->cache->read('usergroups');
	foreach($usergroups as $group)
	{
		if(is_member($mybb->settings['ougc_agi_groups'], array('usergroup' => $group)))
		{
			continue;
		}

		${$var}['ougc_agi_'.$group] = '';

		$displaygroup = $usergroups_cache[$group];
		if(!empty($displaygroup['image']))
		{
			$language = $mybb->settings['bblanguage'];
			if(!empty($mybb->user['language']))
			{
				$language = $mybb->user['language'];
			}

			$usertitle = htmlspecialchars_uni(($displaygroup['usertitle'] ? $displaygroup['usertitle'] : $displaygroup['title']));
			$image = str_replace(array('{lang}', '{theme}'), array($language, $theme['imgdir']), htmlspecialchars_uni($displaygroup['image']));

			$tmpl = isset($templates->cache['ougcagi_'.$group]) ? 'ougcagi_'.$group : 'ougcagi';

			if(my_strpos($templates->cache[$postbit_tmpl], '{$'.$var.'[\'ougc_agi_'.$group.'\']}') !== false)
			{
				eval('$'.$var.'[\'ougc_agi_'.$group.'\'] .= "'.$templates->get($tmpl).'";');
				continue;
			}

			eval('$'.$var.'[\'ougc_agi\'] .= "'.$templates->get($tmpl).'";');
		}
	}
}
