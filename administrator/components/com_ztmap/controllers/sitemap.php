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

jimport('joomla.application.component.controllerform');

/**
 * @package     Ztmap
 * @subpackage  com_ztmap
 * @since       2.0
 */
class ZtmapControllerSitemap extends JControllerForm
{

    /**
     * Method override to check if the user can edit an existing record.
     *
     * @param    array    An array of input data.
     * @param    string   The name of the key for the primary key.
     *
     * @return   boolean
     */
    protected function _allowEdit($data = array(), $key = 'id')
    {
        // Initialise variables.
        $recordId = (int) isset($data[$key]) ? $data[$key] : 0;

        // Assets are being tracked, so no need to look into the category.
        return JFactory::getUser()->authorise('core.edit', 'com_ztmap.sitemap.' . $recordId);
    }

}
