<?php
$i18n = array(
	'PLUGIN_TITLE' 			=> "Blog Manager",
	'PLUGIN_DESC' 			=> "Manage a blog",
	'PLUGIN_SIDE' 			=> "Blog Manager",
	'WRITE_OK' 				=> "File Succesfully Written",
	'EDIT_OK' 				=> "File Successfully Edited",
	'POST_DELETED' 			=> "Post Successfully Deleted",
	'POST_DELETED_ERROR' 	=> "Post Could Not Be Deleted!",
	'CATDELETED' 			=> "Category Successfully Deleted",
	'CATCREATED' 			=> "Successfully Created",
	'HELP' 					=> ">Help",
	'ALL_FAQ' 				=> ">All Posts",
	'POST_SLUG'				=> "Slug/URL",
	'POST_TAGS'				=> "Tags (sepaarate tags with commas)",
	'POST_DATE'				=> "Publish date (mm/dd/yyyy) and time (hh:mm)",
	'POST_PRIVATE'			=> "Post is private",
	'POST_OPTIONS'			=> "Post Option",
	'POST_CATEGORY'			=> "Assign This Post To A Category",
	'SAVE_POST'				=> "Save Post",
	'CANCEL'				=> "Cancel",
	'DELETE'				=> "Delete",
	'OR'					=> "Or",
	'PAGE_URL'				=> "Page to display blog posts",
	'EXCERPT_LENGTH'		=> "Length of excerpt (characters):",
	'LANGUAGE'				=> "Language",
	'POSTS_PER_PAGE'		=> "&num; of posts per page",
	'RECENT_POSTS'			=> "&num; of recent posts",
	'EXCERPT_OPTION'		=> "Posts format on posts page",
	'EXCERPT'				=> "Excerpt",
	'PRETTY_URLS'			=> "Use pretty urls",
	'PRETTY_URLS_PARA'		=> "If Yes, you will have to edit your .htaccess file after saving settings",
	'SAVE_SETTINGS'			=> "Save Settings",
	'BLOG_SETTINGS'			=> "Blog Settings",
	'FULL_TEXT'				=> "Full Text",
	'RSS_IMPORTER'			=> "Enable RSS Auto Importer",
	'RSS_IMPORTER_PASS'		=> "Auto Importer Password (anything)",
	'YES'					=> "Yes",
	'NO'					=> "No",
	'NO_POSTS'				=> "No Posts",
	'OLDER_POSTS'			=> "Older Posts",
	'NEWER_POSTS'			=> "Newer Posts",
	'ALL_CATEOGIRES'		=> "View All Categories",
	'SEARCH'				=> "Search Blog",
	'FOUND'					=> "The following posts have been found:",
	'NOT_FOUND'				=> "Sorry, no posts were found.",
	'POST_DATE'				=> "Slug/URL",
	'CATEGORIES' 			=> "Categories",
	'VIEW_ALL' 				=> "View All",
	'VIEW' 					=> "View",
	'ADD_P' 				=> "Add New Post",
	'EDIT_CONTENT' 			=> "Edit Content: ",
	'EDIT' 					=> "Edit ",
	'POST' 					=> "Post",
	'DEL_CONTENT' 			=> "Delete Content", 
	'QUESTIONS' 			=> "Posts",
	'TITLE' 				=> "Title...", 
	'CHOOSECAT' 			=> "Choose Category...",
	'ADD_CONTENT' 			=> "Add Content",
	'MANAGECAT' 			=> "Manage Categories",
	'ADD_NCAT' 				=> "Add New Category",
	'ADD_CAT' 				=> "Add Category", 
	'CAT_TITLE' 			=> "Category Title...", 
	'DEL_CAT1' 				=> "Delete Category: ",
	'DEL_CAT2' 				=> "?? This Will Delete ALL* content In The Category As Well!",
	'CONTENT' 				=> "content",
	'YR_CATNAME' 			=> "Your Category Name",
	'HELP' 					=> "Help", 
	'SETTINGS' 				=> "Settings", 
	'RSS_FEEDS' 			=> "RSS Feeds", 
	'CATEGORIES' 			=> "Categories", 
	'CREATE_POST' 			=> "Create Post", 
	'MANAGE_POSTS' 			=> "Manage Posts", 
	'CATEGORY_ADDED' 		=> "Successfully Added Category", 
	'CATEGORY_ERROR' 		=> "Category Could Not Be Saved", 
	'FEED_ADDED' 			=> "Successfully Added RSS Feed", 
	'FEED_ERROR' 			=> "RSS Feed Could Not Be Saved", 
	'FEED_DELETED' 			=> "Successfully Deleted Feed", 
	'FEED_DELETE_ERROR' 	=> "Feed Could Not Be Deleted", 
	'AUTO_IMPORTER_TITLE' 	=> "RSS Feed Auto Importer Cronjob Setup", 
	'AUTO_IMPORTER_DESC' 	=> "You should be able to setup cronjobs through your web hosting admin interface. This plugin assume you know how. The below snippet is what your cron job should look like this. <br/><strong>lynx -dump http://yourdomain.com.com/index.php?blogslug&import=your_rss_import_password_above > /dev/null</strong>", 
	'ADD_FEED' 				=> "Add RSS Feed", 
	'MANAGE_FEEDS' 			=> "Add &amp; Manage RSS Feeds", 
	'ADD_NEW_FEED' 			=> "Add New RSS Feed", 
	'BLOG_CATEGORY' 		=> "Blog Category", 
	'CATEGORY_NAME' 		=> "Category Name", 
	'ADD_CATEGORY' 			=> "Add New Category", 
	'MANAGE_CATEGORIES' 	=> "Add &amp; Manage Categories", 
	'RSS_FEED' 				=> "RSS Feed", 
	'FEED_CATEGORY' 		=> "RSS Feed Category", 
	'DELETE_FEED' 			=> "Delete Feed", 
	'TAGS' 					=> "Tags", 
	'DISPLAY_TAGS_UNDER_POST' => "Display tags under post?", 
	'POST_ADDED' 			=> "Successfully Saved Post", 
	'POST_ERROR' 			=> "Post Could Not Be Saved", 
	'HELP_CATEGORIES' 		=> "Display blog categories", 
	'HELP_SEARCH' 			=> "Display blog search bar", 
	'HELP_ARCHIVES' 		=> "Display blog archives", 
	'HELP_RECENT' 			=> "Show your blogs most recent posts", 
	'RSS_TITLE' 			=> "RSS Feed Title", 
	'RSS_DESCRIPTION' 		=> "RSS Feed Description", 
	'RSS_LOCATION' 			=> "Below is your blogs RSS feed", 
	'POST_THUMBNAIL' 		=> "Enable Post Thumbnail", 
	//Added version 1.0.2 
	'NO_POSTS' 		=> "There are no posts found", 
	'CLICK_TO_CREATE' 		=> "Click Here To Create One", 
	'PAGE_TITLE' 			=> "Page Title", 
	'DATE' 					=> "Date", 
	'FRONT_END_FUNCTIONS' 	=> "Front End Functions", 
	'READ_FULL_ARTICLE' 	=> "Read The Full Article", // Used for rss feed importer
	'GO_BACK' 				=> "Go back to the previous page", 
	'' 		=> "", 
	'' 		=> "", 
	'' 		=> "", 
	//Error/Success Messages  (_construct())
	'DATA_BLOG_DIR' 		=> "data/blog Directory Succesfully Created", 
	'DATA_BLOG_DIR_ERR' 	=> "The data/blog_posts folder could not be created!", 
	'DATA_BLOG_DIR_ERR_HINT' => "You are going to have to create this directory yourelf for the plugin to work properly", 
	'DATA_BLOG_CATEGORIES' 	=> "data/other/blog_categories.xml Directory Succesfully Created", 
	'DATA_BLOG_CATEGORIES_ERR' => "data/other/blog_categories.xml folder could not be created!", 
	'DATA_BLOG_RSS' 		=> "data/other/blog_rss.xml File Succesfully Created", 
	'DATA_BLOG_RSS_ERR' 	=> "The data/other/blog_rss.xml file could not be created!",
	//ADDED VERSION 1.1 
	'DISPLAY_DISQUS_COMMENTS' => "Display Disqus comments",
	'DISPLAY_DISQUS_COUNT' 	=> "Display Disqus comment count",
	'DISQUS_SHORTNAME' 	=> "Disqus Shortname",
	'SOCIAL_SETTINGS' 	=> "Social Settings",
	'RSS_FILE_SETTINGS' 	=> "Your Blog's RSS Feed Settings",
	'ENABLE_ADD_THIS' 	=> "Enable AddThis tool",
	'ENABLE_SHARE_THIS' 	=> "Enable ShareThis tool",
	'SHARE_THIS_ID' 	=> "ShareThis ID",
	'ADD_THIS_ID' 	=> "AddThis ID",
	'AD_TITLE' 	=> "Advertisement Settings",
	'AD_DATA' 	=> "Advertisement Code",
	'DISPLAY_ALL_POSTS_AD_TOP' 	=> "Enable all posts top ad",
	'DISPLAY_ALL_POSTS_AD_BOTTOM' 	=> "Enable all posts bottom ad",
	'DISPLAY_POST_AD_TOP' 	=> "Enable individual post top ad",
	'DISPLAY_POST_AD_BOTTOM' 	=> "Enable individual post bottom ad"
);