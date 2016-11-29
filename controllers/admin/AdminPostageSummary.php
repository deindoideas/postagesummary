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

class AdminPostageSummaryController extends ModuleAdminController
{
    private $g_moduloName;
    private $g_date_start;
    private $g_date_end;
    private $g_country;
    private $g_state;
    private $g_zone;
    public $g_orderby;
    public $g_orderway;
    private $g_inicizlizate;
    private $ga_csv;

    public function __construct()
    {
        $this->g_moduloName = 'postagesummary';
        $this->g_inicizlizate = 0;
        $this->bootstrap = true;
        $this->multishop_context = Shop::CONTEXT_ALL;
        $this->lang = false;

        $this->fields_list = array(
            'counter' => array(
                'title' => '',
                'align' => 'text-center',
                'orderby' => false,
            ),
            'order_date' => array(
                'title' => $this->l('Order date'),
                'align' => 'text-center',
                'orderby' => false,
            ),
            'country' => array(
                'title' => $this->l('Country'),
                'align' => 'text-center',
                'orderby' => false,
            ),
            'state' => array(
                'title' => $this->l('State'),
                'align' => 'text-center',
                'orderby' => false,
            ),
            'zone' => array(
                'title' => $this->l('Zone'),
                'align' => 'text-center',
                'orderby' => false,
            ),
            'weight' => array(
                'title' => $this->l('Weight'),
                'align' => 'text-center',
                'orderby' => false,
            ),
            'total_products' => array(
                'title' => $this->l('Product costs'),
                'align' => 'text-center',
                'orderby' => false,
            ),
            'carrier' => array(
                'title' => $this->l('Carrier'),
                'align' => 'text-center',
                'orderby' => false,
            ),
            'total_shipping_tax_incl' => array(
                'title' => $this->l('Shipping costs'),
                'align' => 'text-center',
                'orderby' => false,
            ),
        );
        $this->context = Context::getContext();
        $this->default_form_language = $this->context->language->id;

        $this->g_inicizlizate = 0;

        parent::__construct();
    }

    public function getList($id_lang, $orderBy = null, $orderWay = null, $start = 0, $limit = null, $id_lang_shop = null)
    {
        if (!empty($this->g_inicizlizate)) {
            $a_csv = array();
            $a_csv[] = array('', $this->l('Order date'), $this->l('Country'), $this->l('State'), $this->l('Zone'), $this->l('Weight'), $this->l('Product costs'), $this->l('Carrier'), $this->l('Shipping costs'));
            $sum_wei = $sum_prod = $sum_ship = (float) 0;

            $a_list = $this->loadList();
            $this->_list = $a_list;
            $counter = (int) 0;
            if ($this->_list) {
                foreach ($this->_list as &$row) {
                    ++$counter;
                    $row['counter'] = $counter;
                    $order = new Order($row['weight']);
                    $sum_wei  += $row['weight'] = $order->getTotalWeight();
                    $sum_prod += $row['total_products'];
                    $row['total_products'] = tools::displayPrice($row['total_products']);
                    $sum_ship += $row['total_shipping_tax_incl'];
                    $row['total_shipping_tax_incl'] = tools::displayPrice($row['total_shipping_tax_incl']);

                    $a_csv[] = array($row['counter'], $row['order_date'], $row['country'], $row['state'], $row['zone'],
                                    $row['weight'], $row['total_products'], $row['carrier'], $row['total_shipping_tax_incl'], );
                }
            }
            //SUMATORIOS
            $a_cabecera_sumatorio = array('', '', '', '', '', 'weight' => 'TOTAL', 'total_products' => 'TOTAL', '', 'total_shipping_tax_incl' => 'TOTAL');
            $a_sumatorio = array('', '', '', '', '', 'weight' => $sum_wei, 'total_products' => tools::displayPrice($sum_prod), '', 'total_shipping_tax_incl' => tools::displayPrice($sum_ship));

            array_push($this->_list, $a_cabecera_sumatorio);
            array_push($this->_list, $a_sumatorio);
            array_push($a_csv, $a_cabecera_sumatorio);
            array_push($a_csv, $a_sumatorio);

            $this->ga_csv = $a_csv;
        }
    }

    public function renderList()
    {
        return parent::renderList();
    }

    public function initFieldGeneral()
    {
        $a_country = $this->loadCountry();
        $a_state = $this->loadState();
        $a_zone = $this->loadZone();
        $a_orderby = array(
          array('id_orderby' => 0, 'name' => $this->l('Date')),
          array('id_orderby' => 1, 'name' => $this->l('Country')),
          array('id_orderby' => 2, 'name' => $this->l('State')),
          array('id_orderby' => 3, 'name' => $this->l('Zone')),
          array('id_orderby' => 4, 'name' => $this->l('Product cost')),
          array('id_orderby' => 5, 'name' => $this->l('Carrier')),
          array('id_orderby' => 6, 'name' => $this->l('Shipping cost')),
        );

        $s_csv_url = __PS_BASE_URI__.'modules/'.$this->g_moduloName.'/views/files/'.$this->g_moduloName.'.csv';

        $s_options = array(
          array('id' => 'expo_on', 'value' => 1, 'label' => $this->l('Yes')),
          array('id' => 'expo_off', 'value' => 0, 'label' => $this->l('No')),
        );

        $this->fields_form = array(
            'input' => array(
                array(
                    'type' => 'date',
                    'label' => $this->l('Date start:'),
                    'name' => 'f_date_start',
                    ),
                array(
                    'type' => 'date',
                    'label' => $this->l('Date end:'),
                    'name' => 'f_date_end',
                    ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Shipping country:'),
                    'name' => 'f_country',
                    'options' => array(
                        'query' => $a_country,
                        'id' => 'id_country',
                        'name' => 'name',
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Shipping state:'),
                    'name' => 'f_state',
                    'options' => array(
                        'query' => $a_state,
                        'id' => 'id_state',
                        'name' => 'name',
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Shipping zone:'),
                    'name' => 'f_zone',
                    'options' => array(
                        'query' => $a_zone,
                        'id' => 'id_zone',
                        'name' => 'name',
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Generate CSV'),
                    'name' => 'f_csv',
                    'required' => false,
                    'desc' => '<a target="blank" href="'.$s_csv_url.'">'.$this->l('View CSV').'</a>',
                    'is_bool' => true,
                    'values' => $s_options,
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Order by:'),
                    'name' => 'f_orderby',
                    'options' => array(
                        'query' => $a_orderby,
                        'id' => 'id_orderby',
                        'name' => 'name',
                    ),
                )
                ),
            'submit' => array(
                'title' => $this->l('Filter'),
                'class' => 'f_submit',
            ),
        );
    }

    public function renderForm()
    {
        $this->initFieldGeneral();

        return parent::renderForm();
    }

    public function initContent()
    {
        parent::initContent();
        $this->content = $this->renderForm().$this->content;
        $this->context->smarty->assign(array(
            'content' => $this->content,
            'url_post' => self::$currentIndex.'&token='.$this->token,
        ));
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitAddconfiguration')) {
            $this->g_inicizlizate = 1;
            $this->g_date_start = Tools::getValue('f_date_start');
            $this->g_date_end = Tools::getValue('f_date_end');
            $this->g_country = Tools::getValue('f_country');
            $this->g_state = Tools::getValue('f_state');
            $this->g_zone = Tools::getValue('f_zone');
            $this->g_orderby = Tools::getValue('f_orderby');

            if (Tools::getValue('f_csv')) {
                $this->getList($this->default_form_language);
                $this->generarCsvGuardado();
            }
        }
    }

    public function orderList()
    {
        $result = ' ORDER BY ';
        $way = 'ASC';

        if ($this->g_orderby == 0) {
            $result .= 'o.date_add '.$way;
        }
        if ($this->g_orderby == 1) {
            $result .= 'cl.name '.$way;
        }
        if ($this->g_orderby == 2) {
            $result .= 's.name '.$way;
        }
        if ($this->g_orderby == 3) {
            $result .= 'z.name '.$way;
        }
        if ($this->g_orderby == 4) {
            $result .= 'o.total_products '.$way;
        }
        if ($this->g_orderby == 5) {
            $result .= 'c.name '.$way;
        }
        if ($this->g_orderby == 6) {
            $result .= 'o.total_shipping_tax_incl '.$way;
        }

        return $result;
    }

    public function loadList()
    {
        $context = Context::getContext();

        $sql = 'SELECT o.id_order as counter, o.date_add as order_date, cl.name as country, s.name as state, z.name as zone,
                       o.id_order as weight , o.total_products, c.name as carrier, o.total_shipping_tax_incl
                FROM `'._DB_PREFIX_.'orders` o                
                LEFT JOIN `'._DB_PREFIX_.'address` a ON o.id_address_delivery = a.id_address
                LEFT JOIN `'._DB_PREFIX_.'country_lang` cl ON a.id_country = cl.id_country
                LEFT JOIN `'._DB_PREFIX_.'state` s ON a.id_state = s.id_state
                LEFT JOIN `'._DB_PREFIX_.'carrier` c ON o.id_carrier = c.id_carrier
                LEFT JOIN `'._DB_PREFIX_.'carrier_zone` cz ON o.id_carrier = cz.id_carrier
                LEFT JOIN `'._DB_PREFIX_.'zone` z ON s.id_zone = z.id_zone
                ';
        $a_where = array();
        $a_where[] = 'WHERE cl.id_lang='.(int)$context->employee->id_lang;

        if (!empty($this->g_date_start)) {
            $fecha = $this->g_date_start.' 00:00:00';
            $a_where[] = ' o.date_add >= "'.pSQL($fecha).'" ';
        }

        if (!empty($this->g_date_end)) {
            $fecha = $this->g_date_end.' 23:59:59';
            $a_where[] = ' o.date_add <= "'.pSQL($fecha).'" ';
        }
        if (!empty($this->g_country)) {
            $a_where[] = ' a.id_country = '.(int)$this->g_country;
        }
        if (!empty($this->g_state)) {
            $a_where[] = ' a.id_state = '.(int)$this->g_state;
        }
        if (!empty($this->g_zone)) {
            $a_where[] = ' s.id_zone = '.(int)$this->g_zone;
        }

        $a_where[] = ' o.id_shop = '.(int)$this->context->shop->id;

        $where = implode(' AND ', $a_where);
        $groupby = ' GROUP BY o.id_order';
        $orderby = $this->orderList();
        $sql = $sql.$where.$groupby.$orderby;
        $a_result = Db::getInstance()->ExecuteS($sql);
        return $a_result;
    }

    public function loadCountry()
    {
        $a_default = array('id_country' => 0, 'name' => $this->l('All'));
        $context = Context::getContext();
        $sql = 'SELECT c.id_country, name 
              FROM`'._DB_PREFIX_.'country` c 
              LEFT JOIN `'._DB_PREFIX_.'country_lang` cl ON c.id_country = cl.id_country
              WHERE  active = 1 AND id_lang = '.(int)$context->employee->id_lang;
        $a_result = Db::getInstance()->ExecuteS($sql);
        array_unshift($a_result, $a_default);

        return $a_result;
    }

    public function loadState()
    {
        $a_default = array('id_state' => 0, 'name' => $this->l('All'));
        $sql = 'SELECT s.id_state, s.name 
              FROM`'._DB_PREFIX_.'state` s 
              LEFT JOIN `'._DB_PREFIX_.'country` c ON s.id_country = c.id_country
              WHERE  c.active = 1';
        $a_result = Db::getInstance()->ExecuteS($sql);
        array_unshift($a_result, $a_default);

        return $a_result;
    }

    public function loadZone()
    {
        $a_default = array('id_zone' => 0, 'name' => $this->l('All'));
        $sql = 'SELECT id_zone, name 
              FROM`'._DB_PREFIX_.'zone`
              WHERE active = 1';
        $a_result = Db::getInstance()->ExecuteS($sql);
        array_unshift($a_result, $a_default);

        return $a_result;
    }

    public function generarCsv()
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename=fichero.csv');
        $out = fopen('php://output', 'w');

        foreach ($this->ga_csv as $fila) {
            fputcsv($out, $fila, ';', '"');
        }

        fclose($out);
        die();
    }
    public function generarCsvGuardado()
    {
        if (isset($this->ga_csv)) {
            $out = fopen(_PS_MODULE_DIR_.$this->g_moduloName.'/views/files/'.$this->g_moduloName.'.csv', 'w');
            foreach ($this->ga_csv as $fila) {
                fputcsv($out, $fila, ';', '"');
            }

            fclose($out);
            $this->confirmations[] = $this->l('CSV generated successfully');
        }
    }

    public function initToolbar()
    {
        $this->toolbar_btn = array();
    }

    public function display()
    {
        $this->context->smarty->assign(array(
            'display_header' => $this->display_header,
            'display_header_javascript' => $this->display_header_javascript,
            'display_footer' => $this->display_footer,
            'js_def' => Media::getJsDef(),
        ));

        // Use page title from meta_title if it has been set else from the breadcrumbs array
        if (!$this->meta_title) {
            $this->meta_title = $this->toolbar_title;
        }
        if (is_array($this->meta_title)) {
            $this->meta_title = strip_tags(implode(' '.Configuration::get('PS_NAVIGATION_PIPE').' ', $this->meta_title));
        }
        $this->context->smarty->assign('meta_title', $this->meta_title);

        $template_dirs = $this->context->smarty->getTemplateDir();

        // Check if header/footer have been overriden
        $dir = $this->context->smarty->getTemplateDir(0).'controllers'.DIRECTORY_SEPARATOR.trim($this->override_folder, '\\/').DIRECTORY_SEPARATOR;
        $module_list_dir = $this->context->smarty->getTemplateDir(0).'helpers'.DIRECTORY_SEPARATOR.'modules_list'.DIRECTORY_SEPARATOR;

        $header_tpl = file_exists($dir.'header.tpl') ? $dir.'header.tpl' : 'header.tpl';
        $page_header_toolbar = file_exists($dir.'page_header_toolbar.tpl') ? $dir.'page_header_toolbar.tpl' : 'page_header_toolbar.tpl';
        $footer_tpl = file_exists($dir.'footer.tpl') ? $dir.'footer.tpl' : 'footer.tpl';
        $modal_module_list = file_exists($module_list_dir.'modal.tpl') ? $module_list_dir.'modal.tpl' : 'modal.tpl';
        $tpl_action = $this->tpl_folder.$this->display.'.tpl';

        // Check if action template has been overriden
        foreach ($template_dirs as $template_dir) {
            if (file_exists($template_dir.DIRECTORY_SEPARATOR.$tpl_action) && $this->display != 'view' && $this->display != 'options') {
                if (method_exists($this, $this->display.Tools::toCamelCase($this->className))) {
                    $this->{$this->display.Tools::toCamelCase($this->className)}();
                }
                $this->context->smarty->assign('content', $this->context->smarty->fetch($tpl_action));
                break;
            }
        }

        if (!$this->ajax) {
            $template = $this->createTemplate($this->template);
            $page = $template->fetch();
        } else {
            $page = $this->content;
        }

        if ($conf = Tools::getValue('conf')) {
            $this->context->smarty->assign('conf', $this->json ? Tools::jsonEncode($this->_conf[(int) $conf]) : $this->_conf[(int) $conf]);
        }

        foreach (array('errors', 'warnings', 'informations', 'confirmations') as $type) {
            if (!is_array($this->$type)) {
                $this->$type = (array) $this->$type;
            }
            $this->context->smarty->assign($type, $this->json ? Tools::jsonEncode(array_unique($this->$type)) : array_unique($this->$type));
        }

        if ($this->show_page_header_toolbar && !$this->lite_display) {
            $this->context->smarty->assign(
                array(
                    'page_header_toolbar' => $this->context->smarty->fetch($page_header_toolbar),
                    'modal_module_list' => $this->context->smarty->fetch($modal_module_list),
                )
            );
        }

        $this->context->smarty->assign(
            array(
                'page' => $this->json ? Tools::jsonEncode($page) : $page,
                'header' => $this->context->smarty->fetch($header_tpl),
                'footer' => $this->context->smarty->fetch($footer_tpl),
            )
        );

        $this->smartyOutputContent($this->layout);
    }
}
