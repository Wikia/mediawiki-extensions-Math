<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\Math\TexVC;

use MediaWiki\Extension\Math\TexVC\Nodes\TexArray;

class ParserUtil {
	public static function lst2arr( $l ) {
		$arr = new TexArray();

		while ( $l !== null ) {
			$first = $l->first();
			if ( $first !== null ) {
				$arr->push( $l->first() );
			}
			$l = $l->second();
		}

		return $arr;
	}

	public static function createOptions( $options ) {
		# get reference of the options for usage in functions and initialize with default values.
		$optionsBase = [
			'usemathrm' => false,
			'usemhchem' => false,
			'oldtexvc' => false,
			'oldmhchem' => false,
			'debug' => false
		];

		if ( $options != null ) {
			foreach ( $options as $key => $value ) {
				$optionsBase[$key] = $value;
			}
		}
		return $optionsBase;
	}
}
