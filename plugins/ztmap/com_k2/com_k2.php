<?php

/**
 * @version       $Id$
 * @copyright     Copyright (C) 2007 - 2009 Joomla! Vargas. All rights reserved.
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 * @author        Guillermo Vargas (guille@vargas.co.cr)
 * 
 * @name        Zt Map
 * @version     0.0.5
 * @package     Plugin
 * @subpackage  Ztmap
 * @author      ZooTemplate 
 * @email       support@zootemplate.com 
 * @link        http://www.zootemplate.com 
 * @copyright   Copyright (c) 2015 ZooTemplate
 * @license     GPL v2 
 * 
 * @description Xmap plugin for K2 component
 *
 * Changes:
 * + 0.51   2009/08/21  Do not show deleted items resp. categories
 * + 0.60   2009/08/21  New options "Show K2 Items" added
 * # 0.65   2009/09/28  Correct modification date now shown in XML sitemap
 * # 0.66   2009/10/07  Small bugfix to avoid PHP Notice:  Undefined variable
 * # 0.67   2010/01/30  Small bugfix to avoid PHP warnings in case of null returned from queries
 * + 0.80   2010/02/07  Support of new features of K2 2.2
 * + 0.81   2010/02/19  Modified date was not correct for all items
 * + 0.85   2010/04/11  New option to avoid duplicate items
 *                      Change the date format if used together with SEFServiceMap
 * # 0.86   2010/05/24  Expired items are no longer contained in the site map
 * # 0.86   2010/05/24  Expired items are no longer contained in the site map
 *                      Warnings regarding undefined properties solved
 * # 0.90   2010/08/14  User rights are now taken into account (reported by http://walplanet.com)
 * # 0.91   2010/08/21  Bugfix: wrong SQL statement created
 * # 0.92   2010/10/13  Fixed a bug if last users or last categories has no entries
 * + 0.93   2010/11/28  Add support for Google News sitemap
 * # 0.94   2011/02/13  Small bugfix to avoid PHP warning
 * # 0.95   2011/08/13  Bugfixes regarding empty categories and invalid SQL statements
 * + 1.00   2011/09/22  Support of Joomla 1.7 and K2 2.5
 * # 1.01   2011/09/27  XML sitemap did not show K2 items
 * # 1.05   2011/11/02  Fixed some problems with menu items pointing to multiple categories
 * # 1.06   2011/11/03  Fixed a bug with empty arrays
 * # 1.07   2011/11/11  Follow subcategories did not work as expected
 * # 1.2    2013/01/31  Comatiable with joomla 3.0 and k2 2.6.3 - Mohammad Hasani Eghtedar (m.h.eghtedar@gmail.com)
 */
// no direct access
defined('_JEXEC') or die;

/** Adds support for K2  to Xmap */
class ztmap_com_k2
{

    static $maxAccess = 0;
    static $suppressDups = false;
    static $suppressSub = false;

    /** Get the content tree for this kind of content */
    public static function getTree(&$ztmap, &$parent, &$params)
    {
        $tag = null;
        $limit = null;
        $id = null;
        $link_query = parse_url($parent->link);
        parse_str(html_entity_decode($link_query['query']), $link_vars);
        $parm_vars = $parent->params->toArray();

        $option = ztmap_com_k2::getParam($link_vars, 'option', "");
        if ($option != "com_k2")
            return;

        $view = ztmap_com_k2::getParam($link_vars, 'view', "");
        $showMode = ztmap_com_k2::getParam($params, 'showk2items', "always");

        if ($showMode == "never" || ($showMode == "xml" && $ztmap->view == "html") || ($showMode == "html" && $ztmap->view == "xml"))
            return;
        self::$suppressDups = (ztmap_com_k2::getParam($params, 'suppressdups', 'yes') == "yes");
        self::$suppressSub = (ztmap_com_k2::getParam($params, 'subcategories', "yes") != "yes");

        if ($view == "item")   // for Items the sitemap already contains the correct reference
        {
            if (!isset($ztmap->IDS))
                $ztmap->IDS = "";
            $ztmap->IDS = $ztmap->IDS . "|" . ztmap_com_k2::getParam($link_vars, 'id', $id);
            return;
        }

        if ($ztmap->view == "xml")
            self::$maxAccess = 1;   // XML sitemaps will only see content for guests
        else
            self::$maxAccess = implode(",", JFactory::getUser()->getAuthorisedViewLevels());

        echo ztmap_com_k2::getParam($link_vars, 'task', "");
        switch (ztmap_com_k2::getParam($link_vars, 'task', ""))
        {
            case "user":
                $tag = ztmap_com_k2::getParam($link_vars, 'id', $id);
                $ids = array_key_exists('userCategoriesFilter', $parm_vars) ? $parm_vars['userCategoriesFilter'] : array("");
                $mode = "single user";
                break;
            case "tag":
                $tag = ztmap_com_k2::getParam($link_vars, 'tag', "");
                $ids = array_key_exists('categoriesFilter', $parm_vars) ? $parm_vars['categoriesFilter'] : array("");
                $mode = "tag";
                break;
            case "category":
                $ids = explode("|", ztmap_com_k2::getParam($link_vars, 'id', ""));
                $mode = "category";
                break;
            case "":
                switch (ztmap_com_k2::getParam($link_vars, 'layout', ""))
                {
                    case "category":
                        if (array_key_exists('categories', $parm_vars))
                            $ids = $parm_vars["categories"];
                        else
                            $ids = '';
                        $mode = "categories";
                        break;
                    case "latest":
                        $limit = ztmap_com_k2::getParam($parm_vars, 'latestItemsLimit', "");
                        if (ztmap_com_k2::getParam($parm_vars, 'source', "") == "0")
                        {
                            $ids = array_key_exists("userIDs", $parm_vars) ? $parm_vars["userIDs"] : '';
                            $mode = "latest user";
                        } else
                        {
                            $ids = array_key_exists("categoryIDs", $parm_vars) ? $parm_vars["categoryIDs"] : '';
                            $mode = "latest category";
                        }
                        break;
                    default:
                        return;
                }
                break;
            default:
                return;
        }
        $priority = ztmap_com_k2::getParam($params, 'priority', $parent->priority);
        $changefreq = ztmap_com_k2::getParam($params, 'changefreq', $parent->changefreq);
        if ($priority == '-1')
            $priority = $parent->priority;
        if ($changefreq == '-1')
            $changefreq = $parent->changefreq;

        $params['priority'] = $priority;
        $params['changefreq'] = $changefreq;

        $db = JFactory::getDBO();
        ztmap_com_k2::processTree($db, $ztmap, $parent, $params, $mode, $ids, $tag, $limit);

        return;
    }

    /**
     * 
     * @param type $db
     * @param type $catid
     * @param type $allrows
     * @return type
     */
    public static function collectByCat($db, $catid, &$allrows)
    {
        if (trim($catid) == "") // in this case something strange went wrong
            return;
        $query = "select id,title,alias,UNIX_TIMESTAMP(created) as created, UNIX_TIMESTAMP(modified) as modified, metakey from #__k2_items where "
                . "published = 1 and trash = 0 and (publish_down = \"0000-00-00\" OR publish_down > NOW()) "
                . "and catid = " . $catid . " order by 1 desc";
        $db->setQuery($query);
        $rows = $db->loadObjectList();
        if ($rows != null)
            $allrows = array_merge($allrows, $rows);
        $query = "select id, name, alias  from #__k2_categories where published = 1 and trash = 0 and parent = " . $catid . " order by id";
        $db->setQuery($query);
        $rows = $db->loadObjectList();
        if ($rows == null)
            $rows = array();

        foreach ($rows as $row)
        {
            ztmap_com_k2::collectByCat($db, $row->id, $allrows);
        }
    }

    /**
     * 
     * @param type $db
     * @param type $ztmap
     * @param type $parent
     * @param type $params
     * @param type $mode
     * @param type $ids
     * @param type $tag
     * @param type $limit
     * @return type
     */
    public static function processTree($db, &$ztmap, &$parent, &$params, $mode, $ids, $tag, $limit)
    {
        $baseQuery = "select id,title,alias,UNIX_TIMESTAMP(created) as created, UNIX_TIMESTAMP(modified) as modified, metakey from  #__k2_items where "
                . "published = 1 and trash = 0 and (publish_down = \"0000-00-00\" OR publish_down > NOW()) and "
                . "access in (" . self::$maxAccess . ") and ";

        switch ($mode)
        {
            case "single user":
                $query = $baseQuery . "created_by = " . $tag . " ";
                if ($ids[0] != "")
                    $query .= " and catid in (" . implode(",", $ids) . ")";
                $query .= " order by 1 DESC ";
                $db->setQuery($query);
                $rows = $db->loadObjectList();
                break;
            case "tag":
                $query = "SELECT c.id, title, alias, UNIX_TIMESTAMP(c.created) as created, UNIX_TIMESTAMP(c.modified) as modified FROM #__k2_tags a, #__k2_tags_xref b, #__k2_items c where " . "c.published = 1 and c.trash = 0 and (c.publish_down = \"0000-00-00\" OR c.publish_down > NOW()) "
                        . "and a.Name = '" . $tag . "' and a.id =  b.tagId and c.id = b.itemID and c.access in (" . self::$maxAccess . ")";
                if ($ids[0] != "")
                    $query .= " and c.catid in (" . implode(",", $ids) . ")";
                $query .= " order by 1 DESC ";
                $db->setQuery($query);
                $rows = $db->loadObjectList();
                break;
            case "category":
                $query = $baseQuery . "catid = " . $ids[0] . " order by 1 DESC ";
                $db->setQuery($query);
                $rows = $db->loadObjectList();
                break;
            case "categories":
                if (!self::$suppressSub)
                {
                    if ($ids)
                        $query = $baseQuery . "catid in (" . implode(",", $ids) . ") order by 1 DESC ";
                    else
                        $query = $baseQuery . "1 order by 1 DESC ";
                    $db->setQuery($query);
                    $rows = $db->loadObjectList();
                }
                else
                {
                    $rows = array();
                    if (is_array($ids))
                    {
                        foreach ($ids as $id)
                        {
                            $allrows = array();
                            ztmap_com_k2::collectByCat($db, $id, $allrows);
                            $rows = array_merge($rows, $allrows);
                        }
                    }
                }
                break;
            case "latest user":
                $rows = array();
                if (is_array($ids))
                {
                    foreach ($ids as $id)
                    {
                        $query = $baseQuery . "created_by = " . $id . " order by 1 DESC LIMIT " . $limit;
                        $db->setQuery($query);
                        $res = $db->loadObjectList();
                        if ($res != null)
                            $rows = array_merge($rows, $res);
                    }
                }
                break;
            case "latest category":
                $rows = array();
                if (is_array($ids))
                {
                    foreach ($ids as $id)
                    {
                        $query = $baseQuery . "catid = " . $id . " order by 1 DESC LIMIT " . $limit;
                        $db->setQuery($query);
                        $res = $db->loadObjectList();
                        if ($res != null)
                            $rows = array_merge($rows, $res);
                    }
                }
                break;
            default:
                return;
        }

        $ztmap->changeLevel(1);
        $node = new stdclass ();
        $node->id = $parent->id;

        if ($rows == null)
        {
            $rows = array();
        }
        foreach ($rows as $row)
        {
            if (!(self::$suppressDups && isset($ztmap->IDS) && strstr($ztmap->IDS, "|" . $row->id)))
                ztmap_com_k2::addNode($ztmap, $node, $row, false, $parent, $params);
        }

        if ($mode == "category" && !self::$suppressSub)
        {
            $query = "select id, name, alias  from #__k2_categories where published = 1 and trash = 0 and parent = " . $ids[0]
                    . " and access in (" . self::$maxAccess . ") order by id";
            $db->setQuery($query);
            $rows = $db->loadObjectList();
            if ($rows == null)
            {
                $rows = array();
            }

            foreach ($rows as $row)
            {
                if (!isset($ztmap->IDS))
                    $ztmap->IDS = "";
                if (!(self::$suppressDups && strstr($ztmap->IDS, "|c" . $row->id)))
                {
                    ztmap_com_k2::addNode($ztmap, $node, $row, true, $parent, $params);
                    $newID = array();
                    $newID[0] = $row->id;
                    ztmap_com_k2::processTree($db, $ztmap, $parent, $params, $mode, $newID, "", "");
                }
            }
        }
        $ztmap->changeLevel(-1);
    }

    public static function addNode($ztmap, $node, $row, $iscat, &$parent, &$params)
    {
        $sef = ($_REQUEST['option'] == "com_sefservicemap"); // verallgemeinern

        if ($ztmap->isNews && ($row->modified ? $row->modified : $row->created) > ($ztmap->now - (2 * 86400)))
        {
            $node->newsItem = 1;
            $node->keywords = $row->metakey;
        } else
        {
            $node->newsItem = 0;
            $node->keywords = "";
        }
        if (!isset($ztmap->IDS))
            $ztmap->IDS = "";

        $node->browserNav = $parent->browserNav;
        $node->pid = $row->id;
        $node->uid = $parent->uid . 'item' . $row->id;
        if (isset($row->modified) || isset($row->created))
            $node->modified = (isset($row->modified) ? $row->modified : $row->created);

        if ($sef)
            $node->modified = date('Y-m-d', $node->modified);

        $node->name = ($iscat ? $row->name : $row->title);

        $node->priority = $params['priority'];
        $node->changefreq = $params['changefreq'];

        if ($iscat)
        {
            $ztmap->IDS .= "|c" . $row->id;
            $node->link = 'index.php?option=com_k2&view=itemlist&task=category&id=' . $row->id . ':' . $row->alias . '&Itemid=' . $parent->id;
            $node->expandible = true;
        } else
        {
            $ztmap->IDS .= "|" . $row->id;
            $node->link = 'index.php?option=com_k2&view=item&id=' . $row->id . ':' . $row->alias . '&Itemid=' . $parent->id;
            $node->expandible = false;
        }
        $node->tree = array();
        $ztmap->printNode($node);
    }

    /**
     * 
     * @param type $arr
     * @param type $name
     * @param type $def
     * @return type
     */
    public static function &getParam($arr, $name, $def)
    {
        $var = JArrayHelper::getValue($arr, $name, $def, '');
        return $var;
    }

}
