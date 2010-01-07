<?php

// include_once 'stringparser_bbcode/stringparser_bbcode.class.php';
include_once 'lib/QuickerUbb.php';

/**
 * Shortcut to output safe text. 
 *
 */
class Helper_BbCode extends NovemberHelper 
{
    private $parser;
    
    public function __construct()
    {
        $this->parser = new ubbParser();
		$this->parser->text_handler = '';
    }

	public function BbCode($text, $newlines = true)
	{
		$val = $this->view->escape($text);
		$output = $this->parser->parse($val);
		if ($newlines) $output = nl2br($output);
		
		// do some matching for automatically creating URLs between items
		$output = $this->replaceRequestUrls($output);
		$output = $this->replaceTaskUrls($output);
		echo $output;
	}
	
	private function replaceRequestUrls($text)
	{
		$regex = "/Request (\d+)/is";
		
		if (preg_match_all($regex, $text, $matches)) {
			if (isset($matches[1])) {
				// these are the request ids, so use them in the new url
				foreach ($matches[1] as $id) {
					$text = str_ireplace('request '.$id, '<a href="'.build_url('issue', 'edit', array('id'=> $id)).'">Request #'.$id.'</a>', $text);
				}
			}
		}
		
		return $text;
	}
	
	private function replaceTaskUrls($text)
	{
		$regex = "/Task (\d+)/is";
		
		if (preg_match_all($regex, $text, $matches)) {
			if (isset($matches[1])) {
				// these are the request ids, so use them in the new url
				foreach ($matches[1] as $id) {
					$text = str_ireplace('task '.$id, '<a href="'.build_url('task', 'edit', array('id'=> $id)).'">Task #'.$id.'</a>', $text);
				}
			}
		}
		return $text;
	}
}


?>