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
 *
 * @Support <https://www.nextdom.org>
 * @Email   <admin@nextdom.org>
 * @Authors/Contributors: Sylvaner, Byackee, cyrilphoenix71, ColonelMoutarde, edgd1er, slobberbone, Astral0, DanoneKiD
 */

namespace NextDom\Controller\Modal;

use NextDom\Helpers\Render;
use NextDom\Helpers\AjaxHelper;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\UpdateManager;

class UpdateAdd extends BaseAbstractModal
{
    /**
     * Render update add modal
     *
     * @return string
     * @throws \Exception
     */
    public static function get(): string
    {
        $pageData = [];

        $pageData['repoListType'] = [];
        foreach (UpdateManager::listRepo() as $repoKey => $repoValue) {
            if ($repoValue['configuration'] === false) {
                continue;
            }
            if ($repoValue['scope']['plugin'] === false) {
                continue;
            }
            if (!isset($repoValue['configuration']['parameters_for_add'])) {
                continue;
            }
            if (ConfigManager::byKey($repoKey . '::enable') == 0) {
                continue;
            }
            $pageData['repoListType'][$repoKey] = $repoValue['name'];
        }

        $pageData['repoListConfiguration'] = [];

        foreach (UpdateManager::listRepo() as $repoKey => $repoValue) {
            if ($repoValue['configuration'] === false) {
                continue;
            }
            if ($repoValue['scope']['plugin'] === false) {
                continue;
            }
            if (!isset($repoValue['configuration']['parameters_for_add'])) {
                continue;
            }
            $pageData['repoListConfiguration'][$repoKey] = $repoValue;
        }
        $pageData['ajaxToken'] = AjaxHelper::getToken();

        return Render::getInstance()->get('/modals/update.add.html.twig', $pageData);
    }

}
