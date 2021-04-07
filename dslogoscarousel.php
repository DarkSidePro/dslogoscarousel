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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
if (!class_exists('ImageResize')) {
    include 'class/ImageResize.php';
}
if (!defined('_PS_VERSION_')) {
    exit;
}

class Dslogoscarousel extends Module
{
    protected $config_form = false;
    protected $templateAdmin;
    protected $templateFront;

    public function __construct()
    {
        $this->name = 'dslogoscarousel';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Dark-Side.pro';
        $this->need_instance = 1;
        $this->module_key = '823d08a040db52aecc82f5a87ad3270a';

        /*
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('DS: Logo\'s Carousel');
        $this->description = $this->l('Display carousel of brands');

        $this->confirmUninstall = $this->l('Do you realy want to remove this module?');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    private function createTab()
    {
        $response = true;
        $parentTabID = Tab::getIdFromClassName('AdminDarkSideMenu');
        if ($parentTabID) {
            $parentTab = new Tab($parentTabID);
        } else {
            $parentTab = new Tab();
            $parentTab->active = 1;
            $parentTab->name = array();
            $parentTab->class_name = 'AdminDarkSideMenu';
            foreach (Language::getLanguages() as $lang) {
                $parentTab->name[$lang['id_lang']] = 'Dark-Side.pro';
            }
            $parentTab->id_parent = 0;
            $parentTab->module = '';
            $response &= $parentTab->add();
        }
        $parentTab_2ID = Tab::getIdFromClassName('AdminDarkSideMenuSecond');
        if ($parentTab_2ID) {
            $parentTab_2 = new Tab($parentTab_2ID);
        } else {
            $parentTab_2 = new Tab();
            $parentTab_2->active = 1;
            $parentTab_2->name = array();
            $parentTab_2->class_name = 'AdminDarkSideMenuSecond';
            foreach (Language::getLanguages() as $lang) {
                $parentTab_2->name[$lang['id_lang']] = 'Dark-Side Config';
            }
            $parentTab_2->id_parent = $parentTab->id;
            $parentTab_2->module = '';
            $response &= $parentTab_2->add();
        }
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdministratorDsLogosCarousel';
        $tab->name = array();
        foreach (Language::getLanguages() as $lang) {
            $tab->name[$lang['id_lang']] = 'Logo\'s Carousel';
        }
        $tab->id_parent = $parentTab_2->id;
        $tab->module = $this->name;
        $response &= $tab->add();

        return $response;
    }

    private function tabRem()
    {
        $id_tab = Tab::getIdFromClassName('AdministratorDsLogosCarousel');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            $tab->delete();
        }
        $parentTab_2ID = Tab::getIdFromClassName('AdminDarkSideMenuSecond');
        if ($parentTab_2ID) {
            $tabCount_2 = Tab::getNbTabs($parentTab_2ID);
            if ($tabCount_2 == 0) {
                $parentTab_2 = new Tab($parentTab_2ID);
                $parentTab_2->delete();
            }
        }
        $parentTabID = Tab::getIdFromClassName('AdminDarkSideMenu');
        if ($parentTabID) {
            $tabCount = Tab::getNbTabs($parentTabID);
            if ($tabCount == 0) {
                $parentTab = new Tab($parentTabID);
                $parentTab->delete();
            }
        }

        return true;
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update.
     */
    public function install()
    {
        Configuration::updateValue('DSLOGOSCAROUSEL_MOBILE', true);
        Configuration::updateValue('DSLOGOSCAROUSEL_COLOR', '#000');
        Configuration::updateValue('DSLOGOSCAROUSEL_MOBILEITEM', '1');
        Configuration::updateValue('DSLOGOSCAROUSEL_TABLETITEM', '3');
        Configuration::updateValue('DSLOGOSCAROUSEL_DESKTOPITEM', '6');

        include dirname(__FILE__).'/sql/install.php';

        $this->createTab();

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayHome');
    }

    public function uninstall()
    {
        Configuration::deleteByName('DSLOGOSCAROUSEL_MOBILE');
        Configuration::deleteByName('DSLOGOSCAROUSEL_COLOR');
        Configuration::deleteByName('DSLOGOSCAROUSEL_MOBILEITEM');
        Configuration::deleteByName('DSLOGOSCAROUSEL_TABLETITEM');
        Configuration::deleteByName('DSLOGOSCAROUSEL_DESKTOPITEM');

        include dirname(__FILE__).'/sql/uninstall.php';

        $this->tabRem();

        return parent::uninstall();
    }

    public function clearCache()
    {
        $this->templateAdmin = $this->local_path.'views/admin/configure.tpl';
        $type = Configuration::get('DSLOGOSCAROUSEL_TYPE');

        if ($type == true) {
            $this->templateFront = $this->local_path.'views/hook/grid.tpl';
        } else {
            $this->templateFront = $this->local_path.'views/hook/hookDisplayHome.tpl';
        }

        $this->_clearCache($this->templateAdmin);
        $this->_clearCache($this->templateFront);
    }

    /**
     * Load the configuration form.
     */
    public function getContent()
    {
        if (((bool) Tools::isSubmit('submitDS_LOGOS_CAROUSELModule2')) == true) {
            $link = Tools::getValue('DSLOGOSCAROUSEL_LINK');

            if (Validate::isUrl($link) != true) {
                return $this->displayError($this->trans('The url field is incorect', array(), 'Admin.Dslogoscarousel.Error'));
            }

            $filename = $_FILES['DSLOGOSCAROUSEL_IMAGE']['name'];
            $logo_active = Tools::getValue('DSLOGOSCAROUSEL_ACTIVE');

            if (Validate::isInt($logo_active) != true) {
                return $this->displayError($this->trans('The logo active must be a number', array(), 'Admin.Dslogoscarousel.Error'));
            }

            if ($logo_active != 1) {
                $logo_active = 0;
            }

            $msg = $this->createCarouselItem($link, $filename, $logo_active);
            $this->clearCache();
        }

        if (((bool) Tools::isSubmit('submitDSLOGOSCAROUSEL_edit')) == true) {
            $link = Tools::getValue('DSLOGOSCAROUSEL_LINK');

            if (Validate::isUrl($link) != true) {
                return $this->displayError($this->trans('The url field is incorect', array(), 'Admin.Dslogoscarousel.Error'));
            }

            $filename = $_FILES['DSLOGOSCAROUSEL_IMAGE']['name'];
            $logo_active = Tools::getValue('DSLOGOSCAROUSEL_ACTIVE');

            if (Validate::isInt($logo_active) != true) {
                return $this->displayError($this->trans('The logo active must be a number', array(), 'Admin.Dslogoscarousel.Error'));
            }

            if ($logo_active != 1) {
                $logo_active = 0;
            }
            $itemID = Tools::getValue('editItem');

            if (Validate::isInt($itemID) != true) {
                return $this->displayError($this->trans('The itemID must be a number', array(), 'Admin.Dslogoscarousel.Error'));
            }

            $msg = $this->updateCarouselItem($link, $filename, $logo_active, $itemID);
        }

        if (Tools::isSubmit('deleteButton') == true) {
            if (!Tools::isEmpty('deleteItems')) {
                $deletedItems = Tools::getValue('deleteItems');

                if (Valudate::isArrayWithIds($deletedItems) != true) {
                    return $this->displayError($this->trans('The is not correct array', array(), 'Admin.Dslogoscarousel.Error'));
                }

                foreach ($deletedItems as $selected) {
                    $this->deleteCarousel($selected);
                }
            }
            $msg = $this->displayConfirmation($this->trans('Settings updated.', array(), 'Admin.Dslogoscarousel.Success'));
        }

        $items = $this->getCarouselItemsAdmin();
        $token_b = Tools::getAdminTokenLite('AdministratorDsLogosCarousel');

        $this->context->smarty->assign('items', $items);
        $this->context->smarty->assign('module_dir', $this->_path);
        $this->context->smarty->assign('namemodules', $this->name);
        $this->context->smarty->assign('link', $this->context->link);
        $this->context->smarty->assign('token', $token_b);
        $this->context->controller->addJqueryUI('ui.sortable');

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        if (((bool) Tools::isSubmit('submitDS_LOGOS_CAROUSELModule2')) == true) {
            return $msg.$output.$this->renderForm();
        }

        if (((bool) Tools::isSubmit('submitDSLOGOSCAROUSEL_edit')) == true) {
            return $msg.$output.$this->renderForm();
        }

        if (Tools::isSubmit('addNew') == true && !Tools::isSubmit('deleteButton')) {
            return $output = $this->addListArray();
        }

        if (Tools::isSubmit('editItem') == true && !Tools::isSubmit('deleteButton')) {
            return $output = $this->editItem();
        }

        if (Tools::isSubmit('deleteButton') == true) {
            return $msg.$output.$this->renderForm();
        }

        if (((bool) Tools::isSubmit('submitDs_logos_carouselModule')) == true) {
            $msg = $this->postProcess();

            return $msg.$output.$this->renderForm();
        }

        $this->clearCache();

        return $output.$this->renderForm();
    }

    /**
     * Edit carousel item.
     *
     * @param $itemID (int)
     */
    protected function editItem()
    {
        $id = Tools::getValue('editItem');

        if (Validate::isInt($id) != true) {
            return $this->displayError($this->trans('The id must be a number', array(), 'Admin.Dslogoscarousel.Error'));
        }

        $data = $this->getCarouselItemsAdminID($id);
        $data2 = $this->getCarouselItemsAdminIDTitle($id);
        $data3 = $this->getCarouselItemsAdminIDAlt($id);

        $array = array(
            'DSLOGOSCAROUSEL_IMAGE' => $data[0]['file_name'],
            'DSLOGOSCAROUSEL_LINK' => $data[0]['logo_link'],
            'DSLOGOSCAROUSEL_ACTIVE' => $data[0]['logo_active'],
            'DSLOGOSCAROUSEL_TITLE' => $data2,
            'DSLOGOSCAROUSEL_ALT' => $data3,
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->submit_action = 'submitDSLOGOSCAROUSEL_edit';
        $helper->tpl_vars = array(
            'fields_value' => $array,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array(array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'file',
                        'label' => $this->l('Select image'),
                        'name' => 'DSLOGOSCAROUSEL_IMAGE',
                        'desc' => $this->l('Select image from your drive'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Link'),
                        'name' => 'DSLOGOSCAROUSEL_LINK',
                        'desc' => $this->l('Link to your partner'),
                        'class' => 'rte',
                        'autoload_rte' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Title'),
                        'name' => 'DSLOGOSCAROUSEL_TITLE',
                        'desc' => $this->l('Title for link'),
                        'lang' => true,
                        'class' => 'rte',
                        'autoload_rte' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Alt'),
                        'name' => 'DSLOGOSCAROUSEL_ALT',
                        'desc' => $this->l('Alt description for image'),
                        'lang' => true,
                        'class' => 'rte',
                        'autoload_rte' => true,
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Active'),
                        'name' => 'DSLOGOSCAROUSEL_ACTIVE',
                        'is_bool' => true,
                        'desc' => $this->l('Enable or disable logo'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
        ), )));
    }

    protected function updateCarouselItem($link, $filename, $logo_active, $itemID)
    {
        if (!empty($filename) && $filename != null) {
            $target_dir = $this->local_path.'views/img/carousel_items/';
            $target_file = $target_dir.basename($_FILES['DSLOGOSCAROUSEL_IMAGE']['tmp_name']);

            if ($error = ImageManager::validateUpload($_FILES['DSLOGOSCAROUSEL_IMAGE'], 4000000)) {
                return $this->displayError($error);
            } else {
                $our_image = $_FILES['DSLOGOSCAROUSEL_IMAGE']['tmp_name'];
                $file = $_FILES['DSLOGOSCAROUSEL_IMAGE']['name'];
                $ext = Tools::substr($file, strrpos($file, '.') + 1);
                $preoutput = $this->local_path.'views/img/carousel_items/preoutput.png';

                if (function_exists('exif_imagetype')) {
                    if (exif_imagetype($our_image) == IMAGETYPE_GIF) {
                        $pre = imagepng(imagecreatefromgif($our_image), $preoutput);
                    } elseif (exif_imagetype($our_image) == IMAGETYPE_BMP) {
                        $pre = imagepng(imagecreatefrombmp($our_image), $preoutput);
                    } elseif (exif_imagetype($our_image) == IMAGETYPE_JPEG) {
                        $pre = imagepng(imagecreatefromjpeg($_FILES['image']['tmp_name']), $preoutput);
                    } else {
                        $our_image = $preoutput;
                    }
                } else {
                    if ($ext == 'bmp') {
                        $pre = imagepng(imagecreatefrombmp($our_image), $preoutput);
                    } elseif ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'JPG') {
                        $pre = imagepng(imagecreatefromjpeg($our_image), $preoutput);
                    } elseif ($ext == 'gif') {
                        $pre = imagepng(imagecreatefromgif($our_image), $preoutput);
                    } else {
                        $pre = imagepng(imagecreatefrompng($our_image), $preoutput);
                    }
                }

                list($width, $height) = getimagesize($our_image);

                if ($width < 100 || $height < 100) {
                    $error = $this->displayError($this->trans('Image is too small. Use minimum 100x100px', array(), 'Admin.Dslogoscarousel.Error'));

                    return $error;
                }
                $filename = time();
                $image = new \ImageResize($preoutput);
                $image->save($this->local_path.'views/img/carousel_items/'.$filename);
                $error = $this->displayError($this->trans('Something wrong.', array(), 'Admin.Dslogoscarousel.Error'));
                if (!file_exists($this->local_path.'views/img/carousel_items/'.$filename)) {
                    return $error;
                }

                $this->SQLinsertCarouselItems($filename, $link, $logo_active, $itemID);
                $languages = Language::getLanguages(false);

                $this->SQLinsertCarouselItemsLangDelete($itemID);

                foreach ($languages as $lang) {
                    $titleLang = Tools::getValue('DSLOGOSCAROUSEL_TITLE_'.$lang['id_lang']);

                    if (Validate::isString($titleLang) != true) {
                        return $this->displayError($this->trans('Title is not correct string', array(), 'Admin.Dslogoscarousel.Error'));
                    }

                    $title = htmlspecialchars($titleLang);

                    $altLang = Tools::getValue('DSLOGOSCAROUSEL_ALT_'.$lang['id_lang']);

                    if (Validate::isString($altLang) != true) {
                        return $this->displayError($this->trans('Alt is not correct string', array(), 'Admin.Dslogoscarousel.Error'));
                    }

                    $alt = htmlspecialchars(Tools::getValue('DSLOGOSCAROUSEL_ALT_'.$lang['id_lang']));
                    $id_lang = $lang['id_lang'];

                    if (Validate::isInt($id_lang) != true) {
                        return $this->displayError($this->trans('Lang id is incorrect', array(), 'Admin.Dslogoscarousel.Error'));
                    }

                    $this->SQLinsertCarouselItemsLang($itemID, $title, $alt, $id_lang);
                }
            }
        } else {
            $this->SQLinsertCarouselItemsWithoutGFX($link, $logo_active, $itemID);
            $languages = Language::getLanguages(false);
            $this->SQLinsertCarouselItemsLangDelete($itemID);
            foreach ($languages as $lang) {
                $titleLang = Tools::getValue('DSLOGOSCAROUSEL_TITLE_'.$lang['id_lang']);

                if (Validate::isString($titleLang) != true) {
                    return $this->displayError($this->trans('Title is not correct string', array(), 'Admin.Dslogoscarousel.Error'));
                }

                $title = htmlspecialchars($titleLang);

                $altLang = Tools::getValue('DSLOGOSCAROUSEL_ALT_'.$lang['id_lang']);

                if (Validate::isString($altLang) != true) {
                    return $this->displayError($this->trans('Alt is not correct string', array(), 'Admin.Dslogoscarousel.Error'));
                }

                $alt = htmlspecialchars(Tools::getValue('DSLOGOSCAROUSEL_ALT_'.$lang['id_lang']));
                $id_lang = $lang['id_lang'];

                if (Validate::isInt($id_lang) != true) {
                    return $this->displayError($this->trans('Lang id is incorrect', array(), 'Admin.Dslogoscarousel.Error'));
                }

                $this->SQLinsertCarouselItemsLang($itemID, $title, $alt, $id_lang);
            }

            return $this->displayConfirmation($this->trans('Settings updated.', array(), 'Admin.Dslogoscarousel.Success'));
        }
    }

    protected function addListArray()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->submit_action = 'submitDS_LOGOS_CAROUSELModule2';
        $helper->tpl_vars = array(
            'fields_value' => array(
                'DSLOGOSCAROUSEL_IMAGE' => null,
                'DSLOGOSCAROUSEL_LINK' => null,
                'DSLOGOSCAROUSEL_TITLE' => null,
                'DSLOGOSCAROUSEL_ALT' => null,
                'DSLOGOSCAROUSEL_ACTIVE' => true,
            ),
            'languages' => $this->context->controller->getLanguages(false),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array(array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'file',
                        'label' => $this->l('Select image'),
                        'name' => 'DSLOGOSCAROUSEL_IMAGE',
                        'desc' => $this->l('Select image from your drive'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Link'),
                        'name' => 'DSLOGOSCAROUSEL_LINK',
                        'desc' => $this->l('Link to your partner'),
                        'class' => 'rte',
                        'autoload_rte' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Title'),
                        'name' => 'DSLOGOSCAROUSEL_TITLE',
                        'desc' => $this->l('Title for link'),
                        'lang' => true,
                        'class' => 'rte',
                        'autoload_rte' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Alt'),
                        'name' => 'DSLOGOSCAROUSEL_ALT',
                        'desc' => $this->l('Alt description for image'),
                        'lang' => true,
                        'class' => 'rte',
                        'autoload_rte' => true,
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Active'),
                        'name' => 'DSLOGOSCAROUSEL_ACTIVE',
                        'is_bool' => true,
                        'desc' => $this->l('Enable or disable logo'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ), )));
    }

    protected function createCarouselItem($link, $filename, $logo_active)
    {
        $target_dir = $this->local_path.'views/img/carousel_items/';
        $target_file = $target_dir.basename($_FILES['DSLOGOSCAROUSEL_IMAGE']['tmp_name']);

        if ($error = ImageManager::validateUpload($_FILES['DSLOGOSCAROUSEL_IMAGE'], 4000000)) {
            return $this->displayError($error);
        } else {
            $our_image = $_FILES['DSLOGOSCAROUSEL_IMAGE']['tmp_name'];
            $file = $_FILES['DSLOGOSCAROUSEL_IMAGE']['name'];
            $ext = Tools::substr($file, strrpos($file, '.') + 1);
            $preoutput = $this->local_path.'views/img/carousel_items/preoutput.png';

            if (function_exists('exif_imagetype')) {
                if (exif_imagetype($our_image) == IMAGETYPE_GIF) {
                    $pre = imagepng(imagecreatefromgif($our_image), $preoutput);
                } elseif (exif_imagetype($our_image) == IMAGETYPE_BMP) {
                    $pre = imagepng(imagecreatefrombmp($our_image), $preoutput);
                } elseif (exif_imagetype($our_image) == IMAGETYPE_JPEG) {
                    $pre = imagepng(imagecreatefromjpeg($_FILES['image']['tmp_name']), $preoutput);
                } else {
                    $our_image = $preoutput;
                }
            } else {
                if ($ext == 'bmp') {
                    $pre = imagepng(imagecreatefrombmp($our_image), $preoutput);
                } elseif ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'JPG') {
                    $pre = imagepng(imagecreatefromjpeg($our_image), $preoutput);
                } elseif ($ext == 'gif') {
                    $pre = imagepng(imagecreatefromgif($our_image), $preoutput);
                } else {
                    $pre = imagepng(imagecreatefrompng($our_image), $preoutput);
                }
            }
            list($width, $height) = getimagesize($our_image);

            if ($width < 100 || $height < 100) {
                $error = $this->displayError($this->trans('Image is too small. Use minimum 100x100px', array(), 'Admin.Dslogoscarousel.Error'));

                return $error;
            }

            $image = new \ImageResize($preoutput);
            $filename = time().'.png';
            $image->save($this->local_path.'views/img/carousel_items/'.$filename);
            $error = $this->displayError($this->trans('Something wrong.', array(), 'Admin.Dslogoscarousel.Error'));
            if (!file_exists($this->local_path.'views/img/carousel_items/'.$filename)) {
                return $error;
            }

            $last_id = $this->insertCarouselItems($filename, $link, $logo_active);
            $languages = Language::getLanguages();

            foreach ($languages as $lang) {
                $titleLang = Tools::getValue('DSLOGOSCAROUSEL_TITLE_'.$lang['id_lang']);

                if (Validate::isString($titleLang) != true) {
                    return $this->displayError($this->trans('Title is not correct string', array(), 'Admin.Dslogoscarousel.Error'));
                }

                $title = htmlspecialchars($titleLang);

                $altLang = Tools::getValue('DSLOGOSCAROUSEL_ALT_'.$lang['id_lang']);

                if (Validate::isString($altLang) != true) {
                    return $this->displayError($this->trans('Alt is not correct string', array(), 'Admin.Dslogoscarousel.Error'));
                }

                $alt = htmlspecialchars($altLang);
                $id_lang = $lang['id_lang'];

                if (Validate::isInt($id_lang) != true) {
                    return $this->displayError($this->trans('Lang id is incorrect', array(), 'Admin.Dslogoscarousel.Error'));
                }

                $this->insertCarouselItemsLang($last_id, $title, $alt, $id_lang);
            }

            return $this->displayConfirmation($this->trans('Settings updated.', array(), 'Admin.Dslogoscarousel.Success'));
        }
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitDs_logos_carouselModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show on mobile'),
                        'name' => 'DSLOGOSCAROUSEL_MOBILE',
                        'is_bool' => true,
                        'desc' => $this->l('Show carousel on mobile'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Type of front view'),
                        'name' => 'DSLOGOSCAROUSEL_TYPE',
                        'is_bool' => true,
                        'desc' => $this->l('How wuld you like show items? On grid or like a carousel?'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Grid'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Carousel'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Header content'),
                        'desc' => $this->l('Write your heading before content'),
                        'name' => 'DSLOGOSCAROUSEL_HEADER',
                        'lang' => true,
                        'class' => 'rte',
                        'autoload_rte' => true,
                    ),
                    array(
                        'col' => 3,
                        'type' => 'color',
                        'desc' => $this->l('Choose header color'),
                        'name' => 'DSLOGOSCAROUSEL_COLOR',
                        'label' => $this->l('Link color'),
                        'required' => false,
                    ),

                    array(
                        'type' => 'select',
                        'label' => $this->l('Mobile items'),
                        'desc' => $this->l('How many items you want show on mobile?'),
                        'name' => 'DSLOGOSCAROUSEL_MOBILEITEM',
                        'required' => true,
                        'options' => array(
                            'query' => array(
                                array('key' => '1', 'name' => '1'),
                                array('key' => '2', 'name' => '2'),
                                array('key' => '3', 'name' => '3'),
                                array('key' => '4', 'name' => '4'),
                                array('key' => '5', 'name' => '5'),
                                array('key' => '6', 'name' => '6'),
                                array('key' => '7', 'name' => '7'),
                                array('key' => '8', 'name' => '8'),
                                array('key' => '9', 'name' => '9'),
                            ),
                            'id' => 'key',
                            'name' => 'name'
                        )
                    ),

                    array(
                        'type' => 'select',
                        'label' => $this->l('Tablet items'),
                        'name' => 'DSLOGOSCAROUSEL_TABLETITEM',
                        'desc' => $this->l('How many items you want show on tablet?'),
                        'required' => true,
                        'options' => array(
                            'query' => array(
                                array('key' => '1', 'name' => '1'),
                                array('key' => '2', 'name' => '2'),
                                array('key' => '3', 'name' => '3'),
                                array('key' => '4', 'name' => '4'),
                                array('key' => '5', 'name' => '5'),
                                array('key' => '6', 'name' => '6'),
                                array('key' => '7', 'name' => '7'),
                                array('key' => '8', 'name' => '8'),
                                array('key' => '9', 'name' => '9'),
                            ),
                            'id' => 'key',
                            'name' => 'name'
                        )
                    ),

                    array(
                        'type' => 'select',
                        'label' => $this->l('Desktop items'),
                        'name' => 'DSLOGOSCAROUSEL_DESKTOPITEM',
                        'desc' => $this->l('How many elements do you want show on desktop?'),
                        'required' => true,
                        'options' => array(
                            'query' => array(
                                array('key' => '1', 'name' => '1'),
                                array('key' => '2', 'name' => '2'),
                                array('key' => '3', 'name' => '3'),
                                array('key' => '4', 'name' => '4'),
                                array('key' => '5', 'name' => '5'),
                                array('key' => '6', 'name' => '6'),
                                array('key' => '7', 'name' => '7'),
                                array('key' => '8', 'name' => '8'),
                                array('key' => '9', 'name' => '9'),
                            ),
                            'id' => 'key',
                            'name' => 'name'
                        )
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        $fields = $this->getHeaderNameAdmin();

        return array(
            'DSLOGOSCAROUSEL_MOBILE' => Configuration::get('DSLOGOSCAROUSEL_MOBILE', true),
            'DSLOGOSCAROUSEL_TYPE' => Configuration::get('DSLOGOSCAROUSEL_TYPE', true),
            'DSLOGOSCAROUSEL_HEADER' => $fields,
            'DSLOGOSCAROUSEL_COLOR' => Configuration::get('DSLOGOSCAROUSEL_COLOR', null),
            'DSLOGOSCAROUSEL_MOBILEITEM' => Configuration::get('DSLOGOSCAROUSEL_MOBILEITEM', null),
            'DSLOGOSCAROUSEL_TABLETITEM' => Configuration::get('DSLOGOSCAROUSEL_TABLETITEM', null),
            'DSLOGOSCAROUSEL_DESKTOPITEM' =>Configuration::get('DSLOGOSCAROUSEL_DESKTOPITEM', null),
        );
    }

    protected function getHeaderNameAdmin()
    {
        $fields_value = array();
        $id_info = 1;

        $languages = $this->context->controller->getLanguages();

        foreach ($languages as $lang) {
            $id_lang = $lang['id_lang'];

            if (Validate::isInt($id_lang) != true) {
                return $this->displayError($this->trans('Lang id not correct number', array(), 'Admin.Dslogoscarousel.Error'));
            }

            $sql = 'SELECT logo_header FROM '._DB_PREFIX_.'ds_logocarousel_header WHERE `id_lang` = '.(int) $id_lang;
            $sql = Db::getInstance()->ExecuteS($sql);
            if (empty($sql)) {
                $fields_value[$id_lang] = '';
            } else {
                $fields_value[$id_lang] = htmlspecialchars_decode($sql[0]['logo_header']);
            }
        }

        return $fields_value;
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        $text = array();
        $languages = Language::getLanguages(false);

        foreach ($languages as $lang) {
            $content = Tools::getValue('DSLOGOSCAROUSEL_HEADER_'.$lang['id_lang']);

            if (Validate::isString($content) != true) {
                return $this->displayError($this->trans('Content not correct string', array(), 'Admin.Dslogoscarousel.Error'));
            }

            $content = htmlspecialchars($content);
            $id_lang = $lang['id_lang'];

            if (Validate::isInt($id_lang) != true) {
                return $this->displayError($this->trans('Lang id not correct number', array(), 'Admin.Dslogoscarousel.Error'));
            }

            $this->updateHeader($content, $id_lang);
        }
        $color = Tools::getValue('DSLOGOSCAROUSEL_COLOR');
        $mobileItems = Tools::getValue('DSLOGOSCAROUSEL_MOBILEITEM');
        $tabletItems = Tools::getValue('DSLOGOSCAROUSEL_TABLETITEM');
        $desktopItems = Tools::getValue('DSLOGOSCAROUSEL_DESKTOPITEM');
        $type = Tools::getValue('DSLOGOSCAROUSEL_TYPE');

        if (Validate::isColor($color) != true) {
            return $this->displayError($this->trans('You must correct fill color field', array(), 'Admin.Dslogoscarousel.Error'));
        }

        if (Validate::isInt($mobileItems) != true) {
            return $this->displayError($this->trans('Mobile items field must be a number', array(), 'Admin.Dslogoscarousel.Error'));
        }

        if (Validate::isInt($tabletItems == true)) {
        } else {
            return $this->displayError($this->trans('Tablet items field must be a number', array(), 'Admin.Dslogoscarousel.Error'));
        }

        if (Validate::isInt($desktopItems == true)) {
        } else {
            return $this->displayError($this->trans('Desktop items field must be a number', array(), 'Admin.Dslogoscarousel.Error'));
        }

        if (Validate::isInt($type) != true) {
            return $this->displayError($this->trans('Type field must be a number', array(), 'Admin.Dslogoscarousel.Error'));
        }

        Configuration::updateValue('DSLOGOSCAROUSEL_TYPE', (int) $type);
        Configuration::updateValue('DSLOGOSCAROUSEL_COLOR', $color);
        Configuration::updateValue('DSLOGOSCAROUSEL_MOBILEITEM', (int) $mobileItems);
        Configuration::updateValue('DSLOGOSCAROUSEL_TABLETITEM', (int) $tabletItems);
        Configuration::updateValue('DSLOGOSCAROUSEL_DESKTOPITEM', (int) $desktopItems);

        return $this->displayConfirmation($this->trans('Settings updated.', array(), 'Admin.Dslogoscarousel.Success'));
    }

    protected function getHeaderName()
    {
        $id_lang = Context::getContext()->language->id;
        $sql = 'SELECT logo_header FROM '._DB_PREFIX_.'ds_logocarousel_header WHERE `id_lang` = '.(int) $id_lang;
        $sql = Db::getInstance()->ExecuteS($sql);

        return $sql;
    }

    protected function updateHeader($content, $lang)
    {
        $id_lang = (int) $lang;
        Db::getInstance()->delete('ds_logocarousel_header', 'id_lang = '.(int) $id_lang);
        Db::getInstance()->insert('ds_logocarousel_header', array(
            'logo_header' => pSQL($content),
            'id_lang' => (int) $id_lang,
        ));
    }

    protected function getCarouselItemsFront()
    {
        $lang = Context::getContext()->language->id;
        $sql = 'SELECT lc.file_name, lc.logo_link, lc.logo_order, lct.logo_title, lct.logo_alt FROM '._DB_PREFIX_.'ds_logocarousel AS lc LEFT JOIN '._DB_PREFIX_.'ds_logocarousel_text AS lct ON lc.id_logoCarousel = lct.id_logoCarousel WHERE lct.id_lang = '.(int) $lang.' AND lc.logo_active = 1 ORDER BY logo_order';
        $sql = Db::getInstance()->ExecuteS($sql);

        return $sql;
    }

    /**
     * Pobiera wszystkie kategorie do wyświetlenia dla front.
     *
     * @return array
     */
    protected function getCarouselItemsAdmin()
    {
        $lang = Context::getContext()->language->id;
        $sql = 'SELECT lc.id_logoCarousel, lc.file_name, lc.logo_link, lc.logo_order, lc.logo_active, lct.logo_title, lct.logo_alt FROM '._DB_PREFIX_.'ds_logocarousel AS lc LEFT JOIN '._DB_PREFIX_.'ds_logocarousel_text AS lct ON lc.id_logoCarousel = lct.id_logoCarousel WHERE lct.id_lang = '.(int) $lang.' ORDER BY logo_order';
        $sql = Db::getInstance()->ExecuteS($sql);

        return $sql;
    }

    protected function getCarouselItemsId()
    {
        $lang = Context::getContext()->language->id;

        $sql = 'SELECT lc.id_logoCarousel FROM '._DB_PREFIX_.'ds_logocarousel AS lc LEFT JOIN '._DB_PREFIX_.'ds_logocarousel_text AS lct ON lc.id_logoCarousel = lct.id_logoCarousel WHERE lct.id_lang = '.(int) $lang.' ORDER BY logo_order';
        $sql = Db::getInstance()->ExecuteS($sql);

        return $sql;
    }

    protected function getCarouselItemsAdminID($id_logocarousel)
    {
        $sql = 'SELECT lc.file_name, lc.logo_link, lc.logo_active FROM '._DB_PREFIX_.'ds_logocarousel AS lc WHERE lc.id_logoCarousel = '.(int) $id_logocarousel;
        $sql = Db::getInstance()->ExecuteS($sql);

        return $sql;
    }

    protected function getCarouselItemsAdminIDTitle($id_logocarousel)
    {
        $fields_value = array();
        $id_info = 1;

        $languages = $this->context->controller->getLanguages();

        foreach ($languages as $lang) {
            $id_lang = (int) $lang['id_lang'];

            if (Validate::isInt($id_lang) != true) {
                return $this->displayError($this->trans('Lang id not correct number', array(), 'Admin.Dslogoscarousel.Error'));
            }

            $sql = 'SELECT logo_title FROM '._DB_PREFIX_.'ds_logocarousel_text WHERE `id_lang` = '.(int) $id_lang.' AND `id_logoCarousel` = '.(int) $id_logocarousel;
            $sql = Db::getInstance()->ExecuteS($sql);
            $fields_value[$id_lang] = htmlspecialchars_decode($sql[0]['logo_title']);
        }

        return $fields_value;
    }

    protected function getCarouselItemsAdminIDAlt($id_logocarousel)
    {
        $fields_value = array();
        $id_info = 1;

        $languages = $this->context->controller->getLanguages();

        foreach ($languages as $lang) {
            $id_lang = (int) $lang['id_lang'];

            if (Validate::isInt($id_lang) != true) {
                return $this->displayError($this->trans('Lang id not correct number', array(), 'Admin.Dslogoscarousel.Error'));
            }

            $sql = 'SELECT logo_alt FROM '._DB_PREFIX_.'ds_logocarousel_text WHERE `id_lang` = '.(int) $id_lang.' AND `id_logoCarousel` = '.(int) $id_logocarousel;
            $sql = Db::getInstance()->ExecuteS($sql);
            $fields_value[$id_lang] = htmlspecialchars_decode($sql[0]['logo_alt']);
        }

        return $fields_value;
    }

    protected function SQLinsertCarouselItems($file_name, $logo_link, $logo_active, $id_logocarousel)
    {
        Db::getInstance()->update('ds_logocarousel', array(
            'file_name' => pSQL($file_name),
            'logo_link' => pSQL($logo_link),
            'logo_active' => (int) $logo_active,
        ), 'id_logoCarousel = '.(int) $id_logocarousel.'');
    }

    protected function SQLinsertCarouselItemsWithoutGFX($logo_link, $logo_active, $id_logocarousel)
    {
        Db::getInstance()->update('ds_logocarousel', array(
            'logo_link' => pSQL($logo_link),
            'logo_active' => (int) $logo_active,
        ), 'id_logoCarousel = '.(int) $id_logocarousel.'');
    }

    protected function SQLinsertCarouselItemsLangDelete($last_id)
    {
        Db::getInstance()->delete('ds_logocarousel_text', 'id_logoCarousel = '.(int) $last_id);
    }

    protected function SQLinsertCarouselItemsLang($last_id, $title, $alt, $id_lang)
    {
        Db::getInstance()->insert('ds_logocarousel_text', array(
            'id_logoCarousel' => (int) $last_id,
            'logo_title' => pSQL($title),
            'logo_alt' => pSQL($alt),
            'id_lang' => (int) $id_lang,
        ));
    }

    protected function deleteCarousel($id_logocarousel)
    {
        Db::getInstance()->delete('ds_logocarousel', 'id_logoCarousel = '.(int) $id_logocarousel);
        Db::getInstance()->delete('ds_logocarousel_text', 'id_logoCarousel = '.(int) $id_logocarousel);
    }

    protected function insertCarouselItems($file_name, $logo_link, $logo_active)
    {
        Db::getInstance()->insert('ds_logocarousel', array(
            'file_name' => pSQL($file_name),
            'logo_link' => pSQL($logo_link),
            'logo_active' => (int) $logo_active,
            'logo_order' => 0,
        ));

        return Db::getInstance()->Insert_ID();
    }

    protected function insertCarouselItemsLang($last_id, $text, $alt, $lang)
    {
        Db::getInstance()->insert('ds_logocarousel_text', array(
            'id_logoCarousel' => (int) $last_id,
            'logo_title' => pSQL($text),
            'logo_alt' => pSQL($alt),
            'id_lang' => (int) $lang,
        ));
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $controllerName = 'index';

        if (Validate::isControllerName($controllerName) != true) {
            return $this->displayError($this->trans('This is not a correct controller name', array(), 'Admin.Dslogoscarousel.Error'));
        }

        if (Tools::getValue('controller') == 'index') {
            $version = (int) Tools::substr(_PS_VERSION_, 2, 1);
            if ($version < 7) {
                $this->context->controller->addJS($this->_path.'/views/js/front.js');
                $this->context->controller->addCSS($this->_path.'/views/css/front.css');
            } else {
                $this->context->controller->registerJavascript('cdn', 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js', array('media' => 'all', 'priority' => 75, 'inline' => false, 'server' => 'remote'));
                $this->context->controller->addJS($this->_path.'views/js/carousel.js');
                $this->context->controller->addCSS($this->_path.'views/css/carousel.css');
                $this->context->controller->registerStylesheet('cdn', 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css', array('media' => 'all', 'priority' => 200, 'inline' => false, 'server' => 'remote'));
            }
        }
    }

    public function hookDisplayHome()
    {
        $items = $this->getCarouselItemsFront();
        $ifMobile = Configuration::get('DSLOGOSCAROUSEL_MOBILE');
        $isMobile = Context::getContext()->isMobile();
        $mobielItems = Configuration::get('DSLOGOSCAROUSEL_MOBILEITEM');
        $tabletItems = Configuration::get('DSLOGOSCAROUSEL_TABLETITEM');
        $desktopItem = Configuration::get('DSLOGOSCAROUSEL_DESKTOPITEM');
        $header = $this->getHeaderName();
        $headerColor = Configuration::get('DSLOGOSCAROUSEL_COLOR');
        $type = Configuration::get('DSLOGOSCAROUSEL_TYPE');
        $path = $this->_path;


        $this->context->smarty->assign(array('items' => $items, 'path' => $path, 'mobileItems' => $mobielItems, 'desktopItems' => $desktopItem, 'tabletItems' => $tabletItems));
        $this->context->smarty->assign(array('header' => $header, 'color' => $headerColor));

        if ($type == 0) {
            if ($isMobile == false) {
                $output = $this->display(__FILE__, 'views/templates/hook/grid.tpl');

                return $output;
            } elseif ($isMobile == true && $ifMobile == 1) {
                $output = $this->display(__FILE__, 'views/templates/hook/grid.tpl');

                return $output;
            } elseif ($isMobile == false && $ifMobile == 0) {
            }
        } else {
            if ($isMobile == false) {
                $output = $this->display(__FILE__, 'views/templates/hook/hookDisplayHome.tpl');

                return $output;
            } elseif ($isMobile == true && $ifMobile == 1) {
                $output = $this->display(__FILE__, 'views/templates/hook/hookDisplayHome.tpl');

                return $output;
            } elseif ($isMobile == false && $ifMobile == 0) {
            }
        }
    }
}
