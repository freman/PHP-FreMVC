<?php
namespace MVC;
/**
 * Cryptography
 *
 * @author Shannon Wynter <http://fremnet.net/contact>
 * @package FreMVC
 * @version 0.1
 * @since 0.1
 * @copyright Fremnet.net
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/**
 * FreMVC Cryptography Class
 *
 * Provide some cryptographic functions
 *
 * @author Shannon Wynter <http://fremnet.net/contact>
 * @version 0.1
 * @since 0.1
 */
class Cryptography {
	/**
	 * Password
	 *
	 * Perform a 2 byte salt and hash a password
	 *
	 * Recommended use:
	 * 	$pass = $crypto->password('foo');
	 * 	$valid = $pass == $crypto->password('foo', $pass);
	 *
	 * Note: this method returns a binary string, it's up you to encode it.
	 *
	 * @param string $password to hash
	 * @param string $salt optional salt to use, usually pass a hashed password
	 * @return string
	 */
	public function password ($password, $salt = null) {
		if (is_null($salt)) {
			$salt = pack('S', rand(0,65535));
		}
		else {
			$salt = substr($salt, 0, 2);
		}

		return $salt . sha1($salt . $password, true);
	}
}