<?php
/*

Copyright (c) 2009, SilverStripe Australia PTY LTD - www.silverstripe.com.au
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
 * Output a link that can be used to launch a dialog popin window
 * 
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 */
class Helper_DialogPopin extends NovemberHelper
{
    //put your code here
	public function DialogPopin($name, $label, $url = false, $options = array(), $extra = '', $tag='a')
	{
		?>
<script type="text/javascript">
$().ready(function () { createDialogDiv('<?php echo $this->view->escape($name) ?>') });
</script>
		<?php
		if ($url) {
			$options['url'] = $url;
		}

		if (isset($options['reload'])) {
			$options['onClose'] = 'function () { window.location.reload(false) }';
		}

		$optStr = Zend_Json::encode($options);
		$optStr = preg_replace('/"function\w*\((.+?)}"/e', "'function ('.Helper_DialogPopin::replaceQuotes('$1').'}'", $optStr);
		
		$closeTag = '>'.$label.'</a>';
		if ($tag != 'a') {
			$closeTag = ' type="button" value="'.$label.'" />';
		}

		$str = '<'.$tag.' '.$extra.' href="#" onclick=\'$("#'.$name.'").simpleDialog('.$optStr.'); return false;\' '.$closeTag;
		echo $str;
	}

	public static function replaceQuotes($str)
	{
		$v = str_replace('\\\\"', '"', $str);
		return $v;
	}
}
?>