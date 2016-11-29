<?php
/**   Copyright (C) 2016  Deindo Ideas
*
*    This program is free software: you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*    (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
*    @author Deindo Ideas SLU <contacto@deindo.es>
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Postagesummary extends Module
{
    public function __construct()
    {
        $this->name = 'postagesummary';
        $this->tab = 'shipping_logistics';
        $this->version = '1.0.3';
        $this->author = 'Deindo Ideas';
        $this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('Postage summary');
        $this->description = $this->l('Summary shipping cost by filters');
    }

    public function install()
    {
        if (!parent::install()
            || !$this->registerHook('displayBackOfficeHeader')
            || !$this->installTab('AdminPostageSummary', 'Postage summary')) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall() ||
            !$this->uninstallTab('AdminPostageSummary')
        ) {
            return false;
        }

        return true;
    }

    public function installTab($tabClass, $tabName)
    {
        $subtab = new Tab();
        $subtab->class_name = 'AdminPostageSummary';
        $subtab->id_parent = Tab::getIdFromClassName($tabClass);
        $subtab->module = $this->name;
        $subtab->name[(int) (Configuration::get('PS_LANG_DEFAULT'))] = $this->l($tabName);
        $subtab->add();

        return true;
    }

    public function uninstallTab($tabClass)
    {
        $result = true;

        $idTab = Tab::getIdFromClassName($tabClass);

        if ($idTab != 0) {
            $tab = new Tab($idTab);
            $result = $tab->delete();
        }

        return $result;
    }

    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('controller') == 'AdminPostageSummary') {
            $this->context->controller->addCss($this->_path.'views/css/postagesummary.css');
        }
    }
}
