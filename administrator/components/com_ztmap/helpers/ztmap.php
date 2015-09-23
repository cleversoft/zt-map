<?php

/**
 * @version       $Id$
 * @copyright     Copyright (C) 2007 - 2009 Joomla! Vargas. All rights reserved.
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 * @author        Guillermo Vargas (guille@vargas.co.cr)
 * 
 * @name        Zt Map
 * @version     0.0.5
 * @package     Joomla
 * @subpackage  Component
 * @author      ZooTemplate 
 * @email       support@zootemplate.com 
 * @link        http://www.zootemplate.com 
 * @copyright   Copyright (c) 2015 ZooTemplate
 * @license     GPL v2 
 * 
 */
// no direct access
defined('_JEXEC') or die;

/**
 * Ztmap component helper.
 *
 * @package     Ztmap
 * @subpackage  com_ztmap
 * @since       2.0
 */
class ZtmapHelper
{

    /**
     * Configure the Linkbar.
     *
     * @param    string  The name of the active view.
     */
    public static function addSubmenu($vName)
    {
        /**
         * @todo Use JHtmlSidebar::addEntry
         */
        JSubMenuHelper::addEntry(
                JText::_('Ztmap_Submenu_Sitemaps'), 'index.php?option=com_ztmap', $vName == 'sitemaps'
        );
        JSubMenuHelper::addEntry(
                JText::_('Ztmap_Submenu_Extensions'), 'index.php?option=com_plugins&view=plugins&filter_folder=xmap', $vName == 'extensions');
    }

}
