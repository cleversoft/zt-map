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

JTable::addIncludePath(JPATH_COMPONENT . '/tables');

/**
 * @package       Ztmap
 * @subpackage    com_ztmap
 */
abstract class JHtmlZtmap
{

    /**
     * @param    string  $name
     * @param    string  $value
     * @param    int     $j
     */
    public static function priorities($name, $value = '0.5', $j)
    {
        // Array of options
        for ($i = 0.1; $i <= 1; $i+=0.1)
        {
            $options[] = JHTML::_('select.option', $i, $i);
            ;
        }
        return JHtml::_('select.genericlist', $options, $name, null, 'value', 'text', $value, $name . $j);
    }

    /**
     * @param    string  $name
     * @param    string  $value
     * @param    int     $j
     */
    public static function changefrequency($name, $value = 'weekly', $j)
    {
        // Array of options
        $options[] = JHTML::_('select.option', 'hourly', 'hourly');
        $options[] = JHTML::_('select.option', 'daily', 'daily');
        $options[] = JHTML::_('select.option', 'weekly', 'weekly');
        $options[] = JHTML::_('select.option', 'monthly', 'monthly');
        $options[] = JHTML::_('select.option', 'yearly', 'yearly');
        $options[] = JHTML::_('select.option', 'never', 'never');
        return JHtml::_('select.genericlist', $options, $name, null, 'value', 'text', $value, $name . $j);
    }

}
