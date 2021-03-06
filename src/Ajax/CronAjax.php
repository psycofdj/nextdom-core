<?php
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

namespace NextDom\Ajax;

use NextDom\Enums\UserRight;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\AjaxHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\CronManager;

class CronAjax extends BaseAjax
{
    protected $NEEDED_RIGHTS = UserRight::ADMIN;
    protected $MUST_BE_CONNECTED = true;
    protected $CHECK_AJAX_TOKEN = true;

    public function save()
    {
        Utils::unautorizedInDemo();
        Utils::processJsonObject('cron', Utils::init('crons'));
        AjaxHelper::success();
    }

    public function remove()
    {
        Utils::unautorizedInDemo();
        $cron = CronManager::byId(Utils::init('id'));
        if (!is_object($cron)) {
            throw new CoreException(__('Cron id inconnu'));
        }
        $cron->remove();
        AjaxHelper::success();
    }

    public function all()
    {
        $crons = CronManager::all(true);
        foreach ($crons as $cron) {
            $cron->refresh();
        }
        AjaxHelper::success(Utils::o2a($crons));
    }

    public function start()
    {
        $cron = CronManager::byId(Utils::init('id'));
        if (!is_object($cron)) {
            throw new CoreException(__('Cron id inconnu'));
        }
        $cron->run();
        sleep(1);
        AjaxHelper::success();
    }

    public function stop()
    {
        $cron = CronManager::byId(Utils::init('id'));
        if (!is_object($cron)) {
            throw new CoreException(__('Cron id inconnu'));
        }
        $cron->halt();
        sleep(1);
        AjaxHelper::success();
    }
}