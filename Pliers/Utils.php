<?php
namespace Pliers;

class Utils {
	// Recursively convert an array to an object
	public function arrayToObject(array $in) {
		$obj = new \StdClass;

		foreach($in as $key => $value) {
			if(!is_array($value)) {
				$obj->{$key} = $value;
			} else {
				$obj->{$key} = $this->arrayToObject($value);
			}
		}

		return $obj;
	}
}
?>