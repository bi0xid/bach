</div> <!-- wrap -->
<?php
$run_time = microtime(true) - $init_time;
echo '<!-- Runtime: ' . sprintf('%0.5f', $run_time) . ' -->';
if ( SAVEQUERIES ) {
	echo "<pre>";
	foreach ( $db->queries as $q )
		printf("%0.1f ms: %s\n", $q[1] * 1000, $q[0]);
	echo '</pre>';
	echo "<p>Runtime: $run_time</p>";
	echo '<!-- Querytime: ' . sprintf('%0.5f', $db->query_time) . ' -->';
}
?>

</body>
</html>
