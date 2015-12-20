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
 * HTML Site map View class for the Ztmap component
 *
 * @package         Ztmap
 * @subpackage      com_ztmap
 * @since           0.0.5
 */
class ZtmapViewHtml extends JViewLegacy
{

    protected $state;
    protected $print;

    public function display($tpl = null)
    {
        // Initialise variables.
        $this->app = JFactory::getApplication();
        $this->user = JFactory::getUser();
        $doc = JFactory::getDocument();

        // Get view related request variables.
        $this->print = JRequest::getBool('print');

        // Get model data.
        $this->state = $this->get('State');
        $this->item = $this->get('Item');
        $this->items = $this->get('Items');

        $this->canEdit = JFactory::getUser()->authorise('core.admin', 'com_ztmap');

        // Check for errors.
        if (count($errors = $this->get('Errors')))
        {
            JError::raiseWarning(500, implode("\n", $errors));
            return false;
        }

        $this->extensions = $this->get('Extensions');
        // Add router helpers.
        $this->item->slug = $this->item->alias ? ($this->item->id . ':' . $this->item->alias) : $this->item->id;

        $this->item->rlink = JRoute::_('index.php?option=com_ztmap&view=html&id=' . $this->item->slug);

        // Create a shortcut to the paramemters.
        $params = &$this->state->params;
        $offset = $this->state->get('page.offset');
        if ($params->get('include_css', 0))
        {
            $doc->addStyleSheet(JURI::root() . 'components/com_ztmap/assets/css/xmap.css');
        }

        // If a guest user, they may be able to log in to view the full article
        // TODO: Does this satisfy the show not auth setting?
        if (!$this->item->params->get('access-view'))
        {
            if ($user->get('guest'))
            {
                // Redirect to login
                $uri = JFactory::getURI();
                $app->redirect(
                        'index.php?option=com_users&view=login&return=' . base64_encode($uri), JText::_('Ztmap_Error_Login_to_view_sitemap')
                );
                return;
            } else
            {
                JError::raiseWarning(403, JText::_('Ztmap_Error_Not_auth'));
                return;
            }
        }

        // Override the layout.
        if ($layout = $params->get('layout'))
        {
            $this->setLayout($layout);
        }

        // Load the class used to display the sitemap
        $this->loadTemplate('class');
        $this->displayer = new ZtmapHtmlDisplayer($params, $this->item);

        $this->displayer->setJView($this);
        $this->displayer->canEdit = $this->canEdit;

        $this->_prepareDocument();
        parent::display($tpl);

        $model = $this->getModel();
        $model->hit($this->displayer->getCount());
    }

    /**
     * Prepares the document
     */
    protected function _prepareDocument()
    {
        $app = JFactory::getApplication();
        $pathway = $app->getPathway();
        $menus = $app->getMenu();
        $title = null;

        // Because the application sets a default page title, we need to get it from the menu item itself
        if ($menu = $menus->getActive())
        {
            if (isset($menu->query['view']) && isset($menu->query['id']))
            {

                if ($menu->query['view'] == 'html' && $menu->query['id'] == $this->item->id)
                {
                    $title = $menu->title;
                    if (empty($title))
                    {
                        $title = $app->getCfg('sitename');
                    } else if ($app->getCfg('sitename_pagetitles', 0) == 1)
                    {
                        $title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
                    } else if ($app->getCfg('sitename_pagetitles', 0) == 2)
                    {
                        $title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
                    }
                    // set meta description and keywords from menu item's params
                    $params = new JRegistry();
                    $params->loadString($menu->params);
                    $this->document->setDescription($params->get('menu-meta_description'));
                    $this->document->setMetadata('keywords', $params->get('menu-meta_keywords'));
                }
            }
        }
        $this->document->setTitle($title);

        if ($app->getCfg('MetaTitle') == '1')
        {
            $this->document->setMetaData('title', $title);
        }

        if ($this->print)
        {
            $this->document->setMetaData('robots', 'noindex, nofollow');
        }
    }

}
