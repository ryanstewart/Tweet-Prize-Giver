<!--
Copyright (c) 2009 Ryan Stewart
http://blog.digitalbackcountry.com
 
----------------------------------------------------------------------------
"THE BEER-WARE LICENSE" (Revision 42):
<ryan@ryanstewart.net> wrote this file. As long as you retain this notice you
can do whatever you want with this stuff. If we meet some day, and you think
this stuff is worth it, you can buy me a beer in return. =Ryan Stewart
----------------------------------------------------------------------------
 
Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:
 
The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.
 
THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.
-->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Untitled Document</title>
</head>
<body>
<pre>
<?php
	ini_set('display_errors',1);
	error_reporting(E_ALL | E_STRICT);
	date_default_timezone_set('UTC');
	
	$search_term = "";
	$day = date("d");
	$month = date("m");
	$year = date("Y");
	$hour = date("H");
	$minute = date("i");
	
	$page_number = 1;
	$keep_paging = true;
	$tweet_array = array();
	
	function get_twitter_date($date_string)
	{
		// Format from Twitter: Sun, 25 Oct 2009 17:03:04 +0000
		$date_array = explode(" ",$date_string);
		$time_array = explode(":",$date_array[4]);
		
		switch($date_array[2]) {
			case "Jan":
				$month = 1;
				break;
			case "Feb":
				$month = 2;
				break;
			case "Mar":
				$month = 3;
				break;
			case "Apr":
				$month = 4;
				break;
			case "May":
				$month = 5;
				break;
			case "Jun":
				$month = 6;
				break;
			case "Jul":
				$month = 7;
				break;
			case "Aug":
				$month = 8;
				break;
			case "Sep":
				$month = 9;
				break;
			case "Oct":
				$month = 10;
				break;
			case "Nov":
				$month = 11;
				break;
			case "Dec":
				$month = 12;
				break;
		}
		
		$day = $date_array[1];
		$year = $date_array[3];
		
		$hour = $time_array[0];
		$minute = $time_array[1];
		$second = $time_array[2];
		
		$tweet_time = mktime($hour,$minute,$second,$month,$day,$year);
		return $tweet_time;
		
	}
	
	function get_twitter_results($search,$page)
	{
		// Setting up the call to the Twitter Search API
		$base_url = "http://search.twitter.com/search";
		$format = "json"; //can be "json" or "atom"
		$query_string = "";
		$parameters = array(
			'q'=>"$search",
			'rpp'=>"25",
			'page'=>"$page",
			'showuser'=>"true"
								);
			
		// Loop through all the parameters and create the URI
		foreach( $parameters as $parameter=>$value )
		{
			$query_string = $query_string . "$parameter=".urlencode($value)."&";
		}
			
		$uri="$base_url.$format?$query_string";
		
		// Use curl to make the call
		$curl_object = curl_init($uri);
		curl_setopt($curl_object,CURLOPT_RETURNTRANSFER,1);
			
		$result = curl_exec($curl_object);
		curl_close($curl_object);
			
		$arr_results = json_decode($result,true);
		
		// return the results
		return $arr_results;
		
	}	
	
	function get_random_winner($source_array)
	{
		// Generates a random number based on an array
		$count = count($source_array);
		$random_number = rand(0,$count-1);
		return $random_number;
	}
	
	if( isset($_POST['submitted']) )
	{
		// Make our form sticky
		$search_term = $_POST['search'];
		$hour = $_POST['hour'];
		$minute = $_POST['minute'];
		$month = $_POST['month'];
		$day = $_POST['day'];
		$year = $_POST['year'];
		
		// Create the date the event starts
		$date_time = mktime($hour,$minute,00,$month,$day,$year);
		
		// We pull the search results 25 at a time and this makes the API call,
		// loops through those results, and adds a page number until we find
		// a status that is older than our date.
		while( $keep_paging )
		{
	
			$result_array = get_twitter_results($search_term,$page_number);
	
			$array_length = count($result_array['results']);
			
			
			for($i=0; $i < $array_length; $i++)
			{
				if(get_twitter_date($result_array['results'][$i]['created_at']) < $date_time)
				{
					print "Time reached";
					$keep_paging = false;
					break;
				} else {
					array_push($tweet_array,$result_array['results'][$i]);
				}
			}
			$page_number = $page_number + 1;
		}

		// Print out the winner in crappy format
		print "<br />Winner is: <br />";
		print_r($tweet_array[get_random_winner($tweet_array)]);
	}
?>
</pre>
	<form action="index.php" method="POST">
		<p>Search String: <input type="text" name="search" maxlength="250" value="<?php print $search_term; ?>" /></p>
        <p>Start Date/Time (mm dd yy hh mm): <input type="text" name="month" maxlength="2" value="<?php print $month ?>" /> <input type="text" name="day" maxlength="2" value="<?php print $day ?>" /> <input type="text" name="year" maxlength="4" value="<?php print $year ?>" /> <input type="text" name="hour" maxlength="2" value="<?php print $hour ?>" /> <input type="text" name="minute" maxlength="2" value="<?php print $minute ?>" /></p>
            
        <p><input type="submit" value="Submit!" /></p>
        <input type="hidden" name="submitted" value="true" />
	</form>
</body>
</html>