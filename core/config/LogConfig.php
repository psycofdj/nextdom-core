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

/* GENERAL OPTIONS */
$config['handlers'] = array('file', 'database'); // valid handlers are file | database
$config['channel'] = ENVIRONMENT; // channel name which appears on each line of log
$config['threshold'] = '4'; // 'ERROR' => '1', 'DEBUG' => '2',  'INFO' => '3', 'ALL' => '4'
$config['introspection_processor'] = TRUE; // add some meta data such as controller and line number to log messages

/* FILE HANDLER OPTIONS */
$config['file_logfile'] = APPPATH . 'logs/nextdom.log';

/* DATABASE HANDLER OPTIONS */
$config['database_app_token'] = '';

// exclusion list for pesky messages which you may wish to temporarily suppress with strpos() match
$config['exclusion_list'] = array();