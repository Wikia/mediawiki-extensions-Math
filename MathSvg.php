<?php
/**
 * MediaWiki math extension
 *
 * (c) 2002-2012 Tomasz Wegrzanowski, Brion Vibber, Moritz Schubotz and other MediaWiki contributors
 * GPLv2 license; info in main package.
 *
 * Contains everything related to <math> </math> parsing
 * @file
 */


/**
 * Takes LaTeX fragments and outputs the source directly to the browser
 *
 * @author Tomasz Wegrzanowski
 * @author Brion Vibber
 * @author Moritz Schubotz
 * @ingroup Parser
 */
class MathSvg extends MathRenderer {
	private $svg='';
	/**
	 * Renders TeX by outputting it to the browser in a span tag
	 *
	 * @return string span tag with TeX
	 */
	function getHtmlOutput() {
		# No need to render or parse anything more!
		# New lines are replaced with spaces, which avoids confusing our parser (bugs 23190, 22818)
		return Xml::element( 'span',
			$this->getAttributes(
				'span',
				array(
					'class' => 'tex',
					'dir' => 'ltr'
				)
			),
			'$ ' . str_replace( "\n", " ", $this->getTex() ) . ' $'
		);
	}
	/**
	 * No rendering required in plain text mode
	 * @return boolean
	 */
	function render(){
		global $wgMathLaTeXMLTimeout;
		$post = $this->getTex();
		$host = 'http://localhost:16000/';
		$options = array( 'method' => 'POST', 'postData' => $post, 'timeout' => $wgMathLaTeXMLTimeout );
		$req = MWHttpRequest::factory( $host, $options );
		$status = $req->execute();
		if ( $status->isGood() ) {
			$this->svg = $req->getContent();
			return true;
		} else {
			return false;
		}
	}
	public function getSvg(){
		return $this->svg;
	}
}