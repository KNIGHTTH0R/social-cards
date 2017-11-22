<?php

function scrape_insta($url) {
    $insta_source = file_get_contents($url);
    $shards = explode('window._sharedData = ', $insta_source);
    $insta_json = explode(';</script>', $shards[1]); 
    $insta_array = json_decode($insta_json[0], TRUE);
    return $insta_array;
}

// Grab twitter card urls and convert them to array. Need to find a way to merge the instagram.
foreach($html->find('div.field-name-field-social-card-url .field-item') as $social) {

    $social_feed[] = $social->plaintext;
}
flush();

// Delcare cache files
$cache_array_file = 'app/cache/cache-array.txt';
$cache_data_file = 'app/cache/cache-data.txt';

$cache_array = json_decode(file_get_contents($cache_array_file));

if ($social_feed === $cache_array) 
{
    $cache_cards = include($cache_data_file);

} 
else {

    file_put_contents($cache_array_file, json_encode($social_feed));

    // Tweets
    $social_cards = '';
    foreach ($social_feed as $feed_card) {
        

        // If URL has twitter.com run this
        if (strpos($feed_card, 'twitter.com') !== false) 
        {
            $url = parseURL($feed_card);    
            
            $tweet = new simple_html_dom();
            $tweet->load($url);
            
            $tweet_img = $tweet->find('div[class=js-adaptive-photo]', 0)->innertext;
            $tweet_img = preg_replace('/style=".*"/', '', $tweet_img); // Remmove inline style
        
            // Get image, text, and filter out pic.url
            $tweet_text = $tweet->find('div[class=js-tweet-text-container]', 0)->plaintext;
            $tweet_text = preg_replace('/pic.twitter.com\/.*/', '', $tweet_text);
            
            $profile_link = preg_replace('/\/status.*/', '', $feed_card);
            $profile_sn = preg_replace('/https:\/\/twitter.com\//', '', $profile_link);
        
            $social_cards .= '
                <div class="card-tile">
                    <div class="twitter-icon"></div>
                    <a href="'. $feed_card .'"target="_blank"><div class="card_img">' . $tweet_img . '</div></a>
                    <a href="'. $feed_card .'"target="_blank"><div class="card_text">' . $tweet_text . '</div></a>
                    <a href="'. $profile_link .'"target="_blank"><div class="card_sn">@'. $profile_sn .'</div></a>
                </div>';
                
        }

        // else if has instgram url run this. 
        if (strpos($feed_card, 'instagram.com') !== false) {

            //Supply a username
            $url = $feed_card; 
            $results_array = scrape_insta($url);
            $image = $results_array['entry_data']['PostPage'][0]['graphql']['shortcode_media']['display_url'];
            $caption_text = $results_array['entry_data']['PostPage'][0]['graphql']['shortcode_media']['edge_media_to_caption']['edges'][0]['node']['text'];
            $caption = strlen($caption_text) > 180 ? substr($caption_text,0,180)." ..." : $caption_text;
            
            $username = $results_array['entry_data']['PostPage'][0]['graphql']['shortcode_media']['edge_media_to_comment']['edges'][0]['node']['owner']['username'];
            $profile_url = 'https://www.instagram.com/' . $username . '/';

            $social_cards .= '
            <div class="card-tile">
                <div class="instagram-icon"></div>
                <a href="'. $feed_card .'"target="_blank"><img class="card_img" src=' . $image . '></a>
                <a href="'. $feed_card .'"target="_blank"><div class="card_text">' . $caption . '</div></a>
                <a href="'. $profile_url .'"target="_blank"><div class="card_sn">@'. $username .'</div></a>
            </div>';
            
        }

    }
    flush();

    file_put_contents($cache_data_file, $social_cards);
    flush();
}

?>