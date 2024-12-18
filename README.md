<h3 align="center">ougc Additional Usergroups Images</h3>

<div align="center">

[![Status](https://img.shields.io/badge/status-active-success.svg)]()
[![GitHub Issues](https://img.shields.io/github/issues/OUGC-Network/ougc-Additional-Usergroup-Images.svg)](./issues)
[![GitHub Pull Requests](https://img.shields.io/github/issues-pr/OUGC-Network/ougc-Additional-Usergroup-Images.svg)](./pulls)
[![License](https://img.shields.io/badge/license-GPL-blue)](/LICENSE)

</div>

---

<p align="center"> Show additional user group images in profiles and posts.
    <br> 
</p>

## 📜 Table of Contents <a name = "table_of_contents"></a>

- [About](#about)
- [Getting Started](#getting_started)
    - [Dependencies](#dependencies)
    - [File Structure](#file_structure)
    - [Install](#install)
    - [Update](#update)
    - [Template Modifications](#template_modifications)
- [Settings](#settings)
- [Templates](#templates)
- [Usage](#usage)
- [Built Using](#built_using)
- [Authors](#authors)
- [Acknowledgments](#acknowledgement)
- [Support & Feedback](#support)

## 🚀 About <a name = "about"></a>

Show additional user group images in profiles and posts.

[Go up to Table of Contents](#table_of_contents)

## 📍 Getting Started <a name = "getting_started"></a>

The following information will assist you into getting a copy of this plugin up and running on your forum.

### Dependencies <a name = "dependencies"></a>

A setup that meets the following requirements is necessary to use this plugin.

- [MyBB](https://mybb.com/) >= 1.8
- PHP >= 7
- [MyBB-PluginLibrary](https://github.com/frostschutz/MyBB-PluginLibrary) >= 13

### File structure <a name = "file_structure"></a>

  ```
   .
   ├── inc
   │ ├── languages
   │ │ ├── english
   │ │ │ ├── admin
   │ │ │ │ ├── ougc_agi.lang.php
   │ │ ├── espanol
   │ │ │ ├── admin
   │ │ │ │ ├── ougc_agi.lang.php
   │ ├── plugins
   │ │ ├── ougc_agi.php
   ```

### Installing <a name = "install"></a>

Follow the next steps in order to install a copy of this plugin on your forum.

1. Download the latest package from the [MyBB Extend](https://community.mybb.com/mods.php) site or
   from the [repository releases](https://github.com/OUGC-Network/ougc-Additional-Usergroup-Images/releases/latest).
2. Upload the contents of the _Upload_ folder to your MyBB root directory.
3. Browse to _Configuration » Plugins_ and install this plugin by clicking _Install & Activate_.
4. Browse to _Settings_ to manage the plugin settings.

### Updating <a name = "update"></a>

Follow the next steps in order to update your copy of this plugin.

1. Browse to _Configuration » Plugins_ and deactivate this plugin by clicking _Deactivate_.
2. Follow step 1 and 2 from the [Install](#install) section.
3. Browse to _Configuration » Plugins_ and activate this plugin by clicking _Activate_.
4. Browse to _Settings_ to manage the plugin settings.

### Template Modifications <a name = "template_modifications"></a>

It is required that you edit the following template for each of your themes.

1. Place `{$post['ougc_agi']}` after `{$post['groupimage']}` in the `postbit` and `postbit_classic` templates to
   display all additional user group images in posts.
2. Place `{$memprofile['ougc_agi']}` after `{$groupimage}` in the `member_profile` template to display all additional
   user group images in posts.

[Go up to Table of Contents](#table_of_contents)

## 🛠 Settings <a name = "settings"></a>

Below you can find a description of the plugin settings.

### Main Settings

- **Ignored Groups** `numeric`
    - _Select the groups for which additional group image will not be generated._

[Go up to Table of Contents](#table_of_contents)

## 📐 Templates <a name = "templates"></a>

The following is a list of templates available for this plugin.

- `ougcagi`
    - _front end_;

[Go up to Table of Contents](#table_of_contents)

## 📖 Usage <a name="usage"></a>

It is possible to display additional group images on a per group basis, instead of all-in-one place or together.

1. Place `{$post["ougc_agi_X"]}` in the `postbit` and `postbit_classic` templates to display a specific additional user
   group image in posts.
2. Place `{$memprofile["ougc_agi_X"]}` in the `member_profile` template to display a specific additional user group
   image in profiles.

Where `X` is the group ID.

It is also possible to use a custom template for each group, by creating a custom template for any group with the
following name:

- `ougcagi_X`
    - _front end_;

Where `X` is the group ID.

[Go up to Table of Contents](#table_of_contents)

## ⛏ Built Using <a name = "built_using"></a>

- [MyBB](https://mybb.com/) - Web Framework
- [MyBB PluginLibrary](https://github.com/frostschutz/MyBB-PluginLibrary) - A collection of useful functions for MyBB
- [PHP](https://www.php.net/) - Server Environment

[Go up to Table of Contents](#table_of_contents)

## ✍️ Authors <a name = "authors"></a>

- [@Omar G](https://github.com/Sama34) - Idea & Initial work

See also the list of [contributors](https://github.com/OUGC-Network/ougc-Additional-Usergroup-Images/contributors) who
participated in
this
project.

[Go up to Table of Contents](#table_of_contents)

## 🎉 Acknowledgements <a name = "acknowledgement"></a>

- [The Documentation Compendium](https://github.com/kylelobo/The-Documentation-Compendium)

[Go up to Table of Contents](#table_of_contents)

## 🎈 Support & Feedback <a name="support"></a>

This is free development and any contribution is welcome. Get support or leave feedback at the
official [MyBB Community](https://community.mybb.com/thread-159249.html).

Thanks for downloading and using our plugins!

[Go up to Table of Contents](#table_of_contents)