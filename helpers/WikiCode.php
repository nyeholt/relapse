<?php

include_once dirname(__FILE__).'/class_WikiParser.php';

/**
 * Shortcut to output safe text. 
 *
 */
class Helper_WikiCode extends NovemberHelper 
{
    private $parser;
    private $images = array('jpg', 'jpeg', 'png', 'gif');
    
    public function __construct()
    {
        $this->parser = new WikiParser();
    }

	public function WikiCode($text)
	{
		$val = $this->view->escape($text);
		$output = $this->parser->parse($val);
		// do some matching for automatically creating URLs between items
		$output = $this->replacePastedUrls($output);
		$output = $this->replaceRequestUrls($output);
		$output = $this->replaceTaskUrls($output);
		echo $output;
	}

	private function replacePastedUrls($text)
	{
		// get all HTTP and replace with <a href=>
		if (preg_match_all('"\b(https?|ftp|file)://([-A-Z0-9+&@#/%?=~_|!:,.;]*[-A-Z0-9+&@#/%=~_|])"is', $text, $matches)) {
			if (!isset($matches[0])) {
				return $text;
			}
			foreach ($matches[0] as $url) {
				// see what the extension is
				$extension = mb_substr($url, mb_strrpos($url, '.') + 1);
				$replacement = '<a href="'.$url.'" class="wiki-link">'.$url.'</a>';
				if (in_array($extension, $this->images)) {
					$replacement = '<a href="'.$url.'"><img src="'.$url.'" class="wiki-image" /></a>';
				}

				$text = str_replace($url, $replacement, $text);
			}
			return $text;
		}
		
		return $text;
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