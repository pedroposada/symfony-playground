<?php
/**
 * PSL/ClipperBundle/Command/ClipperCacheCommand.php
 *
 * Clipper Cache Command Class
 * This is the class that interactions with local db cache service
 *
 * @version 1.0
 * @date 2015-07-27
 */

namespace PSL\ClipperBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ClipperCacheCommand extends ContainerAwareCommand {

  private $actions = array('status', 'flush', 'get');
  private $service;

  protected function configure() {
    $this->setName('clipper:cache')
         ->setDescription('Clipper Cache')
         ->addArgument('action', InputArgument::REQUIRED, 'What kind of action do you want? ' . implode(', ', $this->actions))
         ->addArgument('var-name', InputArgument::OPTIONAL, 'Extra for "get": Cache name.')
         ->addOption('flush-all', 'f', InputOption::VALUE_NONE, 'Extra for "flush": Flush all cache including active records.');
  }

  protected function setup_service() {

    $this->service = $this->getContainer()->get('clipper_cache');
  }

  protected function check_service_status($output) {
    if ($this->service->is_enabled()) {
      $timed = $this->service->get_cache_time();
      $output->writeln("<info>Info: Clipper Cache is active with default of {$timed} hour of lifetime.</info>");
      return TRUE;
    }
    $output->writeln("<error>Error: Clipper Cache is disabled.</error>");
    return FALSE;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->setup_service();
    $action = $input->getArgument('action');
    $status = $this->check_service_status($output);
    if (!$status) {
      return;
    }
    switch ($action) {
      case 'status':
        $all      = $this->service->get_cache_count(FALSE);
        $active   = $this->service->get_cache_count(TRUE);
        $inactive = ($all - $active);
        $all = number_format($all, 0, ',', '');
        $msg = array("Found {$all} records;");
        if (!empty($active)) {
          $active = number_format($active, 0, ',', '');
          $msg[]  = "{$active} active";
        }
        if (!empty($inactive)) {
          $msg[]  = "{$inactive} expired";

        }
        $msg = implode(', ', $msg);
        $output->writeln("<info>Info: {$msg}</info>");
        break;

      case 'flush':
        $flush_all = $input->getOption('flush-all');
        if ($flush_all) {
          $output->writeln("<info>Info: Flushing all cache records.</info>");
        }
        else {
          $output->writeln("<info>Info: Flushing expired cache records, use -f option to flush all.</info>");
        }
        $done = $this->service->flush($flush_all);
        if ($done !== FALSE) {
          $output->writeln("<info>Info: Flush complete. {$done} records was removed.</info>");
          break;
        }
        $output->writeln("<error>Error: Flush failed.</error>");
        break;

      case 'get':
        //$this->service->set('ryanharne', array('ryanharne', 'me@ryanharne.net', date('Y-m-d H:i:s')), new \DateTime('-30 days'));

        $name = $input->getArgument('var-name');
        if (empty($name)) {
          $output->writeln("<error>Error: Please define Cache name, see --var-name option.</error>");
          break;
        }
        $value = $this->service->get($name, TRUE, FALSE);
        if ($value === FALSE) {
          $output->writeln("<error>Error: Requested Cache name was not available.</error>");
          break;
        }
        $output->writeln("<info>Info: Cached name found:</info>");
        $value['has_expired'] = ($value['has_expired'] ? 'Yes' : 'No');
        $type = gettype($value['data']);
        $value['data'] = print_r($value['data'], TRUE);
        if (($type == 'string') && (strpos($value['data'], '{') === 0)) {
          $type = 'JSON';
          $value['data'] = json_decode($value['data']);
          $value['data'] = print_r($value['data'], TRUE);
        }
        $set = array(
          'Name'           => $value['name'],
          'Expired on'     => $value['expiries'],
          'Has Expired'    => $value['has_expired'],
          "Data ({$type})" => $value['data'],
        );
        $mlenght = 0;
        array_map(function($key) use (&$mlenght) {
          $mlenght = max($mlenght, strlen($key));
        }, array_keys($set));
        foreach ($set as $key => $value) {
          $msg = array(
            ' - ',
            str_pad($key, $mlenght, ' '),
            ' : ',
            $value
          );
          $output->writeln(implode('', $msg));
        }
        break;

      default:
        $output->writeln("<error>Error: Unknown action \"{$action}\", choose " . implode(', ', $this->actions) . ".</error>");
        break;
    };
    $output->writeln("<info>Exit: Console complete.</info>");
  }
}