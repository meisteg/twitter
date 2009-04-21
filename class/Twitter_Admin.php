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
 * @version     $Id: Twitter_Admin.php,v 1.2 2008/06/02 02:27:03 blindman1344 Exp $
 */

class Twitter_Admin
{
    function action($action)
    {
        if (!Current_User::allow('twitter'))
        {
            Current_User::disallow();
            return;
        }

        switch ($action)
        {
            case 'settings':
                $template['CONTENT'] = Twitter_Admin::editSettings();
                break;

            case 'postSettings':
                Twitter_Admin::postSettings();
                break;
        }

        $template['TITLE'] = dgettext('twitter', 'Twitter Settings');
        $template['MESSAGE'] = Twitter_Admin::getMessage();

        $display = PHPWS_Template::process($template, 'twitter', 'admin.tpl');
        Layout::add(PHPWS_ControlPanel::display($display));
    }

    function sendMessage($message, $command)
    {
        $_SESSION['twitter_message'] = $message;
        PHPWS_Core::reroute(PHPWS_Text::linkAddress('twitter', array('action'=>$command), true));
    }

    function getMessage()
    {
        if (isset($_SESSION['twitter_message']))
        {
            $message = $_SESSION['twitter_message'];
            unset($_SESSION['twitter_message']);
            return $message;
        }

        return NULL;
    }

    function editSettings()
    {
        $form = new PHPWS_Form;
        $form->addHidden('module', 'twitter');
        $form->addHidden('action', 'postSettings');

        $form->addText('twitter_username', PHPWS_Settings::get('twitter', 'twitter_username'));
        $form->setLabel('twitter_username', dgettext('twitter', 'Username'));
        $form->setSize('twitter_username', 40, 200);

        $form->addCheck('enabled');
        $form->setMatch('enabled', PHPWS_Settings::get('twitter', 'enabled'));
        $form->setLabel('enabled', dgettext('twitter', 'Enable'));

        $form->addText('status_box_title', PHPWS_Settings::get('twitter', 'status_box_title'));
        $form->setLabel('status_box_title', dgettext('twitter', 'Title'));
        $form->setSize('status_box_title', 40, 200);

        $form->addText('num_to_display', PHPWS_Settings::get('twitter', 'num_to_display'));
        $form->setLabel('num_to_display', dgettext('twitter', 'Number of Updates to Display (1-20)'));
        $form->setSize('num_to_display', 2, 2);

        $form->addSubmit('submit', dgettext('twitter', 'Update Settings'));

        $template = $form->getTemplate();
        $template['NOTICE'] = dgettext('twitter', 'This module uses the Twitter API but is not endorsed or certified by Twitter.');
        $template['STATUS_BOX_LEGEND'] = dgettext('twitter', 'Status Box');
        $template['USER_INFO_LEGEND'] = dgettext('twitter', 'Twitter User Information');

        return PHPWS_Template::process($template, 'twitter', 'settings.tpl');
    }

    function postSettings()
    {
        PHPWS_Core::initModClass('twitter', 'Twitter_Runtime.php');

        $success_msg      = dgettext('twitter', 'Your settings have been successfully saved.');
        $error_saving_msg = dgettext('twitter', 'Error saving the settings. Check error log for details.');
        $error_inputs_msg = dgettext('twitter', 'Missing or invalid input. Please correct and try again.');
        $ret_msg          = &$error_inputs_msg;

        $twitter_username = trim($_POST['twitter_username']);
        $num_to_display   = trim($_POST['num_to_display']);
        $status_box_title = trim($_POST['status_box_title']);

        /* Verify valid number to display. */
        if (is_numeric($num_to_display) && ($num_to_display > 0) && ($num_to_display <= 20))
        {
            /* Verify valid username input. */
            if (!empty($twitter_username) || !isset($_POST['enabled']))
            {
                PHPWS_Settings::set('twitter', 'enabled',          0                );
                PHPWS_Settings::set('twitter', 'twitter_username', $twitter_username);
                PHPWS_Settings::set('twitter', 'num_to_display',   $num_to_display  );
                PHPWS_Settings::set('twitter', 'status_box_title', $status_box_title);

                if (isset($_POST['enabled']))
                {
                    $test = Twitter_Runtime::query($twitter_username);
                    if (!empty($test))
                    {
                        PHPWS_Settings::set('twitter', 'enabled', 1);
                        $ret_msg = &$success_msg;
                    }
                }
                else
                {
                    $ret_msg = &$success_msg;
                }
            }
        }

        if (PHPWS_Error::logIfError(PHPWS_Settings::save('twitter')))
        {
            $ret_msg = &$error_saving_msg;
        }

        Twitter_Admin::sendMessage($ret_msg, 'settings');
    }

}// END CLASS Twitter_Admin

?>