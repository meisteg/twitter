<?php
/**
 * Twitter module for phpWebSite
 *
 * See docs/CREDITS for copyright information
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @author      Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
 * @version     $Id: config.php,v 1.1 2008/06/03 03:37:31 blindman1344 Exp $
 */

/**
 * Connection timeout in seconds.
 */
define('TWITTER_CONNECT_TIMEOUT', 2);

/**
 * Maximum time allowed for full transfer, specified in seconds.
 */
define('TWITTER_TRANSFER_TIMEOUT', 5);

/**
 * DNS timeout in seconds.  Default value is 1 day.
 */
define('TWITTER_DNS_TIMEOUT', 86400);

?>