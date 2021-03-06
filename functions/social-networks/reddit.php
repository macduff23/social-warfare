<?php

/*****************************************************************
*                                                                *
*   #1: Add the On / Off Switch	and Sortable Option				 *
*                                                                *
******************************************************************/
	add_filter('swp_button_options', 'swp_reddit_options_function',20);
	function swp_reddit_options_function($options) {

		// Create the new option in a variable to be inserted
		$options['content']['reddit'] = array(
			'type' => 'checkbox',
			'content' => 'Reddit',
			'default' => false,
			'premium' => true
		);

		return $options;		 

	};
/*****************************************************************
*                                                                *
*   #2: Add it to global network array          				 *
*                                                                *
******************************************************************/
	// Queue up your filter to be ran on the swp_options hook.
	add_filter('swp_add_networks', 'swp_reddit_network');

	// Create the function that will filter the options
	function swp_reddit_network($networks) {

		// Add your network to the existing network array
		$networks[] = 'reddit';

		// Be sure to return the modified options array or the world will explode
		return $networks;
	};
/*****************************************************************
*                                                                *
*   #3: Generate the API Share Count Request URL	             *
*                                                                *
******************************************************************/
	function swp_reddit_request_link($url) {

		// Create the API request link
		$request_url = 'https://www.reddit.com/api/info.json?url='.$url;

		// Return the constructed link to the Social Warfare Plugin
		return $request_url;
	}
/*****************************************************************
*                                                                *
*   #4: Parse the Response to get the share count	             *
*                                                                *
******************************************************************/
	function swp_format_reddit_response($response) {

		// Parse the JSON response into an associative array
		$response = json_decode($response, true);
		$score = 0;

		// Check to ensure that there was a response
		if(isset($response) && isset($response['data']) && isset($response['data']['children'])):

			// Loop through each post on reddit adding the score to our total
			foreach($response['data']['children'] as $child):
				$score += (int) $child['data']['score'];
			endforeach;
		endif;

		// Return the score to Social Warfare for caching and presentation
		return $score;
	}
/*****************************************************************
*                                                                *
*   #5: Create the Button HTML				  		             *
*                                                                *
******************************************************************/
	add_filter('swp_network_buttons', 'swp_reddit_button_html',10);
	function swp_reddit_button_html($array) {
		
		// If we've already generated this button, just use our existing html
		if(isset($_GLOBALS['sw']['buttons'][$array['postID']]['reddit'])):
			$array['resource']['reddit'] = $_GLOBALS['sw']['buttons'][$array['postID']]['reddit'];

		// If not, let's check if Facebook is activated and create the button HTML
		elseif( (isset($array['options']['newOrderOfIcons']['reddit']) && !isset($array['buttons'])) || (isset($array['buttons']) && isset($array['buttons']['reddit']))  ):
			
			if(isset($array['shares']['reddit'])):
				$array['totes'] += $array['shares']['reddit'];
			endif;
			++$array['count'];

			// Collect the Title
			$title = get_post_meta( $array['postID'] , 'nc_ogTitle' , true );
			if(!$title):
				$title = get_the_title();
			endif;

			$array['resource']['reddit'] = '<div class="nc_tweetContainer swp_reddit" data-id="'.$array['count'].'" data-network="reddit">';
			$link = $array['url'];
			$array['resource']['reddit'] .= '<a target="_blank" href="https://www.reddit.com/submit?url='.$link.'&title='.urlencode($title).'" data-link="https://www.reddit.com/submit?url='.$link.'&title='.urlencode($title).'" class="nc_tweet">';
			if($array['options']['totesEach'] && $array['shares']['totes'] >= $array['options']['minTotes'] && isset($array['shares']['reddit']) && $array['shares']['reddit'] > 0):
				$array['resource']['reddit'] .= '<span class="iconFiller">';
				$array['resource']['reddit'] .= '<span class="spaceManWilly">';
				$array['resource']['reddit'] .= '<i class="sw sw-reddit"></i>';
				$array['resource']['reddit'] .= '<span class="swp_share"> '.__('Reddit','social-warfare').'</span>';
				$array['resource']['reddit'] .= '</span></span>';
				$array['resource']['reddit'] .= '<span class="swp_count">'.swp_kilomega($array['shares']['reddit']).'</span>';
			else:
				$array['resource']['reddit'] .= '<span class="swp_count swp_hide"><span class="iconFiller"><span class="spaceManWilly"><i class="sw sw-reddit"></i><span class="swp_share"> '.__('Reddit','social-warfare').'</span></span></span></span>';
			endif;
			$array['resource']['reddit'] .= '</a>';
			$array['resource']['reddit'] .= '</div>';

			// Store these buttons so that we don't have to generate them for each set
			$_GLOBALS['sw']['buttons'][$array['postID']]['reddit'] = $array['resource']['reddit'];

		endif;

		return $array;

	};
