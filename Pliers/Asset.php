<?php
namespace Pliers;

class Asset extends \Slim\Slim {
	protected static $assetBase = '/assets';

	protected static function getRoot() {
		return \Slim\Slim::getInstance()->appRoot();
	}

	protected static function attributeString($arr) {
		$str = '';

		foreach($arr as $key => $value) {
			$str .= $key . '="' . $value . '" ';
		}

		return rtrim($str);
	}

	protected static function assetBaseUri() {
		$url = parse_url($_SERVER['REQUEST_URI']);

		return rtrim($url['path'], '/') . self::$assetBase;
	}

	public static function css($path, $inline = false) {
		if(!$inline) {
			$path = self::assetBaseUri() . '/css/' . ltrim($path, '/');

			return '<link rel="stylesheet" href="' . $path . '">';
		} else {
			// TODO
		}
	}

	public static function js($path, $attributes = null, $inline = false) {
		if(!$inline) {
			$path = self::$assetBase . '/js/' . ltrim($path, '/');
			$content = '';
			$attributes['src'] = $path;
		} else {
			$file = self::getRoot() . '/' . self::$assetBase . '/js/' . ltrim($path, '/');

			$content = file_get_contents($file);
		}

		return '<script ' . self::attributeString($attributes) . '>' . $content . '</script>';
	}

	// Image path generator. Doesn't generate the tag. Can be used with a CDN in the future
	public static function image($path) {

	}
}
?>