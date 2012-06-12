<?php
/** 
* The Blog Cass
* Handles all major methods
* 
* @return void 
*/  
class Blog 
{
	/** 
	* Construct
	* Creates data/blog_posts directory if it is not already created.
	* Creates blog category file if it is not yet created
	* Creates blog settings file if it is not yet created
	* Crates blog rss feed auto importer file if it is not yet created
	* 
	* @return void
	*/  
	public function __construct()
	{
		//Create data/blog_posts directory
		if(!file_exists(BLOGPOSTSFOLDER))
		{
			$create_post_path = mkdir(BLOGPOSTSFOLDER);
			if($create_post_path)
			{
				echo '<div class="updated">'.i18n_r(BLOGFILE.'/DATA_BLOG_DIR').'</div>';
			}
			else
			{
				echo '<div class="error"><strong>'.i18n_r(BLOGFILE.'/DATA_BLOG_DIR_ERR').'</strong><br/>'.i18n_r(BLOGFILE.'/DATA_BLOG_DIR_ERR_HINT').'</div>';
			}
		}
		if(!file_exists(BLOGCATEGORYFILE))
		{
			$xml = new SimpleXMLExtended('<?xml version="1.0"?><item></item>');
			$create_category_file = XMLsave($xml, BLOGCATEGORYFILE);
			if($create_category_file)
			{
				echo '<div class="updated">'.i18n_r(BLOGFILE.'/DATA_BLOG_CATEGORIES').'</div>';
			}
			else
			{
				echo '<div class="error"><strong>'.i18n_r(BLOGFILE.'/DATA_BLOG_CATEGORIES_ERR').'</strong></div>';
			}
		}
		if(!file_exists(BLOGRSSFILE))
		{
			$xml = new SimpleXMLExtended('<?xml version="1.0"?><item></item>');
			$create_rss_file = XMLsave($xml, BLOGRSSFILE);
			if($create_rss_file)
			{
				echo '<div class="updated">'.i18n_r(BLOGFILE.'/DATA_BLOG_RSS').'</div>';
			}
			else
			{
				echo '<div class="error"><strong>'.i18n_r(BLOGFILE.'/DATA_BLOG_RSS_ERR').'</strong></div>';
			}
		}
		if(!file_exists(BLOGSETTINGS))
		{
			$blog_url = "index";
			$language = "en_US";
			$excerpt_length = '350';
			$show_excerpt = 'Y';
			$posts_per_page = '8';
			$recent_posts = '4';
			$pretty_urls = 'N';
			$auto_importer = 'N';
			$auto_importer_pass = 'passphrase';
			$display_tags = 'Y';
			$create_rss_file = $this->saveSettings($blog_url, $language, $excerpt_length, $show_excerpt, $posts_per_page, $recent_posts, $pretty_urls, $auto_importer, $auto_importer_pass, $display_tags, '', '', '', '', '', '', '', '', '','', '', '', '', '', '', '', i18n_r(BLOGFILE.'/NEWER_POSTS'), i18n_r(BLOGFILE.'/OLDER_POSTS'));
			if($create_rss_file)
			{
				echo '<div class="updated">'.i18n_r(BLOGFILE.'/BLOG_SETTINGS').' '.i18n_r(BLOGFILE.'/WRITE_OK').'</div>';
			}
			else
			{
				echo '<div class="error"><strong>'.i18n_r(BLOGFILE.'/BLOG_SETTINGS').' '. i18n_r(BLOGFILE.'/DATA_FILE_ERROR').'</strong></div>';
			}
		}
	}

	/** 
	* Lists All Blog Posts
	* 
	* @param $array bool if true an array containing each posts filename and publish date will be returned instead of only the filename
	* @param $sort_dates bool if true the posts array will be sorted by post date -- THIS REQUIRES $array param TO BE TRUE
	* @return array the filenames & paths of all posts
	*/  
	public function listPosts($array=false, $sort_dates=false)
	{
		$all_posts = glob(BLOGPOSTSFOLDER . "/*.xml");
		if(count($all_posts) < 1)
		{
			return false;
		}
		else
		{
			$count = 0;			
			if($array==false)
			{
				return $all_posts;
			}
			else
			{
				foreach($all_posts as $post)
				{
					$data = getXML($post);
					$posts[$count]['filename'] = $post;
					$posts[$count]['date'] = (string) $data->date;
					$count++;
				}
				if($sort_dates != false && $array != false)
				{
					usort($posts, array($this, 'sortDates'));  
				}
				return $posts;
			}
		}
	}

	/** 
	* Get Data From Settings File
	* 
	* @param $field the node of the setting to retrieve
	* @return string requested blog settings data
	*/  
	public function getSettingsData($field)
	{
		$settingsData = getXML(BLOGSETTINGS);
		if(is_object($settingsData->$field))
		{
			return $settingsData->$field;	
		}
		else
		{
			return false;
		}
	}

	/** 
	* Get A Blog Post
	* 
	* @param $post_id the filename of the blog post to retrieve
	* @return array blog xml data
	*/  
	public function getPostData($post_id)
	{
		$post = getXML($post_id);
		return $post;
	}

	/** 
	* Saves a post submitted from the admin panel
	* 
	* @param $post_data the post data (eg: 'XML_FIELD_NAME => $POSTDATA')
	* @todo clean up this method... Not happy about it's messiness!
	* @return bool
	*/  
	public function savePost($post_data)
	{
		if ($post_data['slug'] != '')
		{
			$slug = $this->blog_create_slug($post_data['slug']);
		}
		else
		{
			$slug = $this->blog_create_slug($post_data['title']);
		}
		$file = BLOGPOSTSFOLDER . "$slug.xml";
		if($post_data['current_slug'] == '' || $post_data['current_slug'] != $post_data['slug'])
		{
			# delete old post file
			if ($post_data['current_slug'] != '')
			{
				unlink(BLOGPOSTSFOLDER . $post_data['current_slug'] . '.xml');
			}
			# do not overwrite existing files
			if (file_exists($file)) 
			{
				$count = 0;
				while(file_exists($file))
				{
					$file = BLOGPOSTSFOLDER . "$slug-" . ++$count . '.xml';
					$slug .= "-$count";
				}
			}
		}
		else
		{
			unlink(BLOGPOSTSFOLDER . $post_data['current_slug'] . '.xml');
		}


		if($post_data['date'] != '')
		{
			$date = $post_data['date'];
		} 
		else
		{
			$date = date('m/d/Y h:i:s a', time());
		}
		if($post_data['tags'] != '')
		{
			$tags = str_replace(array(' ', ',,'), array('', ','),$post_data['tags']);
		}
		else
		{
			$tags = '';
		}

		$xml = new SimpleXMLExtended('<?xml version="1.0"?><item></item>');
		foreach($post_data as $key => $value)
		{
			if($key == 'current_slug' || $key == 'time')
			{

			}
			elseif($key == 'slug')
			{
				$node = $xml->addChild($key);
				$node->addCData($slug);
			}
			elseif($key == 'title')
			{
				$title = safe_slash_html($value);
				$node = $xml->addChild($key);
				$node->addCData($title);
			}
			elseif($key == 'date')
			{
				$node = $xml->addChild($key);
				$node->addCData($date);
			}
			elseif($key == 'content')
			{
  			  $content = safe_slash_html($value);
				$node = $xml->addChild($key);
				$node->addCData($content);
			}
			elseif($key == 'tags')
			{
				$node = $xml->addChild($key);
				$node->addCData($tags);
			}
			else
			{
				$node = $xml->addChild($key);
				$node->addCData($value);
			}
		}
		    $tags = str_replace(array(' ', ',,'), array('', ','), safe_slash_html($post_data['tags']));
		if (! XMLsave($xml, $file))
		{
			return false;
		}
		else
		{
			$this->createPostsCache();
			return true;
		}

	}

	/** 
	* Deletes a blog post
	* 
	* @param $post_id id of the blog post to delete
	* @return bool
	*/  
	public function deletePost($post_id)
	{
		if(file_exists(BLOGPOSTSFOLDER.$post_id.'.xml'))
		{
			$delete_post = unlink(BLOGPOSTSFOLDER.$post_id.'.xml');
			if($delete_post)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	/** 
	* Saves category added or edited
	* 
	* @param $category the category name
	* @param $existing whether the category exists already
	* @todo  use $existing param to edit a category instead of deleteing it. This would also need to go through and change the category for any posts using the edited category
	* @return bool
	*/  
	public function saveCategory($category, $existing=false)
	{
		$category_file = getXML(BLOGCATEGORYFILE);
		$xml = new SimpleXMLExtended('<?xml version="1.0"?><item></item>');
		foreach($category_file->category as $ind_category)
		{
			$xml->addChild('category', $ind_category);
		}
		$xml->addChild('category', $category);
		$add_category = XMLsave($xml, BLOGCATEGORYFILE);
		if($add_category)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/** 
	* Deletes a category
	* 
	* @param $catgory Category to delete
	* @return bool
	*/  
	public function deleteCategory($category)
	{
		$category_file = getXML(BLOGCATEGORYFILE);
		$xml = new SimpleXMLExtended('<?xml version="1.0"?><item></item>');
		foreach($category_file->category as $ind_category)
		{
			if($ind_category == $category)
			{
				//Do Nothing (Deletes Category)
			}
			else
			{
				$xml->addChild('category', $ind_category);
			}
		}
		$delete_category = XMLsave($xml, BLOGCATEGORYFILE);
		if($delete_category)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/** 
	* Saves RSS feed added or edited
	* 
	* @param $new_rss array all of the posts data
	* @param $existing whether the rss is new
	* @todo  posssible add functionality of editing a feed using the $existing param. Not sure if this is even needed
	* @return bool
	*/  
	public function saveRSS($new_rss, $existing=false)
	{
		$rss_file = getXML(BLOGRSSFILE);
		$xml = new SimpleXMLExtended('<?xml version="1.0"?><item></item>');
		$count = 0;
		foreach($rss_file->rssfeed as $rss_feed)
		{
			$rss_atts = $rss_feed->attributes();
			$rss = $xml->addChild('rssfeed');

			$rss->addAttribute('id', $count);

			$rss_name = $rss->addChild('feed');				
			$rss_name->addCData($rss_feed->feed);
			
			$rss_category = $rss->addChild('category');	
			$rss_category->addCData($rss_feed->category);
			$count++;
		}
		$newfeed = $xml->addChild('rssfeed');
		$newfeed->addAttribute('id', $count);
		$newfeed_name = $newfeed->addChild('feed');
		$newfeed_name->addCData($new_rss['name']);
		$newfeed_category = $newfeed->addChild('category');
		$newfeed_category->addCData($new_rss['category']);

		$add_rss = XMLsave($xml, BLOGRSSFILE);
		if($add_rss)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/** 
	* Delete RSS Feed
	* 
	* @param $feed_id RSS feed to delete
	* @return bool
	*/  
	public function deleteRSS($feed_id)
	{
		$rss_file = getXML(BLOGRSSFILE);
		$xml = new SimpleXMLExtended('<?xml version="1.0"?><item></item>');
		$count = 0;
		foreach($rss_file->rssfeed as $rss_feed)
		{
			$rss_atts = $rss_feed->attributes();
			if($feed_id == $rss_atts['id'])
			{

			}
			else
			{
				$rss = $xml->addChild('rssfeed');

				$rss->addAttribute('id', $count);

				$rss_name = $rss->addChild('feed');				
				$rss_name->addCData($rss_feed->feed);

				$rss_category = $rss->addChild('category');	
				$rss_category->addCData($rss_feed->category);
			}
			$count++;
		}
		$delete_rss = XMLsave($xml, BLOGRSSFILE);
		if($delete_rss)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/** 
	* Save Blog Plugin Settings
	* 
	* @param $blog_url string the page to display the bog
	* @param $language string the language of the plugin
	* @param $excerpt_length int the length of the excerpt
	* @param $show_excerpt string whether or not the excerpt should be displayed
	* @param $posts_per_page int the amount of posts per page
	* @param $recent_posts int amount of recent posts that should be displayed 
	* @param $pretty_urls string whether fancy urls should be supported
	* @param $auto_importer string whether auto importer is enabled
	* @param $auto_importer_pass string passphrase for auto importer
	* @param $display_tags string whether to display tags in post summary and on post details page
	* @param $rss_title string The title to be displayed in the blog RSS feed
	* @param $rss_description string string The description nto be displayed in the RSS feed
	* @param $comments string Whether comments should be enabled
	* @param $disqus_shortname string The disqus account shortname 
	* @param $disqus_count string Whether to display the disqus post counter on each blog page
	* @param $sharethis string Whether sharethis widget is enabled 
	* @param $sharethis_id string The developer ID for sharethis widget 
	* @param $addthis string Whether addthis widget is enabled 
	* @param $addthis_id string  The developer id for addthis widget
	* @param $ad_data string the advertisement data for blog
	* @param $all_posts_ad_top string Display advertisement at the top of all posts page
	* @param $all_posts_ad_bottom string Display advertisement at the bottom of all posts page
	* @param $post_ad_top string Display individual post top advertisement 
	* @param $post_ad_bottom string Display individual post bottom advertisement 
	* @param string $post_thumbnail Whether posts should have thumbnails enabled - If not Y then even if a post has a thumbnail uploaded, it will not display
	* @param $display_date string Whether post date should be displayed 
	* @param $previous_page string The text for the "Previous Blog Page" link 
	* @param $next_page string The text for the "Next Blog Page" link 
	* @return bool
	*/  
	public function saveSettings($blog_url='', $language='', $excerpt_length='', $show_excerpt='', $posts_per_page='', $recent_posts='', $pretty_urls='', $auto_importer='', $auto_importer_pass='', $display_tags='', $rss_title='', $rss_description='', $comments='', $disqus_shortname='', $disqus_count='', $sharethis='', $sharethis_id='', $addthis='', $addthis_id='', $ad_data='', $all_posts_ad_top='', $all_posts_ad_bottom='', $post_ad_top='', $post_ad_bottom='', $post_thubnail='', $display_date='', $previous_page='', $next_page='')
	{

		$xml = new SimpleXMLExtended('<?xml version="1.0"?><item></item>');
		$xml->addChild('blogurl', $blog_url);
		$xml->addChild('lang', $language);
		$xml->addChild('excerptlength', $excerpt_length);
		$xml->addChild('postformat', $show_excerpt);
		$xml->addChild('postperpage', $posts_per_page);
		$xml->addChild('recentposts', $recent_posts);
		$xml->addChild('prettyurls', $pretty_urls);
		$xml->addChild('autoimporter', $auto_importer);
		$xml->addChild('autoimporterpass', $auto_importer_pass);
		$xml->addChild('displaytags', $display_tags);
		$xml->addChild('displaydate', $display_date);
		$xml->addChild('rsstitle', $rss_title);
		$xml->addChild('rssdescription', $rss_description);
		$xml->addChild('comments', $comments);
		$xml->addChild('disqusshortname', $disqus_shortname);
		$xml->addChild('disquscount', $disqus_count);
		$xml->addChild('sharethis', $sharethis);
		$xml->addChild('sharethisid', $sharethis_id);
		$xml->addChild('addthis', $addthis);
		$xml->addChild('addthisid', $addthis_id);
		$xml->addChild('addata', $ad_data);
		$xml->addChild('allpostsadtop', $all_posts_ad_top);
		$xml->addChild('allpostsadbottom', $all_posts_ad_bottom);
		$xml->addChild('postadtop', $post_ad_top);
		$xml->addChild('postadbottom', $post_ad_bottom);
		$xml->addChild('postthumbnail', $post_thubnail);
		$xml->addChild('previouspage', $previous_page);
		$xml->addChild('nextpage', $next_page);
		$blog_settings = XMLsave($xml, BLOGSETTINGS);
		if($blog_settings)
		{
			return true;
		}
		else
		{
			return false;
		}
	}


	/** 
	* Gets fields for blog post xml files
	* 
	* @param $array bool if the xml nodes should be returned as an array (true) or a object (null or false)
	* @todo this function will be very usefull once custom fields are implemented. For now it is here for preparation for the inevitable!
	* @return array xml nodes if $array param is true
	* @return object xml nodes if $array param is false
	*/  
	public function getXMLnodes($array=false)
	{
		$blog_data = array('title' => '',
							'slug' => '',
							'date' => '',
							'private' => '',
							'tags' => '',
							'category' => '',
							'content' => '',
							'excerpt' => '',
							'thumbnail' => '',
							'current_slug' => '',
							);
		if($array == false)
		{
			return $blog_data = (object) $blog_data;
		}
		else
		{
			return $blog_data;
		}
	}

	/** 
	* Generates link to blog or blog area
	* 
	* @param $query string Optionally you can provide the type of blog url you are looking for (eg: 'post', 'category', 'archive', etc..)
	* @return url to requested blog area
	*/  
	public function get_blog_url($query=FALSE) 
	{
		$Blog = new Blog;
		global $SITEURL, $PRETTYURLS;
		$blogurl = $Blog->getSettingsData("blogurl");
		$data = getXML(GSDATAPAGESPATH . $blogurl . '.xml');
		$url = find_url($blogurl, $data->parent);

		if($query) 
		{
			if($PRETTYURLS == 1 && $Blog->getSettingsData("prettyurls") == 'Y')
			{
				$url .= $query . '/';
			}
			elseif($blogurl == 'index')
			{
				$url = $SITEURL . "index.php?$query=";
			}
			else
			{
				$url = $SITEURL . "index.php?id=$blogurl&$query=";
			}
		}
		return $url;
	}

	/** 
	* Creates slug for blog posts
	* 
	* @return string the generated slug
	*/  
	public function blog_create_slug($str) 
	{
		$str = to7bit($str, 'UTF-8');
		$str = clean_url($str);
		return $str;
	}

	/** 
	* Gets available blog plugin langauges
	* 
	* @return array available langauges
	*/  
	public function blog_get_languages() 
	{
		$count = 0;
		foreach(glob(BLOGPLUGINFOLDER."lang/*.php") as $filename)
		{
			$filename = basename(str_replace(".php", "", $filename));
			$languages[$count] = $filename;
			$count++;
		}
		return $languages;
	}

	/** 
	* Create Excerpt for post
	* 
	* @param $content string the content to be excerpted
	* @param $start int the starting character to create excerpt from
	* @param $maxchars int the amount of characters excerpt should be
	* @return string The created excerpt
	*/  
	public function create_excerpt($content, $start, $maxchars)
	{
		$maxchars = (int) $maxchars;
		$content = substr($content, $start, $maxchars);
		$pos = strrpos($content, " ");
		if ($pos>0) 
		{
			$content = substr($content, $start, $pos);
		}
		$content = htmlspecialchars_decode(strip_tags(strip_decode($content)));
		$content = str_replace(i18n_r(BLOGFILE.'/READ_FULL_ARTICLE'), "", $content);
		return $content;
	}

	/** 
	* Gets and sorts archives for blog
	* 
	* @return array archives
	*/  
	public function get_blog_archives() 
	{
		$posts = $this->listPosts();
		$archives = array();
		foreach ($posts as $file) 
		{
			$data = getXML($file);
			$date = strtotime($data->date);
			$title = $this->get_locale_date($date, '%B %Y');
			$archive = date('Ym', $date);
			if (!array_key_exists($archive, $archives))
			{
				$archives[$archive] = $title;
			}
		}
		krsort($archives);
		return $archives;
	}

	/** 
	* Generates search results
	* 
	* @param $keyphrase string the keyphrase to search for
	* @return array Search results
	*/  
	public function searchPosts($keyphrase)
	{
		$keywords = @explode(' ', $keyphrase);
		$posts = $this->listPosts();
		foreach ($keywords as $keyword) 
		{
			$match = array();
			$count = 0;
			foreach ($posts as $file) 
			{
				$data = getXML($file);
				$content = $data->title . $data->content;
				$slug = $data->slug;
				if (stripos($content, $keyword) !== FALSE)
				{
					$match[$count] = $file;
				}

				$count++;
			}
			$posts = $match;
		}
		return $posts;
	}

	/** 
	* get_locale_date
	* @param $timestamp UNIX timestamp
	* @return string date according to lang
	*/  
	public function get_locale_date($timestamp, $format) 
	{
		$locale = setlocale(LC_TIME, NULL);
		setlocale(LC_TIME, $this->getSettingsData("lang"));
		$date = strftime($format, $timestamp);
		setlocale(LC_TIME, $locale);
		return $date;
	}

	/** 
	* Generates RSS Feed of posts
	* 
	* @return bool
	*/  
	public function generateRSSFeed()
	{
		global $SITEURL;

		$RSSString      = "";
		$RSSString     .= "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
		$RSSString     .= "<rss version=\"2.0\"  xmlns:atom=\"http://www.w3.org/2005/Atom\">\n";
		$RSSString     .= "<channel>\n";
		$RSSString     .= "<title>".$this->getSettingsData("rsstitle")."</title>\n";
		$RSSString     .= "<link>".$locationOfFeed."</link>\n";
		$RSSString     .= "<description>".$this->getSettingsData("rssdescription")."</description>\n";
		$RSSString     .= "<lastBuildDate>".date("D, j M Y H:i:s T")."</lastBuildDate>\n";
		$RSSString     .= "<language>".str_replace("_", "-",$this->getSettingsData("lang"))."</language>\n";
		$RSSString     .= '<atom:link href="'.$locationOfFeed."\" rel=\"self\" type=\"application/rss+xml\" />\n";

		$limit = $this->getSettingsData("rssfeedposts");
		array_multisort(array_map('filemtime', $post_array), SORT_DESC, $post_array); 
		$post_array = array_slice($post_array, 0, $limit);

		foreach ($posts as $post) 
		{
			$blog_post = simplexml_load_file($post['filename']);
			$RSSDate    = $blog_post->date;
			$RSSTitle   = $blog_post->title;
			$RSSBody 	= html_entity_decode(str_replace("&nbsp;", " ", substr(htmlspecialchars(strip_tags($blog_post->content)),0,200)));
			$ID 		= $blog_post->slug;
			$RSSString .= "<item>\n";
			$RSSString .= "\t  <title>".$RSSTitle."</title>\n";
			$RSSString .= "\t  <link>".$this->get_blog_url('post').$ID."</link>\n";
			$RSSString .= "\t  <guid>".$this->get_blog_url('post').$ID."</guid>\n";
			$RSSString .= "\t  <description>".htmlspecialchars($RSSBody)."</description>\n";
			if(isset($blog_post->category) and !empty($blog_post->category) and $blog_post->category!='') $RSSString .= "\t  <category>".$blog_post->category."</category>\n";
			$RSSString .= "</item>\n";
		}

		$RSSString .= "</channel>\n";
		$RSSString .= "</rss>\n";

		if(!$fp = fopen(GSROOTPATH."rss.rss",'w'))
		{
			echo "Could not open the rss.rss file";
			exit();
		}
		if(!fwrite($fp,$RSSString))
		{
			echo "Could not write to rss.rss file";
			exit();
		}
		fclose($fp);
	}

	/** 
	* Creates Blog Posts Cache File
	* 
	* @return bool
	*/  
	public function createPostsCache()
	{
		$posts = $this->listPosts(true, true);
		$count = 0;
		$xml = new SimpleXMLExtended('<?xml version="1.0"?><item></item>');
		foreach($posts as $post)
		{
			$data = getXML($post['filename']);
			$new_post = $xml->addChild("post");
			foreach($data as $key => $value)
			{
				$post_parent = $new_post->addChild($key);
				$post_parent->addCData($value);
			}
		}
		$save_cache = XMLsave($xml, BLOGCACHEFILE);
	}

	/** 
	* Sorts dates of blog posts (launched through usort function)
	* 
	* @param $a $b array the data to be sorted (from usort)
	* @return bool
	*/  
	public function sortDates($a, $b)
	{
		$a = strtotime($a['date']); 
		$b = strtotime($b['date']); 
		if ($a == $b) 
		{ 
			return 0; 
		} 
		else
		{  
			if($a<$b) 
			{ 
				return 1; 
			} 
			else 
			{ 
				return -1; 
			} 
		} 
	}
}