<?php

class Helper_AtoZPager
{
    /**
     * @param array $letters the letters to be shown
     * @param string $name the name of the page
     * @param boolean $showHidden whether to show hidden values
     */
	public function AtoZPager($letters, $name='page', $showHidden = true, $params=array(), $currentLetter = 0, $prev = '&laquo;', $next='&raquo;')
	{
	    $total = count($letters);
	    if ($total == 0) {
	        return;
	    }
	    
	    $lettersToDisplay = array();
	    $activeLetters = array_flip($letters);
	    
	    if ($showHidden) {
	        $i = 0; 
	        while ($letters[$i] < 'A') {
	            $lettersToDisplay[] = $letters[$i++];
	        }
	        // Figure out how many of the letters between A and Z are used, and
            // if we need to account for more characters after Z

	        $lastCharacter = $i;
	        for ($i = ord('A'), $z = ord('Z'); $i <= $z; $i++) {
	            $lettersToDisplay[] = chr($i); 
	            $lastCharacter = ifset($activeLetters, chr($i), $lastCharacter);
	        }
	        
	        if ($lastCharacter++ < $total - 1) {
                // from last character + 1 => end of letters, 
                while (isset($letters[$lastCharacter])) {
                    $lettersToDisplay[] = $letters[$lastCharacter++];
                }
	        }
	    } else {
	        $lettersToDisplay = $letters;
	    }
	    
	    // If not set, check the request, otherwise just
	    // assume first page
	    if (!$currentLetter) {
	        if (isset($_GET[$name])) {
	            $currentLetter = urldecode($_GET[$name]);
 	        } else {
	            $currentLetter = $letters[0]; 	            
 	        }
	    }

	    // Figure out the current page number
        $currentPageNumber = array_search($currentLetter, $letters);
        
	    $args = '';
	    foreach ($params as $key => $value) {
	        if (strpos($value, '#') === 0) {
	            $args .= $value;
	        } else {
	            $args .= "&amp;$key=$value";
	        }
	    }
	    
	    echo '<div class="pager-links">';
	    if ($currentPageNumber > 0) {
	       echo '<a class="pager-prev-link pager-link" href="'.current_url().'?'.$name.'='.($letters[$currentPageNumber-1]).$args.'">'.$prev.'</a>';
	    }
	    

	    for ($i = 0, $c = count($lettersToDisplay); $i < $c; $i++) {
	        $letter = $lettersToDisplay[$i];
	        if ($letter == $currentLetter) {
	            echo '<span class="pager-current-link pager-link">'.$letter.'</span>';
	        } else {
	            
	            // If it's an active letter, link it
	            if (isset($activeLetters[$letter])) {
    	            echo '<a class="pager-link" href="'.current_url().'?'.$name.'='.urlencode($letter).$args.'"> '.$letter.' </a>';	                
	            } else {
	                echo '<span class="pager-inactive-link pager-link">'.$letter.'</span>';
	            }
	        }
	    }
	    
	    $finalPage = count($letters) - 1;
	    if ($currentPageNumber < $finalPage) {
	       echo '<a class="pager-next-link pager-link" href="'.current_url().'?'.$name.'='.($letters[$currentPageNumber+1]).$args.'">'.$next.'</a>';
	    }

	    echo '</div>';
	}
}

?>