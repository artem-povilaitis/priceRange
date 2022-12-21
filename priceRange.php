<?php 

if (!defined('_PS_VERSION_')) {
    exit;
}

class PriceRange extends Module implements \PrestaShop\PrestaShop\Core\Module\WidgetInterface
{

    private $templateFile;

    public function __construct()
    {
        $this->name = 'pricerange';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Artem Povilaitis';
        $this->need_instance = 1;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Price Range');
        $this->description = $this->l('Установка диапозона цен');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);

        $this->templateFile = 'module:priceRange/views/templates/hook/PriceRange.tpl';
    }

    public function install()
    {
        Configuration::updateValue('PRICE_RANGE_MIN', null);
        Configuration::updateValue('PRICE_RANGE_MAX', null);

        return parent::install()&&
            $this->registerHook('footer');
    }

    public function uninstall()
    {
        Configuration::deleteByName('PRICE_RANGE_MIN');
        Configuration::deleteByName('PRICE_RANGE_MAX');

        return parent::uninstall();
    }
    
    protected function getConfigFormValues()
    {
      return array(
        'PRICE_RANGE_MIN' => Configuration::get('PRICE_RANGE_MIN', null),
        'PRICE_RANGE_MAX' => Configuration::get('PRICE_RANGE_MAX', null),
      );
    }
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();
        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    public function renderWidget($hookName, array $configuration)
    {
        $this->smarty->assign($this->getWidgetVariables($hookName, $configuration));
        return $this->fetch($this->templateFile);   
    }

    public function getWidgetVariables($hookName, array $configuration)
    {
        $min = Configuration::get('PRICE_RANGE_MIN');
        $max = Configuration::get('PRICE_RANGE_MAX');
        $query = 'SELECT * FROM ps_product WHERE price between 12 and 13';
        $result = Db::getInstance()->getValue('SELECT COUNT(*) FROM `'._DB_PREFIX_.'product` where price between '.$min.' and '.$max);
        return array(
            'message' => $result

        );
    } 

    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitMyfirstmoduleModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch('module:priceRange/views/templates/hook/BackForm.tpl');

        return $output . $this->renderForm();
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
        $helper->submit_action = 'submitMyfirstmoduleModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
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
                        'type' => 'text',
                        'name' => 'PRICE_RANGE_MIN',
                        'label' => $this->l('От')
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'PRICE_RANGE_MAX',
                        'label' => $this->l('До'),
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
   

    /**
     * Save form data.
     */
   
}