<?php
// PSL/ClipperBundle/DependencyInjection/PSLClipperExtension.php

namespace PSL\ClipperBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class PSLClipperExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        // Google Spreadsheets Parameters
        $container->setParameter('psl_clipper.google_spreadsheets.client_id', $config['google_spreadsheets']['client_id']);
        $container->setParameter('psl_clipper.google_spreadsheets.service_account_name', $config['google_spreadsheets']['service_account_name']);
        $container->setParameter('psl_clipper.google_spreadsheets.p12_file_name', $config['google_spreadsheets']['p12_file_name']);
        $container->setParameter('psl_clipper.google_spreadsheets.spreadsheet_name', $config['google_spreadsheets']['spreadsheet_name']);
        $container->setParameter('psl_clipper.google_spreadsheets.worksheet_name', $config['google_spreadsheets']['worksheet_name']);
        // $container->setParameter('psl_clipper.google_spreadsheets.sheet_id', $config['google_spreadsheets']['sheet_id']);
    }
}
