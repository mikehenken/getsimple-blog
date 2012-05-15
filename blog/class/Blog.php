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
				echo '<div class="updated">data/blog_posts Directory Succesfully Created</div>';
			}
			else
			{
				echo '<div class="error"><strong>The data/blog_posts folder could not be created!</strong><br/>You are going to have to create this directory yourelf for the plugin to work properly</div>';
			}
		}
		if(!file_exists(BLOGCATEGORYFILE))
		{
			$xml = new SimpleXMLExtended('<?xml version="1.0"?><item></item>');
			$create_category_file = XMLsave($xml, BLOGCATEGORYFILE);
			if($create_category_file)
			{
				echo '<div class="updated">data/other/blog_categories.xml Directory Succesfully Created</div>';
			}
			else
			{
				echo '<div class="error"><strong>The data/blog_posts folder could not be created!</strong><br/>You are going to have to create this directory yourelf for the plugin to work properly</div>';
			}
		}
		if(!file_exists(BLOGRSSFILE))
		{
			$xml = new SimpleXMLExtended('<?xml version="1.0"?><item></item>');
			$create_rss_file = XMLsave($xml, BLOGRSSFILE);
			if($create_rss_file)
			{
				echo '<div class="updated">data/other/blog_rss.xml File Succesfully Created</div>';
			}
			else
			{
				echo '<div class="error"><strong>The data/rss file could not be created!</strong><br/>You are going to have to create this directory yourelf for the plugin to work properly</div>';
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
			$create_rss_file = $this->saveSettings($blog_url, $language, $excerpt_length, $show_excerpt, $posts_per_page, $recent_posts, $pretty_urls, $auto_importer, $auto_importer_pass, $display_tags);
			if($create_rss_file)
			{
				echo '<div class="updated">data/other/blog_rss.xml File Succesfully Created</div>';
			}
			else
			{
				echo '<div class="error"><strong>The data/rss file could not be created!</strong><br/>You are going to have to create this directory yourelf for the plugin to work properly</div>';
			}
		}
	}

	/** 
	* Lists All Blog Posts
	* 
	* @return array the filenames & paths of all posts
	*/  
	public function listPosts()
	{
		$all_posts = glob(BLOGPOSTSFOLDER . "/*.xml");
		if(count($all_posts) < 1)
		{
			return false;
		}
		else
		{
			return $all_posts;
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
		if($post_data['time'] != '')
		{
			$date = date('r', $post_data['time']);
		} 
		else
		{
			$date = date('r');
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
		    $timestamp = strtotime($post_data['date'] . ' ' . $post_data['time']);
		    $date = $timestamp ? date('r', $timestamp) : date('r');
		    $tags = str_replace(array(' ', ',,'), array('', ','), safe_slash_html($post_data['tags']));
		if (! XMLsave($xml, $file))
		{
			return false;
		}
		else
		{
			return true;
		}

	}

	/** 
	* Deletes a blog post
	* 
	* @param $post_id id of the blog post to delete
	* @todo  create function :)
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
	* @param $blog_url the page to display the bog
	* @param $language the language of the plugin
	* @param $excerpt_length the length of the excerpt
	* @param $show_excerpt whether or not the excerpt should be displayed
	* @param $posts_per_page the amount of posts per page
	* @param $recent_posts amount of recent posts that should be displayed 
	* @param $pretty_urls whether fancy urls should be supported
	* @param $auto_importer whether auto importer is enabled
	* @param $auto_importer_pass passphrase for auto importer
	* @param $display_tags whether to display tags in post summary and on post details page
	* @return string bool
	*/  
	public function saveSettings($blog_url, $language, $excerpt_length, $show_excerpt, $posts_per_page, $recent_posts, $pretty_urls, $auto_importer, $auto_importer_pass, $display_tags, $rss_title='', $rss_description='')
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
		$xml->addChild('rsstitle', $rss_title);
		$xml->addChild('rssdescription', $rss_description);
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
							'time' => '',
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
	* 
	* 
	* @return
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
	* 
	* 
	* @return
	*/  
	public function blog_create_slug($str) 
	{
		$str = to7bit($str, 'UTF-8');
		$str = clean_url($str);
		return $str;
	}

	/** 
	* 
	* 
	* @return
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
	* @return
	*/  
	public function create_excerpt($content, $start, $maxchars)
	{
		$content = substr($content, $start, $maxchars);
		$pos = strrpos($content, " ");
		if ($pos>0) 
		{
			$content = substr($content, $start, $pos);
		}
		$content = html_entity_decode(strip_tags(strip_decode($content)));
		$content = str_replace("Read The Full", "", $content);
		return $content;
	}

	/** 
	* 
	* 
	* @return
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
	* 
	* 
	* @return
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
	* @return bool
	*/  
	public function generateRSSFeed()
	{
		global $SITEURL;
		$RSSString                              = "";
		$RSSString                              .= "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
		$RSSString                              .= "<rss version=\"2.0\"  xmlns:atom=\"http://www.w3.org/2005/Atom\">\n";
		$RSSString                              .= "<channel>\n";
		$RSSString                              .= "<title>".$this->getSettingsData("rsstitle")."</title>\n";
		$RSSString                              .= "<link>".$SITEURL."rss.rss</link>\n";
		$RSSString                              .= "<description>".$this->getSettingsData("rssdescription")."</description>\n";
		$RSSString                              .= "<lastBuildDate>".date("D, j M Y H:i:s T")."</lastBuildDate>\n";
		$RSSString                              .= "<language>".str_replace("_", "-",$this->getSettingsData("lang"))."</language>\n";

		$post_array = glob(BLOGPOSTSFOLDER . "/*.xml");
		$limit = "5";
		array_multisort(array_map('filemtime', $post_array), SORT_DESC, $post_array); 
		$post_array = array_slice($post_array, 0, $limit);

		foreach ($post_array as $filename) 
		{
			$blog_post = simplexml_load_file($filename);
			$RSSDate    = $blog_post->date;
			$RSSTitle   = $blog_post->title;
			$RSSBody 	= html_entity_decode(str_replace("&nbsp;", " ", substr(htmlspecialchars(strip_tags($blog_post->content)),0,200)));
			$ID 		= str_replace("../data/posts/", "", $filename);
			$ID                                     = str_replace(".xml", "", $ID);
			$RSSString .= "<item>\n";
			$RSSString .= "\t  <title>".$RSSTitle."</title>\n";
			$RSSString .= "\t  <link>".$this->get_blog_url('post').$ID."</link>\n";
			$RSSString .= "\t  <guid>".$this->get_blog_url('post').$ID."</guid>\n";
			$RSSString .= "\t  <description>".$RSSBody."</description>\n";
			$RSSString .= "</item>\n";
		}

		$RSSString  .= '<atom:link href="'.$SITEURL."rss.rss\" rel=\"self\" type=\"application/rss+xml\" />\n";
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
}