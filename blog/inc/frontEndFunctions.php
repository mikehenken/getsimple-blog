<?php
/** 
* show individual blog post
* 
* @param $slug slug of post to display
* @param $excerpt bool Whether an excerpt should be displayed. It would be false or null if a user was on the blog details page rather then a results or list all page
* @return void
*/  
function show_blog_post($slug, $excerpt=false)
{
	$Blog = new Blog;
	global $SITEURL, $blogSettings, $post;
	$post = getXML($slug);
	$url = $Blog->get_blog_url('post').$post->slug;
	$date = $Blog->get_locale_date(strtotime($post->date), '%b %e, %Y');
	if($blogSettings["customfields"] != 'Y')
	{
		if(isset($_GET['post']) && $blogSettings["postadtop"] == 'Y')
		{
			?>
			<div class="blog_all_posts_ad">
				<?php echo $blogSettings["addata"]; ?>
			</div>
			<?php
		}
		if(isset($_GET['post']) && isset($blogSettings["disquscount"]) && $blogSettings["disquscount"] == 'Y') { 
		?>
			<a href="<?php echo $url; ?>/#disqus_thread" data-disqus-identifier="<?php echo $_GET['post']; ?>" style="float:right"></a>
		<?php } ?>
		<div class="blog_post_container">
			<h3 class="blog_post_title"><a href="<?php echo $url; ?>" class="blog_post_link"><?php echo $post->title; ?></a></h3>
			<?php if($blogSettings["displaydate"] == 'Y') {  ?>
				<p class="blog_post_date">
					<?php echo $date; ?>
				</p>
			<?php } ?>
			<p class="blog_post_content">
				<?php
				if(!isset($_GET['post']) && $blogSettings["postthumbnail"] == 'Y' && !empty($post->thumbnail)) 
				{ 
					echo '<img src="'.$SITEURL.'data/uploads/'.$post->thumbnail.'" style="" class="blog_post_thumbnail" />';
				}
				if($excerpt == false || $excerpt == true && $blogSettings["postformat"] == "Y")
				{
					echo html_entity_decode($post->content);
				}
				else
				{
					if($excerpt == true && $blogSettings["postformat"] == "N")
					{
						if($blogSettings["excerptlength"] == '')
						{
							$excerpt_length = 250;
						}
						else
						{
							$excerpt_length = $blogSettings["excerptlength"];
						}
						echo $Blog->create_excerpt(html_entity_decode($post->content), 0, $excerpt_length);
					}
				}
				if(!isset($_GET['post']) && $blogSettings['displayreadmore'] == 'Y')
				{
					echo '&nbsp;&nbsp;&nbsp<a href="" class="read_more_link">'.$blogSettings['readmore'].'</a>';
				}
				?>
			</p>
		<?php
		if(isset($_GET['post']))
		{
			echo '<p class="blog_go_back"><a href="javascript:history.back()">&lt;&lt; '.i18n_r(BLOGFILE.'/GO_BACK').'</a></p>';
		}
		if(!empty($post->tags) && $blogSettings["displaytags"] != 'N')
		{
			$tag_url = $Blog->get_blog_url('tag');
			$tags = explode(",", $post->tags);
			?>
			<p class="blog_tags"><b><?php i18n(BLOGFILE.'/TAGS'); ?> :</b> 
			<?php
			foreach($tags as $tag)
			{
				echo '<a href="'.$tag_url.$tag.'">'.$tag.'</a> ';
			}
			echo  '</p>';
		}
		echo '</div>';
		if(isset($_GET['post']) && $blogSettings["postadbottom"] == 'Y')
		{
			?>
			<div class="blog_all_posts_ad">
				<?php echo $blogSettings["addata"]; ?>
			</div>
			<?php
		}
		if(isset($_GET['post']) && $blogSettings["addthis"] == 'Y')
		{
			addThisTool();
		}
		if(isset($_GET['post']) && $blogSettings["sharethis"] == 'Y')
		{
			shareThisTool();
		}
		if(isset($_GET['post']) && $blogSettings["comments"] == 'Y' && isset($_GET['post']))
		{
			disqusTool();
		}
	}
	else
	{	
		$blog_code = (string) $blogSettings["blogpage"];
		eval(' ?>'.$blog_code.'<?php ');
	}
}

/** 
* Shows blog categories list
* 
* @return void
*/  
function show_blog_categories()
{
	$Blog = new Blog;
	$categories = getXML(BLOGCATEGORYFILE);
	$url = $Blog->get_blog_url('category');
	$main_url = $Blog->get_blog_url();
	foreach($categories as $category)
	{
		echo '<li><a href="'.$url.$category.'">'.$category.'</a></li>';
	}
	echo '<li><a href="'.$main_url.'">';
	i18n(BLOGFILE.'/ALL_CATEOGIRES');
	echo '</a></li>';
}

/** 
* Shows posts from a requested category
* 
* @param $category the category to show posts from
* @return void
*/  
function show_blog_category($category)
{
	$Blog = new Blog;
	$all_posts = $Blog->listPosts(true, true);
	$count = 0;
	foreach($all_posts as $file)
	{
		$data = getXML($file['filename']);
		if($data->category == $category || empty($category))
		{
			$count++;
			show_blog_post($file['filename'], true);
		}
	}
	if($count < 1)
	{
		echo '<p class="blog_category_noposts">'.i18n_r(BLOGFILE.'/NO_POSTS').'</p>';
	}
}

/** 
* Show blog search bar
* 
* @return void
*/  
function show_blog_search()
{
	$Blog = new Blog;
	$url = $Blog->get_blog_url();
	?>
	<form id="blog_search" action="<?php echo $url; ?>" method="post">
		<input type="text" class="blog_search_input" name="keyphrase" />
		<input type="submit" class="blog_search_button" name="search_blog" value="<?php i18n(BLOGFILE.'/SEARCH'); ?>" />
	</form>
	<?php
}

/** 
* Show Blog archives list
* 
* @return void
*/  
function show_blog_archives()
{
	global $blogSettings;
	$Blog = new Blog;
	$archives = $Blog->get_blog_archives();
	if (!empty($archives)) 
	{
		echo '<ul>';
		foreach ($archives as $archive => $archive_data) 
		{
			$post_count = ($blogSettings['archivepostcount'] == 'Y') ? ' ('.$archive_data['count'].')' : '';
			$url = $Blog->get_blog_url('archive') . $archive;
			echo "<li><a href=\"{$url}\">{$archive_data['title']} {$post_count}</a></li>";
		}
		echo '</ul>';
	}
}

/** 
* Show Posts from requested archive
* 
* @return void
*/  
function show_blog_archive($archive)
{
	$Blog = new Blog;
	$posts = $Blog->listPosts(true, true);
	foreach ($posts as $file) 
	{
		$data = getXML($file['filename']);
		$date = strtotime($data->date);
		if (date('Ym', $date) == $archive)
		{
			show_blog_post($file['filename'], true);
		}
	}
}

/** 
* Show recent posts list
*
* @param $excerpt bool Choose true to display excerpts of post below post title. Defaults to false (no excerpt)
* @param $excerpt_length int Choose length of excerpt. If no value is provided, it will default to the length defined on the blog settings page
* @param $thumbnail int If true a thumbnail will be displayed for each post
* @param $read_more string if not null, a "Read More" link will be placed at the end of the excerpt. Pass the text you would like to be displayed inside the link
* @return string or void
*/
function show_blog_recent_posts($excerpt=false, $excerpt_length=null, $thumbnail=null, $read_more=null)
{
	$Blog = new Blog;
	$posts = $Blog->listPosts(true, true);
	global $SITEURL,$blogSettings;
	if (!empty($posts)) 
	{
		echo '<ul>';
		$posts = array_slice($posts, 0, $blogSettings["recentposts"], TRUE);
		foreach ($posts as $file) 
		{
			$data = getXML($file['filename']);
			$url = $Blog->get_blog_url('post') . $data->slug;
			$title = strip_tags(strip_decode($data->title));

			if($excerpt != false)
			{
				if($excerpt_length == null)
				{
					$excerpt_length = $blogSettings["excerptlength"];
				}
				$excerpt = $Blog->create_excerpt(html_entity_decode($data->content), 0, $excerpt_length);
				if($thumbnail != null)
				{
					if(!empty($data->thumbnail))
					{
						$excerpt = '<img src="'.$SITEURL.'data/uploads/'.$data->thumbnail.'" class="blog_recent_posts_thumbnail" />'.$excerpt;
					}
				}
				if($read_more != null)
				{
					$excerpt = $excerpt.'<br/><a href="'.$url.'" class="recent_posts_read_more">'.$read_more.'</a>';
				}
				echo '<li><a href="'.$url.'">'.$title.'</a><p class="blog_recent_posts_excerpt">'.$excerpt.'</p></li>';
			}
			else
			{
				echo "<li><a href=\"$url\">$title</a></li>";
			}
		}
		echo '</ul>';
	}
}

/** 
* Show posts for requested tag
* 
* @return void
*/  
function show_blog_tag($tag)
{
	$Blog = new Blog;
	$all_posts = $Blog->listPosts(true, true);
	foreach ($all_posts as $file) 
	{
		$data = getXML($file['filename']);
		if(!empty($data->tags))
		{
			$tags = explode(',', $data->tags);
			if (in_array($tag, $tags))
			{
				show_blog_post($file['filename'], true);	
			}
		}
	}
}

/** 
* Show all postts
* 
* @return void
*/  
function show_all_blog_posts()
{
	$Blog = new Blog;
	if(isset($_GET['page']))
	{
		$page = $_GET['page'];
	}
	else
	{
		$page = 0;
	}
	show_posts_page($page);
}

/** 
* Display blog posts results from a search
* 
* @return void
*/  
function search_posts($keyphrase)
{
	$Blog = new Blog;
	$posts = $Blog->searchPosts($keyphrase);
	if (!empty($posts)) 
	{
		echo '<p class="blog_search_header">';
			i18n(BLOGFILE.'/FOUND');
		echo '</p>';
		foreach ($posts as $file)
		{
			show_blog_post($file, TRUE);
		}
	} 
	else 
	{
		echo '<p class="blog_search_header">';
			i18n(BLOGFILE.'/NOT_FOUND');
		echo '</p>';
	}
}

/** 
* RSS Feed Auto Importer
* Auto imports RSS feeds. Can be launched by a cron job 
* 
* @return void
*/  
function auto_import()
{
	$Blog = new Blog;
	if($_GET['import'] == urldecode($Blog->getSettingsData("autoimporterpass")) && $Blog->getSettingsData("autoimporter") =='Y')
	{
		ini_set("memory_limit","350M");

		require_once(BLOGPLUGINFOLDER.'inc/magpierss/rss_fetch.inc');

		$rss_feed_file = getXML(BLOGRSSFILE);
		foreach($rss_feed_file->rssfeed as $the_fed)
		{
		    $rss_uri = $the_fed->feed;
		    $rss_category = $the_fed->category;
		        
		    $rss = fetch_rss($rss_uri);
		    $items = array_slice($rss->items, 0);
		    foreach ($items as $item )
		    {
		        $post_data['title']         = $item['title'];
		        $post_data['slug']          = '';
		        $post_data['date']          = $item['pubdate'];
		        $post_data['private']       = '';
		        $post_data['tags']          = '';
		        $post_data['category']      = $rss_category;
		        $post_data['content']       = $item['summary'].'<p class="blog_auto_import_readmore"><a href="'.$item['link'].'" target="_blank">'.i18n_r(BLOGFILE.'/READ_FULL_ARTICLE').'</a></p>';
		        $post_data['excerpt']       = '';
		        $post_data['thumbnail']     = '';
		        $post_data['current_slug']  = '';

		        $Blog->savePost($post_data, true);
		    }
		}
	}
}

/** 
* RSS Feed Auto Importer
* Auto imports RSS feeds. Can be launched by a cron job 
* 
* @return void
*/  
/*******************************************************
 * @function nm_show_page
 * param $index - page index (pagination)
 * @action show posts on news page
 */
function show_posts_page($index=0) 
{
	global $blogSettings;
	$Blog = new Blog;
	$posts = $Blog->listPosts(true, true);
	if($blogSettings["allpostsadtop"] == 'Y')
	{
		?>
		<div class="blog_all_posts_ad">
			<?php echo $blogSettings["addata"]; ?>
		</div>
		<?php
	}
	if(!empty($posts))
	{
		$pages = array_chunk($posts, intval($blogSettings["postperpage"]), TRUE);
		if (is_numeric($index) && $index >= 0 && $index < sizeof($pages))
		{
			$posts = $pages[$index];
		}
		else
		{
			$posts = array();	
		}
		$count = 0;
		$lastPostOfPage = false;
		foreach ($posts as $file)
		{
			$count++;
			show_blog_post($file['filename'], true);

			if($count == sizeof($posts) && sizeof($posts) > 0) 
			{
				$lastPostOfPage = true;	
			}

			if (sizeof($pages) > 1)
			{
				// We know here that we have more than one page.
				$maxPageIndex = sizeof($pages) - 1;
				show_blog_navigation($index, $maxPageIndex, $count, $lastPostOfPage);
				if($count == $blogSettings["postperpage"])
				{
					$count = 0;
				}
			}
		}
	} 
	else 
	{
		echo '<p>' . i18n(BLOGFILE.'/NO_POSTS') . '</p>';
	}
	if($blogSettings["allpostsadbottom"] == 'Y')
	{
		?>
		<div class="blog_all_posts_ad">
			<?php echo $blogSettings["addata"]; ?>
		</div>
		<?php
	}
}

/** 
* Blog posts navigation (pagination)
* 
* @param $index the current page index
* @param $total total number of pages
* @param $count current post
* @return void
*/  
function show_blog_navigation($index, $total, $count, $lastPostOfPage) 
{
	global $blogSettings;
	$Blog = new Blog;
	$url = $Blog->get_blog_url('page');

	if ($lastPostOfPage) 
	{
		echo '<div class="blog_page_navigation">';
	}
	
	if($index < $total && $lastPostOfPage)
	{
	?>
		<div class="left blog-next-prev-link">
		<a href="<?php echo $url . ($index+1); ?>">
			&larr; <?php echo $blogSettings["nextpage"]; ?>
		</a>
		</div>
	<?php	
	}
	?>
		
	<?php
	if ($index > 0 && $lastPostOfPage)
	{
	?>
		<div class="right blog-next-prev-link">
		<a href="<?php echo ($index > 1) ? $url . ($index-1) : substr($url, 0, -6); ?>">
			<?php echo $blogSettings["previouspage"]; ?> &rarr;
		</a>
		</div>
	<?php
	}
	?>
	
	<?php
	if ($lastPostOfPage) 
	{
		echo '<div id="clear"></div>';
		echo '</div>';
	}

}

/** 
* Display AddThis Tool
* 
* @return void
*/  
function addThisTool()
{
	global $blogSettings;
	$Blog = new Blog;
	?>
	<div class="addthis_toolbox addthis_default_style addthis_32x32_style">
	<a class="addthis_button_preferred_1"></a>
	<a class="addthis_button_preferred_2"></a>
	<a class="addthis_button_preferred_3"></a>
	<a class="addthis_button_preferred_4"></a>
	<a class="addthis_button_compact"></a>
	<a class="addthis_counter addthis_bubble_style"></a>
	</div>
	<script type="text/javascript">var addthis_config = {"data_track_addressbar":true};</script>
	<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=<?php echo $blogSettings["addthisid"]; ?>"></script>
	<!-- AddThis Button END -->
<?php
}

/** 
* Display ShareThis Tool
* 
* @return void
*/  
function shareThisTool()
{
	?>
	<span class='st_sharethis_large' displayText='ShareThis'></span>
	<span class='st_facebook_large' displayText='Facebook'></span>
	<span class='st_twitter_large' displayText='Tweet'></span>
	<span class='st_pinterest_large' displayText='Pinterest'></span>
	<span class='st_linkedin_large' displayText='LinkedIn'></span>
	<span class='st_googleplus_large' displayText='Google +'></span>
	<span class='st_delicious_large' displayText='Delicious'></span>
	<span class='st_digg_large' displayText='Digg'></span>
	<span class='st_email_large' displayText='Email'></span>
	<?php
}

/** 
* Display ShareThis Scripts
* 
* @return void
*/  
function shareThisToolHeader()
{
	global $blogSettings;
	$Blog = new Blog;
	?>
	<script type="text/javascript">var switchTo5x=true;</script>
	<script type="text/javascript" src="http://w.sharethis.com/button/buttons.js"></script>
	<script type="text/javascript">stLight.options({publisher: "<?php echo $blogSettings["sharethisid"]; ?>"}); </script>
	<?php
}

/** 
* Display Feed Burner Tool
* 
* @return void
*/  
function feedBurnerTool()
{
	$Blog = new Blog;
	?>
		<a href="<?php echo $Blog->getSettingsData("feedburnerlink"); ?>" title="Subscribe to my feed" rel="alternate" type="application/rss+xml"><img src="http://www.feedburner.com/fb/images/pub/feed-icon32x32.png" alt="" style="border:0"/></a><a href="<?php echo $Blog->getSettingsData("feedburnerlink"); ?>" title="Subscribe to my feed" rel="alternate" type="application/rss+xml">Subscribe in a reader</a>
	<?php
}

/** 
* Display Disqus Tool
* 
* @return void
*/  
function disqusTool()
{
	global $blogSettings;
	$Blog = new Blog;
	?>
	<div id="disqus_thread"></div>
	<script type="text/javascript">
	/* * * CONFIGURATION VARIABLES: EDIT BEFORE PASTING INTO YOUR WEBPAGE * * */
	    var disqus_shortname = '<?php echo $blogSettings["disqusshortname"]; ?>'; // required: replace example with your forum shortname
		var disqus_identifier = '<?php echo $_GET['post']; ?>';

	/* * * DON'T EDIT BELOW THIS LINE * * */
	(function() {
	    var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
	    dsq.src = 'http://' + disqus_shortname + '.disqus.com/embed.js';
	    (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
	})();
	</script>
	<?php if($blogSettings["disquscount"] == 'Y') { ?>
		<script type="text/javascript">
			/* * * CONFIGURATION VARIABLES: EDIT BEFORE PASTING INTO YOUR WEBPAGE * * */
		    var disqus_shortname = '<?php echo $blogSettings["disqusshortname"] ?>'; // required: replace example with your forum shortname
			var disqus_identifier = '<?php echo $_GET['post']; ?>';

			/* * * DON'T EDIT BELOW THIS LINE * * */
			(function () {
			var s = document.createElement('script'); s.async = true;
			s.type = 'text/javascript';
			s.src = 'http://' + disqus_shortname + '.disqus.com/count.js';
			(document.getElementsByTagName('HEAD')[0] || document.getElementsByTagName('BODY')[0]).appendChild(s);
			}());
		</script>
	<?php
	}
}

/** 
* Get Page/POST Title
* This function is a modified version of the core get_page_clean_title() function. It will function normally on all pages except individual blog posts, where the post title will be placed in instead of the page title.
* 
* @return void
*/  
function get_blog_title($echo=true) 
{
	global $title, $blogSettings, $post;
	$slug = base64_encode(return_page_slug());
	if($slug == base64_encode($blogSettings["blogurl"]))
	{
		if(isset($_GET['post']) && !empty($post))
		{
			$title = (string) $post->title;
		}
	}
	$myVar = strip_tags(strip_decode($title));
	if ($echo) 
	{
		echo $myVar;
	} 
	else 
	{
		return $myVar;
	}
}

function set_post_description()
{
	global $metad, $post, $blogSettings;
	$Blog = new Blog;
	if($blogSettings["postdescription"] == 'Y')
	{
		$excerpt_length = ($blogSettings["excerptlength"] == '') ? 150 : $blogSettings["excerptlength"];

		$metad = $Blog->create_excerpt(html_entity_decode($post->content), 0, $excerpt_length);
	}
}
