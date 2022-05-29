<?php

use Firebase\JWT\JWT;

function theme_enqueue_styles()
{
	wp_enqueue_style('carbon-child-style', get_bloginfo('stylesheet_url'), array(), '1');
}
add_action('wp_enqueue_scripts', 'theme_enqueue_styles');

//add_action( 'wp_authenticate' , 'check_custom_authentication' );
add_action('init', 'jwt_opnways');
function jwt_opnways()
{

	$username = wp_get_current_user();
	$username = $username->user_login;
	global $wpdb;
	// data JWT basic
	$issued_at  = time();
	$not_before = $issued_at;
	$not_before = apply_filters('jwt_auth_not_before', $not_before, $issued_at);
	$expire     = $issued_at + (DAY_IN_SECONDS * 7);
	$expire     = apply_filters('jwt_auth_expire', $expire, $issued_at);
	// headers COOKIE
	$path = parse_url(get_option('siteurl'), PHP_URL_PATH);
	$host = parse_url(get_option('siteurl'), PHP_URL_HOST);
	$expiry = strtotime('+1 month');
	if (!username_exists($username)) {
			// Create token payload as a JSON string status 403
	$payload = json_encode([
		'iss'  => get_bloginfo('url'),
		'iat'  => $issued_at,
		'nbf'  => $not_before,
		'exp'  => $expire,
		'status' => 403,
		'data' => array(
			
		)
	]);
	$jwt = createJWT($payload);
	setcookie("TestCookie", $jwt, $expiry, $path, $host);

	echo	"<script language='javascript'>
localStorage.setItem('TestCookie', '" . $jwt . "');
</script>";
		return;
	}

	$userinfo = get_user_by('login', $username);




	// Create token payload as a JSON string STATUS 200
	$payload = json_encode([
		'iss'  => get_bloginfo('url'),
		'iat'  => $issued_at,
		'nbf'  => $not_before,
		'exp'  => $expire,
		'status' => 200,
		'data' => array(
			'user_id' => $userinfo->ID
		)
	]);
	$jwt = createJWT($payload);

	//$jwt = 1;
	setcookie("TestCookie", $jwt, $expiry, $path, $host);

	echo	"<script language='javascript'>
localStorage.setItem('TestCookie', '" . $jwt . "');
</script>";
}

function createJWT($payload){

		// Create token header as a JSON string
		$header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);

	// Encode Header to Base64Url String
	$base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));

	// Encode Payload to Base64Url String
	$base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

	// Create Signature Hash
	$signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, 'B8fYGMg088hDbQ8WHRNR', true);

	// Encode Signature to Base64Url String
	$base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

	// Create JWT
	$jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
	return $jwt;
}