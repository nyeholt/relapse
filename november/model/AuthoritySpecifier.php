<?php
/* All code covered by the BSD license located at http://silverstripe.org/bsd-license/ */

/**
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 */
interface AuthoritySpecifier {
	/**
	 * Return the name of this authority.
	 *
	 * Lets the code restrict the name of the authority
	 */
    public function getAuthorityName();
}