<?php
$rootPath =  realpath('../../');

if(!function_exists('getXML'))
{
	require_once($rootPath.'/admin/inc/common.php');
	
}

if(!isset($thisfile)) { $plugin_file = GSPLUGINPATH.'blog.php'; } else { $plugin_file =  $thisfile; }

define('BLOGFILE', $plugin_file);
define('BLOGSETTINGS', GSDATAOTHERPATH  . 'blog_settings.xml');
define('BLOGCATEGORYFILE', GSDATAOTHERPATH  . 'blog_categories.xml');
define('BLOGRSSFILE', GSDATAOTHERPATH  . 'blog_rss.xml');
define('BLOGPLUGINFOLDER', GSPLUGINPATH.'blog/');
define('BLOGPOSTSFOLDER', GSDATAPATH.'blog/');
define('BLOGCACHEFILE', GSDATAOTHERPATH  . 'blog_cache.xml');
?>