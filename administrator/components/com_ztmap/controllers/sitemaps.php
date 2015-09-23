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

jimport('joomla.application.component.controlleradmin');

/**
 * @package     Ztmap
 * @subpackage  com_ztmap
 * @since       2.0
 */
class ZtmapControllerSitemaps extends JControllerAdmin
{

    /**
     *
     * @var string
     */
    protected $text_prefix = 'COM_ZTMAP_SITEMAPS';

    /**
     * Constructor
     */
    public function __construct($config = array())
    {
        parent::__construct($config);

        // Do unpublish by use publish method
        $this->registerTask('unpublish', 'publish');
        // Do tash by use publish method
        $this->registerTask('trash', 'publish');
        // Do unfeatured by use publish method
        $this->registerTask('unfeatured', 'featured');
    }

    /**
     * Method to toggle the default sitemap.
     *
     * @return      void
     * @since       2.0
     */
    function setDefault()
    {
        // Check for request forgeries
        JSession::checkToken() or die('Invalid Token');

        // Get items to publish from the request.
        /**
         * @todo Use JInput instead
         */
        $cid = JRqeuest::getVar('cid', 0, '', 'array');
        $id = @$cid[0];

        if (!$id)
        {
            JError::raiseWarning(500, JText::_('Select an item to set as default'));
        } else
        {
            // Get the model.
            $model = $this->getModel();

            // Publish the items.
            if (!$model->setDefault($id))
            {
                JError::raiseWarning(500, $model->getError());
            }
        }

        // Redirect to dashboard
        $this->setRedirect('index.php?option=com_ztmap&view=sitemaps');
    }

    /**
     * Proxy for getModel.
     *
     * @param    string    $name    The name of the model.
     * @param    string    $prefix    The prefix for the PHP class name.
     *
     * @return    JModel
     * @since    2.0
     */
    public function getModel($name = 'Sitemap', $prefix = 'ZtmapModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

}
