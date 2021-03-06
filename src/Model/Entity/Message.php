<?php
/* This file is part of NextDom Software.
 *
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom Software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom Software. If not, see <http://www.gnu.org/licenses/>.
 */

namespace NextDom\Model\Entity;

use NextDom\Helpers\Utils;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\EventManager;
use NextDom\Managers\ScenarioExpressionManager;

/**
 * Message
 *
 * @ORM\Table(name="message", indexes={@ORM\Index(name="plugin_logicalID", columns={"plugin", "logicalId"})})
 * @ORM\Entity
 */
class Message
{
    const CLASS_NAME = Message::class;
    const DB_CLASS_NAME = '`message`';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", nullable=false)
     */
    protected $date;

    /**
     * @var string
     *
     * @ORM\Column(name="logicalId", type="string", length=127, nullable=true)
     */
    protected $logicalId;

    /**
     * @var string
     *
     * @ORM\Column(name="plugin", type="string", length=127, nullable=false)
     */
    protected $plugin;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text", length=65535, nullable=true)
     */
    protected $message;

    /**
     * @var string
     *
     * @ORM\Column(name="action", type="text", length=65535, nullable=true)
     */
    protected $action;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    protected $_changed = false;

    public function save($_writeMessage = true)
    {
        if ($this->getMessage() == '') {
            return null;
        }
        if ($this->getLogicalId() == '') {
            $this->setLogicalId($this->getPlugin() . '::' . ConfigManager::genKey());
            $values = array(
                'message' => $this->getMessage(),
                'plugin' => $this->getPlugin(),
            );
            $sql = 'SELECT count(*)
                    FROM ' . self::DB_CLASS_NAME . '
                    WHERE plugin = :plugin
                    AND message = :message';
            $result = \DB::Prepare($sql, $values, \DB::FETCH_TYPE_ROW);
        } else {
            $values = array(
                'logicalId' => $this->getLogicalId(),
                'plugin' => $this->getPlugin(),
            );
            $sql = 'SELECT count(*)
            FROM message
            WHERE plugin=:plugin
            AND logicalId=:logicalId';
            $result = \DB::Prepare($sql, $values, \DB::FETCH_TYPE_ROW);
        }
        if ($result['count(*)'] != 0) {
            return null;
        }
        EventManager::add('notify', array('title' => __('Message de ') . $this->getPlugin(), 'message' => $this->getMessage(), 'category' => 'message'));
        if ($_writeMessage) {
            \DB::save($this);
            $params = array(
                '#plugin#' => $this->getPlugin(),
                '#message#' => $this->getMessage(),
            );
            $actions = ConfigManager::byKey('actionOnMessage');
            if (is_array($actions) && count($actions) > 0) {
                foreach ($actions as $action) {
                    $options = array();
                    if (isset($action['options'])) {
                        $options = $action['options'];
                    }
                    foreach ($options as &$value) {
                        $value = str_replace(array_keys($params), $params, $value);
                    }
                    ScenarioExpressionManager::createAndExec('action', $action['cmd'], $options);
                }
            }
            EventManager::add('message::refreshMessageNumber');
        }
        return true;
    }

    public function remove()
    {
        \DB::remove($this);
        EventManager::add('message::refreshMessageNumber');
    }

    /*     * **********************Getteur Setteur*************************** */

    public function getId()
    {
        return $this->id;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getPlugin()
    {
        return $this->plugin;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function setId($_id)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->id, $_id);
        $this->id = $_id;
        return $this;
    }

    public function setDate($_date)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->date, $_date);
        $this->date = $_date;
        return $this;
    }

    public function setPlugin($_plugin)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->plugin, $_plugin);
        $this->plugin = $_plugin;
        return $this;
    }

    public function setMessage($_message)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->message, $_message);
        $this->message = $_message;
        return $this;
    }

    public function setAction($_action)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->action, $_action);
        $this->action = $_action;
        return $this;
    }

    public function getLogicalId()
    {
        return $this->logicalId;
    }

    public function setLogicalId($_logicalId)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->logicalId, $_logicalId);
        $this->logicalId = $_logicalId;
        return $this;
    }

    public function getChanged()
    {
        return $this->_changed;
    }

    public function setChanged($_changed)
    {
        $this->_changed = $_changed;
        return $this;
    }

    public function getTableName()
    {
        return 'message';
    }
}
