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
 * @todo Use JFormField instead
 */
class JElementSitemap extends JElement
{

    /**
     * Element name
     *
     * @var    string
     */
    var $_name = 'Sitemap';

    public function fetchElement($name, $value, &$node, $control_name)
    {

        $db = JFactory::getDBO();
        $fieldName = $control_name . '[' . $name . ']';

        $sql = "SELECT id, name from #__ztmap_sitemap ORDER BY name";
        $db->setQuery($sql);
        $rows = $db->loadObjectList();

        $html = JHTML::_('select.genericlist', $rows, $fieldName, '', 'id', 'name', $value);

        return $html;
    }

}
