<?php

include_once 'extensions/codereview/model/Codereview.php';

class SvnService /* implements SourceControlManager */
{
    /**
     * DbService
     *
     * @var DbService
     */
    public $dbService;
    
    public $projectService;
    
    
    public function getRevisionLog($start, $url)
    {
    	
        $url = $this->setAuthParams($url);
        $log = svn_log($url, $start);

        return $log;
    }
    
    private function setAuthParams($url)
    {
    	set_time_limit(240);
        $pass = $user = ''; 
    	if (preg_match("|//(.*?):(.*?)@|", $url, $matches)) {
    		$user = $matches[1];
    		$pass = $matches[2];
    		$url = preg_replace("|//(.*?):(.*?)@|", "//", $url);

    		svn_auth_set_parameter(SVN_AUTH_PARAM_DEFAULT_USERNAME, $user);
			svn_auth_set_parameter(SVN_AUTH_PARAM_DEFAULT_PASSWORD, $pass);
    	}
    	
    	return rtrim($url, "/");
    }
    
    public function getRawDiff($start, $end, $url)
    {
    		
    	$url = $this->setAuthParams($url);
    	
    	list($diff, $errors) = svn_diff($url, $start, $url, $end);

		if (!$diff) {
			throw new Exception("Failed finding diff");
		}
		
		$contents = '';
		while (!feof($diff)) {
		  $contents .= fread($diff, 8192);
		}
		fclose($diff);
		fclose($errors);
		return $contents;
    }
    
    public function getDiffFromContent($data)
    {
		return $this->parseRawDiff($data);
    }
    
    private function parseUnifiedDiff($diff)
    {
        $edits = array();
        $end = count($diff) - 1;
        for ($i = 0; $i < $end;) {
            $diff1 = array();
            switch (substr($diff[$i], 0, 1)) {
            case ' ':
                do {
                    $diff1[] = substr($diff[$i], 1);
                } while (++$i < $end && substr($diff[$i], 0, 1) == ' ');
                $edits[] = new Text_Diff_Op_copy($diff1);
                break;
            case '+':
                // get all new lines
                do {
                    $diff1[] = substr($diff[$i], 1);
                } while (++$i < $end && substr($diff[$i], 0, 1) == '+');
                $edits[] = new Text_Diff_Op_add($diff1);
                break;
            case '-':
                // get changed or removed lines
                $diff2 = array();
                do {
                    $diff1[] = substr($diff[$i], 1);
                } while (++$i < $end && substr($diff[$i], 0, 1) == '-');
 
                while ($i < $end && substr($diff[$i], 0, 1) == '+') {
                    $diff2[] = substr($diff[$i++], 1);
                }
                if (count($diff2) == 0) {
                    $edits[] = new Text_Diff_Op_delete($diff1);
                } else {
                    $edits[] = new Text_Diff_Op_change($diff1, $diff2);
                }
                break;
            default:
                $i++;
                break;
            }
        }
 

        return $edits;
    }
    
    public function parseRawDiff($content)
    {
        $lines = explode("\n", $content);
        
        $diff = new RevisionDiff();
        $currentDiff = null;
        $currentUniDiff = array();

        foreach ($lines as $line) {
            // we wait till we get an "Index" which indicates a new entry
            if (mb_strpos($line, "Index:") === 0) {
                if ($currentDiff != null) {
                    // work with everything in the uni diff
                    $parsedDiff = $this->parseUnifiedDiff($currentUniDiff);

                    $currentDiff->diffOps = $parsedDiff;

                    $diff->diffs[] = $currentDiff;
                    $currentUniDiff = array();
                }

                $currentDiff = new FileDiff();
                $currentDiff->name = trim(str_replace("Index: ", "", $line));
                continue;
            }

            if (mb_strpos($line, "@@") === 0) {
                // set the current line info into the file diff
                $fileLineInfo = str_replace("@@ ", "", $line);
                $fileLineInfo = str_replace(" @@", "", $fileLineInfo);
                $currentDiff->setLines ($fileLineInfo);
                continue;
            }

            if (mb_strpos($line, '---') === 0) {
                continue;
            }
            if (mb_strpos($line, '+++') === 0) {
                continue;
            }
            
            // now collect all the other lines
            if (mb_strpos($line, ' ')===0 || mb_strpos($line, '-')===0 || mb_strpos($line, '+')===0) {
                $currentUniDiff[] = $line;
            }
        }
        
        // if we still have a diff, add it in
        if ($currentDiff != null) {
            $parsedDiff = $this->parseUnifiedDiff($currentUniDiff);
            $currentDiff->diffOps = $parsedDiff;
            $diff->diffs[] = $currentDiff;
        }

        return $diff;
    }
}

class RevisionDiff
{
    public $diffs = array();
}

class FileDiff
{
    public $name;
    public $lines;
    public $oldStartLine;
    public $newStartLine;
    public $oldEndLine;
    public $newEndLine;
    
    public $diffOps = array();
    
    public function setLines($val)
    {
        if (preg_match("|\-(\d+),(\d+) \+(\d+),(\d+)|", $val, $matches)) {
            $this->oldStartLine = $matches[1];
            $this->oldEndLine = $matches[2];
            $this->newStartLine = $matches[3];
            $this->newEndLine = $matches[4];
        }
    }
}

class Text_Diff_Op {

    public $orig;
    public $final;

    function reverse()
    {
        trigger_error('Abstract method', E_USER_ERROR);
    }

    function norig()
    {
        return $this->orig ? count($this->orig) : 0;
    }

    function nfinal()
    {
        return $this->final ? count($this->final) : 0;
    }

}

class Text_Diff_Op_copy extends Text_Diff_Op {

    function Text_Diff_Op_copy($orig, $final = false)
    {
        if (!is_array($final)) {
            $final = $orig;
        }
        $this->orig = $orig;
        $this->final = $final;
    }

    function reverse()
    {
        return $reverse = new Text_Diff_Op_copy($this->final, $this->orig);
    }

}

class Text_Diff_Op_delete extends Text_Diff_Op {

    function Text_Diff_Op_delete($lines)
    {
        $this->orig = $lines;
        $this->final = false;
    }

    function reverse()
    {
        return $reverse = new Text_Diff_Op_add($this->orig);
    }

}

class Text_Diff_Op_add extends Text_Diff_Op {

    function Text_Diff_Op_add($lines)
    {
        $this->final = $lines;
        $this->orig = false;
    }

    function reverse()
    {
        return $reverse = new Text_Diff_Op_delete($this->final);
    }

}

class Text_Diff_Op_change extends Text_Diff_Op {

    function Text_Diff_Op_change($orig, $final)
    {
        $this->orig = $orig;
        $this->final = $final;
    }

    function reverse()
    {
        return $reverse = new Text_Diff_Op_change($this->final, $this->orig);
    }

}

?>