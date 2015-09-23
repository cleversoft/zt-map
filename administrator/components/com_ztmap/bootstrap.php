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

// Include dependancies
jimport('joomla.form.form');
jimport('joomla.application.component.controller');

JTable::addIncludePath(JPATH_COMPONENT . '/tables');

JForm::addFieldPath(JPATH_COMPONENT . '/models/fields');
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

// Assets
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_ztmap/assets/css/ztmap.css');
$document->addStyleSheet('components/com_ztmap/assets/vendor/font-awesome/css/font-awesome.min.css');

// Register helper class
JLoader::register('ZtmapHelper', dirname(__FILE__) . '/helpers/ztmap.php');


