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

require_once(JPATH_COMPONENT_SITE . '/displayer.php');

class ZtmapNavigatorDisplayer extends ZtmapDisplayer
{

    public function __construct(&$config, &$sitemap)
    {
        $this->_list = array();
        $this->view = 'navigator';

        parent::__construct($config, $sitemap);
    }

    public function printNode(&$node)
    {
        if (!isset($node->selectable))
        {
            $node->selectable = true;
        }
        // For extentions that doesn't set this property as this is new in Ztmap 1.2.3
        if (!isset($node->expandible))
        {
            $node->expandible = true;
        }
        if (empty($this->_list[$node->uid]))
        { // Avoid duplicated items
            $this->_list[$node->uid] = $node;
        }
        return false;
    }

    public function &expandLink(&$parent)
    {
        $items = &JSite::getMenu();
        $extensions = &$this->_extensions;
        $rows = null;
        if (strpos($parent->link, '-menu-') === 0)
        {
            $menutype = str_replace('-menu-', '', $parent->link);
            // Get Menu Items
            $rows = $items->getItems('menutype', $menutype);
        } elseif ($parent->id)
        {
            $rows = $items->getItems('parent_id', $parent->id);
        }

        if ($rows)
        {
            foreach ($rows as $item)
            {
                if ($item->parent_id == $parent->id)
                {
                    $node = new stdclass;
                    $node->name = $item->title;
                    $node->id = $item->id;
                    $node->uid = 'itemid' . $item->id;
                    $node->link = $item->link;
                    $node->expandible = true;
                    $node->selectable = true;
                    // Prepare the node link
                    ZtmapHelper::prepareMenuItem($node);
                    if ($item->home)
                    {
                        $node->link = JURI::root();
                    } elseif (substr($item->link, 0, 9) == 'index.php' && $item->type != 'url')
                    {
                        if ($item->type == 'menulink')
                        {// For Joomla 1.5 SEF compatibility
                            $params = new JParameter($item->params);
                            $node->link = 'index.php?Itemid=' . $params->get('menu_item');
                        } elseif (strpos($item->link, 'Itemid=') === FALSE)
                        {
                            $node->link = 'index.php?Itemid=' . $node->id;
                        }
                    } elseif ($item->type == 'separator')
                    {
                        $node->selectable = false;
                    }
                    $this->printNode($node);  // Add to the internal list
                }
            }
        }
        if ($parent->id)
        {
            $option = null;
            if (preg_match('#^/?index.php.*option=(com_[^&]+)#', $parent->link, $matches))
            {
                $option = $matches[1];
            }
            $Itemid = JRequest::getInt('Itemid');
            if (!$option && $Itemid)
            {
                $item = $items->getItem($Itemid);
                $link_query = parse_url($item->link);
                parse_str(html_entity_decode($link_query['query']), $link_vars);
                $option = JArrayHelper::getValue($link_vars, 'option', '');
                if ($option)
                {
                    $parent->link = $item->link;
                }
            }
            if ($option)
            {
                if (!empty($extensions[$option]))
                {
                    $parent->uid = $option;
                    $className = 'ztmap_' . $option;
                    $result = call_user_func_array(array($className, 'getTree'), array(&$this, &$parent, $extensions[$option]->params));
                }
            }
        }
        return $this->_list;
        ;
    }

    public function &getParam($arr, $name, $def)
    {
        $var = JArrayHelper::getValue($arr, $name, $def, '');
        return $var;
    }

}
