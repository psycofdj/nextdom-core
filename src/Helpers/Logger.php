<?php
/* This file is part of NextDom.
 *
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom. If not, see <http://www.gnu.org/licenses/>.
 */

use Monolog\Logger;
use Monolog\ErrorHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\NewRelicHandler;
use Monolog\Handler\databaseHandler;
use Monolog\Processor\IntrospectionProcessor;
use Enum\LogLevelEnum;
use MySQLHandler\MySQLHandler;

/**
 * Logger class using Monolog
 *
 * see https://github.com/Seldaek/monolog
 *
 */
namespace NextDom\Helper;
class Logger
{
    protected $_levels = array(
       
    );
    protected $config = array();

    /**
     * prepare logging environment with configuration variables
     */
    public function __construct()
    {
        $file_path = APPPATH . 'config/logConfig.php';
               
        if (file_exists($file_path)) {
            require($file_path);
        } else {
            exit('log.php config file does not exist');
        }
        // make $config from config/logConfig.php accessible to $this->write_log()
        $this->config = $config;
        $this->log = new Logger($this->config['channel']);
        // detect and register all PHP errors in this log hence forth
        ErrorHandler::register($this->log);
        if ($this->config['introspection_processor']) {
            // add controller and line number info to each log message
            $this->log->pushProcessor(new IntrospectionProcessor());
        }
        // default handler selection
        foreach ($this->config['handlers'] as $value) {
            switch ($value) {
                case 'file':
                    $handler = new RotatingFileHandler($this->config['file_logfile']);
                    break;
                case 'database':
                    $handler = new databaseHandler(
                        $config['database_app_token'],
                        $config['database_app_room_id'],
                        $config['database_app_notification_name'],
                        $config['database_app_notify'],
                        $config['database_app_loglevel']);
                    break;
                    
                default:
                    exit('log handler not supported: ' . $this->config['handler']);
            }
            $this->log->pushHandler($handler);
        }
        $this->write_log(LogLevelEnum.DEBUG, 'Monolog replacement logger initialized');
    }
    /**
     * Write to defined logger.
     *
     * @param string $level
     * @param $msg
     * @return bool
     */
    public function write_log($level = 'error', $msg)
    {
        $level = strtoupper($level);
        if (!isset($this->_levels[$level])) {
            $this->log->addError('unknown error level: ' . $level);
            $level = LogLevelEnum.ALL;
        }
        // filter out anything in $this->config['exclusion_list']
        if (!empty($this->config['exclusion_list'])) {
            foreach ($this->config['exclusion_list'] as $findme) {
                $pos = strpos($msg, $findme);
                if ($pos !== false) {
                    return true;
                }
            }
        }
        if ($this->_levels[$level] <= $this->config['threshold']) {
            switch ($level) {
                case LogLevelEnum.ERROR:
                    $this->log->addError($msg);
                    break;
                case LogLevelEnum.DEBUG:
                    $this->log->addDebug($msg);
                    break;
                case LogLevelEnum.ALL:
                case LogLevelEnum.INFO:
                    $this->log->addInfo($msg);
                    break;
            }
        }
        return true;
    }
}