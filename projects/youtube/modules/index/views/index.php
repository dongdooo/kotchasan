<?php
/*
 * @filesource index/views/index.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Index;
/*
 * default View
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */

class View extends \Kotchasan\View
{

	public function render()
	{
		/*
		 * Youtube API Key
		 * http://gcms.in.th/howto/%E0%B8%81%E0%B8%B2%E0%B8%A3%E0%B8%AA%E0%B8%A3%E0%B9%89%E0%B8%B2%E0%B8%87%E0%B8%84%E0%B8%B5%E0%B8%A2%E0%B9%8C%E0%B8%AA%E0%B8%B3%E0%B8%AB%E0%B8%A3%E0%B8%B1%E0%B8%9A%E0%B9%83%E0%B8%8A%E0%B9%89%E0%B8%87%E0%B8%B2%E0%B8%99_youtube_api_v3.html
		 */
		$youtube_api_key = 'AIzaSyDQnKmclSxltQBSNy0l1VjHvvDxEO_7E7I';
		/*
		 * video ID เช่น https://www.youtube.com/watch?v=YPeMyo6F5UQ
		 */
		$youtube = 'YPeMyo6F5UQ';
		// get video info
		$url = 'https://www.googleapis.com/youtube/v3/videos?part=snippet,statistics&id='.$youtube.'&key='.$youtube_api_key;
		if (function_exists('curl_init') && $ch = @curl_init()) {
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$feed = curl_exec($ch);
			curl_close($ch);
		} else {
			$feed = file_get_contents($url);
		}
		$datas = json_decode($feed);
		// debug
		//print_r($datas);
		if (isset($datas->error)) {
			echo $datas->error->message;
		} else {
			// แสดงผล
			echo '<html style="height:100%;width:100%"><head>';
			echo '<meta charset=utf-8>';
			echo '<meta name=viewport content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">';
			echo '</head><body style="height:100%;width:100%;margin:0;font-family:Tahoma, Loma;color:#666;">';
			echo '<h1>'.$datas->items[0]->snippet->title.'</h1>';
			echo '<p><a href="https://www.youtube.com/watch?v='.$youtube.'" target=_blank><img src="'.$datas->items[0]->snippet->thumbnails->high->url.'"></a></p>';
			echo '<iframe width="560" height="315" src="https://www.youtube.com/embed/'.$youtube.'" frameborder="0" allowfullscreen></iframe>';
			echo '</body></html>';
		}
	}
}