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
 * XML Sitemap View class for the Ztmap component
 *
 * @package      Ztmap
 * @subpackage   com_ztmap
 * @since        0.0.5
 */
class ZtmapViewXml extends JViewLegacy
{

    protected $state;
    protected $print;
    protected $_obLevel;

    public function display($tpl = null)
    {
        // Initialise variables.
        $app = JFactory::getApplication();
        $this->user = JFactory::getUser();
        $isNewsSitemap = JRequest::getInt('news', 0);
        $this->isImages = JRequest::getInt('images', 0);

        $model = $this->getModel('Sitemap');

        $this->setModel($model);



        # Increase memory and max execution time for XML sitemaps to make it work
        # with very large sites
        @ini_set('memory_limit', '512M');
        @ini_set('max_execution_time', 300);

        $layout = $this->getLayout();

        $this->item = $this->get('Item');
        $this->state = $this->get('State');
        $this->canEdit = JFactory::getUser()->authorise('core.admin', 'com_ztmap');

        // For now, news sitemaps are not editable
        $this->canEdit = $this->canEdit && !$isNewsSitemap;

        if ($layout == 'xsl')
        {
            return $this->displayXSL($layout);
        }

        // Get model data.
        $this->items = $this->get('Items');
        $this->sitemapItems = $this->get('SitemapItems');
        $this->extensions = $this->get('Extensions');

        // Check for errors.
        if (count($errors = $this->get('Errors')))
        {
            JError::raiseWarning(500, implode("\n", $errors));
            return false;
        }

        // Add router helpers.
        $this->item->slug = $this->item->alias ? ($this->item->id . ':' . $this->item->alias) : $this->item->id;

        $this->item->rlink = JRoute::_('index.php?option=com_ztmap&view=xml&id=' . $this->item->slug);

        // Create a shortcut to the paramemters.
        $params = &$this->state->params;
        $offset = $this->state->get('page.offset');

        if (!$this->item->params->get('access-view'))
        {
            if ($this->user->get('guest'))
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
        $this->displayer = new ZtmapXmlDisplayer($params, $this->item);

        $this->displayer->setJView($this);

        $this->displayer->isNews = $isNewsSitemap;
        $this->displayer->isImages = $this->isImages;
        $this->displayer->canEdit = $this->canEdit;

        $doCompression = ($this->item->params->get('compress_xml') && !ini_get('zlib.output_compression') && ini_get('output_handler') != 'ob_gzhandler');
        $this->endAllBuffering();
        if ($doCompression)
        {
            ob_start();
        }

        parent::display($tpl);

        $model = $this->getModel();
        $model->hit($this->displayer->getCount());

        if ($doCompression)
        {
            $data = ob_get_contents();
            JResponse::setBody($data);
            @ob_end_clean();
            echo JResponse::toString(true);
        }
        $this->recreateBuffering();
        exit;
    }

    public function displayXSL()
    {
        $this->setLayout('default');

        $this->endAllBuffering();
        parent::display('xsl');
        $this->recreateBuffering();
        exit;
    }

    private function endAllBuffering()
    {
        $this->_obLevel = ob_get_level();
        $level = FALSE;
        while (ob_get_level() > 0 && $level !== ob_get_level())
        {
            @ob_end_clean();
            $level = ob_get_level();
        }
    }

    private function recreateBuffering()
    {
        while ($this->_obLevel--)
        {
            ob_start();
        }
    }

}
