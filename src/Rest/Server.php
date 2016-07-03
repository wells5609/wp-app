<?php

namespace WordPress\Rest;

use WP_Error;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;

class Server extends WP_REST_Server
{

	const JSONP_PARAM = '_jsonp';
	const METHOD_OVERRIDE_PARAM = '_method';

	protected $jsonpCallback;

	/**
	 * Handles serving an API request.
	 *
	 * Matches the current server URI to a route and runs the first matching
	 * callback then outputs a JSON representation of the returned value.
	 *
	 * @since 4.4.0
	 * @access public
	 *
	 * @see WP_REST_Server::dispatch()
	 *
	 * @param string $path Optional. The request route. If not set, `$_SERVER['PATH_INFO']` will be used.
	 *                     Default null.
	 * @return false|null Null if not served and a HEAD request, false otherwise.
	 */
	public function serve_request($path = null) {
	
		$this->sendInitialHeaders();
	
		if (! Manager::instance()->isEnabled()) {
			echo $this->json_error('rest_disabled', __('The REST API is disabled on this site.'), 404);
			return false;
		}
	
		if (false === $jsonpCallback = $this->discoverJsonpCallback()) {
			// Error message was sent
			return false;
		}
	
		if (empty($path)) {
			if (isset($_SERVER['PATH_INFO'])) {
				$path = $_SERVER['PATH_INFO'];
			} else {
				$path = '/';
			}
		}
	
		$request = $this->createRequest($path);
		$response = $this->check_authentication();
		
		if (! is_wp_error($response)) {
			$response = $this->dispatch($request);
		}
		
		// Normalize to either WP_Error or WP_REST_Response...
		$response = rest_ensure_response($response);
		
		// ...then convert WP_Error across.
		if (is_wp_error($response)) {
			$response = $this->error_to_response($response);
		}
		
		/**
		 * Filter the API response.
		 *
		 * Allows modification of the response before returning.
		 *
		 * @since 4.4.0
		 * @since 4.5.0 Applied to embedded responses.
		 *
		 * @param WP_HTTP_Response $result  Result to send to the client. Usually a WP_REST_Response.
		 * @param WP_REST_Server   $this    Server instance.
		 * @param WP_REST_Request  $request Request used to generate the response.
		 */
		$response = apply_filters('rest_post_dispatch', rest_ensure_response($response), $this, $request);
	
		// Wrap the response in an envelope if asked for.
		if (isset($_GET['_envelope'])) {
			$response = $this->envelope_response($response, isset($_GET['_embed']));
		}
	
		return $this->sendResponse($request, $response, $jsonpCallback);
	}

	protected function discoverJsonpCallback(array $globals = null) {

		$globals || $globals = &$GLOBALS;

		if (! isset($globals['_GET'][self::JSONP_PARAM])) {
			return null;
		}

		if (! Manager::instance()->isJsonpEnabled()) {
			echo $this->json_error('rest_callback_disabled', __('JSONP support is disabled on this site.'), 400);
			return false;
		}

		$callback = $globals['_GET'][self::JSONP_PARAM];

		// Check for invalid characters (only alphanumeric allowed).
		if (is_string($callback)) {
			$callback = preg_replace('/[^\w\.]/', '', wp_unslash($callback), -1, $illegal_char_count);
			if (0 !== $illegal_char_count) {
				$callback = null;
			}
		}
			
		if (null === $callback) {
			echo $this->json_error('rest_callback_invalid', __('The JSONP callback function is invalid.'), 400);
			return false;
		}

		return $callback;
	}

	protected function createRequest($path, array $globals = null, $raw_data = null) {

		$globals || $globals = &$GLOBALS;
		$server = $globals['_SERVER'];
		$get = $globals['_GET'];

		$request = Manager::instance()->createRequest($server['REQUEST_METHOD'], $path);
		$request->set_query_params(wp_unslash($get));
		$request->set_body_params(wp_unslash($globals['_POST']));
		$request->set_file_params($globals['_FILES']);
		$request->set_headers($this->get_headers(wp_unslash($server)));
		$request->set_body($raw_data ?: $this->get_raw_data());

		// HTTP method override for clients that can't use PUT/PATCH/DELETE.
		// First, we check $_GET['_method'].
		// If that is not set, we check for the HTTP_X_HTTP_METHOD_OVERRIDE header.
		if (isset($get[self::METHOD_OVERRIDE_PARAM])) {
			$request->set_method($get[self::METHOD_OVERRIDE_PARAM]);
		} else if (isset($server['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
			$request->set_method($server['HTTP_X_HTTP_METHOD_OVERRIDE']);
		}

		return $request;
	}

	protected function sendInitialHeaders(array $globals = null) {
		
		$globals || $globals = &$GLOBALS;
		$content_type = isset($globals['_GET'][self::JSONP_PARAM]) ? 'application/javascript' : 'application/json';
		
		$this->send_header('Content-Type', $content_type.'; charset='.get_option('blog_charset'));

		// Mitigate possible JSONP Flash attacks.
		// http://miki.it/blog/2014/7/8/abusing-jsonp-with-rosetta-flash/
		$this->send_header('X-Content-Type-Options', 'nosniff');
		$this->send_header('Access-Control-Expose-Headers', 'X-WP-Total, X-WP-TotalPages');
		$this->send_header('Access-Control-Allow-Headers', 'Authorization');

		/**
		 * Send nocache headers on authenticated requests.
		 *
		 * @since 4.4.0
		 *
		 * @param bool $rest_send_nocache_headers Whether to send no-cache headers.
		 */
		$no_cache = apply_filters('rest_send_nocache_headers', is_user_logged_in());

		if ($no_cache) {
			foreach (wp_get_nocache_headers() as $header => $header_value) {
				$this->send_header($header, $header_value);
			}
		}
	}

	protected function sendResponse(WP_REST_Request $request, WP_REST_Response $response, $jsonpCallback = null) {

		// Send extra data from response objects.
		$this->send_headers($response->get_headers());
		$this->set_status($response->get_status());

		/**
		 * Filter whether the request has already been served.
		 *
		 * Allow sending the request manually - by returning true, the API result
		 * will not be sent to the client.
		 *
		 * @since 4.4.0
		 *
		 * @param bool             $served		Whether the request has already been served.
		 *                                           Default false.
		 * @param WP_HTTP_Response $response	Result to send to the client. Usually a WP_REST_Response.
		 * @param WP_REST_Request  $request		Request used to generate the response.
		 * @param WP_REST_Server   $this		Server instance.
		 */
		$sent = apply_filters('rest_pre_serve_request', false, $response, $request, $this);

		if ($sent || 'HEAD' === $request->get_method()) {
			return null;
		}

		// Embed links inside the request.
		$result = $this->response_to_data($response, isset($_GET['_embed']));
			
		$jsonOptions = JSON_NUMERIC_CHECK;
		if (isset($_GET['dev'])) {
			$jsonOptions |= JSON_PRETTY_PRINT;
		}
			
		$result = json_encode($result, $jsonOptions);
			
		if (JSON_ERROR_NONE !== json_last_error()) {
			$wp_error = new WP_Error('rest_encode_error', json_last_error_msg(), ['status' => 500]);
			$result = $this->error_to_response($wp_error);
			$result = json_encode($result->data[0], $jsonOptions);
		}

		if ($jsonpCallback) {
			// Prepend '/**/' to mitigate possible JSONP Flash attacks
			// http://miki.it/blog/2014/7/8/abusing-jsonp-with-rosetta-flash/
			echo '/**/'.$jsonpCallback.'('.$result.')';
		} else {
			echo $result;
		}
			
		return true;
	}

}
