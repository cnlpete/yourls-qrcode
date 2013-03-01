<?php /*
Plugin Name: QR Code Short URLs
Plugin URI: http://github.com/cnlpete/yourls-qrcode
Description: Add .qr to shorturls to display QR Code
Version: 0.1.0
Author: Hauke Schade
Author URI: http://hauke-schade.de
(c) 2013+ Hauke Schade
*/

yourls_add_action( 'loader_failed', 'qrc_yourls_qrcode' );
yourls_add_filter( 'table_add_row_action_array', 'qrc_add_row_action_qrcode' );
yourls_add_action( 'html_head', 'qrc_add_qrcode_css_head' );

// Kick in if the loader does not recognize a valid pattern
function qrc_yourls_qrcode( $request ) {
	// Get authorized charset in keywords and make a regexp pattern
	$pattern = yourls_make_regexp_pattern( yourls_get_shorturl_charset() );

	// Shorturl is like bleh.qr ?
	if( preg_match( "@^([$pattern]+)\.qr?/?$@", $request[0], $matches ) ) {
		// this shorturl exists ?
		$keyword = yourls_sanitize_keyword( $matches[1] );
		if( yourls_is_shorturl( $keyword ) ) {
			include dirname(__FILE__) . "/phpqrcode/qrlib.php";

			$url = yourls_link( $keyword );

			// Show the QR code then!
			QRcode::png($url);
		}
	}
}

// Add our QR Code Button to the Admin interface
function qrc_add_row_action_qrcode( $links ) {
	global $keyword;
	$surl = yourls_link( $keyword );
	$id = yourls_string2htmlid( $keyword ); // used as HTML #id

	// We're adding .qr to the end of the URL, right?
	$qrlink = $surl . '.qr';

	// And add the button to the links in the actions
	$links['qrcode'] = array(
		'href'    => $qrlink,
		'id'      => "qrlink-$id",
		'title'   => 'QR Code',
		'anchor'  => 'QR Code',
	);

	return $links;
}

// Add the CSS to <head>
function qrc_add_qrcode_css_head( $context ) {
	foreach($context as $k)
		if( $k == 'index' ) // If we are on the index page, use this css code for the button
			print '<style type="text/css">td.actions .button_qrcode{background: url("data:image/png;base64,R0lGODlhEAAQAIAAAAAAAP///yH5BAAAAAAALAAAAAAQABAAAAIvjI9pwIztAjjTzYWr1FrS923NAymYSV3borJW26KdaHnr6UUxd4fqL0qNbD2UqQAAOw==") no-repeat scroll 2px center transparent;}</style>' . PHP_EOL;
}