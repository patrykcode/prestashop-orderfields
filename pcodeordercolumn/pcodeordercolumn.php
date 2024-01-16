<?php
/**
 * 2023
 * redicon.pl
 * @author Patryk Pawlicki <patryk@redicon.pl>
 * @copyright redicon.pl
 * @license redicon.pl
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Grid\Column\Type\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Filter\Filter;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class Pcodeordercolumn extends Module
{

    public function __construct()
    {
        $this->name = 'pcodeordercolumn';
        $this->author = 'Patryk Pawlicki';
        $this->version = '1.0.5';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7.1.0',
            'max' => _PS_VERSION_,
        ];

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = 'Kolumna w liście zamówień';
        $this->description = '';

    }

    public function hookActionOrderGridDefinitionModifier(array $params)
    {
        $definition = $params['definition'];

        $definition
            ->getColumns()
            ->remove('country_name')
            ->remove('new')
            ->addAfter(
                'customer',
                (new DataColumn('customer_email'))
                    ->setName($this->trans('Email', [], 'Admin.Global'))
                    ->setOptions([
                        'field' => 'customer_email',
                    ])
            )->addAfter(
            'customer_email',
            (new DataColumn('customer_phone'))
                ->setName($this->trans('Telefon', [], 'Admin.Global'))
                ->setOptions([
                    'field' => 'customer_phone',
                ])
        );

        $definition->getFilters()
            ->add(
                (new Filter('customer_email', TextType::class))
                    ->setTypeOptions([
                        'required' => false,
                    ])
                    ->setAssociatedColumn('customer_email')
            )
            ->add(
                (new Filter('customer_phone', TextType::class))
                    ->setTypeOptions([
                        'required' => false,
                    ])
                    ->setAssociatedColumn('customer_phone')
            );
    }
    public function hookActionOrderGridQueryBuilderModifier(array $params)
    {
        $searchQueryBuilder = $params['search_query_builder'];
        $searchQueryBuilder->addSelect('cu.`email` as customer_email');
        $searchQueryBuilder->addSelect('a.`phone` as customer_phone');
        $searchCriteria = $params['search_criteria'];

        foreach ($searchCriteria->getFilters() as $filterName => $filterValue) {
            if ($filterName == 'customer_email') {
                $searchQueryBuilder->andWhere('cu.`email` = :customer_email');
                $searchQueryBuilder->setParameter('customer_email', $filterValue);
            }
            if ($filterName == 'customer_phone') {
                $searchQueryBuilder->andWhere('a.`phone` = :customer_phone');
                $searchQueryBuilder->setParameter('customer_phone', $filterValue);
            }
        }
    }

    public function install()
    {

        return parent::install() &&
        $this->registerHook('actionOrderGridDefinitionModifier') &&
        $this->registerHook('actionOrderGridQueryBuilderModifier');
    }
    public function uninstall()
    {
        return parent::uninstall();
    }
}
