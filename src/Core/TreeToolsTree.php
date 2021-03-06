<?php

namespace Sintattica\Atk\Core;

use Sintattica\Atk\Session\SessionManager;

/**
 * Tree class, used to build trees of nodes.
 *
 * @author Ivo Jansch <ivo@achievo.org>
 */
class TreeToolsTree
{
    public $m_tree = [];
    public $m_allnodes = [];
    public $m_parentless = []; // Array to keep stuff that can not yet be inserted into the array. 

    public function addNode($id, $naam, $parent = 0, $img = '')
    {
        $n = new TreeToolsNode($id, $naam, $img);
        $this->m_allnodes[$id] = $n;

        if (array_key_exists($id, $this->m_parentless) && is_array($this->m_parentless[$id])) {
            // In the parentless array, there are children that belong to this new record.
            $n->m_sub = $this->m_parentless[$id];
            unset($this->m_parentless[$id]);
        }

        if (empty($parent)) {
            $this->m_tree[] = $n;
        } else {
            $tmp = $this->m_allnodes[$parent];
            if (is_object($tmp)) {
                $tmp->m_sub[] = $n;
            } else {
                // Dangling thingee.
                $this->m_parentless[$parent][] = $n;
            }
        }
    }

    /**
     * Example render function. Implement your own.
     */
    public function render($tree = '', $level = 0)
    {
        // First time: root tree..
        if ($tree == '') {
            $tree = $this->m_tree;
        }
        $res = '';
        while (list($id, $objarr) = each($tree)) {
            $res .= '<tr><td>'.str_repeat('-', (2 * $level)).' '.$objarr->m_label.'</td></tr>';
            if (Tools::count($objarr->m_sub) > 0) {
                $res .= $this->render($objarr->m_sub, $level + 1);
            }
        }

        return $res;
    }

    /**
     * Pops tree's on the session.
     */
    public function sessionTree()
    {
        global $ATK_VARS;
        $postTree = $ATK_VARS['atktree'];
        $sm = SessionManager::getInstance();
        $sessionTree = $sm->getValue('atktree');
        if ($postTree != '' && $sessionTree != $postTree) {
            $sm->globalVar('atktree', $postTree);
            $realTree = $postTree;
        } else {
            $realTree = $sessionTree; // use the last known tree
        }
        $ATK_VARS['atktree'] = $realTree; // postvars now should contain the last Knowtree
    }
}
