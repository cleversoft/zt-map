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

jimport('joomla.application.component.view');

/**
 * @package    Ztmap
 * @subpackage com_ztmap
 */
class ZtmapViewSitemap extends JViewLegacy
{

    protected $item;
    protected $list;
    protected $form;
    protected $state;

    /**
     * Display the view
     *
     * @access    public
     */
    function display($tpl = null)
    {
        $app = JFactory::getApplication();
        $this->state = $this->get('State');
        $this->item = $this->get('Item');
        $this->form = $this->get('Form');

        // Check for errors.
        if (count($errors = $this->get('Errors')))
        {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }

        // Convert dates from UTC
        $offset = $app->getCfg('offset');
        if (intval($this->item->created))
        {
            $this->item->created = JHtml::date($this->item->created, '%Y-%m-%d %H-%M-%S', $offset);
        }

        $this->_setToolbar();

        parent::display($tpl);
        JRequest::setVar('hidemainmenu', true);
    }

    /**
     * Display the view
     *
     * @access    public
     */
    function navigator($tpl = null)
    {
        require_once(JPATH_COMPONENT_SITE . '/helpers/ztmap.php');
        $app = JFactory::getApplication();
        $this->state = $this->get('State');
        $this->item = $this->get('Item');

        # $menuItems = ZtmapHelper::getMenuItems($item->selections);
        # $extensions = ZtmapHelper::getExtensions();
        // Check for errors.
        if (count($errors = $this->get('Errors')))
        {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }

        JHTML::script('mootree.js', 'media/system/js/');
        JHTML::stylesheet('mootree.css', 'media/system/css/');

        $this->loadTemplate('class');
        $displayer = new ZtmapNavigatorDisplayer($state->params, $this->item);

        parent::display($tpl);
    }

    function navigatorLinks($tpl = null)
    {

        require_once(JPATH_COMPONENT_SITE . '/helpers/ztmap.php');
        $link = urldecode(JRequest::getVar('link', ''));
        $name = JRequest::getCmd('e_name', '');
        $Itemid = JRequest::getInt('Itemid');

        $this->item = $this->get('Item');
        $this->state = $this->get('State');
        $menuItems = ZtmapHelper::getMenuItems($item->selections);
        $extensions = ZtmapHelper::getExtensions();

        $this->loadTemplate('class');
        $nav = new ZtmapNavigatorDisplayer($state->params, $item);
        $nav->setExtensions($extensions);

        $this->list = array();
        // Show the menu list
        if (!$link && !$Itemid)
        {
            foreach ($menuItems as $menutype => &$menu)
            {
                $menu = new stdclass();
                #$menu->id = 0;
                #$menu->menutype = $menutype;

                $node = new stdClass;
                $node->uid = "menu-" . $menutype;
                $node->menutype = $menutype;
                $node->ordering = $item->selections->$menutype->ordering;
                $node->priority = $item->selections->$menutype->priority;
                $node->changefreq = $item->selections->$menutype->changefreq;
                $node->browserNav = 3;
                $node->type = 'separator';
                if (!$node->name = $nav->getMenuTitle($menutype, @$menu->module))
                {
                    $node->name = $menutype;
                }
                $node->link = '-menu-' . $menutype;
                $node->expandible = true;
                $node->selectable = false;
                //$node->name = $this->getMenuTitle($menutype,@$menu->module);    // get the mod_mainmenu title from modules table

                $this->list[] = $node;
            }
        } else
        {
            $parent = new stdClass;
            if ($Itemid)
            {
                // Expand a menu Item
                $items = &JSite::getMenu();
                $node = & $items->getItem($Itemid);
                if (isset($menuItems[$node->menutype]))
                {
                    $parent->name = $node->title;
                    $parent->id = $node->id;
                    $parent->uid = 'itemid' . $node->id;
                    $parent->link = $link;
                    $parent->type = $node->type;
                    $parent->browserNav = $node->browserNav;
                    $parent->priority = $item->selections->{$node->menutype}->priority;
                    $parent->changefreq = $item->selections->{$node->menutype}->changefreq;
                    $parent->menutype = $node->menutype;
                    $parent->selectable = false;
                    $parent->expandible = true;
                }
            } else
            {
                $parent->id = 1;
                $parent->link = $link;
            }
            $this->list = $nav->expandLink($parent);
        }

        parent::display('links');
        exit;
    }

    /**
     * Display the toolbar
     *
     * @access    private
     */
    function _setToolbar()
    {
        $isNew = ($this->item->id == 0);

        JToolBarHelper::title(JText::_('ZTMAP_PAGE_' . ($isNew ? 'ADD_SITEMAP' : 'EDIT_SITEMAP')), 'article-add.png');

        JToolBarHelper::apply('sitemap.apply', 'JTOOLBAR_APPLY');
        JToolBarHelper::save('sitemap.save', 'JTOOLBAR_SAVE');
        JToolBarHelper::save2new('sitemap.save2new');
        if (!$isNew)
        {
            JToolBarHelper::save2copy('sitemap.save2copy');
        }
        JToolBarHelper::cancel('sitemap.cancel', 'JTOOLBAR_CLOSE');
    }

}
