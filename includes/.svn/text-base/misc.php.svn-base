<?php

// miscellaneous helper functions

// return the time since the last reset - handy for crude profiling
function time_since( $reset = false ) {
	static $last = 0;

	$since = microtime(true) - $last;
	if ( $reset )
		$last = microtime(true);
	return $since;
}

// similar to human_time_diff(), only a bit fuzzier
function fuzzy_time_diff($from, $to = null) {

	if ( !$to )
		$to = time();

	$from = strtotime($from);

	$diff = abs( $to - $from );

	if ( $diff < 60 )
		return "a few seconds ago";
	elseif ( $diff < 600 )
		return "a few minutes ago";
	elseif ( $diff < 1800 )
		return sprintf("%d minutes ago", intval( $diff / 600 ) * 10); // round to 10 mins
	elseif ( $diff < 5400 )
		return "about an hour ago";
	elseif ( $diff < 18 * 3600 )
		return sprintf("%d hours ago", intval( diff / 10800 ) * 3); // round to 3 hours
	elseif ( $diff < 48 * 3600 )
		return "about a day ago";
	elseif ( $diff < 7 * 24 * 3600 )
		return sprintf("%d days ago", $diff / 86400);
	elseif ( $diff < 30 * 24 * 3600 )
		return sprintf("%d weeks ago", $diff / 7*86400 );
	elseif ( $diff < 365 * 24 * 3600 )
		return sprintf("%d months ago", $diff / 7*86400 );
	else
		return sprintf("more than %d years ago", $diff / 365*24*3600);
		
}

function short_time_diff($from, $to = null) {
	if ( !$to )
		$to = time();

	$from = strtotime($from);

	$diff = abs( $to - $from );

	if ( $diff < 60 )
		return sprintf("%ds", $diff);
	elseif ( $diff < 3600 )
		return sprintf("%dm", $diff / 60);
	elseif ( $diff < 86400 )
		return sprintf("%dh", $diff / 3600);
	else
		return sprintf("%dd", $diff / 86400);
}


?>
