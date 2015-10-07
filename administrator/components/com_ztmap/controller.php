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

jimport('joomla.application.component.controller');

/**
 * Component Controller
 *
 * @package     Ztmap
 * @subpackage  com_ztmap
 */
class ZtmapController extends JControllerLegacy
{

    public function __construct()
    {
        parent::__construct();

        $this->registerTask('navigator-links', 'navigatorLinks');
    }

    /**
     * Display the view
     */
    public function display($cachable = false, $urlparams = false)
    {
        require_once JPATH_COMPONENT . '/helpers/ztmap.php';

        // Get the document object.
        $document = JFactory::getDocument();

        // Set the default view name and format from the Request.
        $vName = JFactory::getApplication()->input->getWord('view', 'sitemaps');
        $vFormat = $document->getType();
        $lName = JFactory::getApplication()->input->getWord('layout', 'default');

        // Get and render the view.
        if ($view = $this->getView($vName, $vFormat))
        {
            // Get the model for the view.
            $model = $this->getModel($vName);

            // Push the model into the view (as default).
            $view->setModel($model, true);
            $view->setLayout($lName);

            // Push document object into the view.
            $view->assignRef('document', $document);

            $view->display();
        }
    }

    /**
     * navigator task
     * @return boolean
     */
    public function navigator()
    {

        $document = JFactory::getDocument();
        $app = JFactory::getApplication('administrator');

        $id = JFactory::getApplication()->input->getInt('sitemap', 0);
        if (!$id)
        {
            $id = $this->_getDefaultSitemapId();
        }

        if (!$id)
        {
            /**
             * @todo JError::raiseWarning() is deprecated
             */
            JError::raiseWarning(500, JText::_('Ztmap_Not_Sitemap_Selected'));
            return false;
        }

        $app->setUserState('com_ztmap.edit.sitemap.id', $id);

        $view = $this->getView('sitemap', $document->getType());
        $model = $this->getModel('Sitemap');
        $view->setLayout('navigator');
        $view->setModel($model, true);

        // Push document object into the view.
        $view->assignRef('document', $document);

        $view->navigator();
    }

    /**
     * navigatorLink task
     * @return boolean
     */
    public function navigatorLinks()
    {

        $document = JFactory::getDocument();
        $app = JFactory::getApplication('administrator');

        $id = JFactory::getApplication()->input->getInt('sitemap', 0);

        if (!$id)
        {
            $id = $this->_getDefaultSitemapId();
        }

        if (!$id)
        {
            /**
             * @todo JError::raiseWarning() is deprecated
             */
            JError::raiseWarning(500, JText::_('Ztmap_Not_Sitemap_Selected'));
            return false;
        }

        $app->setUserState('com_ztmap.edit.sitemap.id', $id);

        $view = $this->getView('sitemap', $document->getType());
        $model = $this->getModel('Sitemap');
        $view->setLayout('navigator');
        $view->setModel($model, true);

        // Push document object into the view.
        $view->assignRef('document', $document);

        $view->navigatorLinks();
    }

    private function _getDefaultSitemapId()
    {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('id');
        $query->from($db->quoteName('#__ztmap_sitemap'));
        $query->where('is_default=1');
        $db->setQuery($query);
        return $db->loadResult();
    }

}
