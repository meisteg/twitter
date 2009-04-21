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
 * @version     $Id: Twitter_Runtime.php,v 1.6 2008/06/04 03:13:27 blindman1344 Exp $
 */

PHPWS_Core::configRequireOnce('twitter', 'config.php');

class Twitter_Runtime
{
    /**
     * Displays the Twitter status on the home page.
     */
    function show()
    {
        $key = Key::getCurrent();
        if (!empty($key) && $key->isHomeKey() && PHPWS_Settings::get('twitter', 'enabled'))
        {
            $user = PHPWS_Settings::get('twitter', 'twitter_username');
            $statuses = Twitter_Runtime::query($user);

            if (!empty($statuses))
            {
                $statuses = unserialize($statuses);

                $title = PHPWS_Settings::get('twitter', 'status_box_title');
                $tags['TITLE'] = empty($title) ? NULL : $title;

                $tags['statuses'] = array();
                for ($i=0; ($i < sizeof($statuses)) && ($i < PHPWS_Settings::get('twitter', 'num_to_display')); $i++)
                {
                    $status_id = $statuses[$i]['ID'];
                    $status = array('TEXT' => PHPWS_Text::parseOutput($statuses[$i]['TEXT'], ENCODE_PARSED_TEXT, true),
                                    'TIME' => PHPWS_Time::relativeTime(Twitter_Runtime::str2time($statuses[$i]['CREATED_AT'])),
                                    'STATUS_URL' => "http://twitter.com/$user/statuses/$status_id");
                    $tags['statuses'][] = $status;
                }

                $tags['MORE_LABEL'] = dgettext('twitter', 'More');
                $tags['MORE_URL'] = "http://twitter.com/$user";

                Layout::add(PHPWS_Template::process($tags, 'twitter', 'statuses_box.tpl'), 'twitter', 'statuses');
            }
        }
    }

    /**
     * Query Twitter
     *
     * @param  string  Twitter username
     */
    function query($username)
    {
        $cache_key = 'twitter_' . $username;
        $url = "http://twitter.com/statuses/user_timeline/$username.xml";

        $statuses = PHPWS_Cache::get($cache_key);
        if (empty($statuses))
        {
            /* Check for curl lib, use in preference to file_get_contents if available. */
            if (function_exists('curl_init'))
            {
                /* Initiate session */
                $oCurl = curl_init($url);

                /* Set options */
                curl_setopt($oCurl, CURLOPT_RETURNTRANSFER,    true);
                curl_setopt($oCurl, CURLOPT_USERAGENT,         'phpWebSite');
                curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT,    TWITTER_CONNECT_TIMEOUT);
                curl_setopt($oCurl, CURLOPT_TIMEOUT,           TWITTER_TRANSFER_TIMEOUT);
                curl_setopt($oCurl, CURLOPT_DNS_CACHE_TIMEOUT, TWITTER_DNS_TIMEOUT);

                /* Request URL */
                if ($sResult = curl_exec($oCurl))
                {
                    /* Check for success. */
                    if (curl_getinfo($oCurl, CURLINFO_HTTP_CODE) == 200)
                    {
                        $xml_parser = xml_parser_create();
                        xml_parse_into_struct($xml_parser, $sResult, $arr_vals);
                        xml_parser_free($xml_parser);
                        $ordered_xml_array = PHPWS_Text::_orderXML($arr_vals);
                        $full_xml_array = getXMLLevel($ordered_xml_array, 1);
                    }
                }

                /* Close session */
                curl_close($oCurl);
            }
            else
            {
                $full_xml_array = PHPWS_Text::xml2php($url, 1);
            }

            if (isset($full_xml_array) && !empty($full_xml_array))
            {
                $tagged_xml_array = PHPWS_Text::tagXML($full_xml_array);
                $statuses = serialize($tagged_xml_array['STATUS']);
                PHPWS_Cache::save($cache_key, $statuses);
            }
        }

        return $statuses;
    }

    /**
     * Convert a Twitter time string into a timestamp.
     *
     * @param  string  Must be in this format: Sun Jun 01 13:25:50 +0000 2008
     */
    function str2time($string)
    {
        $time = strtotime($string);

        /* Check for PHP5 or PHP4 error return. */
        if (($time === false) || ($time < 0))
        {
            /* Changing the string order seems to help. */
            $new_string = substr($string, 0, 11) . substr($string, -4, 4) . substr($string, 10, 15);
            $time = strtotime($new_string);
        }

        return $time;
    }
}

?>