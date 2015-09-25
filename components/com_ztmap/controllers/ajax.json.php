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
 * Ztmap Ajax Controller
 *
 * @package      Ztmap
 * @subpackage   com_ztmap
 * @since        2.0
 */
class ZtmapControllerAjax extends JControllerLegacy
{

    public function editElement()
    {
        JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));

        jimport('joomla.utilities.date');
        jimport('joomla.user.helper');
        $user = JFactory::getUser();

        $result = new JRegistry('_default');
        $sitemapId = JREquest::getInt('id');

        if (!$user->authorise('core.edit', 'com_ztmap.sitemap.' . $sitemapId))
        {
            $result->setValue('result', 'KO');
            $result->setValue('message', 'You are not authorized to perform this action!');
        } else
        {
            $model = $this->getModel('sitemap');
            if ($model->getItem())
            {
                $action = JRequest::getCmd('action', '');
                $uid = JRequest::getCmd('uid', '');
                $itemid = JRequest::getInt('itemid', '');
                switch ($action)
                {
                    case 'toggleElement':
                        if ($uid && $itemid)
                        {
                            $state = $model->toggleItem($uid, $itemid);
                        }
                        break;
                    case 'changeProperty':
                        $uid = JRequest::getCmd('uid', '');
                        $property = JRequest::getCmd('property', '');
                        $value = JRequest::getCmd('value', '');
                        if ($uid && $itemid && $uid && $property)
                        {
                            $state = $model->chageItemPropery($uid, $itemid, 'xml', $property, $value);
                        }
                        break;
                }
            }
            $result->set('result', 'OK');
            $result->set('state', $state);
            $result->set('message', '');
        }

        echo $result->toString();
    }

}
