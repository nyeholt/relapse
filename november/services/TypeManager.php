<?php
/*
Copyright (c) 2006-2007, Marcus Nyeholt
All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

    * Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the
      documentation and/or other materials provided with the distribution.
    * Neither the name of SilverStripe nor the names of its contributors may be used to endorse or promote products derived from this software
      without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE
GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT,
STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY
OF SUCH DAMAGE.
*/

/**
 * Manages types for a given
 *
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 */
class TypeManager implements Configurable
{
	private static $CACHE_FILE = '__type_cache.php';

	/**
	 * The map of types
	 *
	 * @var array
	 */
	private $typeMap;

	public function configure($config)
    {
        try {
            Zend_Loader::loadFile(ifset($config, 'type_cache', self::$CACHE_FILE), 'data/cache', true);
            global $__TYPE_CACHE;
            if (isset($__TYPE_CACHE)) {
                $this->typeMap = $__TYPE_CACHE;
            } else {
                $this->typeMap = array();
            }
        } catch (Zend_Exception $e) {
        }
    }


    /**
     * Ensure that a given type is actually included
     *
     * @param string $class
     */
    public function includeType($class)
    {
        if (empty($class)) throw new Exception("Cannot include null type");

        $class = str_replace('.', '_', $class);

        $dir         = 'model';
        $file        = $class.'.php';

        $source = $dir.DIRECTORY_SEPARATOR.$file;
		$found = false;
        if (!Zend_Loader::isReadable($source)) {
            $extensions = za()->getExtensions();
            foreach ($extensions as $extDir) {
                $source = $extDir.DIRECTORY_SEPARATOR.'model'.DIRECTORY_SEPARATOR.$file;
                if (Zend_Loader::isReadable($source)) {
					$found = true;
                    break;
                }
            }
        } else {
			$found = true;
		}

		if (!$found && endswith($class, 'Version')) {
			// try including the non-version instance instead
			return $this->includeType(substr($class, 0, strrpos($class, 'Version')));
		}

        try {
            Zend_Loader::loadFile(basename($source), dirname($source), true);
        } catch (Zend_Exception $ze) {
            // ignore it, we'll just assume it was loaded elsewhere
        }

        if (!class_exists($class)) {
            throw new Exception("Class $class not found in the model directory");
        }
    }

	/**
     * Get the type map for a given class.
	 *
	 * Returns information about a type in the format
	 *
	 * 'type' => array (
			'description' => 'unknown',
			'projectid' => 'unknown',
			'title' => 'unknown',
			'userid' => 'array',
			'status' => 'unknown',
			'startdate' => 'unknown',
			'due' => 'unknown',
			'complete' => 'unknown',
			'timespent' => 'unknown',
			'estimated' => 'unknown',
			'dependency' => 'unknown',
			'category' => 'string',
			'updated' => 'unknown',
			'created' => 'unknown',
			'creator' => 'unknown',
		  ),
	 *
     */
    public function getTypeMap($class)
    {
        if (!isset($this->typeMap[$class])) {
            $this->cacheType($class);
            if (!isset($this->typeMap[$class])) {
                throw new Exception("Tried getting row for unmapped type $class");
            }
        }

        return $this->typeMap[$class];
    }

    /**
     * Creates the cache type each time there's a new
     * type called.
     *
     * @param unknown_type $type
     */
    protected function cacheType($type)
    {
        $info = new ReflectionClass($type);
        $properties = $info->getProperties();
        $toMap = array();
        foreach ($properties as $property) {
            // If it's public, then we'll use it
            /* @var $property ReflectionProperty  */
            if ($property->isPublic() && $property->getName() != 'id' && $property->getName() != 'constraints' && $property->getName() != 'requiredFields' && $property->getName() != 'searchableFields') {
                $propType = $this->getTypeForProperty($property);
                if ($propType == "unmapped") continue;
                $toMap[$property->getName()] = $propType;
            }
        }

        $this->typeMap[$type] = $toMap;
        $code = '<?php
        global $__TYPE_CACHE;
        $__TYPE_CACHE = '.var_export($this->typeMap, true).';
        ?>';
        $cacheFile = APP_DIR.'/data/cache/'.self::$CACHE_FILE;
        $fp = fopen($cacheFile, "w");
        if ($fp) {
            fwrite($fp, $code);
            fclose($fp);
        }
    }


    /**
     * Get the type for a property definition
     *
     * Checks the @doccomment field of the property to see if
     * the user has type hinted at all by using @var <type>
     */
    protected function getTypeForProperty(ReflectionProperty $property)
    {
        $comment = $property->getDocComment();
        $type = "unknown";
        if (mb_strlen($comment)) {
            // use a regex to find what we're after.
            if (preg_match("/@var ([\w\d_]+)/", $comment, $matches)) {
                $type = mb_strtolower($matches[1]);
            }
        }
        return $type;
    }
}
?>