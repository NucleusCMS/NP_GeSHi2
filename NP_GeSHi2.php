<?
/**
 *	NP_GeSHi2 for Nucleus CMS (http://www.nucleuscms.org/)
 *	
 *	This plugin integrates GeSHi - the Generic Syntax Highlighter - into
 *	Nucleus CMS. 
 *	
 *	It can hihglight code blocks with
 *	<pre class="languageName">...</pre>
 *	and inline code with
 *	<code class="languageName">...</code>
 *	The pre and code tags will replaced with div and span tags and the enclosed
 *	code will be highlighted with the GeSHi library
 *	
 *	The GeSHi library must be downloaded separately from the GeSHi web site:
 *	http://qbnz.com/highlighter/
 *	
 *	The code from GeSHi must be copied into the folder /nucleus/plugins so that
 *	the main file can be found under /nucleus/plugins/geshi/geshi.php
 *	
 *  Please visit the Nucleus plugin wiki (http://wiki.nucleuscms.org/plugin)
 *  for additional information.  
 *
 *	Versions:
 *		0.1  2007-11-10 kg (Kai Greve - http://kgblog.de):
 *			- initial release  
 *			- built with GeSHi version 1.0.7.20
 *
 */

include_once('geshi/geshi.php');
 
class NP_GeSHi2 extends NucleusPlugin {

	function getName() { return 'NP_GeSHi2'; }
	function getAuthor()  { return 'Kai Greve'; }
	function getURL()  { return 'http://kgblog.de/'; }
	function getVersion() { return '0.1'; }
	function getDescription() {
		return 'Integrates GeSHi - the Generic Syntax Highlighter - into Nucleus CMS. GeSHi can highlight code from several programming languages.';
	}

	function getEventList() {
		return array('PreItem', 'PreComment');
	}

	function install() {
		$this->createOption('pre_header','Header for pre','text','<div class="###language###">');
		$this->createOption('pre_footer','Footer for pre','text','</div>');
		
		$this->createOption('code_header','Header for code','text','<span class="###language###">');
		$this->createOption('code_footer','Footer for code','text','</span>');
	}
 
	function geshi ($code) {
		global $blog;

		// remove line breaks if the blog add them
		if ($blog->settings['bconvertbreaks']==1) {
			$code[4]=removeBreaks($code[4]);
		}
    
		$output='';

		$output=$code[4];

		// highlight the code with GeSHi		
		$geshi =& new GeSHi($output, $code[2]);
		
		$geshi->set_header_type(GESHI_HEADER_NONE);
		$output=$geshi->parse_code();
  
		if (stristr ($code[1], 'pre')) {
			$my_header=$this->getOption('pre_header');
			$my_footer=$this->getOption('pre_footer');
		}
		else {
			$my_header=$this->getOption('code_header');
			$my_footer=$this->getOption('code_footer');
		}
  
		// replace ###language### with the actual language
		$my_header=str_replace('###language###', $code[2], $my_header);
  
  		// add header and footer to the code
		$output=$my_header.$output.$my_footer;
  
		return $output;  
	}
 
	function find_code($text) {
		global $CONF, $blog;

		$text = preg_replace_callback('/(\<pre class=\")(.*)(\"\>)(.*)(\<\/pre\>)/Usi',  array(&$this, 'geshi'), $text);
     
		$text = preg_replace_callback('/(\<code class=\")(.*)(\"\>)(.*)(\<\/code\>)/Usi',  array(&$this, 'geshi'), $text);
 
		return $text;
	}
 
	function event_PreItem($_data) {
		$_data[item]->body = $this->find_code($_data[item]->body);
		$_data[item]->more = $this->find_code($_data[item]->more);
	}
 
	function event_PreComment($_data) {
		$_data['comment']['body'] = $this->find_code($_data['comment']['body']);
	}

	function supportsFeature ($what) {
		switch ($what)
		{
			case 'SqlTablePrefix':
				return 1;
			default:
				return 0;
		}
	}
}
?>
