<?php

namespace Sintattica\Atk\Core;

use Sintattica\Atk\Security\SecurityManager;
use Sintattica\Atk\Core\Config;
use Sintattica\Atk\Utils\ActionListener;

class EventLog extends ActionListener
{
    /**
     * This method handles the storage of the action in the database.
     *
     * @param string $action The action being performed
     * @param array $record The record on which the action is performed
     */
    public function actionPerformed($action, $record) : void
    {
        if (!in_array($action, ['view', 'edit'], true)) {

            $user = SecurityManager::atkGetUser();
            $userid = $user[Config::getGlobal('auth_userpk')];
            if ($userid == '') {
                $userid = 0;
            } // probably administrator
            $db = $this->m_node->getDb();
            $query = $db->createQuery('atkeventlog');
            $query->addFields([
                'userid' => $userid,
                'stamp' => date('Y-m-d H:i:s'),
                'node' => $this->m_node->atkNodeUri(),
                'action' => $action,
                'primarykey' => $this->m_node->primaryKeyString($record),
                'record' => serialize($record)
            ]);
            $query->executeInsert();

            $db->commit();
        }
    }
}
