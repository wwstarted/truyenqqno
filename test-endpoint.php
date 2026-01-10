<?php
require_once('wp-load.php');

echo "<h1>WordPress REST API Endpoint Test</h1>";

// 1. Check nettruyen_comic post type
echo "<h2>1. Check Post Type 'nettruyen_comic'</h2>";
if (post_type_exists('nettruyen_comic')) {
    echo "✅ Post type 'nettruyen_comic' EXISTS<br>";

    // Count posts
    $count = wp_count_posts('nettruyen_comic');
    echo "Total comics: " . $count->publish . " published<br>";
} else {
    echo "❌ Post type 'nettruyen_comic' NOT FOUND<br>";
}

// 2. Check all registered routes
echo "<h2>2. All Registered Routes</h2>";
$rest_server = rest_get_server();
$routes = $rest_server->get_routes();

echo "<h3>Looking for /nettruyen/v1/search:</h3>";
if (isset($routes['/nettruyen/v1/search'])) {
    echo "✅ Route '/nettruyen/v1/search' is REGISTERED<br>";
    echo "<pre>";
    print_r($routes['/nettruyen/v1/search']);
    echo "</pre>";
} else {
    echo "❌ Route '/nettruyen/v1/search' NOT FOUND<br><br>";

    echo "<strong>All available routes with 'nettruyen':</strong><br>";
    foreach ($routes as $route => $handlers) {
        if (strpos($route, 'nettruyen') !== false) {
            echo "- " . $route . "<br>";
        }
    }

    if (
        empty(array_filter(array_keys($routes), function ($r) {
            return strpos($r, 'nettruyen') !== false;
        }))
    ) {
        echo "No routes with 'nettruyen' found!<br>";
    }
}

// 3. Test direct function call
echo "<h2>3. Test Function Direct Call</h2>";
if (function_exists('nettruyen_search_comics')) {
    echo "✅ Function 'nettruyen_search_comics' EXISTS<br>";

    // Create fake request
    $request = new WP_REST_Request('GET', '/nettruyen/v1/search');
    $request->set_query_params(array('q' => 'test'));

    echo "<strong>Testing function with keyword 'test':</strong><br>";
    try {
        $result = nettruyen_search_comics($request);
        echo "<pre>";
        print_r($result);
        echo "</pre>";
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage();
    }
} else {
    echo "❌ Function 'nettruyen_search_comics' NOT FOUND<br>";
}

// 4. Check permalink structure
echo "<h2>4. Permalink Structure</h2>";
$permalink_structure = get_option('permalink_structure');
echo "Current structure: " . ($permalink_structure ? $permalink_structure : "Plain (Default)") . "<br>";
if (empty($permalink_structure)) {
    echo "⚠️ WARNING: Using plain permalinks might cause REST API issues!<br>";
}

// 5. Test actual REST request
echo "<h2>5. Test REST Request</h2>";
$api_url = home_url('/wp-json/nettruyen/v1/search?q=test');
echo "Testing URL: <a href='{$api_url}' target='_blank'>{$api_url}</a><br>";

$response = wp_remote_get($api_url);
if (is_wp_error($response)) {
    echo "❌ Error: " . $response->get_error_message() . "<br>";
} else {
    $body = wp_remote_retrieve_body($response);
    $code = wp_remote_retrieve_response_code($response);
    echo "Response Code: " . $code . "<br>";
    echo "Response Body:<br><pre>" . $body . "</pre>";
}

// 6. Check .htaccess
echo "<h2>6. Check .htaccess</h2>";
$htaccess_file = ABSPATH . '.htaccess';
if (file_exists($htaccess_file)) {
    echo "✅ .htaccess file exists<br>";
    echo "<strong>Content:</strong><br>";
    echo "<textarea style='width:100%; height:200px;'>" . file_get_contents($htaccess_file) . "</textarea>";
} else {
    echo "❌ .htaccess file NOT FOUND<br>";
}

// 7. Check if nettruyen_register_search_endpoint is hooked
echo "<h2>7. Check Hook</h2>";
if (function_exists('nettruyen_register_search_endpoint')) {
    echo "✅ Function 'nettruyen_register_search_endpoint' EXISTS<br>";
} else {
    echo "❌ Function 'nettruyen_register_search_endpoint' NOT FOUND<br>";
}

// 8. List all rest_api_init hooks
echo "<h2>8. All rest_api_init Hooks</h2>";
global $wp_filter;
if (isset($wp_filter['rest_api_init'])) {
    echo "<pre>";
    foreach ($wp_filter['rest_api_init']->callbacks as $priority => $callbacks) {
        echo "Priority: $priority\n";
        foreach ($callbacks as $callback) {
            if (is_array($callback['function'])) {
                echo "  - " . get_class($callback['function'][0]) . "::" . $callback['function'][1] . "\n";
            } elseif (is_string($callback['function'])) {
                echo "  - " . $callback['function'] . "\n";
            } else {
                echo "  - [Closure]\n";
            }
        }
    }
    echo "</pre>";
} else {
    echo "No hooks found for 'rest_api_init'<br>";
}
?>