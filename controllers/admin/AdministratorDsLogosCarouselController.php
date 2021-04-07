<?php
/**
 * 2007-2019 PrestaShop.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    "Dark-Side"
 *  @copyright 2007-2019 Dark-Side
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class AdministratorDsLogosCarouselController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();

        if (Tools::isSubmit('array')) {
        } else {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules').'&configure=dslogoscarousel');
        }
    }

    public function updateDatabase($datas)
    {
        $success = false;
        if (!empty($datas) && $datas != null) {
            $success = 1;

            foreach ($datas as $key => $data) {
                $intData = (int) $data;
                $intKey = (int) $key + 1;
                Db::getInstance()->update('ds_logocarousel', array(
                    'logo_order' => (int)$intKey,
                ), 'id_logoCarousel = '.(int)$intData.'');
            }
        }

        die(Tools::jsonEncode(
            'msg'
        ));
    }

    public function ajaxProcessCall()
    {
        $success = false;

        /**
         * Get param
         */
        $datas = Tools::getValue('array');

        $this->updateDatabase($datas);
    }
}
